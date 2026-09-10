<?php

namespace App\Livewire\Admin\UserManagement;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Services\AuditLogger;
use Illuminate\Validation\Rule;

class Index extends Component
{
    use WithPagination;

    public $isModalOpen = false;
    public $userId = null;
    
    // Form fields
    public $name = '';
    public $email = '';
    public $password = '';
    public $role = 'kasir';
    public $pin = '';
    public $phone = '';

    protected $listeners = ['pin-approved' => 'handlePinApproved'];

    // Variabel untuk menyimpan aksi yang di-pending menunggu PIN
    public $pendingAction = null;
    public $pendingUserId = null;

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($this->userId)],
            'password' => $this->userId ? 'nullable|min:8' : 'required|min:8',
            'role' => 'required|in:admin,kasir,gudang',
            'pin' => [
                $this->role === 'kasir' ? 'required' : 'nullable',
                'string',
                'digits:6',
                Rule::unique('users', 'pin')->ignore($this->userId)
            ],
            'phone' => 'nullable|string|max:20',
        ];
    }

    public function create()
    {
        $this->resetFields();
        $this->isModalOpen = true;
    }

    public function edit($id)
    {
        $this->resetFields();
        $user = User::findOrFail($id);
        
        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->role;
        $this->pin = $user->pin;
        $this->phone = $user->phone;
        
        $this->isModalOpen = true;
    }

    public function store()
    {
        $this->validate();

        if ($this->userId) {
            // Jika mau ubah role ke admin, butuh verifikasi PIN dari Super Admin (jika yang mengubah bukan admin, meski page ini cuma buat admin, ini buat proteksi ekstra)
            // Tapi karena halaman ini dilindungi role admin, kita biarkan saja lolos, TAPI untuk penghapusan baru kita minta PIN.
            $user = User::findOrFail($this->userId);
            
            $oldRole = $user->role;

            $data = [
                'name' => $this->name,
                'email' => $this->email,
                'role' => $this->role,
                'pin' => $this->pin,
                'phone' => $this->phone,
            ];

            if (!empty($this->password)) {
                $data['password'] = Hash::make($this->password);
            }

            $user->update($data);

            AuditLogger::log('UPDATE', 'User', $user->id, "Mengubah data user (Role awal: {$oldRole}, jadi: {$this->role})");
            
            session()->flash('message', 'User berhasil diperbarui.');
        } else {
            $user = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => Hash::make($this->password),
                'role' => $this->role,
                'pin' => $this->pin,
                'phone' => $this->phone,
            ]);

            AuditLogger::log('CREATE', 'User', $user->id, "Membuat user baru dengan role {$this->role}");

            session()->flash('message', 'User berhasil ditambahkan.');
        }

        $this->closeModal();
    }

    public $showDeleteModal = false;
    public $userToDeleteId = null;
    public $userToDeleteName = '';
    public $userToDeleteEmail = '';
    public $userToDeleteRole = '';
    public $userToDeletePhone = '';

    public function promptDelete($id)
    {
        $user = User::findOrFail($id);
        if ($user->id === auth()->id()) {
            session()->flash('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
            return;
        }

        $this->userToDeleteId = $user->id;
        $this->userToDeleteName = $user->name;
        $this->userToDeleteEmail = $user->email;
        $this->userToDeleteRole = strtoupper($user->role);
        $this->userToDeletePhone = $user->phone ?? '-';
        $this->showDeleteModal = true;
    }

    public function cancelDeleteUser()
    {
        $this->showDeleteModal = false;
        $this->userToDeleteId = null;
        $this->userToDeleteName = '';
        $this->userToDeleteEmail = '';
        $this->userToDeleteRole = '';
        $this->userToDeletePhone = '';
    }

    public function proceedDeleteUser()
    {
        if (!$this->userToDeleteId) return;
        $id = $this->userToDeleteId;
        $this->cancelDeleteUser();
        $this->confirmDelete($id);
    }

    public function confirmDelete($id)
    {
        // Minta PIN admin untuk delete user
        $this->pendingAction = 'delete_user';
        $this->pendingUserId = $id;
        
        $this->dispatch('requestPinApproval', action: 'delete_user', data: ['user_id' => $id]);
    }

    public function handlePinApproved($action, $data, $approvedBy)
    {
        if ($action === 'delete_user' && $this->pendingUserId == $data['user_id']) {
            $user = User::findOrFail($this->pendingUserId);
            
            // Jangan biarkan hapus diri sendiri
            if ($user->id === auth()->id()) {
                session()->flash('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
                return;
            }

            $user->delete();
            
            AuditLogger::log('DELETE', 'User', $this->pendingUserId, "Menghapus user {$user->email}", $approvedBy);
            
            session()->flash('message', 'User berhasil dihapus.');
            $this->pendingAction = null;
            $this->pendingUserId = null;
        }
    }

    public function resetFields()
    {
        $this->userId = null;
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->role = 'kasir';
        $this->pin = '';
        $this->phone = '';
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->resetFields();
    }

    public function render()
    {
        return view('livewire.admin.user-management.index', [
            'users' => User::latest()->paginate(10)
        ])->layout('components.layouts.app');
    }
}
