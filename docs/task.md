# Task Breakdown — SummitGear POS (Fused Version)

Berdasarkan: PRD v3.0 (Final)
Stack: Laravel + Livewire + Alpine.js + PostgreSQL

---

## 0. Setup & Infrastruktur (Pra-Development)

- [x] Setup repository Git (branching strategy: `main`, `develop`, `feature/*`)
- [x] Install Laravel project baru + konfigurasi `.env` (local, staging, production)
- [x] Setup PostgreSQL lokal & staging (pastikan versi mendukung exclusion constraint — PostgreSQL 12+)
- [x] Install & konfigurasi Livewire + Alpine.js
- [x] Setup Tailwind CSS + konfigurasi warna kustom sesuai palet "Volcanic Twilight"
- [ ] Setup Laravel Queue (driver: database queue)
- [ ] Setup Laravel Scheduler (cron) untuk job retensi data
- [ ] Setup object storage S3-compatible (Cloudflare R2) untuk foto KTP + konfigurasi `.env`
- [ ] Setup akun & sandbox WA Gateway (Fonnte/Wablas) untuk development
- [ ] Setup environment staging untuk testing sebelum production
- [ ] Buat dokumentasi konvensi kode (naming, folder structure Livewire components)

---

## 1. Fase 1 — MVP Transaksi & Inventaris

### 1.1 Database & Model (Skema Inti)
- [x] Migration: `users` (id, name, role, pin, phone)
- [x] Migration: `customers` (id, name, nik, phone, id_photo_url, consent_at)
- [x] Migration: `settings` (key, value) — seed default `booking_window_days = 7`
- [x] Migration: `inventory_items` (id, sku, name, category, is_package, rental_type)
- [x] Migration: `item_units` (id, item_id FK, serial_number, status, condition_notes, replacement_value)
- [x] Migration: `package_items` (package_id FK, component_item_id FK, quantity)
- [x] Migration: `pricing_rules` (id, item_id FK, day_type, price_multiplier)
- [x] Migration: `rentals` (id, rental_code, customer_id FK, start_date, end_date, scheduled_return_time, status)
- [x] Migration: `rental_details` (id, rental_id FK, item_unit_id FK, price_per_day, return_status)
- [x] Migration: `payments` (id, rental_id FK, type, method, amount, paid_at)
- [x] Migration: `deposits` (id, rental_id FK, type, amount, doc_type, status, retention_deadline)
- [x] Migration: `inspections` (id, rental_detail_id FK, stage, condition_category, notes) — **tanpa kolom foto**
- [x] Migration: `penalties` (id, rental_id FK, reason, amount, is_settled, is_override, override_reason)
- [x] Migration: `maintenance_logs` (id, item_unit_id FK, type, start_time, end_time, technician_name)
- [x] Migration: `document_access_log` (id, customer_id FK, accessed_by FK, accessed_at)
- [x] Migration: `audit_logs` (id, user_id FK, action, entity, entity_id, reason, approved_by, timestamp)
- [x] Buat Eloquent Model (Fillable & SoftDeletes) + Setup dasar untuk tabel di atas
- [x] Buat Enum/Constant untuk status (`item_units.status`, `rentals.status`, `rental_details.return_status`, `inspections.condition_category`)
- [x] Seeder data dummy (barang, unit, user) untuk development & testing

