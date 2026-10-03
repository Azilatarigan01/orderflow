# Panduan Deployment & Eksekusi Docker (Tahap 10)
**Sistem Aplikasi**: OrderFlow Enterprise Procurement System  
**Arsitektur Container**: Multi-Container Docker Compose (`app`, `web`, `db`, `frontend`)  
**Tujuan**: Menstandarkan lingkungan pengembangan (*development*) dan *production deployment* agar konsisten, terisolasi, dan mudah dijalankan pada komputer baru.

---

## 1. Arsitektur Container Minimum

Sistem OrderFlow dikemas dalam 4 container mandiri yang saling terhubung melalui *bridge network* internal (`orderflow_network`):

| Container | Image / Base | Peran & Isi Layanan | Port Mapping | Volume / Mount |
| :--- | :--- | :--- | :---: | :--- |
| **`app`** | Custom `Dockerfile` (`php:8.2-fpm`) | Backend core Laravel 11, PHP-FPM 8.2, Composer 2, ekstensi lengkap (`pdo_mysql`, `gd`, `zip`, `bcmath`, `pcntl`, `opcache`). | Internal `9000` | Source code `./` & `/var/www/html/vendor` |
| **`web`** | `nginx:alpine` | Web server reverse proxy, SSL/TLS termination ready, gzip compression, HTTP security headers, static caching, meneruskan request PHP ke `app:9000`. | `80:80` | `./docker/nginx/default.conf` & `./public` |
| **`db`** | `mysql:8.0` | Basis data relasional MySQL 8.0, native password authentication, healthcheck service ping. | `3306:3306` | Persistent volume `orderflow_db_data:/var/lib/mysql` |
| **`frontend`** | `node:20-alpine` | Ekosistem Node.js 20 & Vite Dev Server untuk kompilasi dan hot-reload React Management Dashboard SPA. | `5173:5173` | Source code `./` & `/var/www/html/node_modules` |

---

## 2. Struktur Berkas Docker

Seluruh berkas pendukung container telah diorganisir secara rapi:

```text
OrderFlow/
├── docker-compose.yml             # Orkestrator Compose root (context: ./orderflow_app)
├── .dockerignore                  # Filter pengecualian file root
├── docs/
│   └── 05_Docker_Deployment_Guide.md
└── orderflow_app/
    ├── Dockerfile                 # Blueprint PHP 8.2-FPM + Ekstensi + Composer
    ├── docker-compose.yml         # Orkestrator Compose internal app
    ├── .dockerignore              # Pengecualian node_modules, vendor, .env, caches
    ├── .env.example               # Template environment aman (tanpa credential asli)
    └── docker/
        ├── entrypoint.sh          # Bootstrapper perizinan storage, key, & symlink
        ├── nginx/
        │   └── default.conf       # Konfigurasi virtual host Nginx untuk Laravel
        └── php/
            └── local.ini          # Optimasi memori (512M), upload (20M), & opcache
```

---

## 3. Menjalankan Aplikasi di Komputer Baru (Step-by-Step)

Untuk menjalankan aplikasi OrderFlow pada komputer baru yang memiliki Docker Desktop / Docker Engine:

### Langkah 1: Kloning Repositori & Persiapan Environment
```bash
# 1. Kloning repositori
git clone https://github.com/Azilatarigan01/orderflow.git
cd orderflow/orderflow_app

# 2. Salin template environment (tanpa password sensitif)
cp .env.example .env
```

> [!NOTE]
> Berkas `.env.example` sudah diset default untuk terhubung ke container MySQL (`DB_HOST=db`, `DB_PORT=3306`, `DB_DATABASE=orderflow`, `DB_USERNAME=orderflow_user`).

---

### Langkah 2: Build & Jalankan Seluruh Container
Gunakan perintah utama berikut untuk mengunduh image, membangun image kustom, dan menjalankan container di latar belakang (*detached mode*):

```bash
docker compose up -d --build
```

Setelah beberapa saat, periksa status seluruh container:
```bash
docker compose ps
```
*Hasil yang diharapkan: Keempat container (`orderflow_app`, `orderflow_web`, `orderflow_db`, `orderflow_frontend`) berstatus `Up` (dan `db` berstatus `healthy`).*

---

### Langkah 3: Eksekusi Migrasi Database & Seeder
Jalankan migrasi skema tabel dan data awal akun pengguna (*roles & sample data*) langsung di dalam container `app`:

```bash
docker compose exec app php artisan migrate --seed
```

---

### Langkah 4: Menjalankan Pengujian Otomatis di Dalam Container
Jalankan keseluruhan test suite (165 Automated Tests) di dalam container untuk memastikan integritas lingkungan:

```bash
docker compose exec app php artisan test
```

---

### Langkah 5: Menghentikan Container
Bila ingin menghentikan seluruh layanan tanpa menghapus data database yang tersimpan di volume:

```bash
docker compose down
```

Bila ingin menghentikan layanan dan membersihkan volume database secara penuh:
```bash
docker compose down -v
```

---

## 4. Rangkuman Perintah Target

| Perintah | Deskripsi Fungsi |
| :--- | :--- |
| `docker compose up -d --build` | Membangun dan menjalankan 4 container (`app`, `web`, `db`, `frontend`) di latar belakang. |
| `docker compose exec app php artisan migrate --seed` | Menjalankan migrasi database dan pengisian data dummy seeder di dalam container `app`. |
| `docker compose exec app php artisan test` | Menjalankan 165 skenario pengujian otomatis di lingkungan container. |
| `docker compose exec app composer install` | Menginstal/memperbarui dependensi PHP via Composer di dalam container. |
| `docker compose exec frontend npm run build` | Melakukan build bundle aset produksi CSS & React SPA. |
| `docker compose logs -f app` | Memantau log aplikasi PHP-FPM secara realtime. |
| `docker compose logs -f web` | Memantau log akses dan error Nginx secara realtime. |
| `docker compose down` | Menghentikan seluruh container dengan aman dan mempertahankan data volume. |

---

## 5. Verifikasi Kriteria Selesai (Checklist Kepatuhan)

- [x] **Aplikasi dapat dijalankan pada komputer baru hanya dengan dokumentasi repository**: Cukup ikuti 3 langkah di atas (`cp .env.example .env` $\rightarrow$ `docker compose up -d --build` $\rightarrow$ `php artisan migrate --seed`).
- [x] **Database tersimpan pada volume**: Menggunakan Docker named volume `orderflow_db_data` yang terpasang ke `/var/lib/mysql`. Data transaksi tetap aman dan persisten meskipun container dimatikan (`docker compose down`).
- [x] **Secret tidak disimpan dalam repository**: Berkas `.env` asli dan kredensial privat dimasukkan ke dalam `.gitignore` dan `.dockerignore`. Berkas `.env.example` hanya berisi placeholder dummy (`secret_password_change_me`).
- [x] **Migration, seeder, dan test dapat dijalankan di dalam container**: Perintah `docker compose exec app php artisan migrate --seed` dan `docker compose exec app php artisan test` didukung penuh dengan ekstensi `pdo_mysql` dan lingkungan terisolasi.
