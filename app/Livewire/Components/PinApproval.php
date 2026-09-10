<?php

namespace App\Livewire\Components;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class PinApproval extends Component
{
    public $isOpen = false;
    public $pin = '';
    public $errorMessage = '';
    
    // Konteks aksi yang meminta persetujuan
    public $action = '';
    public $data = [];

    protected $listeners = [
        'requestPinApproval' => 'openModal',
        'openPinApproval' => 'openModal',
    ];

    public function openModal($action, $data = [], $payload = [])
    {
        $this->isOpen = true;
        $this->action = $action;
        $this->data = !empty($data) ? $data : $payload;
        $this->pin = '';
        $this->errorMessage = '';
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->pin = '';
        $this->errorMessage = '';
    }

    public function verify()
    {
        $throttleKey = 'pinapproval|' . request()->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->errorMessage = "Terlalu banyak percobaan salah. Silakan coba lagi dalam " . ceil($seconds / 60) . " menit.";
            return;
        }

        // Cari semua Admin
        $admins = User::where('role', 'admin')->get();
        $isApproved = false;
        $approvedBy = null;

        foreach ($admins as $admin) {
            // Periksa PIN baik plaintext maupun hash
            if ($this->pin === $admin->pin || Hash::check($this->pin, (string)$admin->pin) || Hash::check($this->pin, $admin->password)) {
                $isApproved = true;
                $approvedBy = $admin->id;
                break;
            }
        }

        if ($isApproved) {
            RateLimiter::clear($throttleKey);
            $this->isOpen = false;
            
            // Format untuk UserManagement
            $this->dispatch('pin-approved', action: $this->action, data: $this->data, approvedBy: $approvedBy);

            // Format untuk Settlement & Invoice
            $this->dispatch('pinApproved', [
                'action' => $this->action,
                'payload' => $this->data,
                'approver_id' => $approvedBy,
            ]);
        } else {
            RateLimiter::hit($throttleKey, 300); // Blokir 5 menit
            $this->errorMessage = 'PIN tidak valid atau Anda bukan Admin.';
            $this->pin = '';
        }
    }

    public function render()
    {
        return view('livewire.components.pin-approval');
    }
}