### 1.2 Autentikasi, RBAC, & Kelola Akun
- [x] Login system (email/username + password)
- [x] Middleware role-based (Owner/Admin, Kasir, Staf Gudang) untuk membatasi akses UI/Route
- [x] CRUD Data `users` (Menambah Kasir, QC, Admin) dengan manajemen PIN Approval
- [x] Implementasi PIN Approval Admin untuk aksi sensitif (void transaksi, koreksi harga)
- [x] **Security**: Implementasi Rate Limiter (`throttle`) pada form Login & form Input PIN (Mencegah Brute Force).
- [x] **Security**: Gunakan pesan error generik ("Email atau password salah") di halaman login (Mencegah User Enumeration).
- [x] **Security**: Implementasi `session()->regenerate()` setelah login sukses (Mencegah Session Fixation).
- [x] **Security**: Konfigurasi *Session Timeout* otomatis (idle timeout) khusus untuk role Kasir.
- [x] **Security**: Pastikan form Input PIN Admin di UI menggunakan masked input (`type="password"`).
- [x] Service/helper `AuditLogger` yang dipanggil otomatis (mencatat `user_id`, `action`, `reason`, `approved_by`)
- [x] Halaman Log Audit (`audit_logs`) untuk memantau perubahan penting (Admin Only)
- [x] Fitur Mode Kunci Layar (Screen Lock) Kasir dengan overlay blur & PIN unlock (tanpa logout otomatis)

### 1.3 Modul Manajemen Inventaris (Katalog & Unit)
- [x] Halaman Master Barang / CRUD `inventory_items` (kategori, tipe sewa)
- [x] Halaman Tracking Unit / CRUD `item_units` — generate serial number otomatis, cetak QR/Barcode
- [x] CRUD `package_items` — konfigurasi komponen paket/bundling
- [x] Fitur update `replacement_value` per unit (untuk acuan ganti rugi)
- [x] Pengaturan Skema Harga (`pricing_rules`) dengan multiplier dinamis

### 1.4 Modul Pelanggan (Data Customer)
- [x] Halaman Daftar Pelanggan & Form Tambah Pelanggan
- [x] Integrasi upload & enkripsi foto identitas (KTP/SIM)
- [x] View Detail Riwayat Transaksi Pelanggan
- [x] Checkbox consent pelanggan (mengisi field `consent_at`)

### 1.5 Modul Pengaturan (Settings)
- [x] Halaman Manajemen `settings` (Admin mengatur `booking_window_days`, `hold_duration_hours`, dll)
- [x] Validasi maksimal booking window (20 hari)

### 1.6 Modul Transaksi Kasir (Reservasi & Walk-in)
- [x] Halaman "Buat Transaksi Baru": Pilih pelanggan & pilih rentang tanggal
- [x] Validasi ketersediaan unit fisik dengan cek overlap di rentang tanggal
- [x] UI "Keranjang Sewa": Tambah/hapus multiple unit atau paket ke satu transaksi sebelum checkout
- [x] Kalkulasi harga otomatis dari `pricing_rules`
- [x] Form input jaminan deposit (finansial)
- [x] Generate `rental_code` unik per transaksi
- [x] Pemrosesan Pembayaran (Tunai, Transfer, QRIS) → masuk ke tabel `payments`
- [x] Cetak / Download Invoice PDF (Struk awal)
- [x] Fitur Void Transaksi dengan otorisasi PIN Admin

### 1.7 Modul Serah-Terima & Checklist QC
- [x] Halaman Daftar Transaksi Aktif / Berjalan Hari Ini
- [x] Form Check-Out: Checklist Kondisi Awal (Baik/Cukup/Perhatian) tanpa foto + Tanda Tangan Digital pelanggan
- [x] Form Check-In: Checklist Kondisi Akhir & Pengecekan otomatis perbedaan checklist
- [x] Logic penetapan `return_status` per item detail (mendukung pengembalian sebagian paket)
- [x] Update otomatis status unit: ke `Rented` saat check-out, ke `Returned/Cleaning` saat check-in

### 1.8 Modul Denda & Penyelesaian Transaksi
- [x] Logic kalkulasi denda kerusakan dari hasil checklist (referensi `condition_category`)
- [x] Logic ganti rugi barang hilang/rusak berat (mengacu ke `replacement_value`)
- [x] Fitur override/diskon denda manual oleh kasir (Wajib PIN Admin & isi alasan)
- [x] Logic pemotongan deposit otomatis
- [x] Generate tagihan/invoice tambahan jika denda melebihi deposit yang ditahan

