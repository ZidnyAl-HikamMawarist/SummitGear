<?php

namespace App\Livewire\Admin\Customer;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Customer;
use Carbon\Carbon;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $dateFrom = '';
    public $dateTo = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingDateFrom()
    {
        $this->resetPage();
    }

    public function updatingDateTo()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->resetPage();
    }

    protected function buildQuery()
    {
        return Customer::withCount('rentals')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'ilike', '%' . $this->search . '%')
                      ->orWhere('nik', 'ilike', '%' . $this->search . '%')
                      ->orWhere('phone', 'ilike', '%' . $this->search . '%');
                });
            })
            ->when($this->dateFrom, function ($query) {
                $query->where('created_at', '>=', Carbon::parse($this->dateFrom)->startOfDay());
            })
            ->when($this->dateTo, function ($query) {
                $query->where('created_at', '<=', Carbon::parse($this->dateTo)->endOfDay());
            });
    }

    public function exportCsv()
    {
        $startDateStr = $this->dateFrom ? Carbon::parse($this->dateFrom)->format('Ymd') : 'awal';
        $endDateStr = $this->dateTo ? Carbon::parse($this->dateTo)->format('Ymd') : 'sekarang';
        $filename = "data_pelanggan_summitgear_{$startDateStr}_sd_{$endDateStr}.csv";

        $headers = [
            'Content-type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename={$filename}",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $customers = $this->buildQuery()->latest()->get();

        $callback = function () use ($customers) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF"); // UTF-8 BOM

            fputcsv($file, ['SUMMITGEAR POS - EKSPOR DATA PELANGGAN']);
            fputcsv($file, ['Periode Pendaftaran', ($this->dateFrom ?: 'Semua') . ' s/d ' . ($this->dateTo ?: 'Sekarang')]);
            fputcsv($file, ['Tanggal Cetak', Carbon::now()->format('Y-m-d H:i:s') . ' WIB']);
            fputcsv($file, ['Total Pelanggan', $customers->count() . ' Orang']);
            fputcsv($file, []);

            fputcsv($file, [
                'ID',
                'Nama Lengkap',
                'NIK KTP',
                'Nomor WhatsApp',
                'Tanggal Registrasi',
                'Jumlah Transaksi Rental'
            ]);

            foreach ($customers as $c) {
                fputcsv($file, [
                    $c->id,
                    $c->name,
                    $c->nik,
                    $c->phone,
                    Carbon::parse($c->created_at)->format('Y-m-d H:i'),
                    $c->rentals_count ?? 0,
                ]);
            }

            fclose($file);
        };

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function render()
    {
        $customers = $this->buildQuery()
            ->latest()
            ->paginate(10);

        return view('livewire.admin.customer.index', [
            'customers' => $customers
        ])->layout('components.layouts.app');
    }
}
