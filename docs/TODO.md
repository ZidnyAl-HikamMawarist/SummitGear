# SummitGear — Roadmap & Execution Checklist

## 🗺️ Execution Status (Subgraphs 1-3 Completed)

---

### ✅ Subgraph 1: Critical Bug Fixes & Data Integrity
- [x] **Payment Reminder Calculation Fix**: Menggunakan `$rental->balance_due` (bukan `$rental->total_price`) pada `SendPickupReminderJob` agar nominal pelunasan yang ditagihkan ke pelanggan akurat setelah memperhitungkan DP.
- [x] **Deposit Penalty Deduction Payment Record**: Mengotomatisasi pencatatan entri tabel `payments` (`type = 'penalty'`, `method = 'DEPOSIT_DEDUCTION'`) saat denda/kerusakan dipotong langsung dari uang jaminan (deposit cash).
- [x] **Enum & Model Synchronization**: Sinkronisasi 12 status penyewaan antara `RentalStatus` enum dan konstanta `Rental` (`BOOKED`, `DP_PAID`, `PAID`, `PICKED_UP`, `RENTED_OUT`, `OVERDUE`, `PENDING_SETTLEMENT`, `DISPUTED`, `COMPLETED`, `CANCELLED`, `VOID`, `DEFAULTED`).
- [x] **Protected Unit Deletion**: Mencegah penghapusan unit fisik di `UnitIndex` jika unit masih memiliki riwayat sewa aktif atau terjadwal di masa depan (`status NOT IN ('COMPLETED', 'CANCELLED', 'VOID')`).
- [x] **Customer NIK Overwrite Protection**: Melindungi data NIK pelanggan lama saat nomor telepon digunakan kembali oleh pengguna yang berbeda pada `Booking.php`.
- [x] **Void Audit Log**: Pencatatan alasan pembatalan (VOID) transaksi secara permanen ke `audit_logs` dan `rentals.settlement_notes`.
- [x] **Calendar Freeing on Early Return**: Unit yang dikembalikan lebih awal (`return_time < end_date`) langsung dibebaskan dari kalender operasional (`CalendarIndex.php`).
- [x] **Analytics Discrepancy Fix**: Memperbaiki kalkulasi dispute rate dan pendapatan di `Dashboard.php` agar denda tidak dihitung ganda.

---

### ✅ Subgraph 2: Cashier Flow & SOP Alignment
- [x] **Cash Security Deposit in POS**: Kasir dapat menerima uang jaminan fisik (cash deposit) saat pembuatan transaksi di POS (`Create.php`), tercatat otomatis sebagai `Deposit (type = 'CASH', status = 'HELD')`.
- [x] **Live Blacklist Alert in POS**: Notifikasi real-time di POS jika pelanggan yang dipilih memiliki catatan piutang macet / blacklist, memblokir pembuatan transaksi baru sebelum diselesaikan.
- [x] **Mandatory KTP Verification at Handover**: Halaman check-out serah terima alat mewajibkan verifikasi fisik KTP atau penahanan deposit uang sebelum status sewa berubah menjadi `RENTED_OUT`.
- [x] **Flexible Damage Fee Estimation**: Petugas QC saat check-in dapat menginput estimasi biaya perbaikan/laundry riil (`damage_cost`) secara fleksibel per unit, bukan hanya potongan statis 30%.

---

### ✅ Subgraph 3: Contingency Modules (Kasus Tak Terduga di Lapangan)
- [x] **Unit Swap / Reassignment at Checkout**: Fitur penggantian unit rusak/kotor secara instan di form Handover (`CheckOut.php`) tanpa membatalkan transaksi sewa.
- [x] **Rental Extension on Mountain**: Fitur perpanjangan durasi sewa saat alat masih di gunung via `Invoice.php`, lengkap dengan deteksi tabrakan jadwal sewa pelanggan berikutnya dan rekalkulasi otomatis.
- [x] **Late Pickup Tolerance Extension**: Tombol perpanjangan toleransi ambil alat (+2 jam / custom) di `IncomingBooking.php`, mencegah `ExpireOnlineBookingJob` membatalkan booking secara prematur.
- [x] **Bad Debt Write-Off & Auto-Blacklist**: Penutupan sengketa macet dengan otorisasi PIN supervisor di `Settlement.php`, menyita deposit yang ditahan, mengubah status transaksi menjadi `DEFAULTED`, dan memasukkan NIK/telepon pelanggan ke daftar hitam.
- [x] **Paid Booking Cancellation & Refund Flow**: Pengelolaan pembatalan sewa online ber-DP dengan opsi "DP Hangus" atau "Refund Pelanggan" dengan verifikasi PIN supervisor.
- [x] **Chain-Booking Collision Detection**: Deteksi dini pada `CalculateLatePenaltyJob` untuk menandai unit terlambat yang sudah dipesan oleh pelanggan lain dalam waktu 48 jam ke depan.

---

### ✅ Fase UI/UX Overhaul & Logo Harmonization
- [x] **Universal App Logo Component (`<x-app-logo>`)**: Vektorisasi presisi SVG dari logo resmi referensi pengguna (Squircle Midnight Navy + Volcanic Lava Orange peak & ascending trail ridge), menggantikan semua logo acak di Landing Page, Admin Sidebar, Kasir POS, Gudang, dan Login.
- [x] **Chrome Tab Favicon (`favicon.svg`)**: Pasang ikon resmi SummitGear di `<head>` semua layout (`app.blade.php`, `guest.blade.php`) sehingga logo muncul otomatis di tab browser Chrome di sebelah kiri judul.
- [x] **POS Terminal Keyboard Shortcuts**: Tambah listener shortcut keyboard `F2` (fokus cari alat/SKU) dan `F4` (modal/input pembayaran kasir) serta shortcut badges pada UI kasir.
- [x] **Quick Cash Numpad Chips in POS**: Tambah tombol cepat uang pas (`Pas`, `Rp 50.000`, `Rp 100.000`) untuk mempercepat kasir menyelesaikan transaksi tanpa ketik angka manual.
- [x] **Mobile Floating Cart Bar in Public Booking**: Tambah sticky bottom bar di layar smartphone yang menampilkan jumlah barang dan subtotal saat pengguna memilih alat dari katalog.
- [x] **QC 1-Click "Semua Unit Baik" Bulk Action**: Tambah tombol aksi cepat di `CheckIn.php` dan `check-in.blade.php` untuk menandai seluruh unit rombongan dalam kondisi baik secara instan.

---

## 🔮 Next Phase (Subgraph 4 & Seterusnya)
- [ ] Integrasi webhook payment gateway (Xendit/Midtrans) otomatis untuk auto-reconciliation DP online booking.
- [ ] Export laporan keuangan piutang macet (bad debts) & utilisasi inventaris dalam format Excel/PDF.
- [ ] Integrasi Notifikasi WhatsApp bot interaktif untuk konfirmasi perpanjangan sewa jarak jauh.
