# Dokumentasi Diagram & Panduan Sistem SummitGear

Dokumen ini memuat seluruh diagram arsitektur teknis, alur proses bisnis (*flowchart*), diagram entitas data (*ERD*), *Use Case*, serta panduan ringkas penggunaan sistem **SummitGear POS & Outdoor Rental System**.

---

## 1. Entity Relationship Diagram (ERD)

Diagram relasi antar-entitas database PostgreSQL / Laravel Eloquent yang digunakan pada sistem SummitGear:

```mermaid
erDiagram
    USERS ||--o{ AUDIT_LOGS : "mencatat aksi"
    CUSTOMERS ||--o{ RENTALS : "melakukan transaksi"
    INVENTORY_ITEMS ||--o{ ITEM_UNITS : "memiliki fisik"
    INVENTORY_ITEMS ||--o{ PRICING_RULES : "memiliki aturan tarif"
    INVENTORY_ITEMS ||--o{ PACKAGE_ITEMS : "dapat menjadi paket"
    
    RENTALS ||--|{ RENTAL_DETAILS : "memiliki rincian"
    RENTALS ||--o{ PAYMENTS : "memiliki pembayaran"
    RENTALS ||--o{ DEPOSITS : "memiliki jaminan"
    RENTALS ||--o{ PENALTIES : "dikenakan denda"
    
    ITEM_UNITS ||--o{ RENTAL_DETAILS : "disewakan pada"
    ITEM_UNITS ||--o{ MAINTENANCE_LOGS : "memiliki catatan QC"

    USERS {
        bigint id PK
        string name
        string email UK
        string role "admin | kasir | gudang"
        string password
        string pin "6 digits approval"
        string phone
        text two_factor_secret "Base32 TOTP"
        timestamp two_factor_confirmed_at
        text two_factor_recovery_codes
        timestamps created_at
    }

    CUSTOMERS {
        bigint id PK
        string name
        string phone UK
        string nik "KTP 16 digit"
        timestamps created_at
    }

    INVENTORY_ITEMS {
        bigint id PK
        string name
        string sku UK
        string category
        decimal base_price
        boolean is_package
        string photo_url
        timestamps created_at
    }

    ITEM_UNITS {
        bigint id PK
        bigint item_id FK
        string serial_number UK
        string status "Available | Rented | Cleaning | Maintenance | Damaged | Lost"
        text condition_notes
        decimal replacement_value
        timestamps created_at
    }

    RENTALS {
        bigint id PK
        string rental_code UK
        bigint customer_id FK
        date start_date
        date return_date
        string status "BOOKED | ACTIVE | COMPLETED | OVERDUE | CANCELLED | VOID"
        decimal total_amount
        string source "online | offline"
        string identity_type "KTP | SIM | Paspor"
        timestamps created_at
    }

    RENTAL_DETAILS {
        bigint id PK
        bigint rental_id FK
        bigint item_unit_id FK
        decimal price_per_day
        string return_status "RENTED | RETURNED | DAMAGED | LOST | VOID"
        timestamps created_at
    }

    DEPOSITS {
        bigint id PK
        bigint rental_id FK
        decimal amount
        string status "HOLD | RETURNED | DEDUCTED"
        timestamps created_at
    }

    PENALTIES {
        bigint id PK
        bigint rental_id FK
        string type "LATE | DAMAGE | LOSS"
        decimal amount
        text reason
        timestamps created_at
    }

    SETTINGS {
        bigint id PK
        string key UK
        string value
        timestamps updated_at
    }

    AUDIT_LOGS {
        bigint id PK
        bigint user_id FK
        string action "CREATE | UPDATE | DELETE | LOGIN | SECURITY"
        string entity_type
        bigint entity_id
        text details
        timestamp created_at
    }
```

---

## 2. Flowchart Alur Bisnis End-to-End (Rental Cycle)

Alur operasional mulai dari pelanggan, pelayanan kasir, serah terima, hingga pemeriksaan pengembalian di gudang:

```mermaid
flowchart TD
    Start([Mulai: Pelanggan Butuh Alat]) --> Choice{Pilih Jalur Akses}
    
    %% Jalur Online Booking
    Choice -->|Online Booking| WebKatalog[Buka Landing Page / Katalog Real-Time]
    WebKatalog --> SelectDates[Pilih Tanggal Sewa & Durasi]
    SelectDates --> AddCart[Pilih Peralatan / Paket Hemat]
    AddCart --> InputCustomer[Isi Data Diri & No. WhatsApp]
    InputCustomer --> SubmitBooking[Kirim Booking: Status BOOKED]
    SubmitBooking --> HoldTimer[Sistem Kunci Stok Unit: Toleransi 2 Jam]
    HoldTimer --> ArriveStore[Pelanggan Datang ke Outlet Toko]
    
    %% Jalur Walk-in Langsung
    Choice -->|Datang Langsung / Walk-In| DirectStore[Datang Langsung ke Toko]
    DirectStore --> KasirScreen[Kasir Buka POS Kasir]
    
    %% Temu di Kasir
    ArriveStore --> KasirScreen
    KasirScreen --> VerifyUnit[Kasir & Pelanggan Cek Fisik Peralatan / QC]
    VerifyUnit --> PricingEngine[Sistem Kalkulasi Tarif Otomatis: Weekday / Weekend]
    PricingEngine --> SetDeposit[Tetapkan Uang Jaminan / Deposit & Titip KTP]
    SetDeposit --> PayInvoice[Pembayaran & Cetak Invoice Struk]
    PayInvoice --> CheckOut[Serah Terima Barang: Status Rental ACTIVE]
    
    %% Masa Pendakian
    CheckOut --> Hiking[Pendakian Gunung / Pemakaian Alat]
    Hiking --> ReturnOutlet[Kembali ke Outlet Meja Pengembalian]
    
    %% Pemeriksaan Pengembalian
    ReturnOutlet --> CheckIn[Staf Lakukan Check-In & Inspeksi Fisik]
    CheckIn --> IsOverdue{Terlambat Kembali?}
    IsOverdue -->|Ya| CalcLateFee[Hitung Denda Otomatis per Jam]
    IsOverdue -->|Tidak| CheckDamage{Ada Kerusakan / Hilang?}
    CalcLateFee --> CheckDamage
    
    CheckDamage -->|Ada Kerusakan| DeductDeposit[Potong Biaya dari Deposit]
    CheckDamage -->|Kondisi Baik & Lengkap| RefundDeposit[Kembalikan Uang Deposit 100%]
    DeductDeposit --> MoveToGudang[Unit Masuk Antrian Gudang: Cleaning / Maintenance]
    RefundDeposit --> MoveToGudang
    
    MoveToGudang --> WarehouseQC[Staf Gudang Cuci / Servis Alat]
    WarehouseQC --> ReadyStock[Unit Bersih & Siap Sewa: Status Available]
    ReadyStock --> Selesai([Selesai: Transaksi COMPLETED])
```

---

## 3. Flowchart Keamanan Login Administrator (Google Authenticator 2FA)

Alur verifikasi login Administrator yang menjamin keamanan berlapis:

```mermaid
flowchart TD
    StartLogin([Admin Buka Halaman Login]) --> InputCreds[Masukkan Alamat Email & Kata Sandi]
    InputCreds --> ValidatePass{Kredensial Cocok di Database?}
    
    ValidatePass -->|Salah| Throttle[Rate Limiter Hit + Pesan Kesalahan]
    Throttle --> InputCreds
    
    ValidatePass -->|Benar| CheckRole{Apakah Akun Admin?}
    CheckRole -->|Bukan: Kasir/Gudang| LoginSuccess[Login Berhasil: Arahkan ke Halaman Tugas]
    
    CheckRole -->|Ya: Role Admin| Check2FA{Apakah 2FA Google Authenticator Aktif?}
    Check2FA -->|Belum Aktif| DirectAdmin[Masuk ke Dashboard + Rekomendasi Aktivasi 2FA]
    
    Check2FA -->|Aktif| Show2FAScreen[Tampilkan Layar Tantangan: Masukkan 6 Digit OTP]
    Show2FAScreen --> SelectMethod{Pilihan Input}
    
    SelectMethod -->|Kode 6-Digit TOTP| EnterTOTP[Buka Aplikasi Google Authenticator di HP]
    EnterTOTP --> VerifyTOTP{Verifikasi TOTP RFC 6238 Valid?}
    
    SelectMethod -->|Kehilangan HP| EnterRecovery[Masukkan 10-Karakter Recovery Code Cadangan]
    EnterRecovery --> VerifyRecovery{Kode Pemulihan Belum Terpakai?}
    
    VerifyTOTP -->|Tidak Cocok / Expired| Error2FA[Tampilkan Galat: Kode Tidak Valid]
    Error2FA --> Show2FAScreen
    
    VerifyRecovery -->|Salah / Sudah Dipakai| Error2FA
    
    VerifyTOTP -->|Valid| LogAudit[Catat Audit Log: Admin 2FA Success]
    VerifyRecovery -->|Valid| InvalidateCode[Hapus Recovery Code yang Digunakan]
    InvalidateCode --> LogAudit
    
    LogAudit --> AdminDashboard([Masuk ke Dashboard Super Admin])
```

---

## 4. Use Case Diagram

Interaksi antara 4 aktor sistem SummitGear:

```mermaid
flowchart LR
    subgraph AKTOR
        Cust((Pelanggan))
        Kasir((Staf Kasir))
        Gudang((Staf Gudang))
        Admin((Super Admin))
    end

    subgraph SISTEM_SUMMITGEAR [Sistem SummitGear POS & Rental]
        UC1[Lihat Katalog & Stok Real-Time]
        UC2[Pesan Alat Online / Bundling]
        UC3[Login PIN Cepat & Kunci Layar]
        UC4[Transaksi Sewa Langsung POS]
        UC5[Validasi Booking Masuk]
        UC6[Cetak Invoice & Thermal Struk]
        UC7[Check-In & Inspeksi Fisik Alat]
        UC8[Kelola Tab Gudang: Cuci & Servis]
        UC9[Login 2FA Google Authenticator]
        UC10[Manajemen Pengguna & Otorisasi PIN]
        UC11[Audit Trail Log & Pengaturan Toko]
    end

    Cust --> UC1
    Cust --> UC2

    Kasir --> UC3
    Kasir --> UC4
    Kasir --> UC5
    Kasir --> UC6
    Kasir --> UC7

    Gudang --> UC7
    Gudang --> UC8

    Admin --> UC9
    Admin --> UC10
    Admin --> UC11
    Admin --> UC4
    Admin --> UC8
```

---

## 5. Panduan Penggunaan & Kredensial Demo Cepat

### Kredensial Pengguna Bawaan (Default Demo Accounts)
| Peran (Role) | Email Login | Kata Sandi | PIN Cepat | Halaman Awal |
| :--- | :--- | :--- | :--- | :--- |
| **Super Admin** | `admin@summitgear.com` | `password123` | `123456` | `/dashboard` |
| **Kasir Utama** | `kasir@summitgear.com` | `password123` | `111111` | `/admin/transactions/create` |
| **Staf Gudang** | `gudang@summitgear.com` | `password123` | `222222` | `/admin/maintenance/kanban` |

### Langkah Aktivasi 2FA Google Authenticator
1. Login sebagai Admin di `http://localhost:8000/login`.
2. Buka menu **Pengaturan** di sidebar navigasi.
3. Gulir ke bagian **Keamanan 2FA (Google Authenticator)**.
4. Klik tombol **Aktifkan Google Authenticator (2FA)**.
5. Pindai kode QR yang muncul menggunakan aplikasi Google Authenticator di ponsel Anda (atau masukkan kunci rahasia manual).
6. Masukkan 6 digit angka yang tampil di ponsel Anda, lalu klik **Konfirmasi & Aktifkan 2FA**.
7. Salin/simpan 8 kode pemulihan cadangan (*Recovery Codes*). Selesai!
