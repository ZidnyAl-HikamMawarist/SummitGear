<?php

namespace App\Livewire\Admin\Customer;

use Livewire\Component;
use App\Models\Customer;
use App\Services\AuditLogger;
use Illuminate\Validation\Rule;

class Form extends Component
{
    public $customerId = null;
    
    public $name = '';
    public $nik = '';
    public $phone = '';
    public $address = '';
    public $has_consent = false;

    public function mount($id = null)
    {
        if ($id) {
            $customer = Customer::findOrFail($id);
            $this->customerId = $customer->id;
            $this->name = $customer->name;
            $this->nik = $customer->nik;
            $this->phone = $customer->phone;
            $this->address = $customer->address ?? '';
            $this->has_consent = !is_null($customer->consent_at);
        }
    }

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'nik' => [
                'required',
                'string',
                'size:16',
                Rule::unique('customers', 'nik')->ignore($this->customerId),
            ],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'has_consent' => 'accepted', // Harus dicentang
        ];
    }

    protected $messages = [
        'has_consent.accepted' => 'Pelanggan harus menyetujui Syarat & Ketentuan.',
        'nik.size' => 'NIK harus tepat 16 digit.',
    ];

    public function save()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'nik' => $this->nik,
            'phone' => $this->phone,
            'address' => $this->address,
            'consent_at' => $this->has_consent ? now() : null,
        ];

        if ($this->customerId) {
            $customer = Customer::findOrFail($this->customerId);
            $customer->update($data);
            AuditLogger::log('UPDATE', 'Customer', $customer->id, "Mengupdate data pelanggan: {$this->name}");
        } else {
            $customer = Customer::create($data);
            AuditLogger::log('CREATE', 'Customer', $customer->id, "Mendaftar pelanggan baru: {$this->name}");
        }

        session()->flash('message', 'Data pelanggan berhasil disimpan.');
        return redirect()->route('admin.customers');
    }

    public function render()
    {
        // Jika mode edit, ambil transaksi pelanggan
        $rentals = collect();
        if ($this->customerId) {
            $rentals = \App\Models\Rental::where('customer_id', $this->customerId)
                ->latest()
                ->take(5)
                ->get();
        }

        return view('livewire.admin.customer.form', [
            'rentals' => $rentals
        ])->layout('components.layouts.app');
    }
}
