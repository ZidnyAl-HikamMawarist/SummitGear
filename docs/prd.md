# Product Requirement Document (PRD)
# SummitGear POS — Sistem Kasir & Manajemen Rental Alat Pendakian

**Versi:** 3.0 (Final — Siap Development)
**Target Platform:** Web App (Single Outlet, Single Kasir Terminal, Responsive Desktop & Tablet)
**Tech Stack:** Laravel + Livewire + Alpine.js + PostgreSQL
**Status:** Final Draft

---

## 1. Ringkasan Eksekutif

SummitGear POS adalah sistem end-to-end untuk mengelola bisnis persewaan alat pendakian dengan **satu titik kasir (single cashier terminal)** di satu outlet. Sistem ini mengotomatisasi reservasi, serah-terima barang berbasis checklist kondisi, pengelolaan jaminan, penghitungan denda otomatis, dan siklus perawatan alat.

**Asumsi Skala:**
- 1 outlet, 1 terminal kasir aktif pada satu waktu.
- Staf gudang/QC bisa lebih dari satu orang, akses sistem secara sekuensial.
- Tidak ada channel booking online mandiri oleh pelanggan di fase awal.

---

## 2. Tujuan Produk & Metrik Keberhasilan

| Tujuan | Metrik Keberhasilan (KPI) | Target Fase 1 |
|---|---|---|
| Menghilangkan double-booking unit fisik | Jumlah kasus bentrok jadwal per bulan | 0 kasus |
| Mempercepat transaksi kasir | Waktu rata-rata proses check-out per transaksi | < 5 menit |
| Mengurangi sengketa denda kerusakan | % transaksi dengan komplain denda | < 5% |
| Meningkatkan utilisasi aset | Tingkat keterpakaian unit per bulan | Terukur (baseline dulu) |
| Transparansi jaminan | % pengembalian deposit tanpa perselisihan | > 95% |

---

## 3. Peran Pengguna & Hak Akses (RBAC)

| Peran | Hak Akses |
|---|---|
| **Owner/Admin** | Akses penuh: laporan keuangan, ubah harga master, atur `booking_window_days`, void transaksi, kelola user, lihat semua audit log |
| **Kasir** | Buat reservasi, transaksi sewa, input jaminan, check-out/check-in barang, cetak invoice. Void transaksi butuh PIN approval Admin |
| **Staf Gudang/QC** | Update status unit (cuci, servis, siap pakai), input catatan maintenance. Tidak akses data transaksi/keuangan |

Void transaksi atau koreksi harga manual oleh Kasir wajib memakai PIN Approval Admin, otomatis tercatat di `audit_logs` dengan `approved_by`.

---

## 4. Ruang Lingkup

**In-scope (Fase 1–3):** Reservasi & kalender ketersediaan, transaksi kasir, jaminan, checklist QC check-in/out, denda otomatis, maintenance, notifikasi WA, bundling.

**Out-of-scope:** Multi-outlet, booking self-service pelanggan, aplikasi mobile terpisah, integrasi akuntansi pihak ketiga.

---

## 5. Alur Kerja End-to-End

```
[Kasir Input Booking/Walk-in]
          │
          ▼
[Cek Ketersediaan Kalender per Unit Fisik]
   (validasi: tanggal ambil ≤ H+booking_window_days dari hari ini)
          │
          ├─► Stok komponen paket tidak lengkap ──► [Tawarkan Substitusi Unit Sejenis]
          │
          ▼
[Pilih Unit / Paket] → [Pilih Tipe Sewa: Harian/24 Jam] → [Input Data Pelanggan + Jaminan]
          │
          ▼
[Check-Out: Checklist Kondisi (kategori: Baik/Cukup/Perlu Perhatian) + TTD Digital]
          │
          ▼
[Masa Sewa Berjalan] → [Notifikasi WA H-1]
          │
          ▼
[Pengembalian & Checklist Check-In]
   │
   ├─► Sesuai & Tepat Waktu ──────────────► Deposit Kembali Penuh ──► Selesai
   │
   ├─► Rusak Sebagian (per komponen paket) ─► Denda Parsial per unit
   │
   ├─► Rusak Berat / Hilang Total ───────────► Ganti rugi = replacement_value ─► Potong Deposit → Kurang? → Invoice Tambahan
   │
   └─► Telat Kembali ───────────────────────► Denda otomatis per jam sejak scheduled_return_time terlewati
          │
          ▼
[Unit Masuk Antrean Cuci/Servis] → [QC Ulang] → [Available]
```

