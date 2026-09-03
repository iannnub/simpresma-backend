# SIMPRESMA — Tech Document
## Stack, Konvensi Kode, Kontrak API, & Autentikasi

> **STATUS:** FINAL — Mengacu pada `requirements.md` dan `structure.md`.
> Semua pengerjaan backend wajib mengikuti konvensi di dokumen ini.

---

## 1. Tech Stack

| Layer | Teknologi | Versi |
|---|---|---|
| Backend | Laravel | 11.x |
| Database | MySQL | 8.x |
| Autentikasi | Laravel Sanctum | (bawaan Laravel 11) |
| PHP | PHP | >= 8.2 |
| Frontend | Tim terpisah (belum ditentukan) | - |

**Aturan Keras:**
- Backend **hanya REST API (JSON)** — tidak ada Blade, tidak ada view render.
- Tidak ada coupling ke framework/library frontend apapun.
- Semua response menggunakan format JSON standar (lihat bagian 4).

---

## 2. Struktur Folder Laravel

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Auth/
│   │   │   └── AuthController.php
│   │   ├── Mahasiswa/
│   │   │   └── PengajuanController.php
│   │   ├── Verifikator/
│   │   │   └── PengajuanController.php
│   │   ├── Tendik/
│   │   │   └── PengajuanController.php
│   │   ├── Wadek/
│   │   │   ├── MatriksController.php
│   │   │   ├── VerifikatorController.php
│   │   │   └── BidangMataKuliahController.php
│   │   └── Shared/
│   │       ├── DashboardController.php
│   │       └── VerifikatorDirektoriController.php
│   ├── Middleware/
│   │   └── RoleMiddleware.php
│   └── Requests/
│       ├── Mahasiswa/
│       │   └── StorePengajuanRequest.php
│       ├── Verifikator/
│       │   └── ProcessPengajuanRequest.php
│       ├── Tendik/
│       │   └── FinalisasiPengajuanRequest.php
│       └── Wadek/
│           ├── UpdateMatriksRequest.php
│           ├── AssignVerifikatorRequest.php
│           └── BidangMataKuliahRequest.php
├── Models/
│   ├── User.php
│   ├── Role.php
│   ├── Prodi.php
│   ├── VerifikatorProdi.php
│   ├── TingkatanLomba.php
│   ├── TahapanLomba.php
│   ├── MatriksKonversi.php
│   ├── BidangLomba.php
│   ├── MataKuliah.php
│   ├── BidangMataKuliah.php
│   ├── Pengajuan.php
│   └── PengajuanMataKuliah.php
├── Services/
│   ├── MatriksService.php      -- lookup & snapshot matriks
│   └── PengajuanService.php    -- logika submit, validasi duplikasi
└── Policies/
    └── PengajuanPolicy.php

database/
├── migrations/         -- urutan sesuai structure.md bagian 6
├── seeders/
│   ├── DatabaseSeeder.php
│   ├── ProdiSeeder.php
│   ├── RoleSeeder.php
│   ├── TingkatanLombaSeeder.php
│   ├── TahapanLombaSeeder.php
│   ├── MatriksKonversiSeeder.php
│   ├── BidangLombaSeeder.php
│   ├── MataKuliahSeeder.php
│   ├── BidangMataKuliahSeeder.php
│   └── DummyUserSeeder.php

```

---

## 3. Autentikasi (Laravel Sanctum)

### 3.1 Tahap 1 — Dummy Auth

**Login:**
```
POST /api/auth/login
Body: { "email": "...", "password": "..." }
```

Response sukses:
```json
{
  "success": true,
  "data": {
    "token": "1|abc123...",
    "user": {
      "id": 1,
      "nama": "Budi Santoso",
      "email": "budi@test.com",
      "nim_nip": "222410101001",
      "prodi": { "id": 1, "singkatan": "SI", "nama": "Sistem Informasi" },
      "roles": ["mahasiswa"]
    }
  }
}
```

**Logout:**
```
POST /api/auth/logout
Header: Authorization: Bearer {token}
```

**Me (cek user aktif):**
```
GET /api/auth/me
Header: Authorization: Bearer {token}
```

### 3.2 Token & Role

- Semua endpoint protected pakai header `Authorization: Bearer {token}`.
- Response `me` dan `login` selalu menyertakan array `roles` lengkap milik user.
- Frontend menyimpan token + roles di local state (localStorage/cookie).
- Role switcher di frontend hanya mengubah tampilan UI — **tidak mengubah token**.
- Backend memvalidasi role aktif via middleware berdasarkan token (bukan dari body request).

### 3.3 Middleware Role

File: `app/Http/Middleware/RoleMiddleware.php`

```php
// Contoh penggunaan di routes/api.php:
Route::middleware(['auth:sanctum', 'role:mahasiswa'])->group(function () {
    // route mahasiswa
});

