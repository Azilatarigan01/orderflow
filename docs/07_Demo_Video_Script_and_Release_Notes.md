# 🎬 Naskah Video Demo (3–5 Menit) & Panduan Rilis v1.0.0

Dokumen ini menyediakan naskah rekaman video presentasi terstruktur (durasi 3 hingga 5 menit) dan catatan rilis resmi untuk sistem **OrderFlow Enterprise Procurement**.

---

## Bagian A: Naskah Rekaman Video Demo (Durasi: ~4 Menit 30 Detik)

### Profil Rekaman
- **Target Audiens**: Stakeholder Korporat, Tim Audit Internal, Lead Developer, Evaluator Sistem.
- **Resolusi Disarankan**: 1080p (1920x1080) atau 1440p pada 60 FPS.
- **Software Rekaman**: OBS Studio, Loom, atau Camtasia.

---

### Timeline & Skenario Narasi

#### Menit 00:00 – 00:45 | Pembukaan & Gambaran Umum Sistem
* **Tampilan Layar**: Halaman Utama / Landing Page OrderFlow (`/`) & Halaman Login (`/login`). Tunjukkan desain berstandar Good Corporate Governance (GCG) dengan tombol quick-login role demo.
* **Narasi Presenter**:
  > *"Halo rekan-rekan sekalian, selamat datang dalam demonstrasi resmi **OrderFlow Enterprise Procurement System** — platform digital tata kelola pengadaan barang dan jasa korporat yang dirancang berlandaskan prinsip akuntabilitas dan efisiensi anggaran.*
  > 
  > *Pada video kali ini, kita akan menyaksikan bagaimana OrderFlow menyelesaikan siklus lengkap procure-to-pay melalui pemisahan peran (Separation of Duties), persetujuan berjenjang, hingga rekonsiliasi Three-Way Matching yang otomatis dan aman. Mari kita mulai."*

---

#### Menit 00:45 – 01:45 | Siklus 1: Pengajuan PR oleh Requester
* **Tampilan Layar**:
  1. Klik tombol quick demo **Requester** (`requester@orderflow.demo` / `password`).
  2. Buka menu **Purchase Requests** $\rightarrow$ Klik **Buat Permintaan Baru**.
  3. Masukkan judul `"Pengadaan 5 Unit Laptop Developer & Monitor 4K"`.
  4. Tambah item barang, estimasi harga, upload file spesifikasi teknis (PDF).
  5. Klik tombol **Submit Permintaan**.
* **Narasi Presenter**:
  > *"Kita masuk sebagai Requester dari Departemen IT. Di sini pemohon dapat mengisi rincian barang, estimasi biaya, dan mengunggah dokumen pendukung secara aman.*
  > 
  > *Sistem secara otomatis menghitung total kalkulasi nilai permintaan dan meneruskan berkas ke manajer divisi untuk peninjauan anggaran."*

---

#### Menit 01:45 – 02:30 | Siklus 2: Otorisasi & Review oleh Manager
* **Tampilan Layar**:
  1. Logout, lalu login sebagai **Manager** (`manager@orderflow.demo`).
  2. Masuk ke tab **Approvals**. Terlihat notifikasi badge permintaan baru.
  3. Buka detail pengajuan, periksa rincian item, dan tekan tombol **Approve**.
  4. Perlihatkan log audit persetujuan tercatat dengan stempel waktu dan tanda tangan digital user.
* **Narasi Presenter**:
  > *"Beralih ke akun Manager. Di panel Approvals, manajer departemen dapat menelaah urgensi pengadaan dan ketersediaan anggaran.*
  > 
  > *Dengan satu klik 'Setujui', otorisasi berjenjang langsung tercatat dalam audit trail yang tidak dapat diubah (immutable), dan dokumen otomatis diteruskan ke Divisi Procurement."*

---

#### Menit 02:30 – 03:30 | Siklus 3: Komparasi Penawaran & Penerbitan PO (Procurement)
* **Tampilan Layar**:
  1. Login sebagai **Procurement** (`procurement@orderflow.demo`).
  2. Buka menu **Quotations**, tampilkan fitur komparasi minimal 2 vendor rekanan.
  3. Pilih penawaran terbaik berdasarkan harga dan garansi.
  4. Buat **Purchase Order (PO)** resmi dari PR yang telah approved.
* **Narasi Presenter**:
  > *"Sebagai Tim Procurement, tata kelola yang baik mewajibkan perbandingan penawaran rekanan terdaftar. OrderFlow menyediakan komparasi matriks vendor secara transparan.*
  > 
  > *Setelah penawaran disetujui, Purchase Order berkode unik resmi diterbitkan dan siap dikirimkan kepada vendor pemenang."*

---