---

## 6. Kebutuhan Fungsional

### A. Reservasi & Kalender Ketersediaan
- Query ketersediaan per unit fisik, dengan **exclusion constraint PostgreSQL** pada `item_unit_id + rentang tanggal` untuk mencegah double-booking di level database (bukan hanya validasi aplikasi).
- **Booking window dapat dikonfigurasi Admin**: field `booking_window_days` di tabel `settings`, default **7 hari**, maksimal dapat diatur hingga **20 hari**. Validasi ditolak jika kasir input tanggal pengambilan melebihi batas ini.
- DP & auto-release hold booking, durasi hold dikonfigurasi Admin (default 2 jam).
- Tarif dinamis via `pricing_rules` (weekday/weekend/holiday multiplier).

### B. Inventaris, Bundling & Serial Tracking
- Unit fisik dengan QR/Barcode unik, siklus status: `Available`, `Booked`, `Rented`, `Returned/Cleaning`, `Maintenance`, `Decommissioned`.
- Substitusi manual saat komponen bundling kurang stok (dikonfirmasi kasir ke pelanggan, bukan otomatis).
- Pengembalian sebagian paket didukung via `return_status` independen per baris `rental_details`.

### C. Tipe Sewa & Denda Keterlambatan
- Setiap unit/paket punya field `rental_type`: `daily_24h` (per periode 24 jam sejak waktu check-out).
- **Denda telat**: dihitung otomatis oleh sistem begitu `NOW() > scheduled_return_time` dan status unit masih `Rented` — dibulatkan ke atas per jam, langsung tercatat sebagai baris baru di `penalties` dengan `reason = 'late_return'`. Tidak menunggu input manual kasir.

### D. Transaksi Kasir & Jaminan
- Tabel `payments` terpisah dari `deposits` — mencatat setiap baris DP, pelunasan, dan denda tambahan sebagai transaksi independen untuk rekonsiliasi kas harian.
- Multi-metode pembayaran: Tunai, Transfer, QRIS.
- Jaminan dokumen (KTP/SIM) dan finansial, dengan `retention_deadline` untuk kepatuhan data.

### E. Checklist QC Check-In/Check-Out (Tanpa Foto)
- **Keputusan final: sistem tidak menyimpan foto kondisi barang.** Verifikasi visual dilakukan langsung oleh kasir/staf secara manual di lokasi saat serah-terima.
- Checklist tetap dicatat di sistem sebagai data terstruktur: per komponen barang, kategori kondisi (`Baik` / `Cukup` / `Perlu Perhatian` / `Rusak` / `Hilang`) + catatan teks bebas.
- Perbandingan kondisi awal (check-out) vs kondisi akhir (check-in) dari checklist ini yang jadi dasar penetapan denda otomatis — bukan dari foto.
- **Catatan risiko yang perlu disadari**: tanpa foto, bukti kondisi barang sepenuhnya bergantung pada kejelian dan konsistensi kasir saat mengisi checklist. Disarankan checklist punya kategori yang cukup spesifik (bukan cuma "baik/rusak") supaya minim ambiguitas saat terjadi perselisihan.
- Ganti rugi barang hilang/rusak berat pakai `replacement_value` per unit sebagai acuan baku, bukan estimasi manual.
- Kasir bisa override denda otomatis dengan alasan wajib diisi (tercatat di audit log).

