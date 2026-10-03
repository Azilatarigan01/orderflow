# Dokumen Strategi Testing & Laporan Pengujian (Tahap 9)
**Sistem Aplikasi**: OrderFlow Enterprise Procurement System  
**Framework**: Laravel 11 (PHP 8.2+), React 18 SPA Module, Tailwind CSS, SQLite / PostgreSQL  
**Standar Pengujian**: Good Corporate Governance, Segregation of Duties (SoD), 3-Way Matching, OWASP Security Safeguards  
**Status Eksekusi**: 164 Automated Tests Passed (731 Assertions), 100% Green  

---

## 1. Manual Testing (36 Test Cases)

Berikut adalah daftar 36 skenario uji manual yang mencakup 6 domain utama: *Happy Path*, *Validation*, *Authorization*, *Perubahan Status*, *File Upload*, dan *Perhitungan*.

### Kategori A: Happy Path (Alur Sukses Utama)

| Test ID | Modul / Fitur | Deskripsi Uji | Pra-kondisi | Langkah-langkah Pengujian | Hasil yang Diharapkan | Status |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **TC-001** | PR Creation | Pengajuan PR lengkap dengan multi-item | Login sebagai `requester` | 1. Buka menu PR Baru.<br>2. Masukkan judul & tanggal.<br>3. Tambahkan 2 item barang.<br>4. Klik "Kirim Pengajuan". | PR tersimpan dengan status `submitted`, total terhitung otomatis, antrean approval terbentuk. | **PASS** |
| **TC-002** | Multi-tier Approval | Persetujuan bertingkat Manager & Finance | PR > Rp 5.000.000 berstatus `submitted` | 1. Login sebagai `manager`, approve Tier 1.<br>2. Login sebagai `finance`, approve Tier 2. | Tier 1 status `approved`, lanjut ke Tier 2. Setelah Tier 2 disetujui, status PR berubah menjadi `approved`. | **PASS** |
| **TC-003** | RFQ & Scoring | Perbandingan penawaran vendor & pemilihan pemenang | PR `approved` (> Rp 10 Juta) | 1. Login sebagai `procurement`.<br>2. Input 2 penawaran vendor.<br>3. Buka matrix scoring.<br>4. Pilih vendor terbaik & submit. | Penawaran pemenang terpilih, audit trail tercatat, status quotation berubah menjadi `awarded`. | **PASS** |
| **TC-004** | Penerbitan PO | Pembuatan Purchase Order dari penawaran terpilih | Quotation telah di-`awarded` | 1. Masuk menu PO Baru.<br>2. Pilih PR & vendor terpilih.<br>3. Set tanggal target pengiriman.<br>4. Terbitkan PO. | PO terbit dengan nomor unik `PO-YYYY-MM-XXXX`, status `issued`, email notifikasi terkirim. | **PASS** |
| **TC-005** | Penerimaan Barang | Penerimaan fisik barang di gudang (GR) | PO berstatus `issued` | 1. Login sebagai `warehouse`.<br>2. Buat GR baru referensi PO.<br>3. Input no surat jalan & qty 100%.<br>4. Simpan. | Dokumen GR tersimpan, status PO otomatis berubah menjadi `completed`, log fisik tercatat. | **PASS** |
| **TC-006** | 3-Way Match & Bayar | Verifikasi faktur & pencairan pembayaran | PO & GR berstatus `completed` | 1. Login sebagai `finance`.<br>2. Input invoice vendor.<br>3. Lakukan verifikasi 3-Way Match.<br>4. Proses bayar. | Sistem memvalidasi kesesuaian PO-GR-Invoice, status invoice berubah menjadi `paid`. | **PASS** |

---

### Kategori B: Validation (Validasi Input & Format Data)