Route::middleware(['auth:sanctum', 'role:verifikator'])->group(function () {
    // route verifikator
});
```

Middleware `role:{nama_role}` mengecek apakah user punya role tersebut di tabel `user_roles`.

---

## 4. Format Response JSON (Standar Wajib)

### 4.1 Success Response

```json
{
  "success": true,
  "message": "Pengajuan berhasil disubmit",
  "data": { ... }
}
```

Untuk list/paginated:
```json
{
  "success": true,
  "message": "OK",
  "data": {
    "items": [ ... ],
    "meta": {
      "current_page": 1,
      "last_page": 5,
      "per_page": 15,
      "total": 72
    }
  }
}
```

### 4.2 Error Response

```json
{
  "success": false,
  "message": "Pesan error singkat",
  "errors": {
    "field_name": ["Pesan validasi field ini"]
  }
}
```

### 4.3 HTTP Status Code

| Kondisi | Status |
|---|---|
| Sukses (GET, PUT, PATCH) | 200 |
| Sukses (POST create) | 201 |
| Sukses (DELETE) | 200 |
| Validasi gagal | 422 |
| Tidak terautentikasi | 401 |
| Tidak punya izin (role salah) | 403 |
| Resource tidak ditemukan | 404 |
| Server error | 500 |

---

## 5. Daftar Endpoint API

### 5.1 Auth
| Method | Endpoint | Middleware | Deskripsi |
|---|---|---|---|
| POST | `/api/auth/login` | - | Login, dapatkan token |
| POST | `/api/auth/logout` | auth:sanctum | Logout, revoke token |
| GET | `/api/auth/me` | auth:sanctum | Data user + roles aktif |

### 5.2 Shared (Semua Role)
| Method | Endpoint | Middleware | Deskripsi |
|---|---|---|---|
| GET | `/api/dashboard/statistik` | auth:sanctum | Persentase pengajuan per prodi |
| GET | `/api/direktori-verifikator` | auth:sanctum | Daftar verifikator per prodi |
| GET | `/api/ref/prodi` | auth:sanctum | Master data prodi |
| GET | `/api/ref/tingkatan` | auth:sanctum | Master tingkatan lomba |
| GET | `/api/ref/tahapan` | auth:sanctum | Master tahapan lomba |
| GET | `/api/ref/bidang` | auth:sanctum | Master bidang lomba |
| GET | `/api/ref/matriks` | auth:sanctum | Lookup matriks (query: tingkatan_id, tahapan_id) |
| GET | `/api/ref/mata-kuliah` | auth:sanctum | MK per bidang per prodi (query: bidang_id, prodi_id) |

### 5.3 Mahasiswa
| Method | Endpoint | Middleware | Deskripsi |
|---|---|---|---|
| GET | `/api/mahasiswa/pengajuan` | auth + role:mahasiswa | List pengajuan milik sendiri |
| POST | `/api/mahasiswa/pengajuan` | auth + role:mahasiswa | Submit pengajuan baru |
| GET | `/api/mahasiswa/pengajuan/{id}` | auth + role:mahasiswa | Detail pengajuan (termasuk hasil konversi jika selesai) |

### 5.4 Verifikator
| Method | Endpoint | Middleware | Deskripsi |
|---|---|---|---|
| GET | `/api/verifikator/pengajuan` | auth + role:verifikator | List pengajuan pending (scope prodi verifikator) |
| GET | `/api/verifikator/pengajuan/{id}` | auth + role:verifikator | Detail pengajuan |
| POST | `/api/verifikator/pengajuan/{id}/terima` | auth + role:verifikator | Terima pengajuan |
| POST | `/api/verifikator/pengajuan/{id}/tolak` | auth + role:verifikator | Tolak + feedback |

### 5.5 Tendik
| Method | Endpoint | Middleware | Deskripsi |
|---|---|---|---|
| GET | `/api/tendik/pengajuan` | auth + role:tendik | List pengajuan berstatus diterima |
| GET | `/api/tendik/pengajuan/{id}` | auth + role:tendik | Detail pengajuan |
| POST | `/api/tendik/pengajuan/{id}/finalisasi` | auth + role:tendik | Finalisasi + input nilai per MK + link SK (opsional) |

### 5.6 Wadek
| Method | Endpoint | Middleware | Deskripsi |
|---|---|---|---|
| GET | `/api/wadek/matriks` | auth + role:wadek | List semua baris matriks |
| PUT | `/api/wadek/matriks/{id}` | auth + role:wadek | Update 1 baris matriks |
| GET | `/api/wadek/verifikator` | auth + role:wadek | List verifikator per prodi |
| POST | `/api/wadek/verifikator` | auth + role:wadek | Assign verifikator ke prodi |
| DELETE | `/api/wadek/verifikator/{id}` | auth + role:wadek | Cabut verifikator dari prodi |
| GET | `/api/wadek/bidang-mk` | auth + role:wadek | List mapping bidang -> MK |
| POST | `/api/wadek/bidang-mk` | auth + role:wadek | Tambah mapping |
| DELETE | `/api/wadek/bidang-mk/{id}` | auth + role:wadek | Hapus mapping |

---

## 6. Konvensi Kode Laravel

### 6.1 Model

```php
// Contoh: app/Models/Pengajuan.php
class Pengajuan extends Model
{
    protected $fillable = [...]; // eksplisit, tidak pakai guarded = []
    protected $casts = [
        'snapshot_min_sks' => 'integer',
        'verified_at'      => 'datetime',
        'processed_at'     => 'datetime',
    ];

