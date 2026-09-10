# STANDAR OPERASIONAL PROSEDUR (SOP)
## SUMMITGEAR OUTDOOR GEAR RENTAL & POS SYSTEM

| Dokumen No. | Revisi | Tanggal Efektif | Kategori |
| :--- | :--- | :--- | :--- |
| **SOP-SGM-2026-001** | **v2.0 (Sistem Terintegrasi POS & 2FA)** | **September 2026** | **Operasional & IT** |

---

### DAFTAR ISI SOP
1. **SOP-01:** Prosedur Pemesanan & Reservasi Online oleh Pelanggan
2. **SOP-02:** Prosedur Layanan Kasir & Transaksi Sewa Langsung (Walk-In)
3. **SOP-03:** Prosedur Serah Terima Peralatan (Check-Out) & Jaminan Identitas
4. **SOP-04:** Prosedur Pengembalian Peralatan (Check-In), Pemeriksaan Fisik & Restitusi Deposit
5. **SOP-05:** Prosedur Penanganan Keterlambatan & Penetapan Denda Otomatis
6. **SOP-06:** Prosedur Manajemen Gudang, Pembersihan & Pemeliharaan Alat (Maintenance Cycle)
7. **SOP-07:** Prosedur Keamanan Sistem Informasi, Google Authenticator 2FA & Otorisasi PIN Admin

---

### SOP-01: Prosedur Pemesanan & Reservasi Online Pelanggan
* **Tujuan:** Memberikan kepastian ketersediaan alat bagi calon pendaki, menghilangkan miskomunikasi stok, serta mencegah *overbooking*.
* **Penanggung Jawab:** Pelanggan & Kasir Toko.
* **Prosedur:**
  1. Pelanggan mengakses portal publik SummitGear di website resmi.
  2. Pelanggan memeriksa ketersediaan peralatan atau paket sewa bundling hemat secara real-time.
  3. Pelanggan menentukan tanggal mulai sewa (*Start Date*) dan tanggal pengembalian (*Return Date*).
  4. Pelanggan melengkapi data identitas (Nama Lengkap sesuai KTP, Nomor WhatsApp aktif, NIK KTP).
  5. Setelah pesanan dikirim, sistem menerbitkan **Kode Booking Unik** (contoh: `SG-20260909-001`).
  6. **Aturan Hold Stock:** Seluruh unit fisik bernomor seri (SN) yang dipesan otomatis dikunci statusnya dari stok publik selama durasi toleransi (maksimal 2 jam dari jadwal pengambilan yang disepakati).
  7. Jika pelanggan tidak datang ke outlet melewati batas waktu toleransi pengambilan, kasir berhak memvalidasi pembatalan (*Expire & Cancel*) sehingga unit otomatis kembali berstatus Siap Sewa (*Available*).

---

### SOP-02: Prosedur Layanan Kasir & Transaksi Sewa Langsung (Walk-In)
* **Tujuan:** Memastikan alur pelayanan cepat, akurat, tanpa risiko salah hitung tarif hari kerja/akhir pekan.
* **Penanggung Jawab:** Kasir Operasional.
* **Prosedur:**
  1. Kasir login ke sistem POS menggunakan **PIN 6-digit** pribadi di terminal kasir.
  2. Menyapa pelanggan dengan ramah dan menanyakan rencana pendakian serta kebutuhan peralatan.
  3. Kasir mencari dan memasukkan item yang diinginkan ke keranjang transaksi di layar POS.
  4. Kasir memilih nomor seri fisik (*Serial Number*) yang tersedia di rak pajang/gudang.
  5. **Penentuan Tarif Otomatis:** Sistem secara otomatis menerapkan aturan tarif (*Pricing Rules*) harian:
     - Tarif *Weekday* berlaku untuk peminjaman hari Senin s.d. Kamis.
     - Tarif *Weekend* berlaku untuk peminjaman hari Jumat s.d. Minggu.
  6. Kasir menginput nominal uang jaminan (*Security Deposit*) yang disepakati (minimal Rp 50.000 atau sesuai nilai risiko peralatan).
  7. Kasir menerima pembayaran (Tunai / Transfer / QRIS) dan menekan tombol **Proses Transaksi**.
  8. Sistem mencetak 2 rangkap: 1 rangkap struk nota untuk pelanggan, dan 1 rangkap tanda terima untuk arsip kasir.

---

### SOP-03: Prosedur Serah Terima Peralatan (Check-Out) & Jaminan Identitas
* **Tujuan:** Menjamin keaslian identitas penyewa dan memastikan alat diserahkan dalam kondisi 100% layak pakai.
* **Penanggung Jawab:** Kasir & Staf Gudang.
* **Prosedur:**
  1. Kasir meminta identitas asli penyewa (KTP / SIM / Kartu Pelajar Asli) yang masih berlaku.
  2. Kasir mencocokkan wajah penyewa dengan foto pada kartu identitas dan mencatat nomor dokumen pada sistem.
  3. Staf mendampingi penyewa melakukan pengecekan fisik bersama sebelum barang dimasukkan ke tas:
     - **Tenda:** Cek kelengkapan pasak, frame, inner, flysheet (tidak robek/bocor), dan kelancaran resleting.
     - **Carrier/Backpack:** Cek kondisi busa bahu, buckle gesper, dan resleting.
     - **Kompor & Alat Masak:** Cek pemantik api dan fungsi katup gas.
  4. Penyewa menandatangani formulir serah terima yang menyatakan barang diterima dalam keadaan lengkap dan baik.

---

