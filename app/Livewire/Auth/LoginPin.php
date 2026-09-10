<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class LoginPin extends Component
{
    public $pin = '';

    public function mount()
    {
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }
    }

    public function addDigit($digit)
    {
        if (strlen($this->pin) < 6) {
            $this->pin .= $digit;
        }
    }

    public function removeDigit()
    {
        if (strlen($this->pin) > 0) {
            $this->pin = substr($this->pin, 0, -1);
        }
    }

    public function login($pinValue = null)
    {
        if ($pinValue !== null && $pinValue !== '') {
            $this->pin = $pinValue;
        }

        if (empty($this->pin)) {
            $this->addError('pin', 'Masukkan 6 digit PIN kasir.');
            $this->dispatch('pin-error');
            return;
        }

        if (strlen($this->pin) !== 6 || !ctype_digit($this->pin)) {
            $this->addError('pin', 'PIN harus terdiri dari 6 digit angka.');
            $this->dispatch('pin-error');
            return;
        }

        $throttleKey = 'loginpin|' . request()->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->addError('pin', "Terlalu banyak percobaan salah. Silakan coba lagi dalam " . ceil($seconds / 60) . " menit.");
            $this->dispatch('pin-error');
            return;
        }

        // Cari user dengan role kasir (dukung hash dan plaintext)
        $cashiers = User::where('role', 'kasir')->get();
        $matchedUser = null;

        foreach ($cashiers as $c) {
            if ($this->pin === $c->pin || Hash::check($this->pin, (string)$c->pin)) {
                $matchedUser = $c;
                break;
            }
        }

        if ($matchedUser) {
            RateLimiter::clear($throttleKey);
            Auth::login($matchedUser);
            session()->regenerate();

            \App\Services\AuditLogger::log('LOGIN', 'User', $matchedUser->id, "Kasir memulai shift (Login PIN)");

            return redirect()->intended(route('admin.transactions.create'));
        }

        RateLimiter::hit($throttleKey, 300); // 5 menit cooldown
        $this->addError('pin', 'PIN kasir tidak valid atau tidak ditemukan.');
        $this->pin = '';
        $this->dispatch('pin-error');
    }

    public function render()
    {
        return view('livewire.auth.login-pin')
            ->layout('components.layouts.guest'); // assuming this layout exists
    }
}