    // Relasi selalu didefinisikan lengkap
    public function user(): BelongsTo { ... }
    public function mataKuliahs(): BelongsToMany { ... }
}
```

### 6.2 Controller

- Controller **tipis** — logika bisnis di Service class.
- Selalu gunakan `FormRequest` untuk validasi (tidak inline di controller).
- Selalu return response dengan format standar (bagian 4).

```php
// Contoh pattern controller
public function store(StorePengajuanRequest $request)
{
    $pengajuan = $this->pengajuanService->submit($request->validated(), $request->user());
    return response()->json(['success' => true, 'message' => 'Berhasil', 'data' => $pengajuan], 201);
}
```

### 6.3 Service Class

Logika bisnis kompleks masuk Service:

```php
// app/Services/MatriksService.php
class MatriksService
{
    public function snapshot(int $tingkatanId, int $tahapanId): ?MatriksKonversi
    {
        // Query matriks, return null jika kombinasi tidak valid (min_sks IS NULL)
    }
}

// app/Services/PengajuanService.php
class PengajuanService
{
    public function submit(array $data, User $mahasiswa): Pengajuan
    {
        // 1. Validasi duplikasi aktif
        // 2. Lookup & snapshot matriks
        // 3. Simpan pengajuan + pivot MK
        // 4. Return pengajuan
    }
}
```

### 6.4 FormRequest

```php
// Contoh: app/Http/Requests/Mahasiswa/StorePengajuanRequest.php
class StorePengajuanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('mahasiswa');
    }

    public function rules(): array
    {
        return [
            'nama_tim'                     => 'nullable|string|max:150',
            'no_whatsapp'                  => 'required|string|max:20',
            'nama_lomba'                   => 'required|string|max:200',
            'bidang_id'                    => 'required|exists:bidang_lomba,id',
            'tingkatan_id'                 => 'required|exists:tingkatan_lomba,id',
            'tahapan_id'                   => 'required|exists:tahapan_lomba,id',
            'detail_juara'                 => 'nullable|string|max:50',
            'mata_kuliah_ids'              => 'required|array|min:1',
            'mata_kuliah_ids.*'            => 'exists:mata_kuliah,id',
            // Semua dokumen berupa link/URL -- tidak ada file upload
            'link_sertifikat'              => 'required|url|max:500',
            'status_surat_tugas_mahasiswa' => 'required|boolean',
            'link_surat_tugas_mahasiswa'   => 'required_if:status_surat_tugas_mahasiswa,1|nullable|url|max:500',
            'status_surat_tugas_dosen'     => 'required|boolean',
            'link_surat_tugas_dosen'       => 'required_if:status_surat_tugas_dosen,1|nullable|url|max:500',
            'link_poster'                  => 'nullable|url|max:500',
            'link_sosmed'                  => 'nullable|url|max:500',
            'keterangan'                   => 'nullable|string',
        ];
    }
}
```

### 6.5 Route Naming

```php
// routes/api.php
Route::prefix('api')->group(function () {
    // Auth
    Route::prefix('auth')->group(function () {
        Route::post('login', [AuthController::class, 'login'])->name('auth.login');
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('logout', [AuthController::class, 'logout'])->name('auth.logout');
            Route::get('me', [AuthController::class, 'me'])->name('auth.me');
        });
    });

    Route::middleware('auth:sanctum')->group(function () {
        // Shared
        Route::get('dashboard/statistik', [DashboardController::class, 'statistik']);
        Route::get('direktori-verifikator', [VerifikatorDirektoriController::class, 'index']);

        // Ref
        Route::prefix('ref')->group(function () {
            Route::get('prodi', ...);
            Route::get('tingkatan', ...);
            Route::get('tahapan', ...);
            Route::get('bidang', ...);
            Route::get('matriks', ...);
            Route::get('mata-kuliah', ...);
        });

        // Mahasiswa
        Route::middleware('role:mahasiswa')->prefix('mahasiswa')->group(function () {
            Route::get('pengajuan', [Mahasiswa\PengajuanController::class, 'index']);
            Route::post('pengajuan', [Mahasiswa\PengajuanController::class, 'store']);
            Route::get('pengajuan/{id}', [Mahasiswa\PengajuanController::class, 'show']);
        });

        // Verifikator
        Route::middleware('role:verifikator')->prefix('verifikator')->group(function () {
            Route::get('pengajuan', [Verifikator\PengajuanController::class, 'index']);
            Route::get('pengajuan/{id}', [Verifikator\PengajuanController::class, 'show']);
            Route::post('pengajuan/{id}/terima', [Verifikator\PengajuanController::class, 'terima']);
            Route::post('pengajuan/{id}/tolak', [Verifikator\PengajuanController::class, 'tolak']);
        });

        // Tendik
        Route::middleware('role:tendik')->prefix('tendik')->group(function () {
            Route::get('pengajuan', [Tendik\PengajuanController::class, 'index']);
            Route::get('pengajuan/{id}', [Tendik\PengajuanController::class, 'show']);
            Route::post('pengajuan/{id}/finalisasi', [Tendik\PengajuanController::class, 'finalisasi']);
        });

        // Wadek
        Route::middleware('role:wadek')->prefix('wadek')->group(function () {
            Route::get('matriks', [Wadek\MatriksController::class, 'index']);
            Route::put('matriks/{id}', [Wadek\MatriksController::class, 'update']);
            Route::get('verifikator', [Wadek\VerifikatorController::class, 'index']);
            Route::post('verifikator', [Wadek\VerifikatorController::class, 'store']);
            Route::delete('verifikator/{id}', [Wadek\VerifikatorController::class, 'destroy']);
            Route::get('bidang-mk', [Wadek\BidangMataKuliahController::class, 'index']);
            Route::post('bidang-mk', [Wadek\BidangMataKuliahController::class, 'store']);
            Route::delete('bidang-mk/{id}', [Wadek\BidangMataKuliahController::class, 'destroy']);
        });
    });
});
```

---

## 7. Aturan Dokumen — Semua Berbasis Link (Tidak Ada Upload File)

- Tidak ada `multipart/form-data` file upload di seluruh sistem.
- Semua dokumen/bukti disimpan sebagai string URL (VARCHAR 500) di database.
- Frontend mengirim link sebagai string biasa di JSON body.
- Validasi: semua kolom `link_*` divalidasi dengan rule `url|max:500`.
- Contoh: link Google Drive, link Gdrive, atau link hosting dokumen manapun.

---

## 8. Validasi Khusus Bisnis

### 8.1 Validasi Duplikasi Pengajuan Aktif
Sebelum insert ke tabel `pengajuan`:
```php
$duplikat = Pengajuan::where('user_id', $mahasiswa->id)
    ->where('nama_lomba', $data['nama_lomba'])
    ->whereIn('status', ['pending', 'diterima'])
    ->exists();

