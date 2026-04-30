# PRD: Filament Admin Panel — UX & Page Architecture

## 1. Tujuan

Mendefinisikan struktur menu, klasifikasi halaman (custom vs. standard), dan kebutuhan UX untuk admin panel Filament Yonfootball. Panel ini digunakan oleh internal team (SUPER_ADMIN, OWNER, ADMIN) untuk mengelola katalog produk, memproses order fulfillment, dan memantau transaksi pembayaran.

---

## 2. Role Access Matrix

| Role | Dashboard | Katalog | Operasional | Pemasaran | Sistem |
|---|---|---|---|---|---|
| **SUPER_ADMIN** | ✅ Full | ✅ Full | ✅ Full | ✅ Full | ✅ Full |
| **OWNER** | ✅ Full | 👁 Read | ✅ Full | ✅ Full | 👁 Read |
| **ADMIN** | ✅ Full | ❌ | ✅ Full | ❌ | ❌ |

---

## 3. Struktur Navigasi (Menu Groups)

```
Admin Panel
├── 🏠 Dashboard                    ← Standalone page
│
├── 📦 Katalog
│   ├── Kategori Produk
│   ├── Produk
│   └── (Varian dikelola inline di dalam Produk)
│
├── ⚙️ Operasional
│   ├── Order
│   └── Transaksi
│
├── 🎯 Pemasaran
│   ├── Voucher
│   └── Banner
│
└── 🔧 Sistem
    ├── Pengguna
    └── Audit Log
```

---

## 4. Klasifikasi Halaman

### Legenda
- 🟢 **STANDARD** — `php artisan make:filament-resource`, minimal kustomisasi
- 🟡 **STANDARD+** — resource standar + tambahan custom (actions, columns, atau relation manager)
- 🔴 **CUSTOM** — perlu desain/logika non-standar, tidak cukup dengan scaffolding default

---

### 4.1 Dashboard
**Tipe: 🔴 CUSTOM**

Default Filament dashboard adalah halaman kosong. Kita perlu KPI yang actionable.

**Widgets yang dibutuhkan (semua custom):**

| Widget | Tipe | Deskripsi |
|---|---|---|
| `RevenueStatsOverview` | StatsOverview | Total revenue hari ini / minggu ini / bulan ini (dari `transactions.status = PAID`) |
| `OrderBacklogWidget` | StatsOverview | Jumlah order PENDING yang butuh tindakan (transaksi sudah PAID tapi order belum PROCESSING) |
| `TransactionFunnelWidget` | StatsOverview | Breakdown count: PENDING / PAID / CANCELLED / REFUNDED hari ini |
| `LatestTransactionsWidget` | TableWidget | 10 transaksi terbaru dengan status badge + link ke detail |
| `PendingOrdersWidget` | TableWidget | Order PENDING yang perlu diproses (sudah ada transaksi PAID) |

**Artisan command:**
```bash
php artisan make:filament-page Dashboard --force
php artisan make:filament-widget RevenueStatsOverview --stats-overview
php artisan make:filament-widget OrderBacklogWidget --stats-overview
php artisan make:filament-widget TransactionFunnelWidget --stats-overview
php artisan make:filament-widget LatestTransactionsWidget --table
php artisan make:filament-widget PendingOrdersWidget --table
```

---

### 4.2 Kategori Produk (`ProductCategory`)
**Tipe: 🟢 STANDARD**

CRUD sederhana. Dua kolom saja: `name` dan `sort_order`.

**Kebutuhan khusus:**
- Aktifkan `reorderable()` di table agar sort_order bisa drag-and-drop langsung
- Tidak perlu halaman View (hanya List + Create + Edit)

**Artisan command:**
```bash
php artisan make:filament-resource ProductCategory --generate --no-interaction
```

---

### 4.3 Produk (`Product`)
**Tipe: 🟡 STANDARD+**

Form standar, tapi perlu beberapa tambahan:

**Tambahan custom:**
- `FileUpload` untuk `leading_url` dan `background_url` dengan preview gambar
- `KeyValue` atau JSON editor untuk `inputs` (field dinamis form customer)
- Toggle action di table: `is_active` dan `is_popular` bisa diubah langsung dari list (tanpa buka form)
- **RelationManager `ProductVariantsRelationManager`** untuk mengelola varian inline di halaman Edit Produk

**Artisan command:**
```bash
php artisan make:filament-resource Product --generate --no-interaction
php artisan make:filament-relation-manager ProductResource variants name --no-interaction
```

---

### 4.4 Varian Produk (`ProductVariant`)
**Tipe: 🔴 CUSTOM (dikelola sebagai RelationManager)**

Varian **tidak punya halaman sendiri** di navigasi. Dikelola sepenuhnya sebagai tab dalam halaman Edit Produk.

**Kebutuhan di RelationManager:**
- Kolom harga ditampilkan dalam format Rupiah (`Rp 50.000`)
- `price_discount` diberi badge "PROMO" jika > 0
- Toggle `is_active` dari dalam tabel relation
- Validasi: `price_discount` tidak boleh melebihi `price`

---

### 4.5 Order (`Order`)
**Tipe: 🔴 CUSTOM**

