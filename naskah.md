# Naskah Presentasi: SummitGear POS & Outdoor Rental System

> **Petunjuk Pembicara:**  
> Naskah ini dirancang dengan gaya bahasa yang profesional, percaya diri, namun tetap luwes, santai, dan bercerita (*storytelling*). Jangan menghafal kata per kata, gunakan poin-poin dan transisi ini sebagai panduan mengalir di depan penguji/audiens.

---

## 1. Pembukaan & Latar Belakang Masalah (Storytelling)
*(Waktu estimasi: 2 - 3 Menit)*

"Selamat pagi/siang Bapak/Ibu dewan penguji dan rekan-rekan sekalian. Terima kasih atas kesempatan yang diberikan hari ini.

Perkenalkan, nama saya **[Nama Anda]**. Hari ini, saya sangat antusias untuk mempresentasikan proyek sistem informasi yang saya kembangkan, yaitu **SummitGear POS & Outdoor Rental Management System**.

Sebelum kita masuk ke teknis dan demo aplikasi, izinkan saya berbagi cerita singkat di balik lahirnya aplikasi ini.

Saya pribadi adalah orang yang suka kegiatan luar ruangan dan mendaki gunung. Beberapa waktu yang lalu, saya dan teman-teman berencana melakukan pendakian ke Gunung Papandayan. Seperti biasa, sebelum mendaki kami mencari tempat sewa alat outdoor di sekitar kota kami.

Namun, pengalaman menyewa alat tersebut ternyata meninggalkan catatan tersendiri bagi saya:
1. **Ketidakpastian Stok via Chat Manual:**  
   Hampir semua tempat sewa alat gunung tidak memiliki website resmi yang menampilkan stok secara *real-time*. Kalaupun ada media sosial, komunikasinya hanya lewat WhatsApp. Saat kita tanya ketersediaan tenda atau carrier, admin sering membalas lama, atau jawabannya kurang pasti: *'Sebentar kak, dicek dulu ke gudang.'*
2. **Pengalaman Pahit — Sudah Di-acc, Pas Datang Barang Kosong:**  
   Puncaknya terjadi saat kami sudah memesan beberapa unit tenda kapasitas 4 orang via WhatsApp dan sudah diiyakan oleh admin. Namun, ketika kami datang ke outlet pada hari H keberangkatan, staf di toko bilang tendanya sudah disewakan ke orang lain yang datang lebih dulu secara *walk-in*! Ternyata tidak ada sinkronisasi antara admin WA dengan kasir di toko. Akibatnya, rencana perjalanan kami sempat berantakan karena harus keliling mencari toko sewa lain yang masih memiliki stok sisa.
3. **Internal Toko yang Masih Konvensional:**  
   Dari situ saya menyadari, toko rental outdoor menghadapi masalah serius: pencatatan nota masih pakai buku manual kertas, status unit di gudang tidak terlacak (apakah tenda sedang dicuci, rusak, atau sedang dibawa pendaki), serta tidak ada validasi batas waktu sewa dan denda yang transparan.

Dari pengalaman nyata itulah, saya tergerak untuk membangun **SummitGear**.  
Sebuah sistem terpadu yang menjembatani **pelanggan** agar bisa melihat katalog dan ketersediaan stok secara transparan serta melakukan booking online dengan jaminan penahanan unit (*hold stock*), sekaligus menyediakan **sistem Point of Sale (POS) kasir dan manajemen gudang modern** untuk operasional internal toko."

---

## 2. Arsitektur & Pilihan Tech Stack
*(Waktu estimasi: 2 Menit)*

"Dalam membangun SummitGear, saya memilih teknologi yang berfokus pada **performa tinggi, stabilitas data, dan kecepatan interaksi pengguna**:

1. **Framework Utama: Laravel & PHP 8.3**  
   - Laravel menyediakan fondasi backend yang kokoh, keamanan bawaan (*CSRF protection, secure authentication, hashed credentials*), serta ORM Eloquent yang kuat untuk mengelola relasi kompleks antara master barang, unit serial number fisik, transaksi, dan audit log.
2. **Reaktivitas Antarmuka: Livewire**  
   - Memberikan pengalaman seperti Single Page Application (SPA) tanpa kerumitan framework frontend terpisah. Setiap aksi—seperti perhitungan harga sewa harian/weekend, filter pencarian alat, kalkulasi denda, dan stepper keranjang—berjalan instan dan dinamis (*reactive*) secara langsung dari server.
3. **Penyimpanan Data: PostgreSQL / Database Relasional**  
   - Menggunakan transaksi ACID (`DB::beginTransaction` & `DB::commit`) untuk menjamin tidak akan pernah terjadi *double booking* pada unit fisik yang sama di jam yang sama.