if ($duplikat) {
    throw new ValidationException(...); // 422
}
```

### 8.2 Validasi Total SKS Mata Kuliah
Setelah snapshot matriks:
```php
$totalSks = MataKuliah::whereIn('id', $data['mata_kuliah_ids'])->sum('sks');
if ($totalSks < $matriks->min_sks || $totalSks > $matriks->max_sks) {
    throw new ValidationException(...); // 422
}
```

### 8.3 Validasi Kombinasi Matriks Valid
```php
$matriks = MatriksKonversi::where('tingkatan_id', $data['tingkatan_id'])
    ->where('tahapan_id', $data['tahapan_id'])
    ->whereNotNull('min_sks')
    ->first();

if (!$matriks) {
    // Kombinasi tidak menghasilkan konversi
    throw new ValidationException(...); // 422
}
```

### 8.4 Validasi Scope Verifikator
Saat Verifikator memproses:
```php
$isInScope = VerifikatorProdi::where('user_id', $verifikator->id)
    ->where('prodi_id', $pengajuan->prodi_id)
    ->where('is_active', 1)
    ->exists();

if (!$isInScope) abort(403);
```

### 8.5 Validasi Mata Kuliah Sesuai Prodi & Bidang
```php
// MK yang dipilih harus dari prodi mahasiswa dan bidang yang dipilih
$validMkIds = BidangMataKuliah::where('bidang_id', $data['bidang_id'])
    ->whereHas('mataKuliah', fn($q) => $q->where('prodi_id', $mahasiswa->prodi_id))
    ->pluck('mata_kuliah_id')
    ->toArray();