### F. Maintenance
- Antrean cuci, riwayat servis, tombol "Selesai Cuci" mengembalikan status ke `Available`.

### G. Notifikasi WhatsApp
- Vendor gateway: Fonnte / Wablas (dipanggil via Laravel Queue agar tidak blocking).
- Fallback: alert dashboard kasir jika pengiriman gagal, untuk follow-up manual.

---

## 7. Kebutuhan Non-Fungsional

**A. Performa** — Query ketersediaan < 1 detik.

**B. Perangkat** — Tablet-first landscape, kamera device tetap dipakai untuk foto dokumen identitas (KTP/SIM) — ini beda dari foto kondisi barang yang sudah ditiadakan; foto KTP tetap perlu sebagai syarat jaminan.

**C. Kepatuhan Data Pribadi (UU PDP)**
- Foto KTP/SIM terenkripsi at-rest (AES-256) dan in-transit (TLS).
- `retention_deadline` per deposit — job terjadwal (Laravel Scheduler) hapus/blur foto identitas otomatis setelah deadline terlewati.
- Akses foto dokumen dibatasi Kasir & Admin, dicatat di `document_access_log`.
- Consent checkbox saat input data pelanggan.

**D. Audit Trail** — Semua perubahan status, override denda, void transaksi, perubahan harga manual tercatat dengan `user_id`, `timestamp`, `alasan`, `approved_by`.

**E. Backup & Disaster Recovery** — Backup PostgreSQL harian otomatis, retensi 30 hari.

---

## 8. Desain Visual (UI)

**Palet warna final — "Volcanic Twilight":**

| Peran | Warna | Hex |
|---|---|---|
| Base/Background | Putih | `#FFFFFF` |
| Pendukung/Netral | Slate Gray | `#708090` |
| Aksen CTA (tombol aksi utama) | Coral | `#FF4500` |
| Teks/Heading | Navy | `#101F42` |
| Status Baik/Selesai | Hijau (semantic, terpisah dari palet utama) | `#2E7D32` |
| Status Warning/Denda | Merah (semantic) | `#D32F2F` |

**Prinsip pemakaian:**
- Coral hanya untuk 1 CTA utama per layar (tombol paling penting) — jangan dipakai berulang di banyak elemen supaya tetap punya daya tarik visual.
- Slate gray untuk border, divider, teks sekunder.
- Warna semantic (hijau/merah) dipakai khusus status transaksi (bukan bagian dari palet brand), supaya tidak bentrok makna dengan coral sebagai CTA.
- Base selalu putih terang — tidak ada mode gelap, sesuai requirement bisnis.

---

## 9. Tech Stack Final

- **Backend:** Laravel
- **Frontend:** Livewire + Alpine.js
- **Database:** PostgreSQL (dipilih karena butuh *exclusion constraint* untuk validasi anti double-booking di level database)
- **Storage foto (khusus dokumen identitas):** Object storage S3-compatible (misal Cloudflare R2)
- **Queue/Job:** Laravel Queue (untuk notifikasi WA & job retensi data)
- **WA Gateway:** Fonnte atau Wablas (pilih salah satu di Fase 2, evaluasi biaya per pesan)

---

## 10. Skema Database (Final)

