# Daftar Halaman & Fitur Berdasarkan Role (Hak Akses)

Berikut adalah daftar halaman dan fitur yang tersedia di aplikasi SummitGear POS, dikelompokkan berdasarkan peran atau hak akses pengguna (*Role*).

---

## 1. 👑 Admin (Super Admin / Owner)
Role **Admin** memiliki kendali dan akses penuh ke seluruh fitur, modul, dan konfigurasi yang ada di dalam sistem. 

### Halaman & Fitur Eksklusif Admin:
- **📊 Analitik & Laporan**: Melihat metrik penjualan, performa sewa, dan laporan keuangan tingkat lanjut.
- **👥 Kelola Akun (User Management)**: Menambah, mengubah, mengatur PIN, atau menonaktifkan akun karyawan (Kasir & Gudang).
- **🛠️ Pengaturan Sistem**: Mengatur preferensi toko, tarif denda default, pajak, dan profil bisnis.
- **📜 Log Audit (Audit Trail)**: Memantau histori aktivitas dan jejak rekam semua pengguna di dalam sistem untuk transparansi dan keamanan.
- **📦 Manajemen Master Inventaris (Penuh)**: Membuat (Create) master barang baru, menetapkan harga sewa/denda, mengelola harga bundling, dan mengedit data master barang.
- **🔓 Otorisasi Khusus**: Dapat melakukan *override* atau penghapusan denda saat penyelesaian transaksi (*Settlement*).

### Fitur Lain (Termasuk akses Monitoring & Gudang):
- Modul monitoring Booking Masuk, Invoice, dan Data Pelanggan (Pembuatan Transaksi Kasir & Sewa langsung di POS khusus dilakukan oleh role **Kasir**).
- Semua modul operasional gudang, maintenance, dan serah terima QC.

---

## 2. 💰 Kasir (Front-desk / Cashier)
Role **Kasir** berfokus penuh pada pelayanan pelanggan, pembuatan transaksi sewa, serta manajemen pembayaran.

### Halaman & Fitur Kasir:
- **🏠 Dashboard Kasir**: Ringkasan transaksi hari ini, aktivitas kasir, dan notifikasi mendesak.
- **🛒 Kasir & Sewa (Transaksi Baru)**: 
  - Membuat transaksi sewa/booking baru.
  - Mengelola keranjang belanja (sewa alat & add-on).
  - Menetapkan durasi sewa, diskon manual, pajak, dan pembayaran DP/Lunas.
  - Mencetak Struk / Invoice (SPK - Surat Perjanjian Kontrak).
- **👥 Manajemen Data Pelanggan**:
  - Melihat daftar pelanggan.
  - Mendaftarkan pelanggan baru.
  - Memperbarui profil pelanggan dan mengunggah foto KTP/SIM dengan standar *Privacy Protection*.
  - Mengekspor data pelanggan (Export CSV).
- **📅 Kalender Booking**: Mengecek ketersediaan alat di tanggal tertentu untuk menjawab pertanyaan/reservasi pelanggan dengan cepat.
- **🔄 Serah Terima (Quality Control & Settlement)**:
  - **Check-Out**: Mengonfirmasi dan menyerahkan barang yang sudah disiapkan ke pelanggan.
  - **Check-In**: Menerima barang kembali dari pelanggan.
  - **Settlement**: Memproses penyelesaian tagihan akhir jika terdapat keterlambatan waktu atau denda kerusakan barang dari pengecekan Gudang.
- **🎒 Inventaris Alat (Read-Only)**: Melihat stok barang dan katalog ketersediaan unit.

---

## 3. 📦 Gudang (Warehouse / Maintenance Staff)
Role **Gudang** berfokus pada fisik barang, perawatan, pencucian, serta menjaga kualitas unit agar selalu siap disewakan.

### Halaman & Fitur Gudang:
- **🏠 Dashboard Operasional**: Ringkasan tugas operasional, antrean alat yang harus dicuci/diperbaiki.
- **🔧 Gudang (Kanban Maintenance)**:
  - Mengelola status fisik tiap unit melalui papan Kanban interaktif (*Drag & Drop*).
  - Mengubah status barang: `Ready` (Siap Disewa), `Cleaning` (Sedang Dicuci), `Maintenance` (Dalam Perbaikan), atau `Broken/Lost`.
  - Mencatat detail kerusakan fisik barang.
- **🔄 Serah Terima Fisik (Quality Control)**:
  - **Check-Out**: Membantu staf Kasir melakukan pengecekan fisik (*checklist* komponen) sebelum barang dibawa oleh pelanggan.
  - **Check-In**: Melakukan inspeksi ketat sesaat setelah pelanggan mengembalikan alat untuk mendeteksi tenda basah, frame patah, atau pasak hilang.
- **📅 Kalender Booking**: Memantau jadwal kedatangan pelanggan agar staf gudang bisa menyiapkan, membersihkan, dan mengepak alat tepat waktu sebelum hari-H pengambilan.
- **🎒 Inventaris Alat (Read-Only)**: Mencari rincian fisik alat, melacak SKU unit spesifik, dan mengetahui lokasi barang di rak.