4. **Keamanan Tambahan:**  
   - **Google Authenticator 2FA (TOTP RFC 6238)** khusus akun Super Administrator dengan QR Code offline SVG (BaconQrCode).
   - **Otorisasi PIN 6-digit** untuk persetujuan aksi kritis kasir/staf oleh Admin.

---

### Mengapa Beralih dari Pure Alpine.js ke Flux UI?
*(Bagian ini sangat penting untuk menjelaskan keputusan arsitektur UI Anda)*

"Bapak/Ibu penguji, ada satu pengalaman teknis menarik yang ingin saya ceritakan terkait antarmuka (UI).

Pada awalnya, saya mencoba membangun seluruh interaksi antarmuka hanya mengandalkan **Alpine.js murni**. Namun di tengah proses pengembangan, saya menemukan beberapa kendala teknis:
- **Alpine.js pada dasarnya adalah *Micro-Framework Behavior*, bukan *UI Component Library*.**  
  Alpine sangat bagus untuk interaktivitas kecil seperti *toggle dropdown* atau *tab sederhana*. Namun Alpine tidak menyediakan komponen antarmuka yang lengkap (*no built-in design system*).
- **Kompleksitas Aksesibilitas & Focus Trapping:**  
  Saat membuat form yang kompleks—seperti modal konfirmasi bertingkat, form serial number unit fisik, dan drawer keranjang kasir—saya harus merakit sendiri CSS, transisi, animasi backdrop, *focus trapping*, penanganan tombol ESC, dan standar ARIA secara manual. Hal ini membuat kode JavaScript di Blade menjadi sangat panjang, rentan bentrok (*state collision*), dan sulit distandarkan.

Oleh karena itu, saya memutuskan mengadopsi **Flux UI**.  
Flux UI adalah *official UI library* yang diciptakan langsung oleh **Caleb Porzio** (pencipta Livewire dan Alpine.js itu sendiri). Karena dibuat oleh sang arsitek yang sama:
1. **Sinergi 100% Klop (*Native Integration*):** Flux terhubung secara organik dengan *state* Livewire tanpa perlu konfigurasi rumit.
2. **Headless & Modern:** Komponen seperti `<flux:modal>`, `<flux:input>`, `<flux:select>`, dan `<flux:button>` sudah memiliki standar aksesibilitas terbaik, animasi transisi yang mulus, dan *styling* Tailwind CSS yang bersih.
3. **Hasilnya:** Antarmuka SummitGear sekarang terlihat jauh lebih profesional, elegan, bebas *bug visual*, dan sangat mudah dipelihara."

---

## 3. Alur Demo Langsung (Live Demonstration Walkthrough)
*(Waktu estimasi: 7 - 10 Menit)*

> **Panduan Navigasi Demo:**  
> Buka browser di proyektor dan ikuti urutan berikut langkah demi langkah.

---

### TAHAP 1: Landing Page Publik & Cek Ketersediaan Alat
*(Tunjukkan halaman utama: `http://localhost:8000/`)*

- **Apa yang ditunjukkan:**
  - Halaman Landing Page dengan visual bernuansa petualangan gunung (*Adventure Coral & Deep Navy*).
  - Tunjukkan bagian **Katalog Peralatan**:
    *"Bisa kita lihat di sini, calon pendaki tidak perlu lagi menebak-nebak atau bertanya berulang kali via WhatsApp. Seluruh peralatan mendaki—mulai dari tenda dome, carrier, sleeping bag, kompor portable, hingga nesting—terpampang jelas lengkap dengan foto asli, spesifikasi teknis, harga sewa per hari, dan jumlah stok yang siap disewa secara real-time."*
  - Tunjukkan bagian **Paket Hemat Mendaki (Bundling)**:
    *"SummitGear juga menyediakan paket bundling hemat (seperti Paket Duo Savana atau Paket Rinjani Expert) yang otomatis menghitung gabungan komponen alat dengan harga diskon khusus."*

---

### TAHAP 2: Simulasi Booking Online oleh Pelanggan
*(Klik tombol 'Sewa Sekarang' atau navigasi ke `/booking`)*

- **Apa yang ditunjukkan:**
  - Masuk ke form pemesanan online:
    *"Sekarang, mari kita simulasikan seorang pelanggan yang ingin menyewa alat untuk pendakian akhir pekan."*
  - Pilih **Tanggal Mulai Sewa** dan **Tanggal Pengembalian**.
  - Tunjukkan bagaimana sistem secara otomatis menghitung durasi hari sewa dan menerapkan tarif sewa yang transparan.
  - Tambahkan barang ke keranjang (misal: Tenda Dome 4P dan Carrier 60L).
  - Masukkan data diri pemesan (Nama, No. WhatsApp, NIK KTP).
  - Klik **Kirim Pemesanan**:
    *"Saat pesanan online dikirim, sistem SummitGear langsung mengunci status unit fisik di database (*Hold Stock*) dengan toleransi waktu 2 jam. Pelanggan mendapatkan kode booking unik dan ringkasan jadwal pengambilan. Jika dalam batas waktu tersebut pelanggan tidak datang ke outlet, sistem kasir dapat membatalkan reservasi secara otomatis dan mengembalikan stok ke gudang."*