### 1.9 Testing Fase 1
- [x] Unit test kalkulasi harga & denda
- [x] Feature test alur transaksi lengkap (booking → check-out → check-in → selesai)
- [x] Test RBAC (pastikan Staf Gudang tidak bisa akses data transaksi)
- [x] Test UI responsif di tablet (orientasi landscape) — form check-out/in, kalender booking
- [x] Test audit log tercatat benar di setiap aksi sensitif
- [x] Playwright E2E testing serah terima & operasional kasir otomatis lolos

---

## 2. Fase 2 — Booking Kalender & WhatsApp

### 2.1 Kalender Ketersediaan & Validasi Tingkat Lanjut
- [x] Migration: Enable extension `btree_gist` di PostgreSQL (prasyarat exclusion constraint)
- [x] Migration: **Exclusion constraint** PostgreSQL pada `rental_details` (kombinasi `item_unit_id` + rentang tanggal)
- [x] Livewire component: Kalender visual ketersediaan (Interactive Grid)
- [x] Validasi booking window: Tolak tanggal pengambilan > batas `settings.booking_window_days`
- [x] Fitur DP (Down Payment) untuk Hold Booking
- [x] Job/scheduler auto-release hold booking jika `hold_duration_hours` terlewati tanpa pelunasan

### 2.2 Denda Keterlambatan Otomatis
- [x] Scheduled job (Laravel Scheduler) berjalan periodik tiap jam
- [x] Deteksi unit berstatus `Rented` di mana `NOW() > scheduled_return_time`
- [x] Auto-generate baris `penalties` dengan `reason = 'late_return'`
- [x] Notifikasi/Indikator merah di dashboard kasir untuk transaksi overdue

### 2.3 Notifikasi WhatsApp (WA Gateway)
- [x] Integrasi Fonnte / Wablas API via Laravel Queue (Mocking & Live Service ready)
- [x] Kirim Notifikasi: "Konfirmasi Booking", "Reminder Pengambilan", "Reminder H-1 Pengembalian", "Overdue / Denda"
- [x] Fallback: UI Alert di dashboard kasir jika API WA gagal terkirim agar difollow-up manual
- [x] Logging riwayat pengiriman pesan WA

### 2.4 Kepatuhan Data Pribadi & Keamanan
- [x] Job scheduler harian: Cek `retention_deadline`, lalu hapus/blur file foto KTP/SIM di storage
- [x] Middleware & pencatatan otomatis ke `document_access_log` saat foto identitas dibuka
- [x] Pastikan role Staf Gudang tidak dapat mem-bypass endpoint untuk melihat foto

### 2.5 Testing Fase 2
- [x] Test exclusion constraint (simulasikan insert jadwal overlap paksa di DB)
- [x] Test job denda telat otomatis (jadwal Laravel Scheduler aktif)
- [x] Test end-to-end notifikasi WA dengan sandbox & logger
- [x] Test cron job retensi data KTP menghapus file storage yang validitasnya habis
- [x] Playwright E2E testing visual availability calendar grid lolos (100% pass)

---

## 3. Fase 3 — Bundling, Maintenance & Analitik

### 3.1 Bundling Lanjutan & Substitusi
- [x] Logic deteksi jika komponen individu dalam sebuah paket kurang stoknya
- [x] Fitur Substitusi Manual: Izinkan kasir menukar unit A dengan unit B yang sejenis (dengan konfirmasi pelanggan)
- [x] Update otomatis kalkulasi harga paket jika unit substitusi berbeda harga

### 3.2 Papan Kerja (Kanban) Maintenance
- [x] Halaman khusus role *Staf Gudang/QC*
- [x] Kanban Board interaktif: `Returned` -> `Cleaning` -> `Service` -> `Available`
- [x] Form input riwayat servis ke `maintenance_logs` (waktu, jenis, teknisi)

### 3.3 Dashboard Analitik & Laporan (Super Admin)
- [x] Grafik tren transaksi, pendapatan finansial, & total denda masuk
- [x] Laporan Utilisasi Aset (% keterpakaian per unit barang)
- [x] Laporan Barang Terlaris & Margin Keuntungan
- [x] Analisis Sengketa / Denda (memantau KPI "< 5% komplain")
- [x] Fitur Export Laporan ke format CSV / Excel

