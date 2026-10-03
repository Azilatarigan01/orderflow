# OrderFlow Enterprise Procurement System

[![OrderFlow CI](https://github.com/Azilatarigan01/orderflow/actions/workflows/ci.yml/badge.svg)](https://github.com/Azilatarigan01/orderflow/actions/workflows/ci.yml)
[![Version](https://img.shields.io/badge/version-v1.0.0-gold.svg)](https://github.com/Azilatarigan01/orderflow/releases/tag/v1.0.0)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4.svg?logo=php&logoColor=white)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20.svg?logo=laravel&logoColor=white)](https://laravel.com)
[![Docker](https://img.shields.io/badge/Docker-Ready-2496ED.svg?logo=docker&logoColor=white)](https://www.docker.com)
[![Tests](https://img.shields.io/badge/Automated%20Tests-165%20Passed-success.svg)](docs/04_Testing_Strategy_and_Test_Cases.md)

OrderFlow Enterprise is a robust corporate procurement and spend management application engineered to replace fragmented purchasing processes (spreadsheets, emails, WhatsApp messages) with an accountable, audit-compliant **Good Corporate Governance (GCG)** standard.

---

## 🏛️ System Architecture & Workflow

```
[ Requester ]           [ Manager ]           [ Procurement ]          [ Finance / Warehouse ]
      │                      │                       │                           │
      ├── Submit PR ────────>│                       │                           │
      │   (With Attachments) │                       │                           │
      │                      ├── Approve / Reject ──>│                           │
      │                      │   (Budget Check)      │                           │
      │                      │                       ├── RFQ & Quotation Matrix  │
      │                      │                       │   (Vendor Comparison)     │
      │                      │                       ├── Issue Purchase Order ──>│
      │                      │                       │   (PO Tracking)           │
      │                      │                       │                           ├── Physical Goods Receipt
      │                      │                       │                           │   (GR Quantity Check)
      │                      │                       │                           ├── Three-Way Matching
      │                      │                       │                           │   (PO vs GR vs Invoice)
      ▼                      ▼                       ▼                           ▼
───────────────────────────────────────────────────────────────────────────────────────────
   Immutable Audit Trail  •  Executive KPI Dashboard  •  Role-Based Access Control (RBAC)
```

---

## 👥 Akun Demo Resmi (Official Demo Accounts)

Aplikasi telah disiapkan dengan dataset demo siap pakai yang aman (tanpa data pribadi atau rahasia perusahaan):

| Role Jabatan | Email Akun Demo Resmi | Password Default | Tanggung Jawab Utama |
|---|---|---|---|
| **Requester** | `requester@orderflow.demo` | `password` | Mengajukan Purchase Request (PR), spesifikasi, & dokumen pendukung. |
| **Manager** | `manager@orderflow.demo` | `password` | Review kelayakan anggaran, otorisasi/approval berjenjang. |
| **Procurement** | `procurement@orderflow.demo` | `password` | Pengelolaan rekanan, matriks komparasi penawaran, penerbitan PO. |
| **Finance** | `finance@orderflow.demo` | `password` | Rekonsiliasi Three-Way Matching, verifikasi faktur & pembayaran. |
| **Admin** | `admin@orderflow.demo` | `password` | Manajemen user, konfigurasi sistem, audit trail, reset demo data. |
| **Warehouse** | `warehouse@orderflow.demo` | `password` | Penerimaan barang fisik (Goods Receipt) & pencatatan selisih. |

> **Catatan Login**: Kredensial demo di atas dapat diakses langsung melalui tombol **Akses Instan Demo** pada halaman login (`/login`).

---

## 🚀 Cara Menjalankan Aplikasi

### Opsi A: Menjalankan dengan Docker (Rekomendasi)
Panduan lengkap: [docs/05_Docker_Deployment_Guide.md](file:///c:/Users/Nur%20Azila%20Tarigan/Documents/new%20project%20zila/OrderfFlow/docs/05_Docker_Deployment_Guide.md)

```bash
# 1. Clone repository
git clone https://github.com/Azilatarigan01/orderflow.git
cd orderflow

# 2. Build dan jalankan seluruh container (app, web Nginx, MySQL db, frontend)
docker compose up -d --build

# 3. Jalankan migrasi & seed data demo di dalam container
docker compose exec app php artisan migrate --seed

# 4. Jalankan automated test suite di dalam container
docker compose exec app php artisan test

# Akses aplikasi di web browser:
# http://localhost
```

### Opsi B: Menjalankan Lokal tanpa Docker

```bash
# 1. Masuk ke direktori aplikasi
cd orderflow_app

# 2. Siapkan file environment
cp .env.example .env

# 3. Install dependensi backend & frontend
composer install
npm install
npm run build

# 4. Generate App Key & Database
php artisan key:generate
php artisan migrate --seed
php artisan storage:link

# 5. Jalankan server lokal
php artisan serve
```
Akses aplikasi melalui browser di `http://127.0.0.1:8000`.

---

## 🔄 Pemeliharaan & Utilitas Khusus

### 1. Reset Lingkungan Data Demo
Untuk mereset data demo kembali ke kondisi bersih kapan saja:
```bash
php artisan orderflow:reset-demo --force
```

### 2. Cadangan Database Terjadwal (Backup)
Untuk membuat backup database otomatis (SQLite snapshot atau MySQL dump):
```bash
php artisan orderflow:backup-db --keep=7
```
Berkas cadangan tersimpan di `storage/app/backups/`.

---

## 🧪 Continuous Integration (CI) & Pengujian

Aplikasi dilengkapi pipeline GitHub Actions (`.github/workflows/ci.yml`) yang berjalan otomatis pada setiap Push dan Pull Request:
- **Lint & Dependencies**: Verifikasi Composer & NPM dependencies.
- **Automated Database Test**: Migrasi in-memory test database.
- **Automated Test Suite**: 165 Feature & Unit Tests (100% Passed, 734 assertions).
- **Frontend Asset Compilation**: Vite production build.

Jalankan test lokal:
```bash
php artisan test
```

---

## 📚 Dokumentasi Lengkap Proyek

| No | Dokumen Teknis | Isi Ringkas |
|---|---|---|
| 01 | [PRD OrderFlow](docs/01_PRD_OrderFlow.md) | Cakupan produk, problem statement, KPI & persona. |
| 02 | [Database ERD & Kamus Data](docs/02_Database_ERD_Dictionary.md) | Relasi entitas, skema tabel, tipe data, & constraint. |
| 03 | [User Flow & Wireframes](docs/03_User_Flow_and_Wireframes.md) | Alur navigasi antar-role dan rancangan antarmuka. |
| 04 | [Strategi Testing & Test Cases](docs/04_Testing_Strategy_and_Test_Cases.md) | 30 Manual test cases, API test suite, dan bug report. |
| 05 | [Panduan Deployment Docker](docs/05_Docker_Deployment_Guide.md) | Arsitektur multi-container, port binding, & troubleshooting. |
| 06 | [Checklist Publish & CI/CD](docs/06_Publish_and_CI_CD_Checklist.md) | Konfigurasi staging/production, HTTPS, error pages, zero debug leak. |
| 07 | [Script Video Demo & Release Notes](docs/07_Demo_Video_Script_and_Release_Notes.md) | Naskah presentasi 3-5 menit & panduan tagging rilis v1.0.0. |

---

## 🏷️ Release Version 1.0.0

Tag rilis telah disematkan:
```bash
git tag -a v1.0.0 -m "Release v1.0.0 OrderFlow Enterprise Procurement System"
```

Dikembangkan oleh **Azila Tarigan** untuk tata kelola pengadaan barang dan jasa korporat yang modern, akuntabel, dan transparan.
