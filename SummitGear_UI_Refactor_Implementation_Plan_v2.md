# SummitGear — Revised UI Refactor Implementation Plan
## Flux UI + Tailwind CSS v4 + Livewire + Alpine.js

> **Status:** Implementation-ready  
> **Scope:** UI architecture, CSS cleanup, Flux standardization, layout consistency, modal behavior, typography, responsive behavior  
> **Primary goal:** Menghilangkan CSS specificity war dan mengembalikan kontrol layout kepada Tailwind v4 + Flux UI tanpa merusak fitur Livewire/Alpine yang sudah berjalan.

---

# 1. Executive Summary

Refaktor **tidak boleh dilakukan sebagai big-bang rewrite**.

Audit terhadap file yang diberikan menunjukkan bahwa masalah UI SummitGear lebih kuat berasal dari **CSS global yang mengambil alih utility Tailwind**, bukan karena Alpine.js atau Livewire sebagai framework.

`resources/css/app.css` saat ini memiliki sekitar **2.938 baris**, termasuk banyak utility Tailwind yang didefinisikan ulang secara manual dengan `!important`, reset global, override selector Flux, dan beberapa sistem komponen custom yang tumpang tindih.

Contoh yang berisiko:

- `.flex { display: flex !important; }`
- `.grid { display: grid !important; }`
- `.p-5`, `.p-10`, `.gap-*`, `.items-*`, `.justify-*` didefinisikan ulang.
- Semua `svg` diberi ukuran global.
- Selector `[data-flux-input]` di-override menggunakan `!important`.
- `dialog` Flux dipaksa `position/top/left/transform` menggunakan `!important`.
- Layout admin memiliki `height: 100vh`, `overflow: hidden`, dan `.content-area` memiliki padding global 2rem.
- Dashboard masih memakai `sg-card`, `clean-stat-card`, `stat-grid-4`, inline style, dan utility Tailwind secara bersamaan.

Bukti tersebut terlihat pada `app.css`, misalnya reset global dan SVG sizing di awal file, serta utility manual yang menduplikasi Tailwind. fileciteturn4file0L12-L30 fileciteturn4file0L59-L78

Utility manual tersebut memang berpotensi menjadi sumber utama CSS specificity war. fileciteturn1file6L516-L585

Dashboard juga memperlihatkan pola campuran: custom class + Tailwind + inline style dalam satu elemen. fileciteturn4file3L653-L711

---

# 2. Target Architecture

Gunakan pembagian tanggung jawab berikut sebagai aturan arsitektur permanen.

```text
┌─────────────────────────────────────────────┐
│                  Laravel                    │
│       Routing / Backend / Authorization     │
└──────────────────────┬──────────────────────┘
                       │
┌──────────────────────▼──────────────────────┐
│                  Livewire                   │
│ State / Data / Validation / Server Actions  │
└──────────────────────┬──────────────────────┘
                       │
              ┌────────┴────────┐
              │                 │
┌─────────────▼─────────┐ ┌─────▼────────────┐
│       Flux UI          │ │    Alpine.js     │
│ Main UI Components     │ │ Micro Interaction│
│ Modal/Input/Card/etc.  │ │ Dropdown/Toggle  │
└─────────────┬─────────┘ └─────┬────────────┘
              │                 │
              └────────┬────────┘
                       ▼
              ┌─────────────────┐
              │ Tailwind CSS v4 │
              │ Layout/Spacing  │
              │ Typography      │
              │ Responsive      │
              └─────────────────┘
```

## Ownership Rules

### Laravel
- Routing
- Authorization
- Backend logic
- Database access

### Livewire
- Server-side state
- Validation
- Data binding
- Backend actions
- Modal state apabila modal memang dikontrol oleh server

### Alpine.js
Hanya untuk interaksi browser ringan:

```text
x-data
x-show
x-transition
@click
@keydown.escape
local dropdown state
local toggle state
```

Jangan menyimpan state backend yang sama di Alpine dan Livewire tanpa alasan kuat.

### Flux UI
Gunakan sebagai default untuk:

```text
flux:card
flux:modal
flux:button
flux:input
flux:select
flux:textarea
flux:badge
flux:table
```

### Tailwind v4
Gunakan untuk:

```text
flex
grid
gap
p / px / py
m / mt / mb
max-w
w-full
responsive breakpoints
typography
colors
alignment
```

### app.css
`app.css` hanya boleh berisi:

1. Tailwind/Flux imports
2. Theme tokens
3. Base/global styles yang memang diperlukan
4. Layout custom yang tidak masuk akal jika ditulis sebagai utility
5. Component-specific CSS yang benar-benar tidak dapat digantikan Flux/Tailwind
6. Special components seperti booking matrix / Kanban / cashier workspace apabila memang diperlukan

**Jangan mendefinisikan ulang utility Tailwind di app.css.**

---

# 3. Temuan Audit Aktual

## 3.1 `app.css` terlalu agresif terhadap Tailwind

File dimulai dengan Tailwind dan Flux import yang benar:

```css
@import "tailwindcss";
@import "../../vendor/livewire/flux/dist/flux.css";
```

Namun setelah itu terdapat reset global dan utility manual. fileciteturn4file0L12-L19

Contoh masalah:

```css
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}
```

Ini harus direview karena reset margin/padding global dapat mengubah perilaku default komponen dan typography.

Selain itu terdapat:

```css
svg {
    max-width: 1.5rem;
    max-height: 1.5rem;
    width: 1.25rem;
    height: 1.25rem;
}
```

yang kemudian dilanjutkan dengan banyak override `svg.h-*`, `svg.w-*`, dan `.shrink-0`. fileciteturn4file0L108-L130

**Keputusan:**

- Hapus global SVG sizing.
- Biarkan ukuran icon ditentukan oleh class yang diberikan komponen.
- QR code diberi selector khusus hanya jika memang dibutuhkan.

---

## 3.2 Tailwind utility diduplikasi manual

Ditemukan utility seperti:

```css
.flex { display: flex !important; }
.inline-flex { display: inline-flex !important; }
.grid { display: grid !important; }
.gap-3 { gap: 0.75rem !important; }
.gap-6 { gap: 1.5rem !important; }
```

serta utility positioning dan spacing lainnya. fileciteturn1file6L532-L585

Bagian lain juga mendefinisikan ulang:

```css
.p-0
.p-5
.p-10
.py-1
.px-3
.mb-1
.mb-2
.mb-5
.mt-1
.w-7
.h-7
.w-10
.h-10
```

dengan `!important`. fileciteturn4file1L271-L320

**Keputusan: HAPUS utility duplicates.**

Jangan mengganti dengan utility custom lain.

---

## 3.3 Layout admin terlalu dikunci

Saat ini:

```css
.admin-layout {
    display: flex;
    height: 100vh;
    max-height: 100vh;
    width: 100vw;
    max-width: 100vw;
    overflow: hidden;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
}
```

dan `.content-area` memakai:

```css
padding: 2rem;
height: calc(100vh - 56px);
max-height: calc(100vh - 56px);
overflow-y: auto;
overflow-x: auto;
```

Pola ini valid untuk aplikasi POS tertentu, tetapi terlalu kaku sebagai global admin layout dan berpotensi membuat child component yang membutuhkan overflow/positioning sendiri menjadi bermasalah. fileciteturn2file2L138-L178

**Keputusan:**

Pertahankan shell fixed-height jika memang diperlukan oleh POS, tetapi:

- Jangan membuat `content-area` sebagai sumber spacing page.
- Spacing halaman dipindahkan ke page component.
- Pertahankan scrolling pada area yang memang membutuhkan scrolling.
- Jangan gunakan `overflow: hidden` secara global kecuali memang dibutuhkan oleh shell.
- Hindari `max-width: 100vw` yang tidak diperlukan.

Target:

```blade
<div class="admin-layout">
    <x-admin-sidebar />

    <main class="main-content">
        <x-admin-topbar title="..." />

        <div class="content-area">
            {{ $slot }}
        </div>
    </main>
</div>
```

dan halaman:

```blade
<div class="mx-auto w-full max-w-7xl px-6 py-6">
    <div class="space-y-6">
        ...
    </div>
</div>
```

---

# 4. Theme & Typography

## 4.1 Gunakan `@theme` Tailwind v4

Saat ini warna sudah berada di `:root`, termasuk Navy dan Coral. fileciteturn4file0L23-L56

Pindahkan design tokens yang memang dipakai oleh utility Tailwind ke:

```css
@theme {
    --font-sans: "Poppins", ui-sans-serif, system-ui, sans-serif;

    --color-navy: #101F42;
    --color-navy-light: #1E3A8A;

    --color-coral: #FF4500;
    --color-coral-hover: #E03E00;
    --color-coral-soft: rgb(255 69 0 / 0.08);
}
```

