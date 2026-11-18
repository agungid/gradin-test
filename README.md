# Courier Master Data API (Laravel)

REST API untuk pengelolaan master data **Kurir (`couriers`)** menggunakan Laravel, lengkap dengan pagination, pencarian multi-kata, filter multi-level, validasi input, feature test, dan konfigurasi Docker Compose.

## Skema Tabel `couriers`

| Kolom | Tipe | Keterangan |
| --- | --- | --- |
| `id` | `bigint` (PK) | ID auto increment |
| `code` | `string(32)` (unique) | Kode unik kurir (contoh: `KUR-001`) |
| `name` | `string` (index) | Nama lengkap kurir |
| `email` | `string` (unique) | Email kurir |
| `phone` | `string(20)` (unique) | Nomor telepon kurir |
| `level` | `unsignedTinyInteger` (index) | Level kurir `1` – `5` (default `1`) |
| `vehicle_type` | `string(30)` | Jenis kendaraan: `motorcycle`, `car`, `van`, `bicycle`, `pickup` |
| `vehicle_plate` | `string(20)` (nullable) | Plat nomor kendaraan |
| `service_area` | `string(100)` (nullable) | Area/kota layanan |
| `status` | `string(20)` (index) | Status: `active`, `inactive`, `suspended` (default `active`) |
| `registered_at` | `timestamp` (index) | Tanggal didaftarkan |
| `created_at`, `updated_at` | `timestamp` | Timestamp bawaan Laravel |

---

## Cara Menjalankan

### 1. Menggunakan Docker Compose (Direkomendasikan)

Pastikan Docker sudah berjalan, lalu jalankan:

```bash
docker compose up -d --build
```

Container akan otomatis:
1. Menjalankan MySQL 8.0 (`db`), PHP 8.3-FPM (`app`), dan Nginx (`nginx`).
2. Menunggu MySQL siap, membuat `.env` jika belum ada, dan men-generate `APP_KEY`.
3. Menjalankan `php artisan migrate --force`.

API tersedia di: `http://localhost:8000/api/couriers`

Untuk menghentikan container:

```bash
docker compose down
```

### 2. Menjalankan Secara Lokal (Tanpa Docker)

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

API tersedia di: `http://127.0.0.1:8000/api/couriers`

---

## Menjalankan Test

Secara lokal:

```bash
php artisan test
```

Atau di dalam container Docker:

```bash
docker compose exec app php artisan test
```

---

## Dokumentasi Endpoint API

Base URL: `http://localhost:8000/api/couriers`

### 1. List Kurir (`GET /api/couriers`)

Mendukung pagination, sorting, pencarian nama, dan filter level:

| Query Parameter | Keterangan | Contoh |
| --- | --- | --- |
| `per_page` | Jumlah data per halaman (default `15`, maks `100`) | `?per_page=10` |
| `page` | Nomor halaman | `?page=2` |
| `sort_by` | Kolom pengurutan: `name` (default) atau `registered_at` | `?sort_by=registered_at` |
| `sort_dir` | Arah pengurutan: `asc` atau `desc` (default `asc` untuk `name`, `desc` untuk `registered_at`) | `?sort_by=registered_at&sort_dir=asc` |
| `search` | Pencarian nama multi-kata (mis. `budi+agung` cocok dengan `Budiono Hadi Agung`) | `?search=budi+agung` |
| `level` | Filter satu atau beberapa level (`1`–`5`), dipisah koma | `?level=2,3` |

Contoh:

```bash
curl "http://localhost:8000/api/couriers?search=budi+agung&level=2,3&sort_by=registered_at&per_page=10"
```

### 2. Detail Kurir (`GET /api/couriers/{id}`)

Mengembalikan seluruh data pada kurir tertentu.

```bash
curl "http://localhost:8000/api/couriers/1"
```

### 3. Tambah Kurir (`POST /api/couriers`)

```bash
curl -X POST "http://localhost:8000/api/couriers" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "code": "KUR-001",
    "name": "Budiono Hadi Agung",
    "email": "budiono@example.com",
    "phone": "081234567890",
    "level": 3,
    "vehicle_type": "motorcycle",
    "vehicle_plate": "B 1234 XYZ",
    "service_area": "Jakarta Selatan",
    "status": "active",
    "registered_at": "2026-11-18 10:00:00"
  }'
```

### 4. Ubah Data Kurir (`PUT/PATCH /api/couriers/{id}`)

```bash
curl -X PUT "http://localhost:8000/api/couriers/1" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "Budiono Hadi Agung",
    "level": 4,
    "service_area": "Jakarta Pusat",
    "status": "active"
  }'
```

### 5. Hapus Kurir (`DELETE /api/couriers/{id}`)

```bash
curl -X DELETE "http://localhost:8000/api/couriers/1" \
  -H "Accept: application/json"
```
