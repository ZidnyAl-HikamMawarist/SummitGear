# SummitGear — Operations & Action Contract

Dokumentasi kontrak aksi Livewire, otorisasi PIN supervisor, dan protokol status penyewaan.

---

## 🔐 1. Supervisor PIN Approval Protocol

Untuk aksi-aksi berdampak tinggi terhadap finansial dan operasional, sistem menerapkan otorisasi two-step PIN:

### Aksi yang Memerlukan PIN:
1. `overridePenalty`: Penyesuaian atau diskon denda keterlambatan/kerusakan di luar SOP standar.
2. `writeOffDispute`: Penghapusan sengketa piutang macet (`DEFAULTED`) dan auto-blacklist pelanggan.
3. `cancelBookingRefund`: Pembatalan booking online dengan pengembalian DP uang tunai/transfer ke pelanggan.
4. `voidTransaction`: Pembatalan invoice transaksi sewa yang sudah tercatat.

### Alur Kerja (Workflow):
```
Client (Livewire) ➔ dispatch('openPinApproval', {action, payload})
                  ➔ PinApprovalModal validates PIN against 'admin_supervisor_pin'
                  ➔ If valid, generates signed session token: pin_approval_token_{action}
                  ➔ Dispatches 'pinApproved' back to caller
                  ➔ Caller verifies hash_equals(storedToken, token) within 5 minutes expiry
                  ➔ Executes sensitive action inside DB::transaction
                  ➔ Logs event to audit_logs table
```

---

## 🔄 2. State Transition Contract (Rental Lifecycle)

```
[Public Booking] ➔ PENDING_PAYMENT (10m TTL) ➔ DP_PAID / PAID
[Walk-in POS]    ➔ BOOKED / PAID
      │
      ▼
[Handover Check-Out] ➔ RENTED_OUT (Requires KTP verification or Cash Deposit)
      │
      ├─ Extension Requested ➔ RENTED_OUT (end_date extended, new charge appended)
      │
      ▼
[Return Check-In] ➔ Inspected
      ├─ Good Condition, No Late ➔ COMPLETED (Deposit RETURNED)
      └─ Damaged / Overdue       ➔ PENDING_SETTLEMENT (Penalty created)
            │
            ├─ Paid in full / Deposit covers ➔ COMPLETED
            └─ Refusal / Uncollectible       ➔ DEFAULTED (PIN needed, Auto-Blacklist)
```

---

## 📋 3. Error Codes & Validation Responses

| Kode Error / Atribut | Keterangan | Tindakan Sistem |
| :--- | :--- | :--- |
| `is_customer_blacklisted` | NIK atau Nomor HP terdaftar di blacklist | Memblokir tombol Submit di POS & Booking Publik |
| `unit_conflict` | Unit fisik bertabrakan dengan sewa lain | Mencegah perpanjangan sewa / penambahan keranjang |
| `delete_unit` | Unit sedang disewa atau terikat jadwal | Mencegah penghapusan fisik unit inventaris |
| `pin_authorization_failed` | PIN salah atau token kedaluwarsa | Menolak eksekusi aksi sensitif |