Jika token semantic seperti success/danger/warning masih dipakai oleh custom component, pertahankan sebagai CSS variable atau migrasikan secara bertahap.

**Jangan mengubah nama class yang sudah digunakan tanpa audit pemakaian.**

---

## 4.2 Poppins

`app.blade.php` sudah memuat Poppins dan menggunakan:

```html
<body class="... font-sans">
```

serta `@vite`, `@livewireStyles`, `@fluxAppearance`, `@livewireScripts`, dan `@fluxScripts` dengan struktur yang valid. fileciteturn1file3L300-L330

Target:

- Pertahankan loading Poppins.
- Jadikan `font-sans` sumber utama typography.
- Hapus deklarasi `font-family: Poppins` yang tidak perlu dari component CSS.
- Hindari override typography dengan selector global yang terlalu spesifik.
- Jangan membuat heading global terlalu agresif.

Saat ini heading global diberi:

```css
h1, h2, h3, h4, h5, h6 {
    color: var(--color-navy);
    font-weight: 600;
    margin-bottom: 1rem;
}
```

Ini berpotensi bertabrakan dengan typography Flux/Tailwind. fileciteturn4file0L74-L78

**Keputusan: hapus margin global heading.**

Spacing heading harus berasal dari parent:

```html
<div class="space-y-1">
    <flux:heading>...</flux:heading>
    <flux:text>...</flux:text>
</div>
```

---

# 5. Spacing System

Gunakan spacing hierarchy berikut sebagai default.

| Area | Default |
|---|---|
| Page horizontal | `px-6` |
| Page vertical | `py-6` |
| Antar section | `space-y-6` |
| Antar content dalam section | `space-y-4` |
| Form fields | `space-y-4` |
| Grid | `gap-6` |
| Button group | `gap-3` |
| Card content | `space-y-4` |
| Small inline group | `gap-2` |
| Dense table | gunakan spacing khusus table |

## Page pattern

```blade
<div class="mx-auto w-full max-w-7xl px-6 py-6">
    <div class="space-y-6">

        <section class="space-y-4">
            ...
        </section>

        <section class="space-y-4">
            ...
        </section>

    </div>
</div>
```

Jangan:

```blade
<div class="p-6">
    <div class="p-5">
        <flux:card class="p-6">
            <div class="p-4">
```

kecuali setiap layer memang memiliki alasan visual.

---

# 6. Card Strategy

Jangan membuat semua wrapper menjadi card.

## Gunakan `flux:card` untuk:

- KPI groups
- Filter panels
- Forms
- Detail panels
- Tables
- Confirmation content
- Informational blocks

## Jangan membuat nested cards tanpa alasan

Buruk:

```text
Page
└── Card
    └── Card
        └── Card
```

Lebih baik:

```text
Page
├── Section
│   └── Card
├── Section
│   └── Grid
│       ├── Card
│       ├── Card
│       └── Card
└── Section
    └── Card
```

---

# 7. Dashboard sebagai Golden Reference

Dashboard jangan langsung diganti menjadi Flux seluruhnya sebelum sistem CSS dibersihkan.

Dashboard saat ini menggunakan:

- `sg-card`
- `stat-grid-4`
- `clean-stat-card`
- `clean-stat-top`
- `clean-stat-icon`
- `clean-stat-value`
- `clean-stat-badge`
- `sg-table`
- `badge`
- inline style

Contoh `stat-grid-4` dan `clean-stat-card` memang sudah memiliki sistem visual sendiri di `app.css`. fileciteturn2file4L259-L287

Masalahnya bukan bahwa semua custom CSS tersebut jelek. Masalahnya adalah **custom design system tersebut berjalan paralel dengan Flux dan Tailwind**.

Dashboard juga memakai inline gradient, padding, shadow, dan background. fileciteturn4file3L653-L711

## Target dashboard

Gunakan:

```blade
<div class="mx-auto w-full max-w-7xl px-6 py-6">
    <div class="space-y-6">

        <section>
            <!-- Quick Actions -->
        </section>

        <section>
            <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-4">
                <!-- KPI -->
            </div>
        </section>

        <section>
            <div class="grid gap-6 lg:grid-cols-3">
                <flux:card class="lg:col-span-1">
                    ...
                </flux:card>

                <flux:card class="lg:col-span-2 overflow-hidden">
                    ...
                </flux:card>
            </div>
        </section>

    </div>
</div>
```