---

### TAHAP 3: Halaman Kasir (POS - Point of Sale)
*(Login sebagai kasir via `/login/pin` menggunakan PIN `111111`)*

- **Apa yang ditunjukkan:**
  - Tunjukkan antarmuka khusus kasir (`/admin/transactions/create`):
    *"Mengapa kasir memiliki antarmuka dan alur login tersendiri? Karena kasir di meja counter membutuhkan kecepatan tinggi dan fokus transaksi tanpa distraksi menu manajemen."*
  - Tunjukkan **Login Cepat via PIN 6-Digit** dan fitur **Screen Lock (Kunci Layar Kasir)** jika kasir meninggalkan meja.
  - **Dua Alur Utama di Kasir:**
    1. **Validasi Booking Online Masuk:**
       Kasir membuka menu *Booking Masuk*, mencari kode booking pelanggan tadi, memverifikasi kesiapan unit, lalu memprosesnya ke pembayaran.
    2. **Transaksi Sewa Langsung (Walk-In):**
       - Tambahkan barang ke keranjang kasir.
       - Tunjukkan *stepper* jumlah unit dan pemilihan serial number fisik.
       - Tunjukkan fitur **Dynamic Pricing Rule**: Tarif otomatis membedakan harga *Weekday* (Senin-Kamis) dan *Weekend* (Jumat-Minggu) tanpa kasir perlu menghitung manual!
       - Pilih identitas jaminan (KTP / SIM / Paspor) dan input nominal uang muka / deposit jaminan.
       - Klik **Proses Transaksi**: Muncul cetak **Invoice Resmi & Struk Thermal** lengkap dengan rincian biaya sewa, deposit, dan syarat ketentuan.
  - **Uji Coba Keamanan Baru (Modal Logout):**
    Klik tombol *Keluar* di topbar kasir:
    *"Dapat kita lihat, sistem tidak langsung logout secara gegabah. Muncul modal konfirmasi visual yang menanyakan apakah kasir benar-benar ingin mengakhiri sesi, mencegah tombol tertekan secara tidak sengaja."*

---

### TAHAP 4: Super Administrator (Keamanan 2FA & Tata Kelola)
*(Buka `/login`, masukkan email `admin@summitgear.com` dan kata sandi `password123`)*

- **Apa yang ditunjukkan:**
  1. **Keamanan Baru: Google Authenticator 2FA (Two-Factor Authentication):**
     - *"Bapak/Ibu penguji, akun Administrator adalah akun dengan privilese tertinggi karena dapat mengubah harga, menghapus data, dan melihat seluruh laporan keuangan. Oleh karena itu, saya melengkapi login Admin dengan **Google Authenticator 2FA**."*
     - Buka menu **Pengaturan Sistem** (`/admin/settings`):
       Tunjukkan card *Keamanan 2FA*. Klik *Aktifkan Google Authenticator*.
       Tunjukkan **QR Code SVG** yang di-*render* secara lokal (*offline-ready*).
       Scan menggunakan aplikasi Google Authenticator di smartphone Anda, masukkan 6 digit angka, dan tunjukkan pesan keberhasilan beserta 8 kode darurat (*Recovery Codes*).
     - Lakukan simulasi logout dan login ulang:
       Masukkan email dan password -> Sistem langsung meminta 6 digit kode dari HP -> Masukkan kode dari Google Authenticator -> Berhasil masuk ke Dashboard!
  2. **Dashboard Analytics:**
     Tunjukkan metrik ringkasan omzet bulanan, unit yang sedang disewa, status keterlambatan, dan grafik transaksi.
  3. **Manajemen Pengguna & Otorisasi PIN:**
     Tunjukkan bahwa aksi sensitif (seperti void transaksi atau menghapus data) dilindungi oleh dialog konfirmasi detail dan validasi PIN Admin.
  4. **Audit Trail Log:**
     Setiap login, logout, 2FA, perubahan harga, dan transaksi tercatat detail dengan timestamp dan IP address untuk kepatuhan audit.

---

### TAHAP 5: Gudang & Pemeliharaan Peralatan (Maintenance)
*(Navigasi ke menu Gudang / Kanban: `/admin/maintenance/kanban`)*