| Test ID | Modul / Fitur | Deskripsi Uji | Pra-kondisi | Langkah-langkah Pengujian | Hasil yang Diharapkan | Status |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **TC-007** | Form PR | Pengajuan PR tanpa rincian item barang | Login sebagai `requester` | 1. Isi judul PR & tanggal butuh.<br>2. Hapus seluruh baris item.<br>3. Klik submit. | Form ditolak, muncul pesan error: *"Daftar item pengadaan minimal harus memiliki 1 barang."* | **PASS** |
| **TC-008** | Form PR | Tanggal kebutuhan di masa lalu (backdated) | Login sebagai `requester` | 1. Pilih tanggal kebutuhan kemarin.<br>2. Submit form. | Ditolak oleh validasi `after_or_equal:today`. | **PASS** |
| **TC-009** | Form PR | Format harga dengan titik ribuan (Indonesian Format) | Login sebagai `requester` | 1. Input harga unit `15.000.000`.<br>2. Input qty `2`.<br>3. Submit form. | Sistem berhasil melakukan sanitasi string menjadi float numerik `30000000`. | **PASS** |
| **TC-010** | Vendor Master | Validasi duplikasi kode vendor dan email | Login sebagai `procurement` | 1. Buat vendor dengan email yang sudah ada.<br>2. Klik simpan. | Sistem menolak dengan pesan error duplikasi email unik. | **PASS** |
| **TC-011** | Pembatalan PO | Alasan pembatalan PO wajib diisi | PO aktif berstatus `issued` | 1. Procurement klik tombol batal PO.<br>2. Kosongkan textarea alasan.<br>3. Submit modal. | Form dicegah submit, validasi error muncul: *"Alasan pembatalan wajib diisi."* | **PASS** |
| **TC-012** | Filter Laporan | Validasi rentang tanggal melebihi 365 hari | Login sebagai `finance` / `manager` | 1. Buka menu Laporan.<br>2. Set filter dari 01-01-2023 ke 01-02-2025 (>365 hari).<br>3. Klik Filter. | Muncul safeguard error: *"Rentang tanggal laporan maksimal 365 hari."* | **PASS** |

---

### Kategori C: Authorization (Otorisasi & Hak Akses Berbasis Peran)

| Test ID | Modul / Fitur | Deskripsi Uji | Pra-kondisi | Langkah-langkah Pengujian | Hasil yang Diharapkan | Status |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **TC-013** | Isolasi Divisi | Requester mengakses PR divisi lain via URL | Login sebagai Requester Divisi IT | 1. Masukkan URL `/purchase-requests/{id_milik_HR}` langsung pada address bar browser. | Sistem menolak akses dengan respons HTTP 403 Forbidden. | **PASS** |
| **TC-014** | Segregation of Duties | Requester menyetujui pengajuan milik sendiri | Requester memiliki peran ganda / Plt | 1. Requester membuka PR miliknya sendiri yang butuh persetujuan.<br>2. Akses tombol Approve. | Tombol approve tersembunyi; eksekusi POST diblokir dengan kode 403. | **PASS** |
| **TC-015** | Role Boundary | Requester mengakses pembuatan Master Vendor | Login sebagai `requester` | 1. Akses URL `/vendors/create`. | Sistem memblokir akses dan melempar halaman 403 Forbidden. | **PASS** |
| **TC-016** | Penerimaan Gudang | Warehouse memproses PO bukan untuk gudang | Login sebagai `warehouse` | 1. Akses menu approval PR atau RFQ vendor. | Sistem membatasi menu sidebar dan melempar 403 jika URL diakses paksa. | **PASS** |
| **TC-017** | Approval Prematur | Finance menyetujui sebelum Manager Divisi | PR butuh Tier 1 (Manager) & Tier 2 (Finance) | 1. Login sebagai `finance`.<br>2. Buka PR yang Tier 1-nya masih `pending`.<br>3. Klik Approve. | Sistem memblokir aksi approval prematur dengan pesan 403 / error urutan persetujuan. | **PASS** |
| **TC-018** | Akses Laporan | Requester dilarang melihat laporan eksekutif | Login sebagai `requester` | 1. Akses URL `/reports`. | Sistem menampilkan halaman 403 Unauthorized Access. | **PASS** |

---

### Kategori D: Perubahan Status (State Transitions Lifecycle)

