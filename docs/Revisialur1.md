# RevisiAlur1.md — Revisi Besar Alur SummitGear POS

**Status:** Disepakati dengan Owner, siap dieksekusi
**Konteks:** Aplikasi saat ini sudah setengah jalan dibangun (Fase 1–3 versi awal). Dokumen ini adalah **delta/perubahan**, bukan PRD dari nol. Baca seluruh dokumen ini dulu sebelum mengubah kode apapun, supaya paham bagian mana yang tetap dipakai dan bagian mana yang diganti.

---

## 0. ATURAN PALING PENTING UNTUK AI YANG MENGERJAKAN INI

1. **JANGAN rombak ulang** modul yang tidak disebut di dokumen ini. Yang TIDAK berubah: RBAC/role permission dasar, PIN Approval untuk void transaksi, struktur data `audit_logs` (cuma tampilan sidebar-nya yang di-split, datanya tetap tabel yang sama — lihat Perubahan #4), Checklist QC check-out/check-in (tanpa foto — ini sudah benar sebelumnya, tetap dipakai), Kanban Maintenance, Dashboard Analitik, exclusion constraint booking kalender, cron job denda telat.
2. **Baca dulu section "Pertanyaan yang Perlu Dikonfirmasi"** di bagian akhir dokumen ini sebelum eksekusi — kalau ada yang ambigu terhadap kode yang sudah ada, tanyakan ke Owner dulu, jangan asumsi sendiri.
3. Kalau ada kode/tabel yang disebut "HAPUS" di bawah, cek dulu apakah ada bagian lain sistem yang masih bergantung padanya sebelum benar-benar dihapus (misal foreign key, job scheduler yang mereferensikannya).
4. Tulis laporan hasil eksekusi dengan jujur — kalau suatu bagian belum sempat diuji end-to-end, katakan itu belum diuji, jangan tulis "100% selesai" kalau yang diverifikasi baru sebatas halaman bisa diakses.

---

## 1. Ringkasan Perubahan (Before → After)

| Area | Sebelumnya (yang mungkin sudah dibangun) | Sekarang (revisi) |
|---|---|---|
| UI Transaksi Kasir | Form multi-step/tab ala admin panel (pilih pelanggan → tanggal → validasi → dst) | Grid produk ala kasir minimarket: foto barang + jumlah tersedia real-time, klik untuk tambah ke keranjang |
| Dokumentasi KTP | Upload foto KTP, disimpan terenkripsi, ada retention job hapus otomatis | **Tidak ada foto sama sekali.** Cukup checkbox validasi "KTP sudah diterima? Ya/Tidak" per transaksi |
| Peran Pelanggan | Tidak ada, transaksi selalu dibuat kasir (walk-in) | Tetap tidak ada akun/login pelanggan, TAPI ditambah **booking online tanpa login** via landing page publik |
| Scope Booking Online | Out-of-scope, kandidat Fase 4 | **Masuk ke scope aktif sekarang** (gabung ke Fase 2) |
| Login Kasir | Email + password (sama seperti Admin/Gudang) | Tombol khusus "Masuk sebagai Kasir (PIN)", PIN unik per kasir, langsung ke halaman grid kasir |
| Log Audit Admin | 1 halaman log gabungan | Sidebar "Log Aktivitas" dengan dropdown: Log Kasir & Log Gudang terpisah |

---

## 2. Perubahan #1 — UI Kasir Jadi Grid Ala Minimarket

### Alasan
Tampilan sebelumnya (form/tab) terasa seperti dashboard admin, bukan kasir yang harus melayani pelanggan dengan cepat.

### Yang Diganti
Ganti seluruh komponen pembuatan transaksi (`Transaction/Create` atau sejenisnya yang sudah dibangun sebelumnya, apapun namanya di kode saat ini) dengan alur berikut:

1. **Halaman utama kasir = grid produk**, bukan form. Setiap kartu produk menampilkan:
   - Foto barang (field baru, lihat section Skema Database)
   - Nama barang
   - Badge jumlah tersedia, dihitung **real-time**: `COUNT(item_units) WHERE item_id = X AND status = 'Available'`
2. **Klik produk → sistem otomatis assign 1 unit fisik yang berstatus `Available`** ke keranjang. Kasir **tidak perlu pilih serial number manual** — itu logic backend (ambil unit `Available` mana saja, urutan bebas, misal berdasarkan `id` terkecil).
3. Kasir bisa klik beberapa produk berbeda → semua masuk ke satu keranjang (multi-item per transaksi, ini fitur yang sebelumnya sudah pernah disebut sebagai "keranjang sewa" — tetap dipertahankan konsepnya, cuma cara pemilihan barangnya yang berubah dari form ke grid).
4. Checkout dari keranjang → lanjut ke bagian jaminan (lihat Perubahan #2) → pembayaran → invoice (bagian ini **tidak berubah** dari yang sudah dirancang sebelumnya).

### Konsekuensi Integrasi dengan Gudang
Karena grid kasir dan Kanban Gudang membaca kolom `status` di tabel `item_units` yang sama, begitu Gudang mengubah status unit (misal dari `Cleaning` ke `Available`), angka "tersedia" di grid kasir **otomatis ikut berubah** tanpa perlu sinkronisasi tambahan. Tidak ada pekerjaan ekstra di sisi ini — pastikan saja query grid kasir memang query real-time (bukan cache lama).

---

## 3. Perubahan #2 — KTP Tanpa Foto, Cukup Validasi Checkbox

### Alasan
Owner memutuskan tidak perlu ada foto digital untuk dokumen identitas. Verifikasi KTP dilakukan manual secara fisik oleh kasir, KTP asli disimpan di brankas kasir (bukan di sistem).

### Yang DIHAPUS (kalau sudah sempat dibangun)
- Kolom `customers.id_photo_url` — **hapus**.
- Fitur upload foto KTP di form pelanggan (termasuk logic enkripsi nama file yang sempat disebut sebelumnya) — **hapus**.
- Tabel `document_access_log` beserta model dan semua referensinya — **hapus seluruhnya**. Tabel ini tidak relevan lagi karena tidak ada foto digital yang perlu dilacak aksesnya.
- Bagian dari `DataRetentionJob` (atau job sejenis) yang khusus menghapus/blur foto KTP — **hapus logic ini**. Kalau job yang sama juga menangani hal lain, cukup hapus bagian KTP-nya saja, jangan hapus seluruh job kalau masih dipakai untuk hal lain.
- Setup Object Storage (S3/R2) yang tadinya wajib khusus untuk foto KTP — **jadi opsional**, tidak wajib untuk Fase 1 lagi (karena baik foto kondisi barang maupun foto KTP sekarang sama-sama tidak dipakai di sistem).

### Yang DITAMBAHKAN sebagai gantinya
Validasi KTP diganti jadi 1 tombol/checkbox di alur checkout transaksi: **"KTP sudah diterima? Ya/Tidak"**. Kalau dicentang "Ya", sistem insert 1 baris ke tabel `deposits` yang **sudah ada** (tidak perlu tabel baru):
```
deposits: 
  rental_id = (transaksi ini)
  type = 'dokumen'
  doc_type = 'KTP'
  status = 'diterima'
```
Checkbox ini **wajib dicentang sebelum kasir bisa lanjut ke pembayaran** (validasi blocking, sama seperti validasi wajib lainnya di form).

**Catatan penting:** validasi ini dicatat **per transaksi** (per `rental_id`), bukan sekali per profil pelanggan — karena KTP fisik dipegang & dikembalikan setiap transaksi, bukan cuma dicek sekali di awal jadi pelanggan.

---

## 4. Perubahan #3 — Booking Online Tanpa Login (Landing Page Publik)

### Alasan
Pelanggan yang mau booking dari rumah tidak harus datang dulu ke toko, tapi tidak perlu ada sistem akun/login pelanggan (dianggap menambah friksi yang tidak perlu).

### Perubahan Scope
Fitur ini **sebelumnya ditandai Out-of-scope/Fase 4**, sekarang **resmi masuk Fase 2** (digabung dengan pekerjaan kalender booking yang sudah direncanakan di fase itu).

### Alur Baru
1. **Landing page publik** (di luar area login Admin/Kasir/Gudang, bisa diakses siapa saja tanpa autentikasi) menampilkan grid produk yang sama datanya dengan grid kasir (foto + jumlah tersedia).
2. Pelanggan pilih barang yang diinginkan + pilih tanggal pemakaian, lalu klik "Booking".
3. Sebelum booking disubmit, wajib isi form singkat: **Nama, No HP, Alamat** (memakai tabel `customers` yang sudah ada — **tidak perlu tabel/role pelanggan baru sama sekali**, karena tabel ini dari awal memang bukan akun berpassword).
4. **Tidak ada upload foto KTP di form booking online ini** — konsisten dengan Perubahan #2. Verifikasi KTP tetap dilakukan manual saat pelanggan datang mengambil barang di toko.
5. Setelah submit, sistem:
   - Membuat baris baru di `rentals` dengan `status = 'Booked'` dan **field baru** `source = 'online'`.
   - Unit fisik yang dipilih otomatis berubah status ke `Booked` (mengurangi jumlah "tersedia" di grid kasir & landing page — state machine unit yang sudah ada, tidak perlu logic baru).

### Halaman Baru untuk Kasir: "Booking Masuk"
Halaman khusus role Kasir/Admin untuk memantau booking yang masuk dari online:
- List berbentuk **card**, tiap card menampilkan: Nama, No HP, Alamat, dan **badge angka di pojok kanan atas** = jumlah barang yang dibooking dalam transaksi itu.
- Klik card → expand menampilkan detail lengkap: daftar barang yang dibooking + **tanggal pemakaian** (supaya kasir/gudang bisa siapkan barangnya sebelum hari-H).
- Tombol **"Batalkan"** di card ini (bisa dipakai Kasir atau Admin): mengubah `rentals.status` jadi `cancelled` DAN mengembalikan status unit fisik terkait kembali ke `Available` (menambah stok gudang lagi).

### Anti-Spam / Booking Iseng (untuk MVP, jangan over-engineer dulu)
- Rate limit sederhana per IP/device: maksimal 3 booking per jam.
- Proses konfirmasi tetap manual: kasir/admin boleh telepon/WA nomor yang diisi untuk konfirmasi sebelum benar-benar menyiapkan barang.
- **Verifikasi OTP via WhatsApp BUKAN untuk Fase ini** — ini peningkatan di masa depan kalau booking iseng ternyata jadi masalah nyata, jangan dibangun sekarang.

### Auto-Expire Booking yang Tidak Dikonfirmasi
Booking online yang sudah lewat **24 jam** tanpa ada aksi kasir (belum dikonfirmasi/dibatalkan manual) → job terjadwal otomatis mengubah status jadi `expired` dan mengembalikan unit ke `Available`. Ini reuse pola yang sama dengan job `ReleaseHoldBookingJob` yang sudah ada untuk DP hold — buat job serupa atau perluas job yang sudah ada, jangan bangun sistem terpisah dari nol.

---

## 5. Perubahan #4 — Login Kasir via PIN & Split Log Aktivitas

### Alasan
Sejak PRD awal, Owner sudah menyatakan ingin proses login Kasir cukup pakai PIN (bukan email+password), supaya lebih cepat dipakai di tablet kasir. Selain itu, Admin butuh cara memantau log aktivitas yang lebih terorganisir per role, dan bisa tahu "PIN siapa" yang aktif saat sebuah kejadian/kesalahan terjadi.

### 5.1 Login Kasir via PIN

**Alur baru di halaman login:**
1. Tambahkan tombol/link baru di halaman login utama: **"Masuk sebagai Kasir (PIN)"**.
2. Klik tombol ini membawa ke halaman input PIN (numpad sederhana, bukan form email/password).
3. Kalau PIN yang dimasukkan cocok dengan PIN aktif milik salah satu user berrole Kasir, sistem otomatis login sebagai user tersebut dan langsung diarahkan ke halaman grid kasir (Perubahan #1).
4. Kalau PIN salah, tampilkan pesan generik ("PIN salah") — **jangan bedakan pesan** antara "PIN tidak ditemukan" vs "PIN ditemukan tapi ada masalah lain", supaya konsisten dengan prinsip anti user-enumeration yang sudah dibahas di analisis keamanan sebelumnya.
5. **Login Admin dan Gudang TIDAK berubah** — tetap pakai email/password seperti sebelumnya. Fitur PIN login ini **khusus role Kasir**. *(Konfirmasi asumsi ini ke Owner — lihat section 8.)*

**Wajib — proteksi keamanan untuk endpoint PIN login ini** (karena ini sekarang jadi mekanisme login utama Kasir, bukan cuma tombol approval sesekali):
- Rate limiting ketat (misal maksimal 5 percobaan salah lalu lock beberapa menit) — PIN jauh lebih pendek dari password, jadi jauh lebih gampang ditebak kalau tidak dibatasi.
- Input PIN pakai masked input (titik/bintang), bukan angka polos yang kelihatan di layar tablet.

### 5.2 PIN Per-Kasir (Bukan PIN Global)

Owner minta setiap Kasir punya **PIN sendiri-sendiri**, bukan 1 PIN yang dipakai bersama, supaya log aktivitas bisa tahu persis kasir mana yang sedang bertugas.

**Cara paling sederhana (rekomendasi saya)**, memakai struktur yang sudah ada: kolom `users.pin` yang sudah dirancang dari Fase 1.2 memang **sudah per-user**, bukan global — jadi selama form "Tambah Kasir" (CRUD `users` yang sudah dibangun Admin) mengisi field `pin` unik untuk tiap kasir, requirement ini **sudah otomatis terpenuhi tanpa perlu fitur baru**. Yang perlu ditambahkan cuma:
- **Validasi unique PIN** di form Tambah/Edit Kasir — sistem harus menolak kalau Admin coba kasih PIN yang sama ke 2 kasir aktif berbeda.
- Field form yang Owner sebut ("nama kasir" + "PIN yang didapat") **kemungkinan besar ini adalah form CRUD user yang sudah ada**, bukan fitur terpisah — pastikan cuma ada 1 tempat untuk mengelola ini, jangan sampai dibangun form baru yang duplikat sama form kelola user yang sudah ada.

**Soal kata "shift"**: Owner menyebut ini sebagai "bikin shift", yang bisa berarti 2 hal berbeda — dan ini **perlu dikonfirmasi** (lihat section 8) karena bedanya cukup besar dari sisi effort:
- **Interpretasi A (lebih ringan, direkomendasikan untuk mulai)**: "shift" di sini cuma istilah sehari-hari untuk "kasir yang sedang bertugas", dan cukup diselesaikan dengan PIN unik per user seperti di atas — histori "shift siapa" otomatis kebaca dari `audit_logs.user_id` yang sudah ada.
- **Interpretasi B (lebih berat)**: Owner benar-benar mau tabel `shifts` terpisah dengan waktu mulai/selesai eksplisit (misal 1 kasir bisa "clock-in" pagi dan "clock-out" sore, dan PIN bisa di-generate ulang tiap shift/hari, bukan permanen per orang). Ini butuh tabel baru (`shifts`: id, user_id, pin, started_at, ended_at) dan alur clock-in/clock-out terpisah dari sekadar login biasa.

### 5.3 Split "Log Aktivitas" di Sidebar Admin

Ganti nama menu sidebar Admin yang sebelumnya "Log Audit" (atau sejenisnya) jadi **"Log Aktivitas"**, dengan dropdown 2 sub-menu:
- **Log Kasir** — tampilkan `audit_logs` yang dilakukan oleh user berrole Kasir (transaksi, void, check-out/in, pembatalan booking, login/logout PIN).
- **Log Gudang** — tampilkan `audit_logs` yang dilakukan oleh user berrole Gudang (update status unit, maintenance log, checklist QC).

Ini **tidak butuh tabel baru** — cukup filter query `audit_logs` yang sudah ada, di-join ke `users.role` untuk menentukan masuk ke tab yang mana. Tambahkan juga: setiap kali kasir login/logout via PIN, insert 1 baris ke `audit_logs` (`action = 'login'` / `'logout'`) supaya rentang waktu "shift" seorang kasir tetap tercatat walau kita pakai Interpretasi A (tanpa tabel shift terpisah).

**Catatan:** dokumen ini belum menyebutkan "Log Admin" terpisah — aksi Admin sendiri (ubah harga, ubah settings, approve PIN) untuk sementara tidak difilter ke tab manapun. Konfirmasi ke Owner (section 8) apakah ini perlu tab ke-3, atau memang sengaja tidak perlu karena Admin toh sudah punya akses penuh untuk audit semuanya.

---

## 6. Perubahan Skema Database (Migration Delta)

**Tambahkan kolom/tabel baru:**
```
inventory_items.photo_url       (string, nullable)   -- untuk grid kasir & landing page
customers.address               (string, nullable)   -- dibutuhkan untuk form booking online
rentals.source                  (string, default 'walk_in')  -- nilai: 'walk_in' | 'online'
```
**Untuk Perubahan #4 (PIN & Shift)** — tergantung jawaban konfirmasi di section 8 poin 6:
```
-- Kalau Interpretasi A (rekomendasi): TIDAK PERLU migration baru,
-- cukup tambahkan validasi unique constraint di kolom users.pin yang sudah ada.

-- Kalau Interpretasi B: tabel baru
shifts.id, shifts.user_id (FK -> users), shifts.pin, shifts.started_at, shifts.ended_at
```
Pastikan `rentals.status` mendukung nilai tambahan: `'cancelled'` dan `'expired'` (kalau kolom ini berupa enum yang sudah didefinisikan, tambahkan; kalau string bebas, tidak perlu migration, cukup pastikan konsisten di logic aplikasi).

**Hapus kolom/tabel (kalau sudah sempat dibuat):**
```
customers.id_photo_url          -- HAPUS
document_access_logs (seluruh tabel)  -- HAPUS beserta model & referensinya
```

**Tidak berubah, tetap dipakai:** `deposits` (dipakai ulang untuk validasi KTP, lihat Perubahan #2), `item_units.status` (state machine sudah benar, tidak berubah), semua tabel finansial (`payments`, `penalties`), semua tabel maintenance & audit.

---

## 7. Dampak ke Role Admin & Gudang (Antisipasi)

Perubahan ini tidak cuma berdampak ke Kasir. Berikut yang perlu diantisipasi di role lain supaya tidak kelewat saat eksekusi.

### 7.1 Dampak ke Admin

1. **Form Master Barang butuh field baru: upload foto (`photo_url`)**, dipakai grid kasir & landing page (Perubahan #1 dan #3). Ini foto katalog barang biasa (bukan foto kondisi/KTP), jadi **tidak perlu enkripsi atau storage khusus** — cukup disk storage publik biasa, karena bukan data sensitif seperti KTP.
2. **Halaman Detail Pelanggan (Admin) — hapus elemen "Lihat Foto KTP"** kalau sudah sempat dibangun, karena field `id_photo_url` dihapus (lihat Perubahan #2). Gantikan dengan riwayat validasi KTP per transaksi, diambil dari tabel `deposits` yang `type = 'dokumen'`.
3. **Tambahan baris `settings` baru** yang perlu bisa diatur Admin, konsisten dengan pola `booking_window_days` yang sudah ada:
   - `online_booking_expire_hours` — default `24`, durasi sebelum booking online yang belum dikonfirmasi otomatis di-expire (ini jawaban ke pertanyaan soal durasi expire — dibuat konfigurasi, bukan hardcode di kode).
4. **Dashboard Analitik — opsional, nilai tambah kecil**: tambahkan breakdown transaksi berdasarkan `rentals.source` (`walk_in` vs `online`), termasuk tingkat konversi booking online (jadi transaksi vs dibatalkan/`expired`). Tidak wajib di rilis pertama, tapi datanya sudah otomatis tersedia begitu kolom `source` ditambahkan.
5. **Audit log untuk pembatalan booking online**: pastikan aksi "Batalkan" di halaman Booking Masuk (oleh Kasir maupun Admin) tetap tercatat di `audit_logs` (action: `cancel_booking`). Beda dengan void transaksi finansial, aksi ini **diasumsikan tidak perlu PIN Approval** karena belum ada uang berpindah tangan saat booking online dibuat (pembayaran baru terjadi saat pelanggan datang ke toko) — **tapi asumsi ini wajib dikonfirmasi dulu ke Owner**, lihat pertanyaan tambahan di section 8.

### 7.2 Dampak ke Gudang

1. **Kalender/jadwal Gudang harus menampilkan booking dari kedua sumber** (`walk_in` booking di muka maupun `online`), bukan cuma salah satu. Kalau fitur kalender untuk Gudang sudah dibangun sebelumnya, pastikan query-nya tidak sengaja memfilter cuma `source = 'walk_in'`.
2. **Gudang perlu akses (read-only) ke halaman "Booking Masuk"** — bukan cuma Kasir. Alasannya: Gudang butuh tahu tanggal pemakaian dari booking yang masuk supaya bisa prioritaskan pencucian/penyiapan unit sebelum tanggal itu tiba. Gudang **tidak perlu** bisa membatalkan booking (tetap wewenang Kasir/Admin), cukup melihat.
3. **Tidak ada perubahan pada alur Kanban status unit** (`Cleaning → Service → Available`, dst). Status `Booked` dari booking online sudah otomatis mengurangi hitungan "tersedia" tanpa langkah manual tambahan dari Gudang. Yang berubah cuma **informasi jadwal** yang mereka lihat (poin 1 & 2), bukan alur kerja fisiknya.
4. **Checklist QC check-out/check-in tidak berubah sama sekali** dari yang sudah dirancang sebelumnya (tanpa foto, pakai kategori kondisi) — baik untuk transaksi walk-in maupun yang asalnya dari booking online, begitu pelanggan datang ambil barang, alurnya persis sama.

---

## 8. Pertanyaan yang Perlu Dikonfirmasi Sebelum Eksekusi

Kalau ada bagian kode yang sudah dibangun dan salah satu poin di bawah ini ambigu terhadap kode tersebut, **tanyakan ke Owner dulu**, jangan asumsi:

1. Apakah komponen transaksi kasir yang sudah dibangun sebelumnya (form/tab multi-step) akan **diganti total** dengan grid, atau ada bagian logic di dalamnya (misal kalkulasi harga dari `pricing_rules`) yang bisa **dipakai ulang**, cuma UI pembungkusnya yang diganti?
2. Apakah `DataRetentionJob` yang sudah ada murni khusus untuk foto KTP (sehingga job itu bisa dihapus total), atau job itu juga menangani hal lain yang masih relevan?
3. Landing page publik ini apakah perlu domain/subdomain terpisah, atau cukup route publik di aplikasi Laravel yang sama (tanpa middleware auth)?
4. Auto-expire booking 24 jam — apakah durasi ini final, atau sebaiknya jadi konfigurasi Admin juga (seperti `booking_window_days` dan `hold_duration_hours` yang sudah ada di tabel `settings`)? *(Saran: jadikan konfigurasi juga, supaya konsisten dengan pola yang sudah ada — tapi konfirmasi dulu ke Owner.)*
5. **Pembatalan booking online apakah perlu PIN Approval Admin atau tidak?** Asumsi di section 7.1 poin 5: tidak perlu, karena belum ada uang yang berpindah tangan. Kalau ternyata alur bisnisnya membolehkan pelanggan bayar DP saat booking online (bukan cuma isi data), maka asumsi ini salah dan pembatalan **wajib** pakai PIN Approval + proses refund — ini menentukan apakah perlu logic tambahan yang belum dibahas di dokumen ini.
6. **"Bikin shift" itu maksudnya Interpretasi A atau B (lihat section 5.2)?** Ini menentukan apakah cukup pakai PIN unik per user (`users.pin`, ringan) atau perlu tabel `shifts` terpisah dengan clock-in/clock-out (lebih berat). Jangan bangun yang B kalau ternyata Owner cuma maksud yang A.
7. Apakah role Gudang juga perlu opsi login via PIN, atau memang cuma Kasir yang berubah (Admin & Gudang tetap email/password)?
8. Apakah "Log Aktivitas" perlu tab ke-3 untuk aksi Admin sendiri, atau sengaja cuma 2 tab (Kasir & Gudang) sesuai yang diminta?

---

## 9. Urutan Eksekusi yang Disarankan

1. Migration delta (tambah kolom baru: `inventory_items.photo_url`, `customers.address`, `rentals.source`; hapus `customers.id_photo_url` & tabel `document_access_logs`; seed baris baru `settings.online_booking_expire_hours`; tambah unique constraint di `users.pin`, atau buat tabel `shifts` kalau jawaban section 8 poin 6 adalah Interpretasi B) — lakukan ini duluan sebelum ubah UI, supaya tidak ada mismatch skema saat development UI berjalan.
2. Hapus fitur upload/enkripsi foto KTP + `document_access_logs` + bagian job retensi terkait.
3. **[Admin]** Tambahkan field upload foto ke Form Master Barang, dan hapus elemen "Lihat Foto KTP" dari Detail Pelanggan.
4. **[Admin]** Tambahkan UI untuk atur `online_booking_expire_hours` di halaman Settings.
5. Bangun halaman login PIN untuk Kasir (tombol di halaman login utama → numpad PIN → auto-login ke grid kasir), lengkap dengan rate limiting.
6. **[Admin]** Pastikan form Tambah/Edit Kasir sudah validasi PIN unik antar kasir aktif.
7. **[Admin]** Ubah menu sidebar "Log Audit" jadi "Log Aktivitas" dengan dropdown Log Kasir & Log Gudang (filter dari `audit_logs` berdasarkan role), tambahkan pencatatan event login/logout PIN.
8. Bangun ulang UI grid kasir (menggantikan form lama).
9. Tambahkan checkbox validasi KTP di alur checkout (memakai tabel `deposits` yang sudah ada).
10. Bangun landing page publik + form booking online.
11. Bangun halaman "Booking Masuk" untuk Kasir, plus versi read-only untuk Gudang.
12. **[Gudang]** Pastikan kalender/jadwal Gudang yang sudah ada menampilkan booking dari kedua sumber (`walk_in` & `online`), bukan cuma satu.
13. Bangun job auto-expire booking (reuse pola `ReleaseHoldBookingJob`, durasi ambil dari `settings.online_booking_expire_hours`).
14. Testing menyeluruh end-to-end untuk **seluruh alur baru** ini secara khusus (jangan cuma test "halaman bisa diakses" — test logic: grid menampilkan jumlah tersedia yang benar, checkbox KTP wajib sebelum bayar, unit fisik berubah status dengan benar saat booking online masuk & saat dibatalkan/expired, kalender Gudang menampilkan kedua sumber booking, audit log pembatalan tercatat, login PIN menolak PIN salah & mengunci setelah beberapa kali gagal, Log Kasir/Log Gudang menampilkan data yang benar sesuai role).