### KPI

Jika Flux tidak memiliki variant yang cocok untuk KPI, custom KPI boleh dipertahankan sebagai **component-specific CSS**, tetapi:

- jangan gunakan `stat-grid-4` untuk layout;
- gunakan Tailwind grid;
- jangan gunakan inline style;
- jangan memakai custom class untuk hal yang bisa dilakukan Tailwind.

Contoh:

```blade
<div class="grid gap-6 md:grid-cols-2 xl:grid-cols-4">
    <flux:card class="...">
        ...
    </flux:card>
</div>
```

---

# 8. Quick Actions Banner

Banner dashboard boleh tetap memiliki visual gradient karena itu merupakan branding, bukan utility layout.

Namun pindahkan:

```html
style="background: linear-gradient(...); padding: ...; box-shadow: ..."
```

ke class/component CSS yang terisolasi.

Contoh:

```css
.dashboard-hero {
    background: linear-gradient(...);
}
```

Lalu:

```blade
<flux:card class="dashboard-hero relative overflow-hidden">
```

Dengan begitu Flux tetap menjadi komponen UI, sementara visual branding khusus tetap terkontrol.

---

# 9. Modal Strategy

## Jangan override Flux modal secara global

Saat ini `app.css` memaksa semua dialog Flux:

```css
position: fixed !important;
top: 50% !important;
left: 50% !important;
transform: translate(-50%, -50%) !important;
margin: 0 !important;
```

dan backdrop juga dipaksa dengan `!important`. fileciteturn2file5L426-L444

Ini justru bertentangan dengan tujuan menghilangkan specificity war.

## Keputusan

Hapus global modal override setelah semua modal manual diidentifikasi.

Gunakan:

```blade
<flux:modal wire:model="showDeleteModal">
    ...
</flux:modal>
```

untuk modal Livewire.

Jangan lagi menggunakan:

```html
<div class="fixed inset-0 ... items-end ...">
```

untuk modal biasa.

## Pengecualian

Jika ada kebutuhan khusus seperti:

- drawer
- flyout
- fullscreen modal
- POS screen-lock

maka gunakan mekanisme Flux/Alpine yang sesuai dan beri CSS scoped khusus.

---

# 10. Flux Input Strategy

Saat ini terdapat beberapa override kompleks:

```css
[data-flux-input] input.ps-10
[data-flux-input] input[class*="ps-10"]
[data-flux-input]:has(...)
```

yang semuanya menggunakan `!important`. fileciteturn2file5L397-L424

Ini harus dianggap sebagai **legacy workaround**, bukan fondasi design system.

## Prosedur

1. Identifikasi semua input yang menggunakan icon.
2. Periksa struktur Flux yang sebenarnya dirender.
3. Gunakan API/slot Flux yang sesuai jika tersedia.
4. Gunakan utility Tailwind hanya pada wrapper/input yang memang dapat dikontrol.
5. Hapus override `[data-flux-input]` secara bertahap.
6. Hanya sisakan CSS scoped jika benar-benar diperlukan.

**Jangan langsung menghapus semua input override sebelum input ber-icon diverifikasi.**

---

# 11. SVG Strategy

Global SVG sizing harus dihapus.

Saat ini:

```css
svg {
    max-width: 1.5rem;
    max-height: 1.5rem;
    width: 1.25rem;
    height: 1.25rem;
}
```

dan utility SVG dipaksa dengan `!important`. fileciteturn4file0L108-L130

Ini berpotensi menjelaskan kasus:

- icon terlalu besar/kecil
- icon Flux tidak mengikuti ukuran
- QR code rusak
- icon custom berubah ukuran setelah CSS dimuat

Target:

```blade
<svg class="size-4">
```

atau class ukuran yang sesuai.

QR code:

```css
.qr-code-box svg {
    display: block;
    max-width: 100%;
}
```

hanya jika memang dibutuhkan.

---

# 12. Form Strategy

CSS saat ini memiliki sistem form custom:

```text
.form-group
.form-label
.form-input
.form-control
.form-label-row
.form-label-text
.form-hint
.form-input-group
.input-with-prefix
.input-with-suffix
```

Beberapa di antaranya menggunakan hardcoded spacing dan `!important`. fileciteturn2file9L676-L793

## Target

Untuk form baru:

```blade
<form class="space-y-4">
    <flux:input ... />
    <flux:select ... />
    <flux:textarea ... />
</form>
```