Ini halaman paling kritis secara UX. Admin tidak boleh mengedit order secara bebas — semua perubahan status harus lewat **action buttons** yang punya guard state machine.

**Halaman yang dibuat:**

| Halaman | Tipe | Keterangan |
|---|---|---|
| `ListOrders` | Standard table | Filter by status, tanggal, product. Status badge berwarna |
| `ViewOrder` | 🔴 Custom | Read-only. Menampilkan info order, transaksi terkait, timeline status, dan tombol aksi |
| `EditOrder` | ❌ Tidak dibuat | Perubahan hanya via actions, bukan form edit bebas |

**Custom Actions di `ViewOrder`:**

| Action | Trigger | Guard |
|---|---|---|
| `TandaiProcessing` | Tombol utama | Hanya muncul jika `order.status = PENDING` dan `transaction.status = PAID` |
| `TandaiSuccess` | Tombol konfirmasi | Hanya muncul jika `order.status = PROCESSING`. Wajib isi `notes`. |
| `TambahCatatan` | Tombol sekunder | Selalu tersedia, append ke field `notes` |

**Konten `ViewOrder`:**
- **Section "Detail Order":** ticket_number, product variant, user_inputs (ditampilkan key-value dari JSON), tanggal dibuat
- **Section "Status Transaksi Terkait":** status badge transaksi, customer info, payment method, invoice number (link ke ViewTransaction)
- **Section "Timeline Order":** History dari `order_state_histories` — siapa, kapan, dari status apa ke apa
- **Section "Catatan Internal":** field `notes` (textarea read-only + tombol "Tambah Catatan")

**Artisan command:**
```bash
php artisan make:filament-resource Order --generate --view --no-interaction
php artisan make:filament-action TandaiProcessingAction --no-interaction
php artisan make:filament-action TandaiSuccessAction --no-interaction
```

---

### 4.6 Transaksi (`Transaction`)
**Tipe: 🔴 CUSTOM**

Halaman ini **read-mostly**. Admin jarang mengedit transaksi secara langsung; yang ada hanya beberapa aksi terkontrol.

**Halaman yang dibuat:**

| Halaman | Tipe | Keterangan |
|---|---|---|
| `ListTransactions` | Standard + filter heavy | Search global (email, phone, invoice). Filter: status, payment_method, date range |
| `ViewTransaction` | 🔴 Custom | Read-only. Snapshot produk, snapshot pembayaran, info customer, order terkait |
| `EditTransaction` | ❌ Tidak dibuat | — |

**Filters di `ListTransactions`:**
- `SelectFilter` status (PENDING / PAID / CANCELLED / REFUNDED)
- `SelectFilter` payment method
- `DateRangeFilter` created_at (atau `Filter` dengan 2 DatePicker)
- `Filter` custom: cari by `customer_email` atau `customer_phone`

**Custom Actions di `ViewTransaction`:**

| Action | Guard | Keterangan |
|---|---|---|
| `TandaiPAID` | Hanya jika status PENDING | Manual override, wajib isi alasan |
| `TandaiCANCELLED` | Hanya jika status PENDING | Wajib isi alasan |
| `ProsesRefund` | Hanya jika status PAID | Wajib isi alasan, trigger event audit |

**Konten `ViewTransaction`:**
- **Section "Info Pembayaran":** invoice_number, status badge, payment_method, payment_url (jika PENDING), paid_at
- **Section "Info Customer":** name, phone, email
- **Section "Produk":** product_name + expandable `product_snapshot` (JSON viewer)
- **Section "Pembayaran Provider":** expandable `payment_snapshot` (JSON viewer)
- **Section "Order Terkait":** card ringkas order dengan status dan link ke ViewOrder

**Artisan command:**
```bash
php artisan make:filament-resource Transaction --generate --view --no-interaction
```

---

### 4.7 Voucher (`Voucher`)
**Tipe: 🟡 STANDARD+**

CRUD standar, tapi perlu beberapa tambahan UI:

**Tambahan custom:**
- `DateTimePicker` untuk `effective_date` dan `ended_date`
- Di tabel list: tampilkan progress bar atau badge `usage_count / usage_limit`
- Toggle `is_active` langsung dari tabel (tanpa buka form)
- Validasi: `ended_date` harus setelah `effective_date`
- Badge "EXPIRED" jika `ended_date` sudah lewat meski `is_active = true`

**Artisan command:**
```bash
php artisan make:filament-resource Voucher --generate --no-interaction
```

---

### 4.8 Banner (`Banner`)
**Tipe: 🟡 STANDARD+**

CRUD standar dengan tambahan:

**Tambahan custom:**
- `FileUpload` untuk `cover_url` dengan preview
- Aktifkan `reorderable()` di tabel (drag-and-drop `sort_order`)
- Kolom `cover_url` ditampilkan sebagai `ImageColumn` di tabel

**Artisan command:**
```bash
php artisan make:filament-resource Banner --generate --no-interaction
```

---

### 4.9 Pengguna (`User`)
**Tipe: 🟢 STANDARD**

CRUD standar, hanya bisa diakses oleh SUPER_ADMIN.

