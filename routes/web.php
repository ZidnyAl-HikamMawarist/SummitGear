<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Auth\Login;
use App\Livewire\Admin\Dashboard;

Route::get('/', \App\Livewire\Public\LandingPage::class)->name('home');
Route::get('/booking', \App\Livewire\Public\Booking::class)->name('booking');

Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
    Route::get('/login/pin', \App\Livewire\Auth\LoginPin::class)->name('login.pin');
});

Route::middleware(['auth', 'kasir.timeout'])->group(function () {
    Route::get('/dashboard', Dashboard::class)->middleware('role:admin')->name('dashboard');
    Route::post('/logout', function () {
        $user = auth()->user();
        if ($user) {
            \App\Services\AuditLogger::log('LOGOUT', 'User', $user->id, "Mengakhiri shift (Logout)");
        }
        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('login');
    })->name('logout');
    
    // 1. Rute Khusus Super Admin (Owner)
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/admin/users', \App\Livewire\Admin\UserManagement\Index::class)->name('admin.users');
        Route::get('/admin/audit-logs', \App\Livewire\Admin\AuditLog\Index::class)->name('admin.audit_logs');
        Route::get('/admin/settings', \App\Livewire\Admin\Setting\Index::class)->name('admin.settings');
        Route::get('/admin/analytics/dashboard', \App\Livewire\Admin\Analytics\Dashboard::class)->name('admin.analytics.dashboard');
        
        // Manajemen Master Barang (Tambah & Edit harga master hanya Admin)
        Route::get('/admin/inventory/items/create', \App\Livewire\Admin\Inventory\ItemForm::class)->name('admin.inventory.items.create');
        Route::get('/admin/inventory/items/{id}/edit', \App\Livewire\Admin\Inventory\ItemForm::class)->name('admin.inventory.items.edit');
    });

    // 2. Rute Khusus Kasir (Kasir & Transaksi Sewa Langsung)
    Route::middleware(['role:kasir'])->group(function () {
        Route::get('/admin/transactions/create', \App\Livewire\Admin\Transaction\Create::class)->name('admin.transactions.create');
    });

    // 2b. Rute Bersama Kasir & Admin (Invoice & Data Pelanggan)
    Route::middleware(['role:admin,kasir'])->group(function () {
        Route::get('/admin/transactions/{id}/invoice', \App\Livewire\Admin\Transaction\Invoice::class)->name('admin.transactions.invoice');
        Route::get('/admin/transactions/{id}/print', [\App\Http\Controllers\InvoiceController::class, 'print'])->name('admin.transactions.print');

        // Pelanggan
        Route::get('/admin/customers', \App\Livewire\Admin\Customer\Index::class)->name('admin.customers');
        Route::get('/admin/customers/create', \App\Livewire\Admin\Customer\Form::class)->name('admin.customers.create');
        Route::get('/admin/customers/{id}/edit', \App\Livewire\Admin\Customer\Form::class)->name('admin.customers.edit');
    });

    // 3. Rute Gudang & Admin (Maintenance Kanban)
    Route::middleware(['role:admin,gudang'])->group(function () {
        Route::get('/admin/maintenance/kanban', \App\Livewire\Admin\Maintenance\Kanban::class)->name('admin.maintenance.kanban');
    });

    // 4. Rute Bersama (Admin, Kasir, & Gudang)
    // Serah-Terima QC, Cek Kalender Ketersediaan, dan Lihat Daftar Katalog/Unit
    Route::middleware(['role:admin,kasir,gudang'])->group(function () {
        Route::get('/admin/operations/handover', \App\Livewire\Admin\Operations\HandoverIndex::class)->name('admin.operations.handover');
        Route::get('/admin/operations/{rentalId}/checkout', \App\Livewire\Admin\Operations\CheckOut::class)->name('admin.operations.checkout');
        Route::get('/admin/operations/{rentalId}/checkin', \App\Livewire\Admin\Operations\CheckIn::class)->name('admin.operations.checkin');
        Route::get('/admin/operations/{rentalId}/settlement', \App\Livewire\Admin\Operations\Settlement::class)->name('admin.settlements.show');
        Route::get('/admin/operations/incoming-booking', \App\Livewire\Admin\Operations\IncomingBooking::class)->name('admin.operations.incoming_booking');
        Route::get('/admin/operations/calendar', \App\Livewire\Admin\Operations\CalendarIndex::class)->name('admin.operations.calendar');
        Route::get('/admin/inventory/items', \App\Livewire\Admin\Inventory\ItemIndex::class)->name('admin.inventory.items');
        Route::get('/admin/inventory/items/{itemId}/units', \App\Livewire\Admin\Inventory\UnitIndex::class)->name('admin.inventory.units');
    });
});