Untuk form kompleks:

```blade
<div class="space-y-6">
    <section class="space-y-4">
        ...
    </section>

    <section class="space-y-4">
        ...
    </section>
</div>
```

Legacy form classes tetap dipertahankan sementara jika masih dipakai oleh modul lama.

---

# 13. Table Strategy

Dashboard saat ini menggunakan custom `sg-table`, termasuk padding:

```css
.sg-table th {
    padding: 0.875rem 1.25rem;
}

.sg-table td {
    padding: 1rem 1.25rem;
}
```

fileciteturn1file8L791-L835

Untuk tabel baru:

```blade
<flux:card class="overflow-hidden">
    <flux:table>
        ...
    </flux:table>
</flux:card>
```

Untuk tabel legacy yang kompleks:

- jangan dipaksa migrasi sekaligus;
- pertahankan `sg-table` jika fungsional;
- refactor layout wrapper terlebih dahulu;
- migrasikan ke `flux:table` setelah visual parity tercapai.

---

# 14. Kebab Menu

`x-kebab-menu` saat ini memiliki custom positioning:

```css
.kebab-container {
    position: relative;
}

.kebab-dropdown {
    position: absolute;
    right: 0;
    top: calc(100% + 4px);
    z-index: 60;
}
```

fileciteturn1file8L726-L790

Jangan buru-buru menghapusnya.

### Prioritas:

1. Pastikan menu tidak terpotong oleh parent `overflow-hidden`.
2. Pastikan `z-index` bekerja.
3. Pastikan dropdown tidak terpengaruh oleh `.content-area`.
4. Jika Flux dropdown dapat menggantikan fungsi ini tanpa regresi, migrasikan.
5. Jika tidak, pertahankan sebagai component-specific CSS.

Ini termasuk CSS yang **boleh tetap custom** jika memang tidak tersedia padanan Flux yang sesuai.

---

# 15. Sidebar & Admin Shell

Sidebar saat ini memiliki banyak CSS custom, termasuk width 235px, fixed viewport height, nav scrolling, link spacing, dan SVG sizing. fileciteturn1file7L595-L716

Tidak perlu menghapus semuanya.

## Pertahankan

- struktur sidebar
- responsive behavior
- navigation grouping
- active state
- scrolling sidebar

## Refactor

- buang utility duplicate;
- gunakan Tailwind untuk spacing sederhana;
- gunakan CSS custom hanya untuk sidebar-specific behavior;
- jangan global override `.flex`, `.grid`, `.shrink-0`, `.hidden`.

---

# 16. Cashier Workspace

Jangan migrasikan cashier workspace bersamaan dengan dashboard.

`app.css` memiliki responsive rules khusus untuk cashier workspace pada beberapa breakpoint, termasuk perubahan sidebar, toolbar, cart, dan layout mobile. fileciteturn1file5L463-L505

Modul POS memiliki kebutuhan layout berbeda dari admin CRUD.

## Keputusan

Buat scope:

```text
.cashier-workspace
.cashier-shell
.cashier-toolbar
.cashier-cart
.cashier-catalog-grid
```

tetap sebagai custom subsystem.

Refactor hanya:

- buang duplicate utility global;
- jangan mengubah behavior responsive tanpa screenshot verification;
- jangan memaksa cashier mengikuti page wrapper admin.

---

# 17. Special Components

Komponen berikut **tidak wajib dipaksa menjadi Flux murni**:

### Booking Calendar Matrix
Pertahankan custom CSS dan interactivity.

Yang diubah:

```text
Page wrapper
→ Section
→ flux:card
→ calendar matrix
```

### Kanban
Pertahankan struktur board.

Yang diubah:

```text
Page wrapper
→ Section
→ flux:card
→ Kanban board
```

### QC Segmented Control
Saat ini memiliki custom visual states seperti `selected-baik`, `selected-cukup`, `selected-perhatian`, dll. fileciteturn4file2L562-L628

Pertahankan dahulu karena ini adalah domain-specific UI.

### QR Code
Pertahankan component-specific sizing.

---

# 18. Refactor Order

## Phase 0 — Safety / Baseline

Sebelum perubahan:

```bash
git status
git checkout -b refactor/ui-system
npm run build
```

Jika tersedia:

```bash
npx playwright test tests/e2e/capture_redesign_screenshots.spec.js
```

Simpan screenshot baseline.