#### Menit 03:30 – 04:15 | Siklus 4: Penerimaan Barang & Three-Way Matching (Finance)
* **Tampilan Layar**:
  1. Perlihatkan sekilas penerimaan logistik fisik (Goods Receipt) oleh tim gudang.
  2. Login sebagai **Finance** (`finance@orderflow.demo`).
  3. Buka menu **Invoices** & fitur **Three-Way Matching Reconciliation**.
  4. Tunjukkan verifikasi kesesuaian antara PO, Goods Receipt, dan Tagihan Vendor.
* **Narasi Presenter**:
  > *"Ketika barang tiba, tim logistik mencatat Goods Receipt. Selanjutnya, Divisi Keuangan melakukan Three-Way Matching — memverifikasi bahwa kuantitas dan nominal pada PO, bukti penerimaan barang, serta invoice vendor cocok secara akurat.*
  > 
  > *Hal ini mencegah risiko kebocoran kas, pembayaran ganda, atau ketidaksesuaian barang secara dini."*

---

#### Menit 04:15 – 05:00 | Siklus 5: Audit Trail, Keamanan, & Reset Data Demo (Admin)
* **Tampilan Layar**:
  1. Login sebagai **Admin** (`admin@orderflow.demo`).
  2. Tampilkan dashboard analitik eksekutif, log aktivitas audit lengkap, dan ekspor PDF/Excel.
  3. Tunjukkan konsol terminal dengan perintah pemulihan instan data demo: `php artisan orderflow:reset-demo --force`.
* **Narasi Presenter**:
  > *"Terakhir, Administrator memiliki visibilitas menyeluruh melalui Dashboard Eksekutif dan Jejak Audit yang mencatat setiap aksi dari awal hingga akhir.*
  > 
  > *Lingkungan demo ini juga dilengkapi dengan automated reset command `php artisan orderflow:reset-demo` untuk menjaga data tetap bersih dan terbebas dari data rahasia.*
  > 
  > *OrderFlow Enterprise — Solusi Pengadaan Handal, Transparan, dan Siap Produksi. Terima kasih."*

---

## Bagian B: Catatan Rilis & Prosedur Tagging v1.0.0

### Langkah Membuat Tag Rilis di Git Repository
Jalankan perintah berikut di root folder project:

```bash
# 1. Pastikan semua perubahan sudah ter-commit
git add .
git commit -m "feat(publish): complete phase 11 deployment, staging/production envs, demo accounts, and CI workflows"

# 2. Buat annotated tag versi 1.0.0
git tag -a v1.0.0 -m "OrderFlow Enterprise Procurement System - Release Version 1.0.0"

# 3. Dorong commit dan tag ke GitHub repository
git push origin main
git push origin v1.0.0
```

---

### Rincian Rilis (Release Notes) — Version 1.0.0

#### 🚀 Ikhtisar Rilis
**OrderFlow Enterprise v1.0.0** adalah sistem pengadaan barang dan jasa korporat terintegrasi dengan arsitektur enterprise modern berbasis Laravel 11 dan Vite Tailwind CSS.

#### ✨ Fitur Unggulan
1. **Multi-Role RBAC & Separation of Duties**:
   - 6 Role terintegrasi: Requester, Manager, Procurement, Finance, Warehouse, dan Administrator.
2. **End-to-End Procurement Workflow**:
   - Purchase Request (PR) berjenjang dengan lampiran spesifikasi teknis.
   - Komparasi penawaran rekanan (Vendor Quotation Matrix).
   - Purchase Order (PO) otomatis dengan kode unik perusahaan.
   - Penerimaan barang fisik (Goods Receipt) dengan pelacakan selisih kuantitas.
   - Three-Way Matching Validation (PO vs GR vs Invoice).
3. **Keamanan & Audit Governance**:
   - Enkripsi session cookie, HTTPS enforcement native.
   - Immutable Audit Trail mencatat aktivitas, IP, dan timestamp seluruh transaksi.
   - Halaman error custom (403, 404, 500) tanpa eksposur stack trace atau variabel kredensial.
4. **DevOps & Lingkungan Deployment**:
   - Dukungan kontainerisasi Docker penuh (PHP-FPM 8.2, Nginx Alpine, MySQL 8.0).
   - Pipeline GitHub Actions CI terotomatisasi (Dependency install, migrate test, 165+ automated test cases, frontend build).
   - Perintah pemulihan data demo `php artisan orderflow:reset-demo`.
   - Utilitas pencadangan database berkala `php artisan orderflow:backup-db`.

#### 🧪 Ringkasan Pengujian
- **Automated Tests**: 165 Passed (734 assertions, 0 failure).
- **Manual Test Cases**: 30 Test scenarios terdokumentasi lengkap di `docs/04_Testing_Strategy_and_Test_Cases.md`.
- **API Tests**: Lengkap mencakup 200, 201, 401, 403, 404, 422, pagination, dan filter.
