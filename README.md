# SummitGear - Point of Sale & Outdoor Rental Management System

SummitGear adalah sistem informasi terpadu yang dirancang khusus untuk operasional persewaan peralatan pendakian dan petualangan luar ruangan (*outdoor gear rental*). Sistem ini mengintegrasikan portal reservasi online untuk pelanggan, antarmuka Point of Sale (POS) cepat untuk kasir, manajemen kontrol mutu (QC) dan pemeliharaan alat untuk staf gudang, serta pusat analitik dan keamanan berlapis (termasuk **Google Authenticator 2FA**) untuk Super Administrator.

---

## 📁 Berkas Bahan Presentasi & Dokumentasi Resmi
Seluruh materi yang disiapkan khusus untuk presentasi, SOP, diagram arsitektur, dan panduan penggunaan telah dikelompokkan secara rapi di dalam folder **`bahan presentasi/`**:

1. **[Naskah Presentasi Lengkap (naskah.md)](file:///c:/laragon/www/SummitGear/bahan%20presentasi/naskah.md)**:
   - Cerita latar belakang nyata (pengalaman sewa alat ke Papandayan & kendala komunikasi manual WhatsApp).
   - Penjelasan teknis arsitektur, tech stack, dan library yang digunakan beserta alasannya.
   - Penjelasan mendalam keputusan desain: Mengapa beralih dari Alpine.js murni ke Flux UI.
   - Uraian 7 pilar keamanan data dan cara kerjanya (Google Authenticator 2FA, PIN otorisasi, Rate limiting, Audit Trail, dll).
   - Panduan alur demo interaktif langkah demi langkah (Landing Page -> Booking Online -> Kasir POS -> Admin 2FA -> Gudang).
   - Cheatsheet antisipasi pertanyaan dewan penguji.

2. **[Standar Operasional Prosedur / SOP Resmi (SOP_operasional.md)](file:///c:/laragon/www/SummitGear/bahan%20presentasi/SOP_operasional.md)**:
   - SOP-01: Prosedur Pemesanan & Reservasi Online oleh Pelanggan.
   - SOP-02: Prosedur Layanan Kasir & Transaksi Sewa Langsung (Walk-In).
   - SOP-03: Prosedur Serah Terima Peralatan (Check-Out) & Verifikasi Jaminan Identitas.
   - SOP-04: Prosedur Pengembalian Peralatan (Check-In), Pemeriksaan Mutu (QC) & Restitusi Deposit.
   - SOP-05: Prosedur Penanganan Keterlambatan & Penetapan Denda Otomatis.
   - SOP-06: Prosedur Pemeliharaan Gudang, Pencucian, & Perbaikan Alat (Maintenance Lifecycle).
   - SOP-07: Prosedur Keamanan Sistem Informasi, Google Authenticator 2FA, & Otorisasi PIN Admin.

3. **[Diagram Teknis & Arsitektur (diagram.md)](file:///c:/laragon/www/SummitGear/bahan%20presentasi/diagram.md)**:
   - Entity Relationship Diagram (ERD) lengkap dengan relasi tabel database PostgreSQL/Eloquent (Mermaid).
   - Flowchart Alur Bisnis End-to-End Rental & POS Lifecycle (Mermaid).
   - Flowchart Keamanan Login Administrator Google Authenticator 2FA (Mermaid).
   - Use Case Diagram 4 Aktor Sistem (Pelanggan, Kasir, Gudang, Super Admin) (Mermaid).

4. **[Panduan Penggunaan & Operasional (panduan.md)](file:///c:/laragon/www/SummitGear/bahan%20presentasi/panduan.md)**:
   - Kredensial akun pengujian bawaan (*pre-seeded demo accounts*).
   - Panduan aktivasi & penggunaan 2FA Google Authenticator serta kode pemulihan darurat.
   - Panduan alur transaksi POS kasir & validasi booking masuk.
   - Panduan alur pemeliharaan gudang (Pembersihan, Servis, Sedang Disewa, Siap Disewa).
   - Panduan alur booking online pelanggan.
   - Panduan fitur keamanan, modal konfirmasi logout, dan validasi hapus data.

---

## 🚀 Kredensial Akun Pengujian (Demo Accounts)

| Peran (Role) | Alamat Email | Kata Sandi | PIN Cepat | Halaman Akses |
| :--- | :--- | :--- | :--- | :--- |
| **Super Admin** | `admin@summitgear.com` | `password123` | `123456` | Dashboard Utama (`/dashboard`) |
| **Kasir Utama** | `kasir@summitgear.com` | `password123` | `111111` | Point of Sale Kasir (`/admin/transactions/create`) |
| **Staf Gudang** | `gudang@summitgear.com` | `password123` | `222222` | Operasional Gudang (`/admin/maintenance/kanban`) |