| Test ID | Modul / Fitur | Deskripsi Uji | Pra-kondisi | Langkah-langkah Pengujian | Hasil yang Diharapkan | Status |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **TC-019** | PR Lifecycle | Transisi Draft menjadi Submitted | Dokumen PR baru dibuat | 1. Simpan draf PR.<br>2. Buka detail draf.<br>3. Klik "Kirim Pengajuan". | Status berubah dari `draft` menjadi `submitted`, history status tercatat. | **PASS** |
| **TC-020** | Penarikan PR | Penarikan PR yang sudah diajukan kembali ke Draf | PR berstatus `submitted` sebelum ada yang approve | 1. Pemohon klik tombol "Tarik Kembali ke Draf".<br>2. Konfirmasi penarikan. | Status PR kembali ke `draft`, antrean approval dinonaktifkan. | **PASS** |
| **TC-021** | Revisi PR | Manager meminta revisi dokumen PR | PR berstatus `submitted` | 1. Manager klik "Minta Revisi".<br>2. Masukkan instruksi revisi perbaikan.<br>3. Kirim. | Status PR menjadi `revision_required`, pemohon menerima notifikasi revisi. | **PASS** |
| **TC-022** | Penolakan PR | Penolakan resmi PR dengan alasan wajib | PR berstatus `submitted` | 1. Approver klik tombol "Tolak Pengajuan".<br>2. Masukkan alasan penolakan.<br>3. Konfirmasi. | Status PR menjadi `rejected`, alur pengadaan berakhir permanen. | **PASS** |
| **TC-023** | Parsial ke Selesai | PO Diterima Sebagian menjadi Selesai | PO berstatus `partially_received` | 1. Buat GR kedua untuk sisa kuantitas barang.<br>2. Simpan GR. | Status PO otomatis bertransisi dari `partially_received` menjadi `completed`. | **PASS** |
| **TC-024** | Pembatalan Default | Wanprestasi vendor membatalkan PO | PO aktif melewati batas waktu pengiriman | 1. Procurement klik "Kirim SP Wanprestasi".<br>2. Batalkan PO karena wanprestasi. | Status PO menjadi `cancelled`, status Quotation vendor dicabut (*unawarded*). | **PASS** |

---

### Kategori E: File Upload & Security

| Test ID | Modul / Fitur | Deskripsi Uji | Pra-kondisi | Langkah-langkah Pengujian | Hasil yang Diharapkan | Status |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **TC-025** | Upload Lampiran PR | Unggah dokumen PDF spesifikasi teknis | Form PR create/edit | 1. Pilih file PDF 2 MB.<br>2. Simpan PR. | File tersimpan di direktori aman `storage/app/private/`, tautan unduh aktif. | **PASS** |
| **TC-026** | Ekstensi Terlarang | Penolakan upload file biner berbahaya (.exe, .php) | Form PR lampiran | 1. Unggah file `script.php` atau `malware.exe`.<br>2. Submit form. | Validasi form menolak file dengan error: *"Tipe file harus berupa PDF, JPG, PNG, atau DOCX."* | **PASS** |
| **TC-027** | Pembatasan Ukuran | Penolakan file upload melebihi batas 10 MB | Form penerimaan barang (GR) | 1. Upload dokumen surat jalan berukuran 15 MB.<br>2. Submit form. | Ditolak dengan pesan: *"Ukuran file maksimal adalah 10240 KB (10 MB)."* | **PASS** |
| **TC-028** | Keamanan Unduh | User tidak sah dilarang mengunduh dokumen penawaran | Quotation vendor memiliki file PDF penawaran | 1. Copy tautan download attachment quotation.<br>2. Buka tab incognito tanpa login (guest) / login requester lain. | Akses ditolak dengan kode 401 Unauthenticated atau 403 Forbidden. | **PASS** |

---

### Kategori F: Perhitungan & Formula Keuangan (Calculations)

