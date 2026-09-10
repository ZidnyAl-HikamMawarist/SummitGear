# SummitGear POS & Rental Management System — Blueprint Arsitektur Lengkap (Summit.md)

Dokumen ini adalah **Master Blueprint & Dokumentasi Spesifikasi Menyeluruh** dari sistem **SummitGear POS**. Dokumen ini merangkum seluruh fondasi arsitektur, skema basis data, alur bisnis per role, aturan validasi, hingga panduan perombakan UI/UX agar dapat dijadikan acuan tunggal untuk pengembangan sistem, audit, maupun rekonstruksi aplikasi dari awal.

---

## DAFTAR ISI
1. [Ringkasan Eksekutif & Karakteristik Bisnis](#1-ringkasan-eksekutif--karakteristik-bisnis)
2. [Arsitektur Sistem & Tech Stack](#2-arsitektur-sistem--tech-stack)
3. [Skema Basis Data & Relasi Entitas](#3-skema-basis-data--relasi-entitas)
4. [State Machine & Siklus Hidup Entitas](#4-state-machine--siklus-hidup-entitas)
5. [Spesifikasi Fitur & Alur Kerja Setiap Role](#5-spesifikasi-fitur--alur-kerja-setiap-role)
   - [5.1 Role Publik / Pelanggan (Online Booking Tanpa Login)](#51-role-publik--pelanggan-online-booking-tanpa-login)
   - [5.2 Role Kasir (Front-Desk POS & Reservasi)](#52-role-kasir-front-desk-pos--reservasi)
   - [5.3 Role Staf Gudang & QC (Warehouse & Maintenance)](#53-role-staf-gudang--qc-warehouse--maintenance)
   - [5.4 Role Super Admin / Owner (Management & Security)](#54-role-super-admin--owner-management--security)
6. [Aturan Bisnis Kritis & Algoritma Khusus](#6-aturan-bisnis-kritis--algoritma-khusus)
7. [Evaluasi Arsitektur: Build Ulang vs Refactor UI dengan Flux](#7-evaluasi-arsitektur-build-ulang-vs-refactor-ui-dengan-flux)

---

## 1. Ringkasan Eksekutif & Karakteristik Bisnis

**SummitGear** adalah aplikasi terintegrasi Point-of-Sale (POS) dan Rental Management System khusus untuk penyewaan peralatan outdoor dan pendakian gunung (tenda, carrier, sleeping bag, kompor gunung, dll).

### Tantangan Unik Bisnis Rental Outdoor:
1. **Aset Berwujud dengan Serial Number Unik**: Pelanggan menyewa jenis barang (misal: "Tenda Dome 4P"), namun gudang mengeluarkan unit fisik tertentu dengan Serial Number spesifik (misal: `TND-004-03`) yang harus dipantau kondisi fisiknya.
2. **Kondisi Alat Dinamis Pasca Pemakaian**: Alat outdoor kembali dalam kondisi basah, kotor, atau rusak. Oleh karena itu, unit yang baru kembali tidak boleh langsung disewakan (`Available`), melainkan harus melalui proses pembersihan/pencucian (`Cleaning`) atau perbaikan (`Maintenance`).
3. **Pencegahan Double Booking**: Suatu unit fisik tidak boleh disewakan pada rentang tanggal dan jam yang bertabrakan dengan penyewaan lain.
4. **Alur Booking Online Cepat & Validasi Keterlambatan**: Pelanggan publik dapat melakukan reservasi mandiri dari rumah tanpa akun/login, memilih jadwal ambil, dan jika barang tidak diambil dalam batas toleransi 2 jam, kasir dapat memvalidasi pembatalan dan stok unit otomatis kembali ke gudang.

---

## 2. Arsitektur Sistem & Tech Stack

```
+-----------------------------------------------------------------------------------+
|                                 USER INTERFACE                                    |
|  [Public Landing & Booking]  |  [Kasir POS Grid]  |  [Gudang Kanban]  |  [Admin]  |
+-----------------------------------------------------------------------------------+
                                         |
                                         v
+-----------------------------------------------------------------------------------+
|                        APPLICATION LAYER (Laravel 11 / 12)                        |
|  - Livewire v3 (Reaktivitas Komponen & State Management Server-driven)            |
|  - Alpine.js (Micro-interactions, Drawer, Accordion, Modal Lock)                  |
|  - Tailwind CSS v4 (Design System Tokens, Typography, Layout Grid)                |
|  - Middleware: RoleMiddleware, KasirTimeoutMiddleware (Auto-logout 20 Menit)       |
|  - Services: AuditLogger, WhatsAppService, QrCodeGenerator                        |
+-----------------------------------------------------------------------------------+
                                         |
                                         v
+-----------------------------------------------------------------------------------+
|                         DATABASE LAYER (PostgreSQL / MySQL)                       |
|  - Relasi Transaksional ACID (Pessimistic / Optimistic Locking saat assign unit)   |
|  - Indeks Unik & Exclusion Constraints untuk Rentang Tanggal Booking             |
+-----------------------------------------------------------------------------------+
```

### Rincian Dependensi Inti:
- **Backend Framework**: Laravel 11.x / 12.x (PHP 8.2+)
- **Komponen Reaktif**: Livewire 3.x
- **Interaksi Frontend Ringan**: Alpine.js 3.x
- **CSS Engine**: Tailwind CSS v4 (via `@tailwindcss/vite`)
- **Database Engine**: PostgreSQL 12+ (disukai karena native `tsrange` exclusion constraints) atau MySQL 8.0+
- **Security**: Rate Limiting (Throttle), Session Inactivity Timeout, Audit Logging, PIN Authorization.

---

## 3. Skema Basis Data & Relasi Entitas

Aplikasi memiliki 14 tabel transaksional dan master data utama:

```
 users (Admin, Kasir, Gudang)
   │
   ├──< audit_logs (Mencatat seluruh aksi sensitif)
   │
 customers (Data Pelanggan: NIK, HP, Alamat)
   │
   └──< rentals (Sewa & Reservasi)
          │
          ├──< rental_details (Rincian per item sewa)
          │      │
          │      └──> item_units (Unit fisik bernomor seri)
          │             │
          │             └──> inventory_items (Master katalog barang)
          │
          ├──< payments (Riwayat DP, Pelunasan, Settlement)
          ├──< deposits (Uang jaminan & KTP fisik di brankas)
          ├──< penalties (Denda keterlambatan / kerusakan fisik)
          └──< inspections (Checklist QC Serah Terima Check-Out & Check-In)
```

### Rincian Kolom Tabel Utama:

#### 1. `users` (Karyawan & Manajemen)
- `id` (PK, BigInt)
- `name` (String, Nama Lengkap)
- `email` (String, Unique)
- `password` (String, Hashed)
- `role` (Enum: `'admin'`, `'kasir'`, `'gudang'`)
- `pin` (String, 6 Digit terenkripsi untuk otorisasi void & login kasir)
- `phone` (String, Nomor Kontak)
- `is_active` (Boolean, Default True)
- `timestamps`

#### 2. `customers` (Pelanggan Walk-In & Online)
- `id` (PK, BigInt)
- `name` (String, Nama Pelanggan)
- `nik` (String(16), Wajib 16 digit angka KTP)
- `phone` (String, Nomor WhatsApp diawali format `+62` atau `08`, maks 13-14 digit)
- `address` (Text, Alamat Lengkap)
- `consent_at` (Timestamp, Persetujuan Syarat & Ketentuan Sewa)
- `timestamps`

#### 3. `inventory_items` (Master Katalog Alat)
- `id` (PK, BigInt)
- `sku` (String, Kode Unik Master, misal: `TND-004`)
- `name` (String, misal: "Tenda Dome 4 Orang Waterproof")
- `category` (String: `'Tenda'`, `'Tas'`, `'Cooking'`, `'Sleeping'`, `'Lighting'`, `'Accessories'`)
- `rental_type` (Enum: `'daily'`, `'hourly'`)
- `price_per_day` (Decimal(12,2), Harga sewa normal per 24 jam)
- `photo_url` (String, Path gambar alat di storage)
- `is_package` (Boolean, Apakah produk bundling)
- `is_active` (Boolean, Status aktif katalog)
- `timestamps`

#### 4. `item_units` (Unit Fisik Bernomor Seri)
- `id` (PK, BigInt)
- `item_id` (FK -> `inventory_items.id`, On Delete Cascade)
- `serial_number` (String, Unique, misal: `TND-004-01`, `TND-004-02`)
- `status` (Enum: `'Available'`, `'Reserved'`, `'Rented'`, `'Cleaning'`, `'Maintenance'`, `'Broken/Lost'`)
- `condition_notes` (Text, Catatan kondisi fisik)
- `replacement_value` (Decimal(12,2), Nilai ganti rugi jika alat hilang total)
- `timestamps`

#### 5. `rentals` (Transaksi Sewa & Reservasi)
- `id` (PK, BigInt)
- `rental_code` (String, Unique, format: `TRX-YYYYMMDD-XXXX`)
- `customer_id` (FK -> `customers.id`)
- `start_date` (DateTime, Tanggal & Jam Pengambilan Alat)
- `end_date` (DateTime, Tanggal Selesai Sewa)
- `scheduled_return_time` (DateTime, Batas Jam Pengembalian Alat)
- `total_price` (Decimal(12,2), Total Biaya Sewa Dasar)
- `deposit_amount` (Decimal(12,2), Nominal Uang Jaminan)
- `status` (Enum: `'PENDING_PAYMENT'`, `'BOOKED'`, `'ACTIVE'`, `'COMPLETED'`, `'CANCELLED'`, `'OVERDUE'`)
- `source` (Enum: `'walkin'`, `'online'`)
- `notes` (Text, Catatan Tambahan Kasir)
- `timestamps`

#### 6. `rental_details` (Item yang Disewa dalam Satu Transaksi)
- `id` (PK, BigInt)
- `rental_id` (FK -> `rentals.id`, On Delete Cascade)
- `item_unit_id` (FK -> `item_units.id`)
- `price_per_day` (Decimal(12,2), Snapshot harga sewa harian saat transaksi dibuat)
- `return_status` (Enum: `'Pending'`, `'Returned'`, `'Damaged'`, `'Lost'`)
- `timestamps`

#### 7. `payments` (Pembayaran Transaksi)
- `id` (PK, BigInt)
- `rental_id` (FK -> `rentals.id`)
- `type` (Enum: `'DP'`, `'FULL'`, `'SETTLEMENT'`, `'PENALTY'`)
- `method` (Enum: `'Cash'`, `'QRIS'`, `'Transfer'`)
- `amount` (Decimal(12,2))
- `paid_at` (DateTime)
- `cashier_id` (FK -> `users.id`)
- `timestamps`

#### 8. `inspections` (Checklist QC Serah Terima)
- `id` (PK, BigInt)
- `rental_detail_id` (FK -> `rental_details.id`)
- `stage` (Enum: `'CHECKOUT'`, `'CHECKIN'`)
- `condition_category` (Enum: `'Baik'`, `'Cukup'`, `'Perhatian'`, `'Rusak'`)
- `notes` (Text, Komentar detail fisik, misal: "Frame tenda retak sambungan kedua")
- `inspector_id` (FK -> `users.id`)
- `timestamps`

#### 9. `penalties` (Denda & Ganti Rugi)
- `id` (PK, BigInt)
- `rental_id` (FK -> `rentals.id`)
- `reason` (String, Alasan denda, misal: "Keterlambatan 1 Hari", "Tenda Sobek")
- `amount` (Decimal(12,2))
- `is_settled` (Boolean, Apakah sudah dibayar)
- `is_override` (Boolean, Apakah ada diskon/penghapusan oleh Admin)
- `override_reason` (Text)
- `approved_by` (FK -> `users.id`, Admin yang menyetujui override)
- `timestamps`

#### 10. `audit_logs` (Rekam Jejak Keamanan)
- `id` (PK, BigInt)
- `user_id` (FK -> `users.id`)
- `action` (Enum: `'LOGIN'`, `'LOGOUT'`, `'CREATE'`, `'UPDATE'`, `'DELETE'`, `'VOID'`, `'CANCEL'`, `'OVERRIDE'`)
- `entity` (String, misal: `'Rental'`, `'ItemUnit'`, `'User'`)
- `entity_id` (BigInt)
- `reason` (Text, Alasan tindakan audit)
- `approved_by` (FK -> `users.id`, Opsional jika butuh otorisasi PIN)
- `ip_address` (String)
- `created_at` (Timestamp)

---

## 4. State Machine & Siklus Hidup Entitas

### Siklus Status Rental (`rentals.status`):
```
[Pelanggan Booking Online] ──> PENDING_PAYMENT (Menunggu Diambil)
                                     │
      ┌──────────────────────────────┴──────────────────────────────┐
      │ (Melewati toleransi 2 jam)                                 │ (Pelanggan datang ke outlet)
      v                                                             v
  CANCELLED (Stok Balik ke Gudang)                             ACTIVE (Check-Out QC & Bayar)
                                                                    │
                                                                    v
                                                     ┌──────────────┴──────────────┐
                                                     │ (Kembali tepat waktu)       │ (Terlambat kembali)
                                                     v                             v
                                                 COMPLETED                      OVERDUE
                                            (Setelah Check-In QC)      (Dikenakan denda harian)
                                                                                   │
                                                                                   v
                                                                               COMPLETED
                                                                         (Setelah Settlement Lunas)
```

### Siklus Status Unit Fisik (`item_units.status`):
```
  Available ──(Booking/Kasir)──> Reserved ──(Check-Out)──> Rented
      ^                                                      │
      │                                                (Check-In QC)
      │                                                      v
      ├──(Selesai Cuci)────── Cleaning <─────────────────────┤
      │                                                      │
      ├──(Selesai Servis)──── Maintenance <──────────────────┤
      │                                                      │
      └──────────────────────────────────────────────── Broken/Lost (Ganti rugi)
```

---

## 5. Spesifikasi Fitur & Alur Kerja Setiap Role

---

### 5.1 Role Publik / Pelanggan (Online Booking Tanpa Login)
Halaman publik dirancang untuk penyewa yang ingin menyewa alat secara mandiri dari smartphone/laptop sebelum datang ke toko.

#### Fitur Utama:
1. **Landing Page Interaktif (`/`)**:
   - Menampilkan identitas SummitGear, keunggulan layanan, testimoni, dan katalog alat unggulan.
   - Tombol Call-to-Action (CTA): *"Sewa Sekarang"* yang langsung mengarahkan ke katalog booking.
2. **Katalog Booking dengan Lazy Loading & Search (`/booking`)**:
   - Menampilkan grid produk dengan foto beresolusi tinggi, harga sewa per hari, dan indikator stok tersedia real-time.
   - **Lazy Loading & Infinite Scroll**: Halaman awal hanya me-render 12 produk pertama secara instan untuk efisiensi performa; produk berikutnya dimuat dinamis saat pengguna menggulir layar ke bawah.
   - Filter Kategori instan (`Semua`, `Tenda`, `Carrier/Tas`, `Cooking Set`, `Sleeping Bag`, `Lighting`, `Accessories`) dan pencarian live text.
   - Tombol *"Tambah ke Keranjang"* yang langsung meng-update badge jumlah barang di tombol keranjang mengambang (floating cart button).
3. **Cart Drawer 2-Langkah (Right-Side Slider)**:
   - **Step 1: Perlengkapan (Keranjang Belanja)**:
     - Mengelompokkan alat yang sama menjadi satu baris dengan keterangan kuantitas (misal: `Matras Spons x 3`).
     - Tombol kontrol tambah/kurang jumlah (`+` / `-`) dan tombol hapus.
     - Estimasi subtotal biaya alat per hari.
   - **Step 2: Jadwal & Data Pemesan**:
     - **Pemilihan Tanggal & Jam Pengambilan (Wajib)**: Minimal tanggal hari ini + input jam rencana kedatangan.
     - **Pemilihan Durasi Sewa Bebas**: Disediakan tombol cepat durasi (`[1 Hari]`, `[2 Hari]`, `[3 Hari]`, `[4 Hari]`, `[5 Hari]`, `[7 Hari]`) atau memilih tanggal selesai manual.
     - **Kalkulasi Biaya Transparan**: Menampilkan rincian: `(Total Biaya Unit / Hari) x (Jumlah Hari) = Total Estimasi`.
     - **Form Identitas**:
       - Nama Lengkap Pemesan.
       - Nomor WhatsApp: Wajib format `+62` atau diawali `08`, dibatasi maksimal 13–14 digit.
       - NIK KTP: Wajib tepat 16 digit angka numerik.
       - Alamat Domisili.
     - **Kotak Peringatan Toleransi**: Memberitahukan bahwa booking memiliki batas toleransi pengambilan maksimal **2 jam** dari jadwal ambil.
4. **Auto-Assignment Unit Fisik**:
   - Begitu booking dikirim (`submitBooking`), backend secara otomatis memilih unit fisik berstatus `Available` untuk setiap item yang dipesan dan mengubah statusnya menjadi `Reserved`.
   - Menghasilkan kode booking unik (misal: `TRX-20260909-0001`).

---

### 5.2 Role Kasir (Front-Desk POS & Reservasi)
Kasir adalah pengguna di meja kasir toko yang menangani kedatangan penyewa, memproses transaksi walk-in, pembayaran, dan serah terima unit.

#### Fitur Utama:
1. **Login Kasir Berbasis PIN Cepat (`/login/pin`)**:
   - Kasir login hanya dengan mengetikkan 6 digit PIN unik.
   - Dilengkapi proteksi Rate Limiter (maksimal 5x salah sebelum cooldown 5 menit).
   - Dilengkapi **`KasirTimeoutMiddleware`**: Jika kasir tidak ada aktivitas selama 20 menit, sesi otomatis dikunci demi keamanan transaksi.
   - Menggunakan `redirect()->intended(...)` sehingga jika sesi terkunci saat kasir membuka halaman tertentu, setelah memasukkan PIN kasir langsung kembali ke halaman tersebut.
2. **Katalog POS Kasir Ala Minimarket (`/admin/transactions/create`)**:
   - Halaman kasir berbentuk **Grid Produk Visual** (bukan form kaku).
   - Setiap kartu alat menampilkan foto barang, harga, dan badge stok real-time yang tersedia di gudang.
   - Klik kartu barang otomatis memasukkan 1 unit fisik `Available` ke keranjang kasir.
   - Kasir dapat memilih pelanggan terdaftar atau menginput pelanggan baru secara langsung di kasir.
   - Input uang jaminan (deposit finansial) dan validasi KTP fisik asli disimpan di brankas kasir.
   - Pilihan metode pembayaran: **Tunai (Cash)**, **QRIS Dinamis/Statis**, atau **Transfer Bank**.
   - Input DP (Down Payment) atau Pelunasan langsung.
3. **Menu Booking Masuk Online (`/admin/operations/incoming-booking`)**:
   - Menampilkan seluruh reservasi online yang dibuat pelanggan publik.
   - **Tampilan Grid Kartu Berjarak Lega (Spacious Layout)**:
     - Nama pemesan, nomor HP (+62) dengan ikon telepon, dan NIK 16 digit terstruktur rapi.
     - **Sub-Box Jadwal Ambil**: Tanggal dan Jam Rencana Pengambilan.
     - **Sub-Box Batas Toleransi**: Tanggal dan Jam Batas Maksimal Pengambilan (+2 Jam).
     - **Banner Status Waktu**:
       - Jika masih aktif: Menampilkan *"Sisa waktu pengambilan: X jam Y menit"*.
       - Jika lewat batas: Menampilkan banner merah *"Terlambat X jam Y menit. Barang belum diambil!"*.
     - Toggle daftar alat yang dipesan beserta Serial Number unit fisiknya.
   - **Aksi Validasi Pembatalan Khusus**:
     - Jika pelanggan tidak hadir hingga lewat toleransi, kasir menekan tombol merah lebar **`Validasi Batal & Kembalikan Stok`**.
     - Sistem otomatis mengubah status sewa menjadi `CANCELLED`, mengembalikan seluruh unit terkait menjadi `Available` di gudang, mencatat jejak di `audit_logs`, dan mengirim pesan pembatalan otomatis via WhatsApp.
   - **Aksi Proses / Bayar**:
     - Jika pelanggan datang, kasir menekan tombol hijau **`Proses / Bayar`** yang langsung membawa ke proses pelunasan dan invoice.
4. **Operasional Serah Terima Check-Out & Check-In (`/admin/operations/handover`)**:
   - **Check-Out (Pengambilan Alat)**: Kasir bersama penyewa memeriksa kondisi awal fisik alat, mencatat checklist komponen, dan meminta Tanda Tangan Digital pelanggan di layar sebelum barang dibawa pergi. Status unit berubah menjadi `Rented`.
   - **Check-In (Pengembalian Alat)**: Menerima kembali barang yang disewa. Kondisi akhir diperiksa. Jika barang kotor/basah dialihkan ke `Cleaning`, jika rusak dialihkan ke `Maintenance`.
5. **Penyelesaian Transaksi & Denda (`/admin/operations/{id}/settlement`)**:
   - Jika pelanggan terlambat mengembalikan atau merusak alat, sistem menghitung denda otomatis.
   - Kasir memotong nominal deposit atau menagih selisih kekurangan pembayaran kepada pelanggan.
6. **Cetak Invoice & Dokumen SPK (`/admin/transactions/{id}/print`)**:
   - Menghasilkan cetakan struk resmi dan Surat Perjanjian Kontrak (SPK) sewa alat outdoor yang siap ditandatangani dan dibawa pelanggan.

---

### 5.3 Role Staf Gudang & QC (Warehouse & Maintenance)
Staf gudang bertanggung jawab atas kelaikan fisik alat, kebersihan tenda/alat masak, perbaikan frame/tas, dan kesiapan packing.

#### Fitur Utama:
1. **Papan Kanban Maintenance Interaktif (`/admin/maintenance/kanban`)**:
   - Kolom status visual:
     1. **`Ready` (Siap Disewa / Available)**: Alat bersih, kering, dan lengkap di rak gudang.
     2. **`Cleaning` (Pencucian / Penjemuran)**: Tenda basah, sleeping bag perlu dicuci, nesting kotor.
     3. **`Maintenance` (Dalam Perbaikan)**: Frame patah, resleting macet, tali putus sedang diperbaiki teknisi.
     4. **`Broken / Lost` (Rusak Berat / Hilang)**: Alat tidak bisa dipakai kembali, menunggu klaim ganti rugi.
   - Staf gudang dapat memindahkan unit antar kolom dengan *Drag-and-Drop* instan.
   - Setiap perubahan status di Kanban langsung memperbarui angka stok real-time di POS Kasir dan halaman Booking Online tanpa delay.
2. **Inspeksi Fisik & Checklist Quality Control (QC)**:
   - Membantu kasir melakukan pengecekan mendalam saat pengembalian alat.
   - Mencatat catatan teknis kondisi unit pada tabel `inspections`.
3. **Kalender Monitoring Booking Alat (`/admin/operations/calendar`)**:
   - Menampilkan matriks ketersediaan unit dalam bentuk kalender/timeline (misal jendela 7 hari).
   - Membantu staf gudang mengetahui alat apa saja yang harus di-packing malam ini untuk jadwal pengambilan besok pagi.
4. **Inventaris Alat Read-Only (`/admin/inventory/items`)**:
   - Mencari lokasi rak alat, mengecek riwayat pemakaian unit, dan mencetak label QR/Barcode fisik alat.

---

### 5.4 Role Super Admin / Owner (Management & Security)
Super Admin (Owner) memegang kendali bisnis penuh, mengawasi metrik keuangan, mengelola hak akses karyawan, serta mengaudit seluruh kegiatan sistem.

#### Fitur Utama:
1. **Dashboard Eksekutif & KPI Real-Time (`/dashboard`)**:
   - Banner sambutan dengan tombol aksi cepat ke **`Booking Masuk`**, **`Serah-Terima QC`**, dan **`Kalender Booking`** *(menu kasir belanja walk-in disembunyikan dari Super Admin agar fokus pada manajemen)*.
   - Kartu Metrik: Jumlah Check-out Hari Ini, Check-in Terlambat, Unit dalam Perbaikan, dan Omset Berjalan Bulan Ini.
   - Widget Sebaran Aset Inventaris per Kategori dan Tabel Transaksi Terkini.
2. **Laporan & Analitik Keuangan (`/admin/analytics/dashboard`)**:
   - Grafik tren pendapatan bulanan, rasio okupansi alat, alat paling laris disewa, dan riwayat perolehan denda.
3. **Manajemen Master Inventaris (`/admin/inventory/items`)**:
   - Menambah master produk baru, menentukan kategori, tarif sewa harian, dan upload foto produk.
   - Mengelola serial number unit fisik (`/admin/inventory/items/{id}/units`) dan nilai penggantian alat (`replacement_value`).
4. **Kelola Akun Karyawan & Hak Akses (`/admin/users`)**:
   - Mendaftarkan staf baru sebagai Kasir atau Staf Gudang.
   - Mengatur atau mereset 6-digit PIN Kasir jika karyawan lupa PIN.
   - Menonaktifkan akun karyawan yang sudah tidak aktif.
5. **Log Audit Lengkap (`/admin/audit-logs`)**:
   - Jejak audit tak terhapuskan (immutable log) yang mencatat waktu, user, IP address, entitas yang diubah, dan alasan perubahan.
6. **Otorisasi Khusus & Override Denda**:
   - Fitur pembatalan void transaksi atau diskon/penghapusan denda pelanggan memerlukan verifikasi PIN Admin demi mencegah kecurangan (fraud) di meja kasir.
7. **Pengaturan Sistem Toko (`/admin/settings`)**:
   - Menetapkan batas jendela booking (`booking_window_days`), tarif denda keterlambatan default per hari, dan batas toleransi pengambilan booking.

---

## 6. Aturan Bisnis Kritis & Algoritma Khusus

### 1. Algoritma Toleransi Pengambilan Booking Online (+2 Jam)
```
Jadwal Pengambilan = start_date (misal: 09 Sep 2026, 10:00 WIB)
Batas Toleransi    = start_date + 2 Jam (09 Sep 2026, 12:00 WIB)

IF (Waktu Sekarang > Batas Toleransi) THEN:
    Status Booking = LEWAT BATAS WAKTU (is_expired = true)
    Tindakan Kasir = Validasi Batal & Kembalikan Stok
    Unit Fisik     = Diubah dari 'Reserved' kembali ke 'Available'
ELSE:
    Status Booking = Menunggu Diambil (is_expired = false)
    Sisa Waktu     = Format Absolut Bahasa Indonesia (misal: "10 jam 20 menit")
END IF
```

### 2. Validasi Nomor Telepon & NIK
- **Nomor HP**: Diwajibkan diawali format internasional `+62` atau `08`, dibersihkan dari karakter non-angka kecuali tanda plus di awal, dibatasi maksimal 13–14 digit.
- **NIK KTP**: Wajib validasi numerik `digits:16` tepat 16 karakter tanpa spasi atau strip.

### 3. Perhitungan Biaya Sewa Harian
- Durasi Sewa dihitung dari selisih hari kalender:
  $$\text{Durasi Hari} = \max(1, \text{Hari Selesai} - \text{Hari Mulai})$$
- Total Biaya:
  $$\text{Total} = \sum (\text{Harga Sewa Item} \times \text{Kuantitas}) \times \text{Durasi Hari}$$

### 4. Proteksi Keamanan POS Kasir (20-Menit Inactivity Timeout)
- Middleware `KasirTimeoutMiddleware` membaca session `last_activity`.
- Jika waktu saat ini - `last_activity` > 1200 detik (20 menit), sesi kasir otomatis di-destroy (`auth()->logout()`), dan kasir diarahkan ke layar input PIN.
- URL halaman terakhir yang sedang diakses disimpan di `url.intended` agar setelah PIN dimasukkan, kasir langsung kembali ke pekerjaannya.

---

## 7. Evaluasi Arsitektur: Build Ulang vs Refactor UI dengan Flux

Berikut adalah analisis objektif atas pertanyaan Anda:

### Pertanyaan 1: *"Mending build ulang dari awal atau tetap pakai stack sekarang?"*

> [!IMPORTANT]
> **Rekomendasi Utama: JANGAN bangun ulang backend dari nol.**
> Backend Laravel Anda saat ini sudah **sangat matang, memiliki 22 migrasi terstruktur, relasi model yang lengkap, fitur keamanan ketat, serta 17 test cases otomatis yang lulus 100%**. 
> Frustrasi yang Anda alami **100% bersumber dari lapisan tampilan (UI / CSS)**, bukan karena arsitektur backend atau Livewire.

Jika Anda membangun ulang dari nol (misalnya pindah ke framework JS terpisah seperti Next.js atau Vue SPA):
1. Anda harus menulis ulang seluruh API authentication, middleware role, audit log, state machine unit, dan validasi transaksi.
2. Anda harus men-setup CORS, Sanctum/JWT token, state management client (Redux/Pinia), dan routing ganda yang jauh lebih kompleks.
3. Butuh waktu berminggu-minggu hanya untuk kembali ke titik fungsional yang sudah Anda miliki sekarang.

---

### Pertanyaan 2: *"Apakah kalau pakai library Flux UI, tampilannya akan jadi semakin bagus?"*

> [!TIP]
> **JAWABAN: YA, SANGAT SIGNIFIKAN! Flux UI adalah solusi paling tepat untuk masalah Anda saat ini.**

#### Mengapa Flux UI Sangat Cocok untuk SummitGear?
1. **Dibuat Langsung oleh Pencipta Livewire (Caleb Porzio)**:
   - Flux UI (`fluxui.dev`) adalah official UI library resmi yang dirancang khusus berdampingan dengan **Livewire v3** dan **Tailwind CSS v4**.
   - Tidak akan ada lagi masalah DOM re-render glitch, style class hilang, atau teks putih di atas putih.
2. **Kualitas Desain Standar Dunia (Setara Linear / Apple / Vercel)**:
   - Typography, warna border, radius sudut, bayangan (box shadow), dan focus rings sudah dirancang oleh desainer profesional kelas dunia.
   - Komponen seperti `<flux:button>`, `<flux:card>`, `<flux:modal>`, `<flux:badge>`, `<flux:input>`, `<flux:table>` memiliki animasi mikro dan transisi yang sangat halus.
3. **Menghemat Ribuan Baris CSS Berantakan**:
   - Alih-alih menulis:
     ```html
     <!-- Cara Lama: Rawan typo, berantakan, teks putih di atas putih -->
     <button class="flex-1 py-2.5 px-3 text-xs font-black text-white bg-gradient-to-r from-red-600 to-rose-600 rounded-xl shadow-sm ...">
     ```
   - Dengan Flux UI, cukup menulis:
     ```html
     <!-- Cara Flux: Bersih, elegan, bebas bug, konsisten di semua layar -->
     <flux:button variant="danger" icon="x-circle">Validasi Batal</flux:button>
     ```
4. **Layout Responsif & Bebas Berdempetan**:
   - Flux memiliki container grid dan layout primitives yang otomatis menangani spacing antar elemen sehingga kartu, jadwal, dan tabel tidak akan saling berdesakan di layar kecil maupun laptop.

---

## 8. Kesimpulan & Roadmap Langkah Selanjutnya

1. **Jadikan dokumen `Summit.md` ini sebagai Blueprint Resmi** dari seluruh logika dan fungsionalitas SummitGear POS.
2. **Pertahankan Seluruh Backend Laravel**: Tetap gunakan controller, model, migration, service, dan rute yang sudah teruji.
3. **Migrasikan Lapisan View ke Flux UI**:
   - Pasang Flux UI (`composer require livewire/flux`).
   - Ganti komponen blade layout, sidebar, modal, tombol, dan form input dengan komponen Flux.
   - Anda akan mendapatkan aplikasi web dengan tampilan kelas dunia, interaksi super mulus, kode bersih, dan bebas rasa frustrasi.