### SOP-04: Prosedur Pengembalian Peralatan (Check-In), Pemeriksaan Fisik & Restitusi Deposit
* **Tujuan:** Memeriksa kelengkapan pasca-pendakian, mendeteksi kerusakan dini, serta mengembalikan deposit secara adil.
* **Penanggung Jawab:** Staf Gudang & Kasir.
* **Prosedur:**
  1. Penyewa menyerahkan seluruh peralatan kembali ke outlet meja pengembalian.
  2. Kasir/Staf Gudang mencari data rental berdasarkan nomor invoice atau nama pelanggan.
  3. Staf melakukan inspeksi mutu (*Quality Control Checklist*):
     - Memeriksa keutuhan setiap unit sesuai nomor seri yang terdaftar.
     - Memeriksa ada/tidaknya robekan, kain terbakar puntung rokok, frame patah, atau pasak hilang.
  4. **Kondisi Normal (Lengkap & Layak):**
     - Staf menyetujui check-in.
     - Uang deposit jaminan dikembalikan penuh 100% kepada penyewa.
     - Unit dipindahkan ke status operasional gudang **Cleaning (Pembersihan)** jika kotor, atau **Available (Siap Sewa)** jika bersih.
  5. **Kondisi Rusak/Hilang:**
     - Staf mencatat jenis kerusakan pada catatan kondisi unit.
     - Sistem memotong biaya ganti rugi sesuai nilai penggantian (*Replacement Value*) dari uang deposit.
     - Jika deposit tidak mencukupi, penyewa wajib membayar sisa selisih biaya perbaikan/penggantian.

---

### SOP-05: Prosedur Penanganan Keterlambatan & Penetapan Denda Otomatis
* **Tujuan:** Mencegah keterlambatan pengembalian yang merugikan penyewa berikutnya melalui aturan denda yang adil dan transparan.
* **Penanggung Jawab:** Sistem POS & Kasir.
* **Prosedur:**
  1. Batas waktu pengembalian tercatat jelas pada struk sewa (pukul 20.00 WIB pada tanggal selesai sewa).
  2. Jika unit belum kembali setelah melewati batas waktu, status rental otomatis berubah menjadi **OVERDUE (Terlambat)** pada sistem.
  3. **Penghitungan Denda Otomatis:** Sistem SummitGear menghitung denda per jam secara transparan sesuai tarif pengaturan sistem (contoh: Rp 5.000 / jam keterlambatan).
  4. Kasir dilarang memanipulasi atau menghapus denda tanpa persetujuan **Super Admin** melalui mekanisme **PIN Approval Otorisasi**.
  5. Total denda dipotong langsung dari deposit jaminan pelanggan saat pengembalian dilakukan.

---

### SOP-06: Prosedur Manajemen Gudang & Pemeliharaan Alat (Maintenance Cycle)
* **Tujuan:** Menjaga kebersihan, higienitas, dan keawetan seluruh aset peralatan outdoor.
* **Penanggung Jawab:** Staf Gudang (Maintenance Team).
* **Prosedur:**
  1. Staf gudang memantau papan operasional pada halaman **Manajemen Gudang / Kanban**.
  2. **Siklus Pembersihan (Cleaning):**
     - Setiap tenda, sleeping bag, matras, dan peralatan masak yang baru kembali wajib dibersihkan.
     - Tenda dibentangkan, dicuci dengan air mengalir tanpa deterjen keras, dan dikeringkan hingga tuntas agar tidak berjamur.
     - Sleeping bag diangin-anginkan dan disterilisasi.
     - Setelah kering dan bersih, staf gudang menekan tombol **Selesai Cuci & Masukkan ke Stok Siap Sewa**, status unit otomatis berubah menjadi **Available**.
  3. **Siklus Perbaikan (Maintenance):**
     - Unit yang membutuhkan perbaikan (jahit ulang tali carrier, ganti resleting, ganti tali elastis frame) dipindahkan ke status **Maintenance**.
     - Setelah diperbaiki dan diuji beban, staf memperbarui catatan fisik dan mengembalikannya ke status **Available**.

---

### SOP-07: Prosedur Keamanan Sistem Informasi, Google Authenticator 2FA & Otorisasi PIN
* **Tujuan:** Menjamin integritas data transaksi keuangan, mencegah peretasan akun, serta memitigasi kesalahan manusia (*human error*).
* **Penanggung Jawab:** Super Administrator & Seluruh Staf.
* **Prosedur:**
  1. **Autentikasi 2FA Google Authenticator (Wajib bagi Admin):**
     - Setiap akun Administrator wajib mengaktifkan 2FA melalui menu Pengaturan Akun.
     - Saat login dengan email dan kata sandi benar, sistem meminta kode 6 digit dari aplikasi Google Authenticator pada smartphone Administrator.
     - Administrator wajib mencatat dan menyimpan 8 kode pemulihan (*Recovery Codes*) di tempat fisik yang aman untuk kebutuhan darurat.
  2. **Otorisasi PIN untuk Tindakan Kritis:**
     - Tindakan sensitif seperti: Pembatalan transaksi (*Void Invoice*), Perubahan role pengguna, dan Penghapusan data master dilindungi oleh **PIN Approval 6-digit Super Admin**.
  3. **Pencegahan Salah Klik (Validation Modals):**
     - Setiap aksi Logout dan Penghapusan data di seluruh sistem wajib melalui modal konfirmasi visual yang merinci objek yang dihapus/dibatalkan.
  4. **Audit Trail Digital:**
     - Sistem secara otomatis merekam setiap login, logout, 2FA event, void, dan modifikasi harga ke dalam tabel `audit_logs` yang tidak dapat dimanipulasi.
