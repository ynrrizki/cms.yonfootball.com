# PRODUCT REQUIREMENTS DOCUMENT (PRD): GAME TOP-UP PLATFORM

## 1. PROJECT OVERVIEW
* **Goal:** Build a high-performance game top-up web application that automates payment verification and customer communication via WhatsApp, while maintaining a manual fulfillment backend.
* **Target:** Gamers looking for fast, reliable, and transparent top-up services.

## 2. FUNCTIONAL REQUIREMENTS
### 2.1 Product & Catalog Management
* **Dynamic Catalog:** Display game categories (Mobile, PC, Console) with interactive icons.
* **Product Variants:** Support multiple nominal values (e.g., 100 Diamonds, 500 Credits) per game with real-time price updates.

### 2.2 Order & Validation Flow
* **Smart Input Form:** Context-aware input fields (User ID, Zone ID, Server) based on the selected game.
* **Input Masking:** Basic regex-based validation to minimize user error before order submission.

### 2.3 Automated Communication (WhatsApp API)
* **Phase 1 (Post-Order):** Send Ticket ID, Order Queue number, and Midtrans Snap link immediately.
* **Phase 2 (Post-Payment):** Automated notification upon successful payment confirmation from Midtrans Webhook.
* **Phase 3 (Processing):** Trigger notification when Admin clicks "Process" in the dashboard.
* **Phase 4 (Success):** Trigger notification when Admin confirms the top-up is completed.

### 2.4 Payment Gateway Integration
* **Midtrans Snap:** Integrated checkout for various payment methods (QRIS, E-Wallet, Virtual Accounts).
* **Webhook Listener:** Automated status updates (Pending -> Paid) without manual admin intervention.

### 2.5 Order & Transaction Logic (Core Domain)
* **Entity Separation (Wajib):**
	* `orders` = representasi pekerjaan fulfillment top-up (operasional internal).
	* `transactions` = representasi lifecycle pembayaran (financial lifecycle).
* **Creation Flow (Canonical):**
	1. Customer pilih product variant + isi user inputs.
	2. Sistem validasi input + hitung harga final.
	3. Sistem buat `order` dengan status awal `PENDING`.
	4. Sistem buat `transaction` dengan status awal `PENDING`, generate `invoice_number`, dan minta `payment_url` dari Midtrans Snap.
	5. Sistem kirim WhatsApp Phase 1 (ticket + invoice + payment link).

### 2.6 State Machine: Order
* **Status valid:** `PENDING` -> `PROCESSING` -> `SUCCESS`.
* **Aturan transisi:**
	* `PENDING` -> `PROCESSING` hanya boleh jika `transaction.status = PAID`.
	* `PROCESSING` -> `SUCCESS` hanya boleh oleh role internal (ADMIN/OWNER/SUPER_ADMIN).
	* `SUCCESS` bersifat final (immutable), perubahan hanya via kompensasi operasional + audit log.
* **Operational notes:**
	* `processed_by` wajib terisi saat transisi ke `PROCESSING`.
	* `completed_at` wajib terisi saat transisi ke `SUCCESS`.
	* `notes` dipakai untuk jejak manual handling dan investigasi.

### 2.7 State Machine: Transaction
* **Status valid:** `PENDING`, `PAID`, `CANCELLED`, `REFUNDED`.
* **Aturan transisi yang diizinkan:**
	* `PENDING` -> `PAID` (webhook Midtrans valid).
	* `PENDING` -> `CANCELLED` (expired/deny/cancel dari provider).
	* `PAID` -> `REFUNDED` (refund valid dari admin + provider).
* **Aturan transisi yang dilarang:**
	* `PAID` -> `PENDING`
	* `CANCELLED` -> `PAID`
	* `REFUNDED` -> status lain
* **Financial freeze rules:**
	* `product_snapshot` dan `payment_snapshot` wajib disimpan sebagai immutable snapshot pada saat transaksi dibuat/diperbarui oleh provider.
	* Semua perubahan status transaksi wajib menyimpan metadata asal event (source: webhook/admin/system).

### 2.8 Pricing, Voucher, and Final Amount Rules
* **Harga final variant:**
	* Gunakan `price_discount` jika aktif dan > 0.
	* Jika tidak, fallback ke `price`.