- **Apa yang ditunjukkan:**
  - Tunjukkan **Tampilan Tab Segmen Operasional Gudang**:
    *"Peralatan outdoor yang kembali dari gunung tentu kotor oleh lumpur, basah karena hujan, atau mengalami kerusakan kecil. Maka di SummitGear, staf gudang memiliki sistem kontrol mutu (Quality Control) berbasis status:"*
    - **Tab Pembersihan (Cleaning):** Unit tenda/carrier yang baru dikembalikan dan sedang dalam proses pencucian & pengeringan.
    - **Tab Servis (Maintenance):** Unit yang robek, resleting macet, atau frame patah yang sedang diperbaiki.
    - **Tab Sedang Disewa (Rented):** Daftar seluruh unit fisik yang sedang berada di tangan pelanggan beserta tanggal batas pengembalian.
    - **Tab Siap Sewa (Available):** Unit yang sudah 100% bersih, layak pakai, dan siap disewakan kembali.
  - Tunjukkan alur **Check-In Pengembalian Alat**:
    - Staf gudang/kasir mengecek kondisi fisik.
    - Jika ada keterlambatan pengembalian: sistem otomatis menghitung **denda jam keterlambatan** sesuai tarif di pengaturan.
    - Jika ada kerusakan fisik: sistem memotong biaya dari uang jaminan (deposit).
    - Begitu disetujui, unit berpindah ke status *Cleaning/Available*, deposit dikembalikan, dan transaksi ditutup (*COMPLETED*).

---

## 4. Kesimpulan & Penutup
*(Waktu estimasi: 1 - 2 Menit)*

"Bapak/Ibu dewan penguji yang saya hormati,

Sebagai kesimpulan, **SummitGear** bukan sekadar aplikasi kasir biasa, melainkan **ekosistem solusi menyeluruh** untuk industri rental outdoor:
1. **Bagi Pelanggan:** Menghilangkan trauma *'sudah di-acc via chat tapi barang tidak ada'*, memberikan kepastian stok real-time, dan mempermudah reservasi dari mana saja.
2. **Bagi Kasir & Bisnis:** Menghilangkan pencatatan manual, mencegah *double booking*, otomatisasi tarif weekday/weekend, dan kalkulasi denda yang objektif.
3. **Bagi Staf Gudang:** Menjaga siklus mutu peralatan melalui manajemen status pembersihan dan perbaikan yang teratur.
4. **Dari Sisi Keamanan Perangkat Lunak:** Dilindungi oleh standar modern Google Authenticator 2FA, PIN Approval, modal konfirmasi pencegah *human-error*, dan Audit Trail digital yang akuntabel.

Demikian presentasi yang dapat saya sampaikan. Saya sangat terbuka untuk menerima saran, masukan, maupun pertanyaan dari Bapak/Ibu dewan penguji.

Terima kasih atas perhatiannya, selamat pagi/siang."

---

## 5. Antisipasi Pertanyaan Penguji (Q&A Cheatsheet)

| Kemungkinan Pertanyaan | Jawaban Kunci & Elegan |
|---|---|
| **Kenapa tidak pakai sistem keranjang biasa (seperti e-commerce)?** | Rental outdoor memiliki batas waktu (*time-slot/duration*) dan ketersediaan unit fisik bernomor seri (SN), bukan sekadar kuantitas barang statis. Jadi ada konsep penahanan stok (*hold duration*) agar barang yang sudah dipesan tidak diserobot pelanggan walk-in. |
| **Kenapa memilih Flux UI dibanding membuat komponen Tailwind sendiri dari nol?** | Flux UI dibuat native oleh pencipta Livewire (Caleb Porzio), sehingga sinkronisasi state Livewire 3/4 bekerja mulus tanpa lag. Selain itu Flux sudah memenuhi standar aksesibilitas keyboard (WAI-ARIA), modal focus-trap, dan transisi CSS terstandar. |
| **Bagaimana jika pelanggan tidak mengambil barang yang sudah di-booking online?** | Sistem memiliki parameter `hold_duration_hours` (default 2 jam). Jika melewati toleransi, di kasir muncul status kadaluarsa dan tombol *Validasi Batal & Kembalikan Stok* yang secara otomatis mengembalikan seluruh unit fisik ke status *Available* di gudang. |
| **Bagaimana cara kerja Google Authenticator 2FA di SummitGear?** | Menggunakan algoritma standar industri RFC 6238 (TOTP). Kunci rahasia (Secret Key) disimpan terenkripsi di database. QR code dibuat offline menggunakan SVG murni (BaconQrCode), dan aplikasi Google Authenticator menghasilkan 6 digit kode yang berganti setiap 30 detik. |
| **Apa yang terjadi jika HP Admin hilang saat 2FA aktif?** | Saat aktivasi 2FA pertama kali, sistem otomatis memberikan 8 buah *Recovery Codes* cadangan yang dapat dicatat/disimpan admin untuk login darurat satu kali pakai. |
