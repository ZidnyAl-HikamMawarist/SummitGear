# SummitGear — Database Architecture & ERD Blueprint

Dokumentasi skema basis data PostgreSQL / SQLite untuk aplikasi SummitGear Rental & POS.

---

## 📊 Entity Relationship Diagram (Mermaid)

```mermaid
erDiagram
    USERS ||--o{ AUDIT_LOGS : "approves/performs"
    USERS ||--o{ INSPECTIONS : "inspects"
    CUSTOMERS ||--o{ RENTALS : "places"
    CUSTOMERS ||--o{ DOCUMENT_ACCESS_LOGS : "accessed"
    INVENTORY_ITEMS ||--o{ ITEM_UNITS : "has physical"
    INVENTORY_ITEMS ||--o{ PRICING_RULES : "has rates"
    INVENTORY_ITEMS ||--o{ PACKAGE_ITEMS : "contains"
    RENTALS ||--|{ RENTAL_DETAILS : "includes"
    RENTALS ||--o{ PAYMENTS : "paid via"
    RENTALS ||--o{ DEPOSITS : "secures"
    RENTALS ||--o{ PENALTIES : "penalized"
    RENTAL_DETAILS ||--|| ITEM_UNITS : "references"
    RENTAL_DETAILS ||--o{ INSPECTIONS : "checked by"

    CUSTOMERS {
        bigint id PK
        string name
        string nik "Unique or guarded"
        string phone "Indexed"
        string email
        text address
        string id_photo_url
        timestamp consent_at
        boolean is_blacklisted "Default: false"
        text blacklist_notes "Nullable"
        timestamps created_at_updated_at
    }

    RENTALS {
        bigint id PK
        string rental_code "Unique TRX-YYYYMMDD-XXXX"
        bigint customer_id FK
        datetime start_date
        date end_date
        datetime scheduled_return_time
        datetime actual_return_time
        timestamp pickup_extended_until "Nullable (Contingency tolerance)"
        decimal total_price
        decimal down_payment_amount
        decimal discount
        string status "BOOKED, DP_PAID, PAID, PICKED_UP, RENTED_OUT, OVERDUE, PENDING_SETTLEMENT, DISPUTED, COMPLETED, CANCELLED, VOID, DEFAULTED"
        string payment_type "dp, full"
        string source "walk_in, online"
        timestamp expires_at "10 min hold timeout"
        text settlement_notes "Nullable (Void / Defaulted notes)"
        timestamps created_at_updated_at
    }

    RENTAL_DETAILS {
        bigint id PK
        bigint rental_id FK
        bigint item_unit_id FK
        decimal price_per_day
        string return_status "RENTED, RETURNED_GOOD, RETURNED_DAMAGED, RETURNED_LOST"
        timestamps created_at_updated_at
    }

    ITEM_UNITS {
        bigint id PK
        bigint item_id FK
        string serial_number "Unique (KAT-ID-XXXX)"
        string status "Available, Rented, Maintenance, Decommissioned"
        text condition_notes
        decimal replacement_value
        soft_deletes deleted_at
        timestamps created_at_updated_at
    }

    INVENTORY_ITEMS {
        bigint id PK
        string sku "Unique"
        string name
        string category
        string rental_type "daily"
        decimal price_per_day
        boolean is_package
        string photo_url
        timestamps created_at_updated_at
    }

    DEPOSITS {
        bigint id PK
        bigint rental_id FK
        string type "CASH, DOC"
        decimal amount "Nullable for DOC"
        string doc_type "KTP, SIM, PASSPORT"
        string status "HELD, RETURNED, FORFEITED"
        datetime retention_deadline
        timestamps created_at_updated_at
    }

    PAYMENTS {
        bigint id PK
        bigint rental_id FK
        string type "DP, FULL, settlement, penalty, rental_balance"
        string method "CASH, QRIS, TRANSFER, DEPOSIT_DEDUCTION"
        decimal amount
        datetime paid_at
        string reference_number
        timestamps created_at_updated_at
    }

    PENALTIES {
        bigint id PK
        bigint rental_id FK
        string reason
        decimal amount
        boolean is_settled
        boolean is_override
        text override_reason
        timestamps created_at_updated_at
    }
```

---

## 🛡️ Database Constraints & Business Invariants

1. **Anti-Hoarding Exclusion**:
   - Transaksi online yang belum dibayar mengunci unit dengan status `PENDING_PAYMENT` dan batas kadaluwarsa `expires_at` maksimal 10 menit.
2. **Exclusion Check on Unit Deletion**:
   - Unit fisik (`item_units`) tidak boleh dihapus jika masih terikat pada `rental_details` dengan status sewa aktif/terjadwal (`NOT IN ('COMPLETED', 'CANCELLED', 'VOID')`).
3. **Pessimistic Locking (`lockForUpdate`)**:
   - Seluruh alur checkout publik dan kasir mengunci baris unit terkait di database untuk menjamin integritas data (ACID) dan mencegah race condition.
4. **Deposit Settlement Balance**:
   - Pelunasan denda/kekurangan sewa dari deposit cash secara otomatis menciptakan record pembayaran `type = 'penalty', method = 'DEPOSIT_DEDUCTION'`, memastikan neraca keuangan toko selalu seimbang.
