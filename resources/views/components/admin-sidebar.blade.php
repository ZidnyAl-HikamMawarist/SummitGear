<aside class="sidebar">
    <div class="sidebar-header">
        <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 shadow-2xs" style="background: linear-gradient(135deg, #101F42 0%, #1E3A8A 100%);">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="color: var(--color-coral)">
                <path d="m12 3-9 17h18Z"/>
                <path d="m12 3 3 8-6 4"/>
            </svg>
        </div>
        <div class="flex items-center gap-1.5">
            <h2 class="sidebar-brand text-sm font-black text-navy leading-tight">SummitGear</h2>
            <span class="text-[8px] font-extrabold px-1 py-0.5 rounded bg-slate-100 text-slate-500 font-mono">v2.0</span>
        </div>
    </div>

    <nav class="sidebar-nav">
        <!-- SEKSI: UTAMA -->
        <span class="sidebar-section-title first">Utama</span>

        <!-- 1. Dashboard -->
        <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/>
            </svg>
            <span>Dashboard</span>
        </a>

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

        @if(in_array(auth()->user()->role, ['admin', 'kasir']))
        <!-- 2b. Booking Masuk (Online) -->
        <a href="{{ route('admin.operations.incoming_booking') }}" class="sidebar-link {{ request()->routeIs('admin.operations.incoming_booking') ? 'active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
            </svg>
            <span>Booking Masuk</span>
        </a>
        @endif

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

        <!-- 5. Inventaris Alat -->
        <a href="{{ route('admin.inventory.items') }}" class="sidebar-link {{ request()->routeIs('admin.inventory.*') ? 'active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/>
            </svg>
            <span>Inventaris Alat</span>
        </a>

        @if(in_array(auth()->user()->role, ['admin', 'gudang']))
        <!-- 7. Maintenance Kanban -->
        <a href="{{ route('admin.maintenance.kanban') }}" class="sidebar-link {{ request()->routeIs('admin.maintenance.*') ? 'active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
            </svg>
            <span>Gudang (Kanban)</span>
        </a>
        @endif

        @if(in_array(auth()->user()->role, ['admin', 'kasir']))
        <!-- 6. Pelanggan -->
        <a href="{{ route('admin.customers') }}" class="sidebar-link {{ request()->routeIs('admin.customers*') ? 'active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
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
                <circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/>
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
        <button type="button" 
                @click="$dispatch('open-logout-modal')" 
                class="sidebar-link logout-link m-0 w-full text-left cursor-pointer border-0 bg-transparent"
                title="Keluar dari sesi akun">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/>
            </svg>
            <span>Keluar (Logout)</span>
        </button>
    </div>
</aside>
