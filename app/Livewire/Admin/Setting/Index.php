<?php

namespace App\Livewire\Admin\Setting;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use App\Services\AuditLogger;

class Index extends Component
{
    public $booking_window_days;
    public $hold_duration_hours;
    public $late_fee_per_hour;
    public $online_booking_expire_hours;

    // State 2FA Google Authenticator
    public bool $twoFactorEnabled = false;
    public bool $showingQrCode = false;
    public string $qrCodeSvg = '';
    public string $secretKey = '';
    public string $confirmationCode = '';
    public array $recoveryCodes = [];
    public bool $showRecoveryCodesModal = false;

    public function mount()
    {
        // Load settings dari DB
        $settings = DB::table('settings')->pluck('value', 'key');
        
        $this->booking_window_days = $settings['booking_window_days'] ?? 7;
        $this->hold_duration_hours = $settings['hold_duration_hours'] ?? 2;
        $this->late_fee_per_hour = $settings['late_fee_per_hour'] ?? 5000;
        $this->online_booking_expire_hours = $settings['online_booking_expire_hours'] ?? 24;

        $user = auth()->user();
        if ($user) {
            $this->twoFactorEnabled = $user->hasTwoFactorEnabled();
            $this->recoveryCodes = $user->two_factor_recovery_codes ?? [];
        }
    }

    public function initiateTwoFactor(\App\Services\TwoFactorService $service)
    {
        $user = auth()->user();
        if (!$user) return;

        $this->secretKey = $service->generateSecretKey();
        $this->qrCodeSvg = $service->getQrCodeSvg($user->email, $this->secretKey, 280);
        $this->confirmationCode = '';
        $this->showingQrCode = true;
    }

    public function cancelInitiateTwoFactor()
    {
        $this->showingQrCode = false;
        $this->secretKey = '';
        $this->qrCodeSvg = '';
        $this->confirmationCode = '';
    }

    public function confirmTwoFactor(\App\Services\TwoFactorService $service)
    {
        $this->validate([
            'confirmationCode' => 'required|digits:6',
        ], [
            'confirmationCode.required' => 'Masukkan 6 digit kode dari Google Authenticator.',
            'confirmationCode.digits' => 'Kode harus terdiri dari 6 angka.',
        ]);

        $user = auth()->user();
        if (!$user) return;

        if (!$service->verifyKey($this->secretKey, $this->confirmationCode)) {
            $this->addError('confirmationCode', 'Kode verifikasi tidak cocok. Pastikan jam di ponsel Anda akurat.');
            return;
        }

        $generatedRecovery = $service->generateRecoveryCodes();

        $user->update([
            'two_factor_secret' => $this->secretKey,
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => $generatedRecovery,
        ]);

        AuditLogger::log('SECURITY', 'User', $user->id, "Admin mengaktifkan Google Authenticator 2FA.");

        $this->twoFactorEnabled = true;
        $this->recoveryCodes = $generatedRecovery;
        $this->showingQrCode = false;
        $this->showRecoveryCodesModal = true;
        $this->secretKey = '';
        $this->confirmationCode = '';

        session()->flash('message_2fa', 'Google Authenticator 2FA berhasil diaktifkan untuk akun Admin Anda!');
    }

    public function disableTwoFactor()
    {
        $user = auth()->user();
        if (!$user) return;

        $user->update([
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
            'two_factor_recovery_codes' => null,
        ]);

        AuditLogger::log('SECURITY', 'User', $user->id, "Admin menonaktifkan Google Authenticator 2FA.");

        $this->twoFactorEnabled = false;
        $this->recoveryCodes = [];
        $this->showingQrCode = false;
        $this->showRecoveryCodesModal = false;

        session()->flash('message_2fa', 'Autentikasi dua faktor (2FA) telah dinonaktifkan.');
    }

    public function closeRecoveryModal()
    {
        $this->showRecoveryCodesModal = false;
    }

    protected function rules()
    {
        return [
            // GAP 1 FIXED: Validasi maksimal 20 hari
            'booking_window_days' => 'required|integer|min:1|max:20',
            'hold_duration_hours' => 'required|integer|min:1|max:48',
            'late_fee_per_hour' => 'required|numeric|min:0',
            'online_booking_expire_hours' => 'required|integer|min:1|max:168', // up to 1 week
        ];
    }

    public function save()
    {
        $this->validate();

        $settingsToUpdate = [
            'booking_window_days' => $this->booking_window_days,
            'hold_duration_hours' => $this->hold_duration_hours,
            'late_fee_per_hour' => $this->late_fee_per_hour,
            'online_booking_expire_hours' => $this->online_booking_expire_hours,
        ];

        DB::transaction(function () use ($settingsToUpdate) {
            foreach ($settingsToUpdate as $key => $value) {
                DB::table('settings')->updateOrInsert(
                    ['key' => $key],
                    ['value' => $value, 'updated_at' => now()]
                );
            }
            
            AuditLogger::log('UPDATE', 'Settings', 0, "Mengupdate pengaturan sistem inti.");
        });

        session()->flash('message', 'Pengaturan berhasil disimpan.');
    }

    public function render()
    {
        return view('livewire.admin.setting.index')->layout('components.layouts.app');
    }
}