**Kebutuhan:**
- `SelectColumn` untuk `role` (SUPER_ADMIN / OWNER / ADMIN)
- Sembunyikan kolom `password` dari tampilan
- Tidak bisa delete akun sendiri (guard)

**Artisan command:**
```bash
php artisan make:filament-resource User --generate --no-interaction
```

---

### 4.10 Audit Log (`Audit`)
**Tipe: 🔴 CUSTOM (Read-Only)**

Tidak ada Create, Edit, atau Delete. Murni log viewer.

**Kebutuhan:**
- Table-only resource (`--view` tidak perlu, tapi tidak ada form create/edit)
- Kolom: tanggal, user, `resource_type`, `resource_id`, `type` (badge INSERT/UPDATE/DELETE), link ke resource terkait
- Filter: `resource_type`, `type`, date range, `users_id`
- `resource_snapshot` bisa expand (modal atau JSON collapsible)
- Disable semua bulk actions

**Artisan command:**
```bash
php artisan make:filament-resource Audit --generate --view --no-interaction
# Kemudian hapus CreateAudit, EditAudit, dan semua form schema
```

---

## 5. Ringkasan Klasifikasi

| Resource / Page | Tipe | Artisan Command Cukup? | Custom Effort |
|---|---|---|---|
| Dashboard | 🔴 CUSTOM | ❌ | Tinggi — 5 custom widgets |
| Kategori Produk | 🟢 STANDARD | ✅ | Minimal — tambah reorderable() |
| Produk | 🟡 STANDARD+ | Sebagian | Sedang — FileUpload, toggle actions, RelationManager |
| Varian Produk | 🔴 CUSTOM | ❌ | Sedang — hanya RelationManager, tidak ada halaman sendiri |
| Order | 🔴 CUSTOM | ❌ | Tinggi — ViewOrder custom, state machine actions, timeline |
| Transaksi | 🔴 CUSTOM | ❌ | Tinggi — ViewTransaction custom, actions terkontrol, heavy filter |
| Voucher | 🟡 STANDARD+ | Sebagian | Ringan — DateTimePicker, progress usage, toggle |
| Banner | 🟡 STANDARD+ | Sebagian | Ringan — FileUpload, reorderable() |
| Pengguna | 🟢 STANDARD | ✅ | Minimal — guard delete diri sendiri |
| Audit Log | 🔴 CUSTOM | ❌ | Sedang — hapus semua form, tambah JSON viewer |

---

## 6. Kebutuhan Tambahan (Cross-Cutting)

### 6.1 Navigasi & Menu
- Sembunyikan group menu berdasarkan role via `canAccess()` atau `navigationGroup` guard
- Badge counter di menu "Order" untuk jumlah order yang butuh tindakan (PENDING + transaksi PAID)
- Ikon yang relevan di setiap menu item (heroicons sudah bundled di Filament)

### 6.2 Panel Provider — Hal yang Perlu Diupdate
```php
// Di AdminPanelProvider.php, tambahkan:
->navigationGroups([
    NavigationGroup::make('Katalog')->icon('heroicon-o-cube'),
    NavigationGroup::make('Operasional')->icon('heroicon-o-bolt'),
    NavigationGroup::make('Pemasaran')->icon('heroicon-o-megaphone'),
    NavigationGroup::make('Sistem')->icon('heroicon-o-cog-6-tooth'),
])
->databaseNotifications()  // untuk notifikasi in-app (opsional)
->globalSearch(true)       // search global lintas resource
```

### 6.3 UX Patterns yang Harus Konsisten
- **Status badges:** Gunakan warna konsisten — PENDING=kuning, PAID/SUCCESS=hijau, CANCELLED=merah, PROCESSING=biru, REFUNDED=ungu
- **Currency format:** Semua field harga selalu tampil `Rp X.XXX` (bukan integer mentah)
- **Konfirmasi destruktif:** Semua action yang mengubah status keuangan (CANCELLED, REFUNDED) wajib pakai `requiresConfirmation()` + `form` untuk alasan
- **Skeleton loading:** Gunakan Filament loading states bawaan, tidak perlu custom
- **Notifikasi:** Setiap action sukses/gagal wajib ada `Notification::make()` toast

### 6.4 Global Search
Resource yang harus bisa dicari dari global search Filament:
- Order (by `ticket_number`)
- Transaksi (by `invoice_number`, `customer_email`, `customer_phone`)
- Produk (by `name`, `code`)
- Voucher (by `code`, `name`)

---

## 7. Urutan Pengerjaan yang Disarankan

Urutan ini meminimalkan blocker antar resource:

```
1. Kategori Produk     ← paling simpel, tidak punya dependency
2. Produk + Varian     ← Varian butuh Produk ada dulu
3. Voucher             ← independent
4. Banner              ← independent
5. Transaksi           ← butuh Order ada dulu (relation)
6. Order               ← inti operasional, butuh Transaksi
7. Audit Log           ← bisa dikerjakan kapan saja
8. Pengguna            ← terakhir, SUPER_ADMIN only
9. Dashboard Widgets   ← dikerjakan setelah data dari resource lain sudah ada
```
