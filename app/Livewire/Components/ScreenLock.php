<?php

namespace App\Livewire\Components;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class ScreenLock extends Component
{
    public $isLocked = false;
    public $pin = '';
    public $errorMessage = '';

    protected $listeners = ['lockScreen' => 'lock'];

    public function mount()
    {
        if (!Auth::check() || Auth::user()->role !== 'kasir') {
            $this->isLocked = false;
        }
    }

    public function lock()
    {
        // Hanya pengguna dengan role kasir yang dapat mengunci layar
        if (Auth::check() && Auth::user()->role === 'kasir') {
            $this->isLocked = true;
            $this->pin = '';
            $this->errorMessage = '';
        }
    }

    public function unlock()
    {
        $throttleKey = 'screenlock|' . Auth::id() . '|' . request()->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->errorMessage = "Terlalu banyak percobaan. Silakan coba lagi dalam " . ceil($seconds / 60) . " menit.";
            return;
        }

        $user = Auth::user();

        // Check if input matches PIN (support both hashed and plain string)
        if ($this->pin === $user->pin || Hash::check($this->pin, (string)$user->pin) || Hash::check($this->pin, (string)$user->password)) {
            RateLimiter::clear($throttleKey);
            $this->isLocked = false;
            $this->pin = '';
            $this->errorMessage = '';
            $this->dispatch('screen-unlocked');
        } else {
            RateLimiter::hit($throttleKey, 300); // 5 minutes block
            $this->errorMessage = 'PIN tidak valid.';
            $this->pin = '';
        }
    }

    public function render()
    {
        return view('livewire.components.screen-lock');
    }
}
