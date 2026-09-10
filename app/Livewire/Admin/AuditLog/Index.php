<?php

namespace App\Livewire\Admin\AuditLog;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AuditLog;
use Carbon\Carbon;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $dateFrom = '';
    public $dateTo = '';
    public $actionFilter = 'ALL';
    public $activeTab = 'semua'; // 'semua', 'kasir', 'gudang', 'admin', 'system'

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingActiveTab()
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

    public function updatingActionFilter()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->actionFilter = 'ALL';
        $this->activeTab = 'semua';
        $this->resetPage();
    }

    protected function buildQuery()
    {
        return AuditLog::with(['user', 'approver'])
            ->when($this->activeTab !== 'semua' && $this->activeTab !== 'ALL', function ($query) {
                if ($this->activeTab === 'system') {
                    $query->whereNull('user_id');
                } else {
                    $query->whereHas('user', function ($q) {
                        $q->where('role', $this->activeTab);
                    });
                }
            })
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('action', 'ilike', '%' . $this->search . '%')
                      ->orWhere('entity', 'ilike', '%' . $this->search . '%')
                      ->orWhere('reason', 'ilike', '%' . $this->search . '%');
                });
            })
            ->when($this->actionFilter !== 'ALL', function ($query) {
                $query->where('action', $this->actionFilter);
            })
            ->when($this->dateFrom, function ($query) {
                $query->where('created_at', '>=', Carbon::parse($this->dateFrom)->startOfDay());
            })
            ->when($this->dateTo, function ($query) {
                $query->where('created_at', '<=', Carbon::parse($this->dateTo)->endOfDay());
            })
            ->orderBy('created_at', 'desc');
    }

    public function exportCsv()
    {
        $startDateStr = $this->dateFrom ? Carbon::parse($this->dateFrom)->format('Ymd') : 'awal';
        $endDateStr = $this->dateTo ? Carbon::parse($this->dateTo)->format('Ymd') : 'sekarang';
        $filename = "audit_log_summitgear_{$startDateStr}_sd_{$endDateStr}.csv";

        $logs = $this->buildQuery()->latest('created_at')->get();

        $callback = function () use ($logs) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF"); // UTF-8 BOM

            fputcsv($file, ['SUMMITGEAR POS - EKSPOR LOG AUDIT KEAMANAN']);
            fputcsv($file, ['Periode Filter', ($this->dateFrom ?: 'Semua') . ' s/d ' . ($this->dateTo ?: 'Sekarang')]);
            fputcsv($file, ['Tanggal Cetak', Carbon::now()->format('Y-m-d H:i:s') . ' WIB']);
            fputcsv($file, ['Jumlah Log', $logs->count() . ' Catatan']);
            fputcsv($file, []);

            fputcsv($file, [
                'ID Log',
                'Waktu (WIB)',
                'Aktor / Pengguna',
                'Aksi',
                'Entitas',
                'ID Entitas',
                'Alasan / Rincian',
                'Disetujui Oleh'
            ]);

            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    Carbon::parse($log->created_at)->format('Y-m-d H:i:s'),
                    $log->user ? $log->user->name : 'SISTEM OTOMATIS',
                    $log->action,
                    $log->entity,
                    $log->entity_id,
                    $log->reason ?? '-',
                    $log->approver ? $log->approver->name : '-'
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
        $logs = $this->buildQuery()
            ->latest('created_at')
            ->paginate(10);

        return view('livewire.admin.audit-log.index', [
            'logs' => $logs
        ])->layout('components.layouts.app');
    }
}