| Test ID | Modul / Fitur | Deskripsi Uji | Pra-kondisi | Langkah-langkah Pengujian | Hasil yang Diharapkan | Status |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **TC-029** | Subtotal Item PR | Perhitungan otomatis `qty * unit_price` | Form pembuatan PR | 1. Masukkan Qty = 5, Harga = Rp 1.500.000.<br>2. Tambah baris Qty = 2, Harga = Rp 250.000. | Subtotal baris 1 = Rp 7.500.000, Subtotal baris 2 = Rp 500.000, Total PR = Rp 8.000.000. | **PASS** |
| **TC-030** | Kalkulasi PPN 11% | Penambahan PPN 11% pada penerbitan PO | Subtotal PO = Rp 100.000.000 | 1. Pilih PPN 11%.<br>2. Simpan PO. | Nilai pajak terhitung tepat Rp 11.000.000, Grand Total = Rp 111.000.000. | **PASS** |
| **TC-031** | Toleransi Pembulatan | Toleransi selisih pembulatan pajak header vs item | Subtotal bernilai pecahan desimal | 1. Sistem menghitung pajak header vs akumulasi baris.<br>2. Selisih toleransi Rp 12. | Selisih dicatat pada kolom `tax_rounding_difference` tanpa membatalkan transaksi. | **PASS** |
| **TC-032** | Toleransi Over-Delivery | Penerimaan gudang dalam batas toleransi 5% | PO Qty = 100 unit (Toleransi 5%) | 1. Warehouse input qty diterima = 104 unit.<br>2. Simpan GR. | Diterima sukses; sistem mencatat 100 unit reguler + 4 unit over-delivered. | **PASS** |
| **TC-033** | Blokir Over-Delivery | Penolakan penerimaan gudang melebihi batas 5% | PO Qty = 100 unit (Maks = 105 unit) | 1. Warehouse input qty diterima = 115 unit.<br>2. Simpan GR. | Transaksi diblokir: *"Kuantitas fisik (115) melebihi batas toleransi over-delivery 5% (105)."* | **PASS** |
| **TC-034** | Denda Keterlambatan | Perhitungan denda wanprestasi 1‰ per hari | PO Rp 50.000.000 terlambat 10 hari | 1. Sistem memeriksa tanggal target vs hari ini.<br>2. Hitung denda: `10 * (0.001 * 50.000.000)`. | Denda dihitung tepat Rp 500.000 (tidak melebihi batas maksimal 5%). | **PASS** |
| **TC-035** | Kumulatif BAST Jasa | Validasi akumulasi termin jasa maksimal 100% | PO Jasa Termin 1 telah diterima 70% | 1. Warehouse/Pengelola input termin 2 sebesar 40%.<br>2. Submit BAST. | Ditolak dengan error: *"Akumulasi termin melampaui 100% (70% + 40% = 110%). Maksimal 30%."* | **PASS** |
| **TC-036** | Weighted Scoring RFQ | Perhitungan skor pembobotan penawaran vendor | RFQ dengan bobot: Harga 40%, Kualitas 35%, Delivery 25% | 1. Masukkan penawaran Vendor A dan Vendor B dengan skor masing-masing. | Sistem menghitung skor komposit secara matematis dan merekomendasikan skor tertinggi. | **PASS** |

---

## 2. Format & Laporan Bug (Bug Reports)

Berikut adalah catatan bug nyata yang ditemukan dan telah diperbaiki selama siklus pengujian:

### Bug Report 1
| Field | Isi |
| :--- | :--- |
| **Bug ID** | **BUG-001** |
| **Judul** | Requester dapat mengubah formulir PR setelah status berubah menjadi `submitted` |
| **Environment** | Chrome 129, Windows 11, Local Environment (`php artisan serve`) |
| **Precondition** | Requester telah login dan memiliki dokumen PR dengan status `submitted`. |
| **Steps** | 1. Login sebagai `requester`.<br>2. Akses halaman `/purchase-requests/{id}/edit` secara langsung via URL.<br>3. Mengubah nama barang dan harga estimasi.<br>4. Menekan tombol "Perbarui Pengajuan". |
| **Expected** | Sistem menolak aksi pengeditan, menampilkan error 403 Forbidden atau me-redirect dengan pesan bahwa dokumen yang sudah diajukan terkunci. |
| **Actual** | Form edit masih terbuka dan data di database berhasil diperbarui meskipun PR sedang dalam antrean approval. |
| **Severity** | **High** |
| **Evidence** | Controller diperbaiki dengan menambahkan guard check: `if ($pr->status !== 'draft') abort(403);`. Automated test: `test_requester_cannot_edit_submitted_pr` (**PASS**). |

