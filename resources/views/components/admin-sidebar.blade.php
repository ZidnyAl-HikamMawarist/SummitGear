<div x-data="{ open: false }" 
     @toggle-sidebar.window="open = !open" 
     @close-sidebar.window="open = false" 
     @keydown.escape.window="open = false"
     class="contents">

    <!-- Mobile Backdrop -->
    <div x-show="open" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="open = false" 
         class="fixed inset-0 z-40 bg-slate-900/50 backdrop-blur-xs lg:hidden"
         style="display: none;">
    </div>

    <!-- Main Sidebar -->
    <aside class="sidebar" :class="{ 'open': open }">
        <div class="sidebar-header">
            <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 shadow-2xs" style="background: linear-gradient(135deg, #101F42 0%, #1E3A8A 100%);">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="color: var(--color-coral)">
                    <path d="m12 3-9 17h18Z"/>
                    <path d="m12 3 3 8-6 4"/>
                </svg>
            </div>
            <div class="flex items-center gap-1.5 flex-1 min-w-0">
                <h2 class="sidebar-brand text-sm font-black text-navy leading-tight">SummitGear</h2>
                <span class="text-[8px] font-extrabold px-1 py-0.5 rounded bg-slate-100 text-slate-500 font-mono">v2.0</span>
            </div>
            <button type="button" @click="open = false" class="lg:hidden p-1 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 cursor-pointer">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

    <nav class="sidebar-nav">
        <!-- SEKSI: UTAMA -->
        <span class="sidebar-section-title first">Utama</span>

        @if(auth()->user()->role === 'admin')
        <!-- 1. Dashboard Utama (Super Admin) -->
        <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.dashboard') || (request()->routeIs('dashboard') && auth()->user()->role === 'admin') ? 'active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect width="7" height="7" x="3" y="3" rx="1.5"/><rect width="7" height="7" x="14" y="3" rx="1.5"/><rect width="7" height="7" x="14" y="14" rx="1.5"/><rect width="7" height="7" x="3" y="14" rx="1.5"/>
            </svg>
            <span>Dashboard</span>
        </a>
        @endif

        @if(in_array(auth()->user()->role, ['admin', 'gudang']))
        <!-- 1b. Dashboard Pergudangan & Unit -->
        <a href="{{ route('gudang.dashboard') }}" class="sidebar-link {{ request()->routeIs('gudang.dashboard') || (request()->routeIs('dashboard') && auth()->user()->role === 'gudang') ? 'active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
            <span>Dashboard Gudang</span>
        </a>
        @endif

        @if(auth()->user()->role === 'kasir')
        <!-- 2. Kasir / Transaksi Baru (Khusus Role Kasir) -->
        <a href="{{ route('admin.transactions.create') }}" class="sidebar-link {{ request()->routeIs('admin.transactions.*') ? 'active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/>
            </svg>
            <span>Kasir & Sewa</span>
        </a>
        @endif

        <!-- SEKSI: OPERASIONAL & QC -->
        <span class="sidebar-section-title">Operasional & QC</span>

        <!-- 2b. Booking Masuk (Online with Real-time Red Badge) -->
        <livewire:components.incoming-booking-badge type="sidebar" />

        <!-- 3. Operasional Serah Terima (Handover) -->
        <a href="{{ route('admin.operations.handover') }}" class="sidebar-link {{ request()->routeIs('admin.operations.handover') || request()->routeIs('admin.operations.check*') || request()->routeIs('admin.settlements.*') ? 'active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M16 3h5v5"/><path d="M8 21H3v-5"/><path d="M21 3l-7 7"/><path d="M3 21l7-7"/>
            </svg>
            <span>Serah Terima (QC)</span>
        </a>

        <!-- 4. Kalender Booking -->
        <a href="{{ route('admin.operations.calendar') }}" class="sidebar-link {{ request()->routeIs('admin.operations.calendar') ? 'active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/>
            </svg>
            <span>Kalender Booking</span>
        </a>

        <!-- SEKSI: INVENTARIS & GUDANG -->
        <span class="sidebar-section-title">Inventaris & Gudang</span>

        @if(in_array(auth()->user()->role, ['admin', 'gudang']))
        <!-- 7. Maintenance Kanban -->
        <a href="{{ route('admin.maintenance.kanban') }}" class="sidebar-link {{ request()->routeIs('admin.maintenance.*') ? 'active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
            </svg>
            <span>Kanban Perawatan</span>
        </a>
        @endif

        <!-- 5. Inventaris Alat -->
        <a href="{{ route('admin.inventory.items') }}" class="sidebar-link {{ request()->routeIs('admin.inventory.*') ? 'active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/>
            </svg>
            <span>Daftar Alat & Unit</span>
        </a>

        @if(in_array(auth()->user()->role, ['admin', 'kasir']))
        <!-- 6. Pelanggan -->
        <a href="{{ route('admin.customers') }}" class="sidebar-link {{ request()->routeIs('admin.customers*') ? 'active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
            </svg>
            <span>Data Pelanggan</span>
        </a>
        @endif

        @if(auth()->user()->role === 'admin')
        <!-- SEKSI: LAPORAN & SISTEM -->
        <span class="sidebar-section-title">Laporan & Sistem</span>

        <!-- 8. Laporan & Analitik -->
        <a href="{{ route('admin.analytics.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.analytics.*') ? 'active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" x2="8" y1="13" y2="13"/><line x1="16" x2="8" y1="17" y2="17"/><polyline points="10 9 9 9 8 9"/>
            </svg>
            <span>Analitik & Laporan</span>
        </a>

        <!-- 9. Kelola Akun -->
        <a href="{{ route('admin.users') }}" class="sidebar-link {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
            <span>Kelola Akun</span>
        </a>

        <!-- 10. Audit Log -->
        <a href="{{ route('admin.audit_logs') }}" class="sidebar-link {{ request()->routeIs('admin.audit_logs*') ? 'active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
            </svg>
            <span>Log Audit</span>
        </a>

        <!-- 11. Pengaturan -->
        <a href="{{ route('admin.settings') }}" class="sidebar-link {{ request()->routeIs('admin.settings*') ? 'active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>
            </svg>
            <span>Pengaturan</span>
        </a>
        @endif
    </nav>

    <div class="mt-auto pt-2 pb-2 px-2 border-t border-slate-100">
        <flux:modal.trigger name="logout-modal">
            <button type="button" 
                    @click="$dispatch('open-logout-modal')"
                    class="sidebar-link logout-link m-0 w-full text-left cursor-pointer border-0 bg-transparent"
                    title="Keluar dari sesi akun">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/>
                </svg>
                <span>Keluar (Logout)</span>
            </button>
        </flux:modal.trigger>
    </div>
</aside>
</div>