---

# Phase 1 — CSS Foundation

Target:

```text
resources/css/app.css
```

Pekerjaan:

- [ ] Audit seluruh utility duplicate.
- [ ] Hapus `.flex`, `.grid`, `.inline-flex`, `.gap-*`, `.p-*`, `.m-*`, `.items-*`, `.justify-*` duplicate.
- [ ] Hapus global SVG sizing.
- [ ] Hapus global heading margin.
- [ ] Migrasikan theme colors ke `@theme`.
- [ ] Tetapkan Poppins sebagai `--font-sans`.
- [ ] Review reset global.
- [ ] Pisahkan component-specific CSS.
- [ ] Jangan menghapus special component CSS.

**Output:** Tailwind menjadi authority utama untuk utility.

---

# Phase 2 — Admin Shell

Target:

```text
resources/views/components/layouts/app.blade.php
resources/views/components/layouts/admin*.blade.php
x-admin-sidebar
x-admin-topbar
```

Pekerjaan:

- [ ] Pertahankan Flux scripts/styles.
- [ ] Pertahankan Poppins.
- [ ] Review `.admin-layout`.
- [ ] Kurangi global responsibility `.content-area`.
- [ ] Page component menjadi pemilik spacing.
- [ ] Pastikan scrolling tetap bekerja.
- [ ] Pastikan sidebar tidak berubah behavior.

---

# Phase 3 — Modal System

Target:

```text
logout-modal
pin-approval
cancel booking modal
delete user modal
inventory modal
unit modal
```

Pekerjaan:

- [ ] Hapus modal manual `items-end`.
- [ ] Gunakan `<flux:modal>`.
- [ ] Pertahankan `wire:model`.
- [ ] Hapus global dialog positioning override setelah modal verified.
- [ ] Test ESC.
- [ ] Test backdrop.
- [ ] Test focus.
- [ ] Test Livewire state setelah modal ditutup/dibuka.

---

# Phase 4 — Golden Reference: Dashboard

Refactor hanya dashboard terlebih dahulu.

Target struktur:

```text
Dashboard
│
├── Page Wrapper
│
├── Quick Actions
│
├── KPI Grid
│   ├── KPI
│   ├── KPI
│   ├── KPI
│   └── KPI
│
└── Main Grid
    ├── Asset Distribution
    └── Recent Transactions
```

Acceptance criteria:

- [ ] Tidak ada inline padding.
- [ ] Tidak ada inline color.
- [ ] Tidak ada duplicate Tailwind utility CSS.
- [ ] Tidak ada nested card berlebihan.
- [ ] KPI spacing konsisten.
- [ ] Table tidak pecah.
- [ ] Kebab menu tidak terpotong.
- [ ] Responsive 375/430/768/1280/1440/1920.

---

# Phase 5 — Extract Reusable Patterns

Setelah dashboard stabil, buat pola reusable.

Contoh:

```text
PageHeader
SectionHeader
FilterCard
StatCard
TableCard
EmptyState
FormSection
ActionGroup
```

Jangan membuat component hanya untuk mengurangi jumlah baris HTML.

Component dibuat jika:

- dipakai minimal beberapa kali;
- memiliki visual behavior sendiri;
- perlu standardisasi.

---

# Phase 6 — Admin CRUD / Operations

Urutan:

### 6.1 Incoming Booking
- Page wrapper
- Filter card
- Booking card
- Flux badge
- Cancel modal

### 6.2 User Management
- Page wrapper
- Search/filter
- Table card
- Delete modal

### 6.3 Inventory Item
- Page wrapper
- Filter
- Catalog
- Modal

### 6.4 Inventory Unit
- Page wrapper
- Filter
- Unit table
- Status modal

### 6.5 Handover
- Page wrapper
- QC sections
- Preserve domain-specific controls

### 6.6 Calendar
- Page wrapper
- Flux card
- Preserve matrix

### 6.7 Settlement
- Page wrapper
- Cards
- Tables
- Forms

### 6.8 Maintenance Kanban
- Page wrapper
- Flux card
- Preserve Kanban behavior

### 6.9 Customer
- Index
- Form
- Flux form controls

### 6.10 Settings
- Page wrapper
- Form sections

### 6.11 Analytics
- Filter toolbar
- Charts
- Cards

### 6.12 Transaction Create
- Treat separately as cashier/POS-like workspace.
- Do not force standard admin grid into the cashier workspace.