---

### Bug Report 2
| Field | Isi |
| :--- | :--- |
| **Bug ID** | **BUG-002** |
| **Judul** | Ikon indikator pada dashboard React berukuran terlalu besar (oversized SVG) |
| **Environment** | Microsoft Edge, Windows 11, Vite Dev Server |
| **Precondition** | Pengguna dengan peran manajemen membuka halaman `/dashboard`. |
| **Steps** | 1. Login sebagai `admin` atau `manager`.<br>2. Membuka halaman dashboard React.<br>3. Mengamati kartu KPI dan tombol filter. |
| **Expected** | Ikon SVG berukuran proporsional (16px s.d. 24px) di dalam wadah badge 44px. |
| **Actual** | Ikon SVG merenggang memenuhi seluruh lebar kontainer karena Tailwind tidak memindai file JSX. |
| **Severity** | **Medium** |
| **Evidence** | Menambahkan `./resources/js/**/*.{js,jsx}` ke `tailwind.config.js` dan menambahkan inline styles `width: 16px; height: 16px`. Tampilan kini rapi dan proporsional. |

---

### Bug Report 3
| Field | Isi |
| :--- | :--- |
| **Bug ID** | **BUG-003** |
| **Judul** | Approver level atas (Finance / HOD) dapat menyetujui PR mendahului Manager Divisi |
| **Environment** | Chrome 129, Windows 11, SQLite In-Memory Testing |
| **Precondition** | PR bernilai > Rp 25.000.000 baru saja diajukan dan Tier 1 (Manager) masih berstatus `pending`. |
| **Steps** | 1. Login sebagai akun `finance` atau `hod`.<br>2. Menembak endpoint POST `/approvals/{pr}/approve`. |
| **Expected** | Sistem memblokir aksi approval prematur dengan status 403 Forbidden karena Tier 1 belum selesai. |
| **Actual** | Sebelum diperbaiki, status Tier 2 dapat berubah menjadi `approved` mendahului Tier 1. |
| **Severity** | **High** |
| **Evidence** | Logika `ApprovalService::canUserApprove()` diperketat dengan memeriksa `activeTier->tier_level === currentPendingTier`. Automated test: `test_approval_in_strict_sequential_order` (**PASS**). |

---

### Bug Report 4
| Field | Isi |
| :--- | :--- |
| **Bug ID** | **BUG-004** |
| **Judul** | Staf gudang dapat menginput penerimaan barang melebihi kuantitas PO tanpa batas |
| **Environment** | Firefox Developer Edition, Windows 11 |
| **Precondition** | PO memiliki sisa pesanan 10 unit barang. |
| **Steps** | 1. Login sebagai `warehouse`.<br>2. Buka form Goods Receipt untuk PO tersebut.<br>3. Masukkan kuantitas diterima = 50 unit.<br>4. Klik Simpan Penerimaan. |
| **Expected** | Sistem memvalidasi dan menolak penerimaan yang melampaui sisa PO ditambah toleransi over-delivery 5% (maks 10,5 unit). |
| **Actual** | Penerimaan 50 unit tersimpan ke database dan menyebabkan kuantitas penerimaan bernilai negatif pada sisa PO. |
| **Severity** | **High** |
| **Evidence** | Ditambahkan validasi kuantitas maksimum pada `GoodsReceiptController::store()`. Automated test: `test_receipt_quantity_cannot_exceed_po_quantity` (**PASS**). |

---

## 3. API Testing Matrix

Pengujian API RESTful OrderFlow dijalankan menggunakan Laravel Sanctum Bearer Token dan Postman Test Suite (`docs/postman/OrderFlow_API_Collection.json`).

