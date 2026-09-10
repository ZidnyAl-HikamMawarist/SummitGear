<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Rental;
use App\Models\ItemUnit;
use App\Models\Payment;
use App\Models\InventoryItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Dashboard extends Component
{
    public function logout()
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
        
        return redirect()->route('login');
    }

    public function render()
    {
        $today = Carbon::today();

        // 1. KPI Cards data
        $todayCheckouts = Rental::whereDate('start_date', $today)
            ->whereIn('status', ['BOOKED', 'PENDING_PAYMENT'])
            ->count();

        $overdueCheckins = Rental::where(function ($q) {
                $q->where('status', 'OVERDUE')
                  ->orWhere(function ($sub) {
                      $sub->where('status', 'RENTED_OUT')
                          ->where('scheduled_return_time', '<', Carbon::now());
                  });
            })
            ->count();

        $maintenanceCount = ItemUnit::whereIn('status', ['Maintenance', 'Cleaning'])->count();

        $monthlyRevenue = Payment::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('amount');

        // 2. Category Distribution
        $categoryDistribution = InventoryItem::select('category', DB::raw('count(item_units.id) as unit_count'))
            ->leftJoin('item_units', 'inventory_items.id', '=', 'item_units.item_id')
            ->groupBy('category')
            ->get();

        $totalUnits = ItemUnit::count();

        // 3. Recent Transactions
        $recentRentals = Rental::with(['customer', 'details.itemUnit.item'])
            ->latest()
            ->take(5)
            ->get();

        return view('livewire.admin.dashboard', [
            'todayCheckouts' => $todayCheckouts,
            'overdueCheckins' => $overdueCheckins,
            'maintenanceCount' => $maintenanceCount,
            'monthlyRevenue' => $monthlyRevenue,
            'categoryDistribution' => $categoryDistribution,
            'totalUnits' => $totalUnits,
            'recentRentals' => $recentRentals,
        ])->layout('components.layouts.app');
    }
}