foreach ($data['mata_kuliah_ids'] as $mkId) {
    if (!in_array($mkId, $validMkIds)) {
        throw new ValidationException(...);
    }
}
```

### 8.6 Validasi Nilai Tendik (Strict = Snapshot Matriks)
Saat Tendik finalisasi, setiap huruf nilai yang diinput divalidasi harus sama dengan snapshot:
```php
// app/Http/Requests/Tendik/FinalisasiPengajuanRequest.php rules:
'nilai_per_mk'           => 'required|array|min:1',
'nilai_per_mk.*.mk_id'   => 'required|exists:mata_kuliah,id',
'nilai_per_mk.*.huruf_nilai' => 'required|string|max:5',
'link_sk_konversi'       => 'nullable|url|max:500', // opsional, bukan file upload

// Di PengajuanService::finalisasi():
foreach ($data['nilai_per_mk'] as $item) {
    if ($item['huruf_nilai'] !== $pengajuan->snapshot_huruf_nilai) {
        // Nilai tidak sama dengan snapshot matriks
        throw new ValidationException(['nilai_per_mk' => ['Huruf nilai wajib sama dengan nilai matriks: ' . $pengajuan->snapshot_huruf_nilai]]);
    }
}
```

---

## 9. Seeder Dummy Users

Akun dummy untuk testing Tahap 1:

| Role | Email | Password | Prodi |
|---|---|---|---|
| Mahasiswa SI | `mhs.si@test.com` | `password` | SI |
| Mahasiswa TI | `mhs.ti@test.com` | `password` | TI |
| Mahasiswa IF | `mhs.if@test.com` | `password` | IF |
| Verifikator SI | `verif.si@test.com` | `password` | - (scope SI) |
| Verifikator TI | `verif.ti@test.com` | `password` | - (scope TI) |
| Verifikator IF | `verif.if@test.com` | `password` | - (scope IF) |
| Tendik | `tendik@test.com` | `password` | - |
| Wadek | `wadek@test.com` | `password` | - |
| Multi-role (Verif SI + Tendik) | `multi@test.com` | `password` | - |

---

## 10. Environment & Config

```env
# .env wajib diset
APP_NAME=SIMPRESMA
APP_URL=http://localhost:8000
DB_CONNECTION=mysql
DB_DATABASE=simpresma
DB_USERNAME=root
DB_PASSWORD=

SANCTUM_STATEFUL_DOMAINS=localhost:3000,localhost:5173
SESSION_DRIVER=cookie
```

```php
// config/cors.php - untuk development
'allowed_origins' => ['*'],
'allowed_methods' => ['*'],
'allowed_headers' => ['*'],
```

---

## 11. Perintah Setup Awal

```bash
composer create-project laravel/laravel simpresma
cd simpresma

# Setup database
php artisan migrate

# Seed semua data referensi + dummy users
php artisan db:seed

# Jalankan server development
php artisan serve
```

Catatan: `php artisan storage:link` tidak diperlukan karena tidak ada upload file.

---

## 12. Catatan Tahap 2 (SSO — Belum Dikerjakan)

Saat integrasi SSO CAS nanti:
- Tambah endpoint `GET /api/auth/cas/redirect` dan `GET /api/auth/cas/callback`.
- Backend validasi tiket CAS ke `https://sso.unej.ac.id/cas/serviceValidate`.
- Setelah validasi sukses: upsert data user di tabel `users` (NIM/NIP, nama, prodi dari response CAS), lalu issue Sanctum token seperti biasa.
- Tidak ada perubahan struktur tabel `users` — kolom sudah disiapkan sejak Tahap 1.