| Tabel | Atribut Kunci | Catatan |
|---|---|---|
| `users` | `id`, `name`, `role`, `pin`, `phone` | |
| `customers` | `id`, `name`, `nik`, `phone`, `id_photo_url`, `consent_at` | |
| `settings` | `key`, `value` | Menyimpan `booking_window_days` (default 7, max 20), `hold_duration_hours`, dll — dikonfigurasi Admin tanpa perlu ubah kode |
| `inventory_items` | `id`, `sku`, `name`, `category`, `is_package`, `rental_type` | |
| `item_units` | `id`, `item_id`, `serial_number`, `status`, `condition_notes`, `replacement_value` | |
| `package_items` | `package_id`, `component_item_id`, `quantity` | |
| `pricing_rules` | `id`, `item_id`, `day_type`, `price_multiplier` | |
| `rentals` | `id`, `rental_code`, `customer_id`, `start_date`, `end_date`, `scheduled_return_time`, `status` | |
| `rental_details` | `id`, `rental_id`, `item_unit_id`, `price_per_day`, `return_status` | |
| `payments` | `id`, `rental_id`, `type`, `method`, `amount`, `paid_at` | |
| `deposits` | `id`, `rental_id`, `type`, `amount`, `doc_type`, `status`, `retention_deadline` | |
| `inspections` | `id`, `rental_detail_id`, `stage`, `condition_category`, `notes` | **Tanpa kolom foto** — hanya kategori + catatan teks |
| `penalties` | `id`, `rental_id`, `reason`, `amount`, `is_settled`, `is_override`, `override_reason` | |
| `maintenance_logs` | `id`, `item_unit_id`, `type`, `start_time`, `end_time`, `technician_name` | |
| `document_access_log` | `id`, `customer_id`, `accessed_by`, `accessed_at` | |
| `audit_logs` | `id`, `user_id`, `action`, `entity`, `entity_id`, `reason`, `approved_by`, `timestamp` | |

---

## 11. Roadmap Rilis

**Fase 1 — MVP Transaksi & Inventaris (4–6 minggu)**
Katalog & unit tracking, walk-in rental, check-out/check-in checklist, jaminan, `payments`, cetak invoice, RBAC dasar, `settings` untuk booking window.

**Fase 2 — Booking Kalender & WhatsApp (3–4 minggu)**
Kalender ketersediaan + exclusion constraint, notifikasi WA + fallback manual, retensi data KTP otomatis.

**Fase 3 — Bundling, Maintenance & Analitik (3–4 minggu)**
Bundling otomatis + substitusi, kanban maintenance, laporan utilisasi & margin, dashboard KPI.

---

## 12. Risiko & Mitigasi

| Risiko | Mitigasi |
|---|---|
| Sengketa kondisi barang tanpa bukti foto | Checklist dengan kategori kondisi yang spesifik + catatan teks wajib diisi detail, bukan sekadar centang |
| Kehilangan data jaminan finansial akibat bug/crash | Backup harian PostgreSQL + tabel `payments` terpisah dari `deposits` |
| Nomor WA gateway diblokir | Fallback alert dashboard + evaluasi upgrade ke WA Business API resmi jika volume naik |
| Kasir lupa retensi hapus data KTP | Laravel Scheduler job otomatis sesuai `retention_deadline` |

---

## 13. Kriteria Penerimaan (Acceptance Criteria) — Fase 1

- [ ] Kasir dapat membuat transaksi walk-in dan mencetak invoice dalam < 5 menit.
- [ ] Sistem menolak booking di luar `booking_window_days` yang berlaku (default 7, maks 20).
- [ ] Sistem menolak booking unit yang sudah `Booked`/`Rented` pada rentang tanggal yang sama (via exclusion constraint).
- [ ] Denda telat otomatis muncul di `penalties` begitu `scheduled_return_time` terlewati tanpa check-in.
- [ ] Checklist check-in/check-out tersimpan sebagai data terstruktur (kategori + catatan), tanpa upload foto kondisi barang.
- [ ] Void transaksi oleh Kasir wajib PIN Admin dan tercatat `approved_by`.
- [ ] Foto KTP tersimpan terenkripsi dan tidak dapat diakses role Staf Gudang.

---

*Dokumen ini merupakan hasil finalisasi dari seluruh diskusi keputusan produk: tech stack (Laravel + Livewire + Alpine.js + PostgreSQL), penghapusan dokumentasi foto kondisi barang, aturan booking window fleksibel (7–20 hari), tipe sewa harian/24 jam dengan denda otomatis, dan palet warna UI "Volcanic Twilight".*