---

# 19. Auth Pages

Target:

```text
login
login-pin
guest layout
```

Pattern:

```blade
<flux:card class="w-full max-w-md">
    <div class="space-y-6">
        ...
    </div>
</flux:card>
```

Rules:

- Poppins konsisten.
- Tidak ada inline CSS untuk input.
- Tidak ada manual card jika Flux dapat menggantikannya.
- Background boleh tetap custom branding.

---

# 20. CSS Classification Rules

Setiap CSS dalam `app.css` harus masuk salah satu kategori:

```text
A. Tailwind/Flux duplicate
   → DELETE

B. Global base style
   → KEEP / simplify

C. Admin shell
   → KEEP, scoped

D. Domain component
   → KEEP, scoped

E. Temporary workaround
   → REVIEW → REMOVE if possible

F. Legacy component
   → KEEP temporarily → migrate later
```

## Contoh

### DELETE

```css
.flex { display: flex !important; }
.grid { display: grid !important; }
.p-5 { padding: 1.25rem !important; }
```

### REVIEW

```css
[data-flux-input] input.ps-10 {
    padding-left: 2.65rem !important;
}
```

### KEEP if required

```css
.booking-matrix-table { ... }
```

### KEEP scoped

```css
.cashier-workspace .sidebar { ... }
```

---

# 21. Jangan Menggunakan `!important` Sebagai Default

Target akhir:

```text
!important
↓
hanya boleh muncul pada kasus exceptional
```

Jika ditemukan:

```css
.foo {
    padding: 1rem !important;
}
```

pertanyaan pertama:

> Mengapa harus `!important`?

Jika jawabannya karena Tailwind/Flux kalah specificity:

> **Jangan tambah `!important`. Cari sumber specificity-nya.**

---

# 22. Livewire / Alpine Rules

## Jangan melakukan ini

```blade
<div
    x-data="{ open: false }"
    wire:model="open"
>
```

atau membuat state yang sama dikontrol dua framework tanpa kebutuhan.

## Lebih baik

Backend state:

```php
public bool $showModal = false;
```

UI:

```blade
<flux:modal wire:model="showModal">
```

Alpine hanya untuk state lokal:

```blade
<div x-data="{ open: false }">
```

Jika state harus persist di server/database:

**Livewire.**

Jika state hanya hidup di browser:

**Alpine.**

---

# 23. Responsive Strategy

Jangan membuat breakpoint custom untuk setiap komponen kecuali ada alasan kuat.

Default:

```text
mobile
→ md
→ lg
→ xl
```

Contoh:

```html
<div class="grid gap-6 md:grid-cols-2 xl:grid-cols-4">
```

Untuk layout 3/1:

```html
<div class="grid gap-6 lg:grid-cols-3">
```

Untuk responsive form:

```html
<div class="grid gap-4 md:grid-cols-2">
```

---

# 24. Verification Plan

## Build

```bash
npm run build
```

## E2E

```bash
npx playwright test tests/e2e/capture_redesign_screenshots.spec.js
```

Jika test belum mencakup semua halaman, tambahkan screenshot coverage secara bertahap.

---

# 25. Visual Regression Matrix

Minimal:

```text
375px
430px
768px
1280px
1440px
1920px
```

Test:

- [ ] Font Poppins
- [ ] Sidebar
- [ ] Topbar
- [ ] Page padding
- [ ] Section spacing
- [ ] Card spacing
- [ ] Table
- [ ] Modal
- [ ] Dropdown
- [ ] Input icon
- [ ] QR code
- [ ] Kanban
- [ ] Calendar
- [ ] Cashier workspace

---

# 26. Acceptance Criteria

Refaktor dianggap berhasil jika:

### Layout

- [ ] Tidak ada spacing yang terasa random.
- [ ] Page menggunakan max-width konsisten.
- [ ] Section memiliki vertical rhythm konsisten.
- [ ] Card tidak nested tanpa alasan.
- [ ] Grid memiliki gap konsisten.

### CSS

- [ ] Utility Tailwind tidak didefinisikan ulang.
- [ ] Tidak ada global SVG sizing.
- [ ] Tidak ada global heading margin.
- [ ] `!important` turun drastis dan hanya tersisa untuk exceptional cases.
- [ ] Tidak ada CSS yang mengalahkan Flux tanpa alasan.

### Flux

