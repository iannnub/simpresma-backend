# SIMPRESMA Backend (API)

**Sistem Informasi Manajemen Prestasi Mahasiswa** — RESTful API Backend berbasis Laravel 11.

Backend ini menangani autentikasi multi-role (Sanctum), validasi pengajuan prestasi mahasiswa, konversi SKS mata kuliah, verifikasi bertingkat (Verifikator & Tendik), manajemen matriks konversi (Wadek), audit role history (Multi-Role Admin), ekspor laporan Excel, dan notifikasi real-time via Telegram Bot.

---

## Tech Stack

| Layer | Teknologi |
|---|---|
| **Framework** | Laravel 11 |
| **PHP Version** | PHP ≥ 8.2 |
| **Authentication** | Laravel Sanctum (Bearer Token) |
| **Database** | MySQL / SQLite (Testing) |
| **Export Engine** | PhpSpreadsheet |
| **Notification** | Telegram Bot API |
| **Testing** | PHPUnit / Pest (73 tests passed, 509 assertions) |

---

## Fitur Utama

1. **Multi-Role Authentication & Role Management:**
   - Mendukung multiple roles per akun (`mahasiswa`, `verifikator`, `tendik`, `wadek`, `admin`).
   - Audit trail riwayat penambahan dan pencabutan role (`role_history`).
2. **Pengajuan Prestasi & Konversi SKS:**
   - Validasi dokumen wajib (SK Mahasiswa, SK Dosen, Sertifikat, Poster, Sosmed).
   - Validasi mata kuliah sesuai program studi dan bidang lomba aktif.
   - Snapshot matriks otomatis (min/max SKS, huruf nilai) yang *immutable*.
3. **Workflow Verifikasi & Finalisasi:**
   - **Verifikator:** Validasi pengajuan sesuai lingkup program studi (ACC/Tolak dengan feedback).
   - **Tendik:** Finalisasi nilai SKS, upload SK konversi, catatan akademik.
4. **Notifikasi Real-time Telegram:**
   - Integrasi bot `@simpresma_unej_bot` dengan auto-linking deep link `/start <code>`.
   - Notifikasi real-time saat pengajuan masuk, diverifikasi, atau difinalisasi.
5. **Security Hardening & Global Error Redaction:**
   - Global exception handling menyamarkan detail teknis pada response 500 dan mencatat ke `storage/logs/laravel.log`.

---

## Panduan Instalasi

```bash
# 1. Clone repository
git clone https://github.com/iannnub/simpresma-backend.git
cd simpresma-backend

# 2. Install dependencies
composer install

# 3. Setup environment file
cp .env.example .env
php artisan key:generate

# 4. Konfigurasi database di .env, lalu migrasi & seed
php artisan migrate --seed

# 5. Jalankan unit test
php artisan test

# 6. Jalankan dev server
php artisan serve
```
