# SummitGear — Design System & UI Specification

Dokumentasi cetak biru visual, token antarmuka, dan standarisasi komponen untuk SummitGear POS & Rental Application.

---

## 🎨 1. Design Tokens ("Volcanic Twilight" Palette)

Tema visual menggabungkan nuansa petualangan alam terbuka (outdoor mountaineering) dengan ketegasan dashboard POS profesional:

| Token Name | Hex Code | Utility Class | Penggunaan |
| :--- | :--- | :--- | :--- |
| **Primary Lava** | `#F97316` | `bg-orange-500` / `text-orange-500` | CTA Utama, Aksen Brand, status aktif |
| **Volcanic Dark** | `#0F172A` | `bg-slate-900` / `text-slate-900` | Background sidebar, card kontras tinggi |
| **Summit Slate** | `#1E293B` | `bg-slate-800` / `border-slate-800` | Header tabel, background modal |
| **Deep Forest** | `#059669` | `bg-emerald-600` / `text-emerald-600` | Status sukses, unit `Available`, tag `PAID` |
| **Alpine Warning** | `#EAB308` | `bg-yellow-500` / `text-yellow-500` | Peringatan toleransi, status `DP_PAID` |
| **Magma Red** | `#DC2626` | `bg-red-600` / `text-red-600` | Denda, pembatalan, blacklist, `OVERDUE` |
| **Neutral Surface** | `#F8FAFC` | `bg-slate-50` / `text-slate-900` | Background halaman kerja kasir |

---

## 🔤 2. Tipografi & Spacing

- **Font Family**: Inter, sans-serif
- **Scale**:
  - `h1`: `text-2xl font-bold tracking-tight text-slate-900`
  - `h2`: `text-xl font-semibold text-slate-800`
  - `body`: `text-sm text-slate-600 leading-relaxed`
  - `badge`: `text-xs font-semibold uppercase px-2.5 py-0.5 rounded-full`
- **Spacing Grid**: 4pt / 8pt standard (`gap-2`, `gap-4`, `p-4`, `p-6`).
- **Corner Radius**: `rounded-xl` (12px) untuk Card dan Modal; `rounded-lg` (8px) untuk Button dan Input.

---

## 📱 3. 5 Status Interaksi Layar (The 5 Core Screen States)

Setiap modul Livewire wajib mengimplementasikan 5 status layar:

1. **Empty State**:
   - Tampilan ramah saat tidak ada data (misal: Keranjang kosong, Antrean booking kosong).
   - Ikon informatif + pesan penjelas + tombol ajakan bertindak (CTA).
2. **Loading State**:
   - Indikator visual saat proses async berlangsung (misal: `wire:loading` spinner, skeleton loading card untuk katalog).
3. **Error State**:
   - Pesan validasi yang jelas di bawah input (`text-xs text-red-600 font-medium`).
   - Modal konfirmasi PIN menampilkan pesan penolakan yang spesifik jika PIN salah.
4. **Success State**:
   - Banner notifikasi flash hijau (`bg-emerald-50 text-emerald-800 border-emerald-200`) dan badge status real-time.
5. **Offline / Guarded State**:
   - Banner penolakan pelanggan blacklist berwarna merah mencolok dengan ikon blokir.
   - Screen Lock modal dengan overlay backdrop blur saat kasir meninggalkan meja kasir.