* **Voucher validation pipeline:**
	1. `code` valid dan `is_active = true`.
	2. Tanggal transaksi berada di antara `effective_date` dan `ended_date` (jika keduanya diisi).
	3. `usage_limit` belum terlampaui (`usage_count < usage_limit`, jika limit diisi).
* **Voucher calculation rules:**
	* `price_flat` mengurangi nominal secara langsung.
	* `price_percentage` mengurangi berdasarkan persentase.
	* Jika dua nilai tersedia, terapkan rule prioritas bisnis eksplisit (default: pilih diskon terbesar, dengan floor total >= 0).
* **Nilai pembayaran akhir:**
	* `final_amount = max(0, variant_effective_price - voucher_discount)`.
	* Nilai `final_amount` yang dipakai checkout harus disimpan di snapshot transaksi untuk mencegah drift harga.

### 2.9 Midtrans Webhook, Idempotency, and Retry
* **Webhook security:**
	* Validasi signature/HMAC Midtrans sebelum memproses event.
	* Tolak request dengan signature tidak valid (HTTP 401/403 sesuai kebijakan).
* **Idempotency:**
	* Satu event provider (`order_id`/`transaction_id` + `transaction_status`) hanya boleh dieksekusi sekali.
	* Event duplikat harus di-ack tanpa mengubah state final.
* **Ordering policy:**
	* Terapkan last-valid-state-wins berdasarkan aturan transisi yang diizinkan, bukan urutan kedatangan request semata.
* **Retry policy:**
	* Jika downstream gagal (DB lock/network issue), event masuk queue retry dengan exponential backoff.
	* Setelah retry limit tercapai, tandai sebagai dead-letter untuk investigasi manual.

### 2.10 Cross-Entity Synchronization Rules
* **Saat transaksi jadi `PAID`:**
	* Order tetap `PENDING` sampai admin mulai proses fulfillment.
	* Sistem kirim WhatsApp Phase 2 (payment confirmed).
* **Saat order jadi `PROCESSING`:**
	* Sistem kirim WhatsApp Phase 3.
* **Saat order jadi `SUCCESS`:**
	* Sistem kirim WhatsApp Phase 4.
* **Saat transaksi jadi `CANCELLED`:**
	* Order tidak boleh pindah ke `PROCESSING`.
	* Tandai order sebagai non-actionable melalui notes/internal marker sampai ada ekspansi enum order.
* **Saat transaksi jadi `REFUNDED`:**
	* Wajib ada jejak alasan refund dan actor.
	* Jika fulfillment belum dilakukan, order tetap tidak boleh diproses.

### 2.11 Admin Operations & Guardrails
* **Role constraints:**
	* Hanya role internal (`ADMIN`, `OWNER`, `SUPER_ADMIN`) yang bisa ubah status order operasional.
* **Concurrency guard:**
	* Gunakan transaksi database/locking saat update status untuk mencegah double-processing.
* **Auditability:**
	* Perubahan status order/transaksi harus masuk ke `audits` dengan snapshot sebelum/sesudah.
* **Manual intervention mode:**
	* Saat Midtrans/WA outage, admin bisa lakukan update terkontrol dengan mandatory notes.
	* Setelah layanan pulih, jalankan reconciliation job untuk sinkronisasi status.

## 3. NON-FUNCTIONAL REQUIREMENTS
### 3.1 Performance
* **Responsiveness:** UI/UX must load in < 2 seconds.
* **API Latency:** WhatsApp message triggers must occur within < 3 seconds of status change.
* **Concurrent Handling:** System must handle multiple concurrent Midtrans Webhooks using an idempotent logic.

### 3.2 Security & Compliance
* **Data Privacy:** Compliance with UU PDP (Indonesia); PII (Phone numbers/IDs) must be encrypted and anonymized for analytics.
* **Transaction Security:** Use HMAC signatures for Webhook validation to prevent unauthorized status changes.
* **Encryption:** Enforced HTTPS/TLS for all data transfers.

### 3.3 Reliability & Availability
* **Uptime Target:** 99.9% availability during peak gaming hours.
* **Retry Mechanism:** Implementation of a "Queue & Retry" system for WhatsApp API failures.
* **Fault Tolerance:** Graceful degradation if Midtrans or WhatsApp Gateway is down.