| Kategori Pengujian | Endpoint / Skenario | Method | Payload / Parameter | Status Code | Verifikasi Assertions |
| :--- | :--- | :---: | :--- | :---: | :--- |
| **Respons Sukses** | `/api/purchase-requests` | `POST` | Judul, tgl kebutuhan, array items lengkap | **201 Created** | Response JSON `{success: true, data: {id, pr_number, status: "submitted"}}` |
| **Respons Sukses** | `/api/purchase-requests/{id}` | `GET` | Valid Bearer Token | **200 OK** | Resource data PR lengkap beserta rincian item barang dan status approval |
| **Validation Error** | `/api/purchase-requests` | `POST` | Payload kosong / tanpa judul & items | **422 Unprocessable** | Response error JSON mencakup validasi key `title`, `required_date`, `items` |
| **Unauthenticated** | `/api/purchase-requests` | `GET` | Request tanpa Authorization Header | **401 Unauthorized** | Menolak permintaan dengan pesan: *"Unauthenticated."* |
| **Forbidden** | `/api/purchase-requests/{id}` | `GET` | Requester IT membuka PR milik Divisi HR | **403 Forbidden** | Response JSON `{success: false, message: "Akses Ditolak..."}` |
| **Not Found** | `/api/purchase-requests/999999` | `GET` | ID acak yang tidak ada di basis data | **404 Not Found** | Response JSON `{success: false, message: "Purchase Request tidak ditemukan"}` |
| **Pagination** | `/api/purchase-requests?per_page=5` | `GET` | `per_page=5` | **200 OK** | `data.pagination.per_page = 5`, item list berjumlah tepat 5 baris |
| **Filter Status** | `/api/purchase-requests?status=submitted` | `GET` | `status=submitted` | **200 OK** | Seluruh data yang dikembalikan memiliki atribut `status: "submitted"` |
| **Filter Divisi** | `/api/purchase-requests?department_id=2` | `GET` | `department_id=2` | **200 OK** | Seluruh data yang dikembalikan berasal dari ID divisi yang diminta |

---

## 4. Automated Testing Suite

Seluruh pengujian otomatis diimplementasikan menggunakan PHPUnit & Laravel Testing Framework, berjalan pada basis data SQLite In-Memory berkecepatan tinggi.

### Ringkasan Eksekusi Pengujian Utama

| Kebutuhan Pengujian | Berkas Pengujian | Metode Uji Otomatis | Hasil |
| :--- | :--- | :--- | :---: |
| **1. Login dan Logout** | `tests/Feature/CoreWorkflowAutomatedTest.php` | `test_login_and_logout_flow` | **PASS** |
| **2. Role dan Permission** | `tests/Feature/CoreWorkflowAutomatedTest.php` | `test_role_and_permission_enforcement` | **PASS** |
| **3. Membuat PR dan Item** | `tests/Feature/CoreWorkflowAutomatedTest.php` | `test_create_pr_and_items` | **PASS** |
| **4. Perhitungan Total** | `tests/Feature/CoreWorkflowAutomatedTest.php` | `test_automatic_calculation_of_totals` | **PASS** |
| **5. Approval Sesuai Urutan** | `tests/Feature/CoreWorkflowAutomatedTest.php` | `test_approval_in_strict_sequential_order` | **PASS** |
| **6. Anti Self-Approval** | `tests/Feature/CoreWorkflowAutomatedTest.php` | `test_requester_cannot_approve_own_pr` | **PASS** |
| **7. PO Hanya dari PR Approved** | `tests/Feature/CoreWorkflowAutomatedTest.php` | `test_po_can_only_be_issued_from_approved_pr` | **PASS** |
| **8. Batas Receipt $\le$ PO** | `tests/Feature/CoreWorkflowAutomatedTest.php` | `test_receipt_quantity_cannot_exceed_po_quantity` | **PASS** |
| **9. REST API Suite** | `tests/Feature/Api/OrderFlowRestApiTest.php` | 6 metode (200, 201, 422, 401, 403, 404, Pagination & Filter) | **PASS** |

### Perintah Menjalankan Pengujian:
```bash
# Menjalankan pengujian alur otomatis inti (Tahap 9)
php artisan test --filter=CoreWorkflowAutomatedTest

# Menjalankan pengujian REST API
php artisan test --filter=OrderFlowRestApiTest

# Menjalankan keseluruhan test suite (164 Tests)
php artisan test
```
