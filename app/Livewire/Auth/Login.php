<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use App\Models\User;
use App\Services\TwoFactorService;
use App\Services\AuditLogger;

class Login extends Component
{
    public $email = '';
    public $password = '';
    public $showPassword = false;

    // 2FA Challenge States
    public bool $requires2fa = false;
    public ?int $tempUserId = null;
    public string $twoFactorCode = '';
    public bool $useRecoveryCode = false;
    public string $recoveryCode = '';

    public function toggleShowPassword()
    {
        $this->showPassword = !$this->showPassword;
    }
    
    public function login()
    {
        $this->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        
        $throttleKey = strtolower($this->email).'|'.request()->ip();
        
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->addError('email', "Terlalu banyak percobaan login. Silakan coba lagi dalam {$seconds} detik.");
            return;
        }

        $user = User::where('email', $this->email)->first();

        if ($user && Hash::check($this->password, $user->password)) {
            // Cek apakah user adalah admin dan memiliki 2FA aktif
            if ($user->role === 'admin' && $user->hasTwoFactorEnabled()) {
                RateLimiter::clear($throttleKey);
                $this->tempUserId = $user->id;
                $this->requires2fa = true;
                $this->twoFactorCode = '';
                $this->useRecoveryCode = false;
                $this->recoveryCode = '';
                return;
            }

            // Login normal untuk user tanpa 2FA
            RateLimiter::clear($throttleKey);
            Auth::login($user);
            session()->regenerate();
            
            if ($user->role === 'kasir') {
                return redirect()->route('admin.transactions.create');
            } elseif ($user->role === 'gudang') {
                return redirect()->route('admin.maintenance.kanban');
            }
            
            return redirect()->route('dashboard');
        }
        
        RateLimiter::hit($throttleKey, 60);
        $this->addError('email', 'Email atau kata sandi salah.');
    }

    public function verify2fa(TwoFactorService $twoFactorService)
    {
        if (!$this->tempUserId) {
            $this->cancel2fa();
            return;
        }

        $user = User::findOrFail($this->tempUserId);

        if ($this->useRecoveryCode) {
            $this->validate([
                'recoveryCode' => 'required|string',
            ], [
                'recoveryCode.required' => 'Masukkan kode pemulihan cadangan Anda.',
            ]);

            $codes = $user->two_factor_recovery_codes ?? [];
            $inputCode = strtoupper(trim($this->recoveryCode));

            if (($key = array_search($inputCode, $codes)) !== false) {
                // Hapus kode recovery yang sudah terpakai
                unset($codes[$key]);
                $user->update(['two_factor_recovery_codes' => array_values($codes)]);

                Auth::login($user);
                AuditLogger::log('LOGIN', 'User', $user->id, "Admin login menggunakan Recovery Code darurat.");
                session()->regenerate();
                return redirect()->route('dashboard');
            }

            $this->addError('recoveryCode', 'Kode pemulihan tidak valid atau sudah pernah digunakan.');
            return;
        }

        $this->validate([
            'twoFactorCode' => 'required|digits:6',
        ], [
            'twoFactorCode.required' => 'Masukkan 6 digit kode dari Google Authenticator.',
            'twoFactorCode.digits' => 'Kode harus terdiri dari 6 angka.',
        ]);

        if ($twoFactorService->verifyKey($user->two_factor_secret, $this->twoFactorCode)) {
            Auth::login($user);
            AuditLogger::log('LOGIN', 'User', $user->id, "Admin login sukses dengan verifikasi Google Authenticator 2FA.");
            session()->regenerate();
            return redirect()->route('dashboard');
        }

        $this->addError('twoFactorCode', 'Kode verifikasi Google Authenticator tidak cocok atau telah kedaluwarsa.');
    }

    public function cancel2fa()
    {
        $this->requires2fa = false;
        $this->tempUserId = null;
        $this->twoFactorCode = '';
        $this->useRecoveryCode = false;
        $this->recoveryCode = '';
        $this->resetErrorBag();
    }

    public function toggleRecoveryCode()
    {
        $this->useRecoveryCode = !$this->useRecoveryCode;
        $this->twoFactorCode = '';
        $this->recoveryCode = '';
        $this->resetErrorBag();
    }

    public function render()
    {
        return view('livewire.auth.login')->layout('components.layouts.app');
    }
}