- [ ] Modal menggunakan Flux.
- [ ] Input baru menggunakan Flux.
- [ ] Button baru menggunakan Flux.
- [ ] Card baru menggunakan Flux.
- [ ] Badge baru menggunakan Flux.
- [ ] Table baru menggunakan Flux jika cocok.

### Livewire

- [ ] Semua server state tetap bekerja.
- [ ] Validation tetap bekerja.
- [ ] Modal state tetap sinkron.
- [ ] Tidak ada regresi setelah re-render.

### Alpine

- [ ] Dropdown tetap bekerja.
- [ ] Toggle tetap bekerja.
- [ ] Local interaction tetap bekerja.
- [ ] Tidak ada duplikasi state backend.

### Visual

- [ ] Poppins tampil konsisten.
- [ ] Modal center.
- [ ] Tidak ada transparansi yang rusak.
- [ ] Tidak ada teks overlap.
- [ ] Tidak ada icon berubah ukuran.
- [ ] Tidak ada QR code rusak.
- [ ] Tidak ada overflow horizontal tidak sengaja.

---

# 27. Final Recommended File Strategy

## Core

```text
resources/css/app.css
resources/js/app.js
resources/views/components/layouts/app.blade.php
resources/views/components/layouts/guest.blade.php
```

## Global UI

```text
resources/views/components/logout-modal.blade.php
resources/views/livewire/components/pin-approval.blade.php
resources/views/components/admin-sidebar.blade.php
resources/views/components/admin-topbar.blade.php
```

## Golden reference

```text
resources/views/livewire/admin/dashboard.blade.php
```

## Admin modules

```text
admin/operations/
admin/inventory/
admin/user-management/
admin/maintenance/
admin/customer/
admin/setting/
admin/analytics/
admin/transaction/
```

## Special systems

```text
calendar matrix
kanban
cashier workspace
QR code
QC controls
kebab menu
```

Special systems **tidak dipaksa menjadi pure Flux**.

---

# 28. Important: What NOT To Do

Jangan:

1. Menghapus 1.500+ baris CSS sekaligus tanpa klasifikasi.
2. Menghapus semua custom CSS hanya karena menggunakan Flux.
3. Menghapus Alpine.
4. Memindahkan semua state ke Alpine.
5. Memindahkan semua state ke Livewire jika hanya local browser state.
6. Membuat setiap wrapper menjadi `flux:card`.
7. Menambahkan `!important` baru untuk menyelesaikan konflik.
8. Menggunakan inline `style` untuk spacing.
9. Menggunakan CSS global untuk mengatur ukuran SVG.
10. Memaksa cashier, calendar, dan Kanban mengikuti layout CRUD biasa.
11. Refactor seluruh halaman sebelum dashboard menjadi reference yang stabil.
12. Mengubah behavior bisnis Livewire saat tujuan perubahan hanya UI.

---

# 29. Final Architecture Rule

Setelah refaktor, developer SummitGear harus bisa mengikuti aturan sederhana:

```text
"Kalau masalahnya layout → Tailwind."

"Kalau masalahnya komponen UI → Flux."

"Kalau masalahnya server/data → Livewire."

"Kalau masalahnya interaksi browser kecil → Alpine."

"Kalau perlu CSS custom → scope-kan ke component."

"Kalau butuh !important → cari dulu kenapa."

"Kalau bisa pakai Tailwind → jangan bikin utility CSS sendiri."
```

---

# 30. Expected Result

Hasil akhir yang diharapkan:

```text
                    SUMMITGEAR UI SYSTEM

                         Page
                          │
                    max-w-7xl
                          │
                     space-y-6
                          │
            ┌─────────────┴─────────────┐
            │                           │
         Section                     Section
            │                           │
        Flux Card                  Tailwind Grid
            │                     ┌─────┼─────┐
        space-y-4               Card   Card   Card
            │
       Flux Components
            │
       Livewire State
            │
      Alpine Micro UI
```

Tujuan refaktor bukan sekadar membuat kode "lebih modern".

Tujuan utamanya adalah mengembalikan **single source of truth untuk styling**:

```text
Tailwind v4
    +
Flux UI
    +
small scoped custom CSS
```

bukan:

```text
Tailwind
+
Flux
+
1.500+ utility !important
+
global SVG override
+
inline style
+
custom card system
+
custom form system
+
custom modal override
```

Dengan kondisi file saat ini, pendekatan bertahap di atas lebih aman dan lebih tepat daripada melakukan rewrite besar sekaligus.