### 3.4 Scalability & Maintenance
* **Containerization:** Fully Dockerized architecture for seamless deployment.
* **Modularity:** Decoupled architecture to allow swapping WhatsApp or Payment providers without refactoring core logic.
* **High-Performance Stack:** Optimized resource usage (CPU/RAM) to maintain low overhead under high traffic.

### 3.5 Usability
* **Mobile-First UI:** Fully responsive design optimized for mobile gamers.
* **Immediate Feedback:** Visual cues (spinners/alerts) for every asynchronous action.

### 3.6 Observability & Reconciliation
* **Traceability:** Semua order dan transaksi harus punya correlation key (`ticket_number` <-> `invoice_number`) yang mudah ditelusuri.
* **Operational Dashboard:** Tampilkan metrik minimum: pending payment count, processing backlog, success rate, cancel/refund rate.
* **Daily Reconciliation:** Job harian untuk mendeteksi mismatch antara status internal dan provider (Midtrans).
* **Alerting:** Trigger alert saat anomali terjadi (lonjakan webhook gagal, retry queue menumpuk, atau mismatch reconciliation).

## 4. ACCEPTANCE CRITERIA (ORDER & TRANSACTION)
1. Sistem hanya membuat order/transaksi jika input user valid dan variant aktif.
2. Sistem tidak mengizinkan order diproses sebelum transaksi `PAID`.
3. Webhook duplikat tidak menimbulkan duplicate state transition.
4. Snapshot harga/pembayaran tersimpan dan tidak berubah setelah transaksi dibuat.
5. Notifikasi WhatsApp terkirim sesuai fase status (1 sampai 4) dengan retry saat gagal.
6. Semua transisi status kritikal menghasilkan jejak audit.
7. Sistem mampu menangani skenario `CANCELLED` dan `REFUNDED` tanpa merusak integritas order.

## 5. EDGE CASES WAJIB DITANGANI
* Customer bayar setelah invoice expired.
* Customer klik bayar dua kali / menerima webhook duplikat.
* Harga produk berubah saat checkout sedang berjalan (harus tetap pakai snapshot transaksi).
* Voucher mencapai limit penggunaan pada saat trafik tinggi (race condition).
* Admin mencoba memproses order dari transaksi yang belum `PAID`.
* Provider callback terlambat setelah order sudah ditindak manual.

**ERD (Referensi Desain Basis Data)**

- **Lokasi file ERD:** lihat [docs/erd.dbml](docs/erd.dbml)
- **Klasifikasi relevansi ERD (wajib dipahami agent):**
	- **ACTIVE_CORE:** tabel/enum yang menjadi source of truth implementasi saat ini.
	- **PLANNED_NEXT:** desain valid untuk fase pengembangan berikutnya, bukan default implementasi hari ini.
	- **DEPRECATED:** elemen yang tidak boleh dipakai untuk implementasi baru.
- **Default policy untuk AI Agent:**
	- Gunakan hanya komponen **ACTIVE_CORE** untuk task normal (fix/feature current scope).
	- Gunakan **PLANNED_NEXT** hanya jika user secara eksplisit meminta schema evolution atau phase-next migration.
	- Tolak penggunaan elemen **DEPRECATED** dan sarankan alternatif aktif.
- **Catatan penting:** file erd.dbml adalah representasi abstrak dari model data (ERD) yang digunakan oleh fitur inti aplikasi. Artinya:
	- erd.dbml fokus pada hubungan entitas dan atribut fungsional yang diperlukan untuk fitur inti (produk, varian produk, pesanan, transaksi, voucher, audit, user, banner).
	- erd.dbml **tidak** harus mencakup semua detail teknis yang ada pada migration (tipe kolom spesifik, index teknis, constraint DB-level tambahan, atau optimasi engine). Migration dapat menambah/ubah detail implementasi tanpa mengubah tujuan fungsional yang digambarkan di erd.dbml.
	- Gunakan erd.dbml sebagai panduan desain konseptual saat menulis migration dan model Eloquent; selalu sesuaikan implementasi migrations dengan kebutuhan performa, backwards-compatibility, dan constraint lingkungan produksi.

Jika mau, saya bisa bantu konversi `erd.dbml` ke diagram visual (PNG/SVG) atau membuat draft migrations untuk tabel inti.