### 3.4 Testing Fase 3
- [x] Test alur substitusi unit di kasir end-to-end
- [x] Test alur Kanban maintenance update status dengan benar
- [x] Validasi akurasi perhitungan agregat di Dashboard terhadap raw data transaksi
- [x] Playwright E2E testing Kanban & Analytics Dashboard lolos (100% pass)

### 3.5 Perombakan UI Admin & Modern SaaS Design System
- [x] Desain sistem kartu modern (`.sg-card`, `.sg-stat-card`, `.stat-icon-circle`, border lembut & soft shadows)
- [x] Perbaikan root-cause ikon SVG cacat/raksasa dengan batasan dimensi universal dan pemetaan ukuran
- [x] Komponen Reusable Blade Dropdown Kebab Menu Titik Tiga (`<x-kebab-menu>`) untuk semua tabel
- [x] Batasan 10 data per halaman (`paginate(10)`) di seluruh Livewire Index Components
- [x] Komponen Pagination Kustom (`resources/views/vendor/pagination/summitgear.blade.php`) tema Volcanic Twilight
- [x] Redesain Dashboard Admin Utama
- [x] Redesain Halaman Kasir & Transaksi Sewa (`/admin/transactions/create`)
- [x] Redesain Master Katalog Barang (`/admin/inventory/items`)
- [x] Redesain Manajemen Data Pelanggan (`/admin/customers`)
- [x] Redesain Serah-Terima & Checklist QC Operasional (`/admin/operations/handover`)
- [x] Redesain Kalender Visual Ketersediaan Alat (`/admin/operations/calendar`)
- [x] Redesain Papan Kerja Kanban Gudang (`/admin/maintenance/kanban`)
- [x] Redesain Dashboard Analitik & Laporan Bisnis (`/admin/analytics/dashboard`)
- [x] Redesain Manajemen Akun Staf (`/admin/users`)
- [x] Redesain Log Audit Sistem Keamanan (`/admin/audit-logs`)
- [x] Redesain Pengaturan Sistem Inti (`/admin/settings`)
- [x] Verifikasi Playwright E2E & visual screenshot capture lolos 100% (6 passed, 0 failed)

---
## 4. Deployment & Go-Live
- [ ] Setup server production (VPS/cloud) + PostgreSQL production
- [ ] Konfigurasi HTTPS/SSL (Wajib untuk form login & transaksi)
- [ ] **Security**: Pastikan `APP_DEBUG=false` di `.env` production (Mencegah kebocoran struktur db/kode).
- [ ] (Opsional) Konfigurasi pembatasan IP (IP Whitelisting) / VPN untuk endpoint admin jika diakses dari luar toko.
- [ ] Setup backup otomatis harian PostgreSQL (retensi 30 hari)
- [ ] Migrasi data awal (inventaris existing)
- [ ] **Storage Migration**: Pindahkan penyimpanan file KTP/SIM dari `storage/app/public` (local) ke Object Storage S3-compatible (Cloudflare R2)
- [ ] Load testing ringan (simulasi)
- [ ] Security review (akses endpoint foto & transaksi finansial)
- [ ] Training penggunaan sistem / SOP (Kasir & Staf Gudang)
- [ ] Soft launch dengan monitoring 1-2 minggu
- [ ] Kumpulkan feedback awal (Quick-fix UI/UX kasir)

---

## 5. Post-Launch (Fase 4 / Opsional)
- [ ] Evaluasi kapabilitas booking online self-service mandiri untuk pelanggan
- [ ] Evaluasi kesiapan codebase jika bisnis butuh skala multi-outlet
- [ ] Review pencapaian KPI bisnis setelah kuartal pertama beroperasi
- [ ] **Integrasi API Payment Gateway**: Integrasi pembayaran otomatis (Midtrans/Xendit) menggantikan verifikasi mutasi manual