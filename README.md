# SeriCheck API

Sistem Verifikasi Materai Proposal berbasis REST API.

---

## Instalasi

```bash
# 1. Clone / buat project Laravel baru
composer create-project laravel/laravel sericheck-api
cd sericheck-api

# 2. Install Sanctum
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"

# 3. Copy semua file dari folder ini ke project Laravel

# 4. Buat database di MySQL
mysql -u root -p < database/materai_api.sql

# 5. Set .env
DB_DATABASE=materai_api
DB_USERNAME=root
DB_PASSWORD=your_password

ANTHROPIC_API_KEY=sk-ant-xxxxxxxx

# 6. Jalankan migration (opsional, sudah ada SQL)
php artisan migrate

# 7. Link storage
php artisan storage:link

# 8. Jalankan server
php artisan serve
```

---

## Cara pakai

### Daftarkan web client baru

```bash
# Login dulu sebagai admin
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@sericheck.id","password":"password"}'

# Daftarkan web baru
curl -X POST http://localhost:8000/api/clients \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{"nama":"Web Fakultas Hukum","keterangan":"Sistem proposal penelitian"}'
```

### Upload materai dari web client

```bash
curl -X POST http://localhost:8000/api/materai/upload \
  -H "X-API-Key: sk_ft_a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4" \
  -F "file=@/path/to/materai.jpg"
```

### Simpan hasil OCR

```bash
curl -X POST http://localhost:8000/api/materai/simpan \
  -H "X-API-Key: sk_ft_a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4" \
  -H "Content-Type: application/json" \
  -d '{
    "nomor_seri": "AB1234567CD",
    "status": "unik",
    "nama_file": "materai.jpg",
    "path_file": "materai/original/mat_abc123.jpg",
    "jenis": "fisik",
    "confidence": 94
  }'
```

---

## Struktur file yang perlu disalin ke Laravel

```
app/
  Http/
    Controllers/
      AuthController.php
      MateraiController.php
      ApiClientController.php
    Middleware/
      ApiKeyCheck.php
  Models/
    ApiClient.php
    Materai.php
    ApiLog.php

routes/
  api.php

database/
  materai_api.sql   ← import ke MySQL

docs/
  API_DOCUMENTATION.md
```

---

## API Keys default (untuk testing)

| Client               | API Key                                    |
| -------------------- | ------------------------------------------ |
| Web Fakultas Teknik  | `sk_ft_a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4`   |
| Web Fakultas Ekonomi | `sk_fe_b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4e5`   |
| Sistem BAAK Pusat    | `sk_baak_c3d4e5f6a1b2c3d4e5f6a1b2c3d4e5f6` |
| Aplikasi Mobile KKN  | _(nonaktif)_                               |
