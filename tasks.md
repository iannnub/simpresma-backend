# SIMPRESMA — Tasks Document
## Checklist Pengerjaan Bertahap

> **STATUS:** AKTIF — Update status task saat pengerjaan dimulai/selesai.
> Referensi: `requirements.md`, `structure.md`, `tech.md`.
>
> **Aturan Pengerjaan:**
> - [ ] = Belum dikerjakan
> - [/] = Sedang dikerjakan
> - [x] = Selesai & terverifikasi
>
> **JANGAN lanjut ke task berikutnya sebelum task sebelumnya selesai dan dikonfirmasi.**

---

## PHASE 0 — Dokumen Spesifikasi

- [x] **T0.1** Buat `requirements.md` (aturan bisnis & alur final)
- [x] **T0.2** Buat `structure.md` (skema database & relasi)
- [x] **T0.3** Buat `tech.md` (stack, API, konvensi kode)
- [x] **T0.4** Buat `tasks.md` (checklist ini)

---

## PHASE 1 — Setup Project Laravel

### T1.1 — Inisialisasi Project

- [x] Buat project Laravel 11 baru: `composer create-project laravel/laravel simpresma`
- [x] Setup `.env` (DB_DATABASE=simpresma, APP_NAME=SIMPRESMA)
- [x] Install Laravel Sanctum: sudah bawaan Laravel 11, jalankan `php artisan install:api`
- [x] Buat database `simpresma` di MySQL
- [x] Verifikasi koneksi database: `php artisan migrate` (tabel default Laravel terbuat)
- [x] Setup CORS: update `config/cors.php` sesuai `tech.md` bagian 10
- [x] Test `php artisan serve` berjalan normal (routes terverifikasi)

### T1.2 — Struktur Folder

- [x] Buat folder `app/Http/Controllers/Auth/`
- [x] Buat folder `app/Http/Controllers/Mahasiswa/`
- [x] Buat folder `app/Http/Controllers/Verifikator/`
- [x] Buat folder `app/Http/Controllers/Tendik/`
- [x] Buat folder `app/Http/Controllers/Wadek/`
- [x] Buat folder `app/Http/Controllers/Shared/`
- [x] Buat folder `app/Http/Requests/Mahasiswa/`
- [x] Buat folder `app/Http/Requests/Verifikator/`
- [x] Buat folder `app/Http/Requests/Tendik/`
- [x] Buat folder `app/Http/Requests/Wadek/`
- [x] Buat folder `app/Services/`
- [x] Hapus boilerplate tidak dipakai: `resources/views/`, `routes/web.php` disederhanakan API-only

---

## PHASE 2 — Database Migration

> Urutan migration mengikuti dependency (tabel induk lebih dulu).

### T2.1 — Tabel Master

- [x] Migration `prodi` (id, kode, singkatan, nama)
- [x] Migration `roles` (id, name, display_name)
- [x] Modifikasi migration `users` — tambah kolom: `nim_nip`, `prodi_id (FK)`, `no_whatsapp`; hapus kolom tidak perlu
- [x] Migration `user_roles` (pivot: user_id, role_id)
- [x] Migration `verifikator_prodi` (id, user_id, prodi_id, assigned_by, is_active)
- [x] Migration `tingkatan_lomba` (id, nama, urutan)
- [x] Migration `tahapan_lomba` (id, kode, nama, urutan)

### T2.2 — Tabel Matriks & Bidang

- [x] Migration `matriks_konversi` (id, tingkatan_id, tahapan_id, min_sks, max_sks, huruf_nilai, is_active, updated_by)
- [x] Migration `bidang_lomba` (id, nama, keterangan, is_active)
- [x] Migration `mata_kuliah` (id, prodi_id, kode_mk, nama_mk, sks, semester, is_active)
- [x] Migration `bidang_mata_kuliah` (pivot: bidang_id, mata_kuliah_id, is_active)

### T2.3 — Tabel Transaksi

- [x] Migration `pengajuan` (semua kolom sesuai `structure.md` bagian 2.12)
- [x] Migration `pengajuan_mata_kuliah` (id, pengajuan_id, mata_kuliah_id, sks_snapshot, huruf_nilai)

### T2.4 — Verifikasi

- [x] Jalankan `php artisan migrate:fresh` — semua tabel terbuat tanpa error
- [x] Cek semua FK constraint terdefinisi dengan benar di MySQL
- [x] Cek index `idx_pengajuan_prodi_status`, `idx_pengajuan_user`, `idx_matriks_kombo`, `idx_mk_prodi` terbuat

---

## PHASE 3 — Model Eloquent

- [x] Model `Prodi` — relasi: hasMany Users, hasMany MataKuliah, hasMany VerifikatorProdi
- [x] Model `Role` — relasi: belongsToMany Users (via user_roles)
- [x] Model `User` — relasi: belongsToMany Roles, hasOne Prodi, hasMany Pengajuan, hasMany VerifikatorProdi; method `hasRole(string $role): bool`
- [x] Model `VerifikatorProdi` — relasi: belongsTo User, belongsTo Prodi
- [x] Model `TingkatanLomba` — relasi: hasMany MatriksKonversi, hasMany Pengajuan
- [x] Model `TahapanLomba` — relasi: hasMany MatriksKonversi, hasMany Pengajuan
- [x] Model `MatriksKonversi` — relasi: belongsTo TingkatanLomba, belongsTo TahapanLomba; scope `valid()` (whereNotNull min_sks)
- [x] Model `BidangLomba` — relasi: belongsToMany MataKuliah (via bidang_mata_kuliah), hasMany Pengajuan
- [x] Model `MataKuliah` — relasi: belongsTo Prodi, belongsToMany BidangLomba (via bidang_mata_kuliah), belongsToMany Pengajuan (via pengajuan_mata_kuliah)
- [x] Model `BidangMataKuliah` — relasi: belongsTo BidangLomba, belongsTo MataKuliah
- [x] Model `Pengajuan` — relasi: belongsTo User (mahasiswa), belongsTo Prodi, belongsTo BidangLomba, belongsTo TingkatanLomba, belongsTo TahapanLomba, belongsTo User (verifikator), belongsTo User (tendik), belongsToMany MataKuliah (via pengajuan_mata_kuliah); cast enum status, timestamps verified_at & processed_at
- [x] Model `PengajuanMataKuliah` — relasi: belongsTo Pengajuan, belongsTo MataKuliah

---

## PHASE 4 — Seeder Data Referensi & Dummy

### T4.1 — Data Referensi

- [x] `ProdiSeeder` — seed 3 prodi (SI/TI/IF) sesuai `structure.md`
- [x] `RoleSeeder` — seed 4 role (mahasiswa/verifikator/tendik/wadek)
- [x] `TingkatanLombaSeeder` — seed 6 tingkatan
- [x] `TahapanLombaSeeder` — seed 4 tahapan
- [x] `MatriksKonversiSeeder` — seed 24 baris matriks (dari tabel `structure.md` bagian 2.8)
- [x] `BidangLombaSeeder` — seed 18 bidang
- [x] `MataKuliahSeeder` — seed 44 MK (SI, TI, IF) — representative per bidang
- [x] `BidangMataKuliahSeeder` — seed 55 mapping bidang -> MK per prodi

### T4.2 — Dummy Users

- [x] `DummyUserSeeder` — seed 9 akun sesuai `tech.md` bagian 9:
  - 3 mahasiswa (SI, TI, IF)
  - 3 verifikator (scope SI, TI, IF) + insert ke `verifikator_prodi`
  - 1 tendik
  - 1 wadek
  - 1 multi-role (verif SI + tendik)
- [x] Verifikasi: `php artisan db:seed` berjalan tanpa error
- [x] Verifikasi: Cek data via MySQL — 24 matriks, 18 bidang, 9 users, 4 verifikator_prodi ✅

---

## PHASE 5 — Autentikasi & Middleware

### T5.1 — AuthController

- [x] `login` — validasi email+password, return `{token, user{id,nim_nip,nama,email,no_whatsapp,prodi,roles}}`
- [x] `logout` — revoke current token
- [x] `me` — return user aktif + roles
- [x] Format response mengikuti `tech.md §4` (`success`, `message`, `data`)

### T5.2 — Middleware

- [x] `RoleMiddleware` — cek `user->hasRole($role)` via DB, return 403 JSON jika tidak punya
- [x] Register alias `'role'` di `bootstrap/app.php`
- [x] Handle `AuthenticationException` — return 401 JSON untuk semua `api/*` request
- [x] `statefulApi()` — mencegah redirect ke named route `login`

### T5.3 — Routes Auth

- [x] `POST /api/auth/login` — public
- [x] `POST /api/auth/logout` — `auth:sanctum`
- [x] `GET /api/auth/me` — `auth:sanctum`
- [x] Placeholder group: `role:mahasiswa`, `role:verifikator`, `role:tendik`, `role:wadek`
- [x] Test 7 skenario: login OK, login gagal 422, /me OK, /me tanpa token 401, wadek login, logout OK, token expired 401

---

## PHASE 6 — Service Classes

- [x] `app/Services/MatriksService.php`
  - Method `snapshot(tingkatanId, tahapanId)`: return MatriksKonversi atau null jika kombinasi tidak valid
- [x] `app/Services/PengajuanService.php`
  - Method `submit(data, mahasiswa)`: validasi duplikasi aktif, snapshot matriks, simpan pengajuan + pivot MK
  - Method `validateMataKuliah(mkIds, bidangId, prodiId, minSks, maxSks)`: validasi SKS & scope
  - Method `terima(pengajuan, verifikator)`: validasi scope verifikator & update status diterima
  - Method `tolak(pengajuan, verifikator, feedback)`: validasi feedback & update status ditolak
  - Method `finalisasi(pengajuan, tendik, data)`: validasi strict nilai = snapshot matriks & update status selesai
- [x] Unit test `ServiceClassesTest` (7 test scenarios, 27 assertions, 100% pass)

---

## PHASE 7 — Modul Mahasiswa

### T7.1 — Ref Endpoints (dipakai form pengajuan)

- [x] `GET /api/ref/prodi` — list prodi
- [x] `GET /api/ref/tingkatan` — list tingkatan lomba
- [x] `GET /api/ref/tahapan` — list tahapan lomba
- [x] `GET /api/ref/bidang` — list bidang lomba aktif
- [x] `GET /api/ref/matriks?tingkatan_id={}&tahapan_id={}` — lookup 1 baris matriks
- [x] `GET /api/ref/mata-kuliah?bidang_id={}&prodi_id={}` — list MK per bidang per prodi
- [x] Daftarkan semua route ref di `routes/api.php`

### T7.2 — Pengajuan Controller (Mahasiswa)

- [x] Buat `StorePengajuanRequest` — validasi lengkap sesuai `tech.md` bagian 6.4 (zero upload file, URL links)
- [x] Buat `app/Http/Controllers/Mahasiswa/PengajuanController.php`
- [x] Method `index`: list pengajuan milik mahasiswa login (paginated, include relasi bidang/tingkatan/tahapan)
- [x] Method `store`: panggil `PengajuanService::submit()`
- [x] Method `show`: detail 1 pengajuan milik sendiri (include `pengajuan_mata_kuliah` + `huruf_nilai` jika sudah selesai)
- [x] Validasi: user hanya bisa lihat pengajuan miliknya sendiri (bukan milik mahasiswa lain) (403)
- [x] Daftarkan route di `routes/api.php`

### T7.3 — Testing Modul Mahasiswa

- [x] Test `POST /api/mahasiswa/pengajuan` — submit sukses dengan semua link valid (201)
- [x] Test submit tanpa `link_sertifikat` — return 422
- [x] Test `link_sertifikat` bukan URL valid — return 422
- [x] Test `status_surat_tugas_mahasiswa = 1` tapi `link_surat_tugas_mahasiswa` kosong — return 422
- [x] Test kombinasi matriks tidak valid — return 422
- [x] Test total SKS di luar rentang — return 422
- [x] Test duplikasi pengajuan aktif — return 422
- [x] Test `GET /api/mahasiswa/pengajuan` — list benar sesuai user login
- [x] Test `GET /api/mahasiswa/pengajuan/{id}` — tidak bisa akses pengajuan orang lain (403)
- [x] Test non-mahasiswa tidak bisa akses endpoint mahasiswa (403)

---

## PHASE 8 — Modul Verifikator

### T8.1 — Pengajuan Controller (Verifikator)

- [x] Buat `ProcessPengajuanRequest` — validasi: `feedback_verifikator` required jika aksi tolak
- [x] Buat `app/Http/Controllers/Verifikator/PengajuanController.php`
- [x] Method `index`: list pengajuan berstatus `pending` dari prodi scope verifikator (JOIN `verifikator_prodi`)
- [x] Method `show`: detail 1 pengajuan (termasuk file sertifikat, surat tugas)
- [x] Method `terima`: ubah status `pending` -> `diterima`, set `verifikator_id` + `verified_at`
- [x] Method `tolak`: ubah status `pending` -> `ditolak`, simpan `feedback_verifikator`, set `verifikator_id` + `verified_at`
- [x] Validasi scope: verifikator hanya bisa proses pengajuan dari prodinya (`T8.2`)
- [x] Daftarkan route di `routes/api.php`

### T8.2 — Validasi Scope Verifikator

- [x] Logic cek scope di `terima` dan `tolak`: query `verifikator_prodi` untuk pastikan prodi pengajuan ada di scope verifikator
- [x] Jika di luar scope: return 403

### T8.3 — Testing Modul Verifikator

- [x] Test login verifikator SI + lihat pengajuan SI (muncul)
- [x] Test verifikator SI tidak bisa lihat pengajuan TI (403)
- [x] Test terima pengajuan — status berubah ke `diterima`
- [x] Test tolak tanpa feedback — return 422
- [x] Test tolak dengan feedback — status berubah ke `ditolak`
- [x] Test pengajuan `diterima` muncul di dashboard Tendik (query benar)

---

## PHASE 9 — Modul Tendik

### T9.1 — Pengajuan Controller (Tendik)

- [x] Buat `FinalisasiPengajuanRequest` — validasi: `nilai_per_mk` array of {mk_id, huruf_nilai}, `link_sk_konversi` URL opsional
- [x] Buat `app/Http/Controllers/Tendik/PengajuanController.php`
- [x] Method `index`: list pengajuan berstatus `diterima` (semua prodi, Tendik tidak filter prodi)
- [x] Method `show`: detail lengkap pengajuan + list MK yang dipilih + tampilkan `snapshot_huruf_nilai` sebagai referensi wajib
- [x] Method `finalisasi`:
  - [x] Validasi setiap MK di `nilai_per_mk` ada di `pengajuan_mata_kuliah` milik pengajuan ini
  - [x] Validasi setiap `huruf_nilai` yang diinput **wajib sama persis** dengan `pengajuan.snapshot_huruf_nilai` — jika berbeda return 422
  - [x] Update `huruf_nilai` per baris di `pengajuan_mata_kuliah`
  - [x] Simpan `link_sk_konversi` jika ada (URL string, bukan file)
  - [x] Set `tendik_id`, `processed_at`, ubah status -> `selesai`
- [x] Daftarkan route di `routes/api.php`

### T9.2 — Testing Modul Tendik

- [x] Test `GET /api/tendik/pengajuan` — hanya tampil pengajuan berstatus `diterima`
- [x] Test `show` — tampil list MK + `snapshot_huruf_nilai` sebagai referensi wajib
- [x] Test `finalisasi` dengan `huruf_nilai` yang sama dengan snapshot — berhasil
- [x] Test `finalisasi` dengan `huruf_nilai` BERBEDA dari snapshot — return 422 (validasi strict)
- [x] Test `finalisasi` tanpa `link_sk_konversi` — tetap berhasil (opsional)
- [x] Test `finalisasi` dengan `link_sk_konversi` URL valid — tersimpan
- [x] Test `finalisasi` dengan `link_sk_konversi` bukan URL valid — return 422
- [x] Test `huruf_nilai` tersimpan per MK di `pengajuan_mata_kuliah`
- [x] Test status pengajuan berubah ke `selesai`
- [x] Test mahasiswa bisa lihat hasil setelah status `selesai`

---

## PHASE 10 — Modul Wadek

### T10.1 — Matriks Controller

- [x] Buat `UpdateMatriksRequest` — validasi: `min_sks` integer nullable, `max_sks` >= min_sks, `huruf_nilai` nullable string max 5
- [x] Buat `app/Http/Controllers/Wadek/MatriksController.php`
- [x] Method `index`: list semua 24 baris matriks dengan relasi tingkatan + tahapan
- [x] Method `update`: update 1 baris matriks (min_sks, max_sks, huruf_nilai), set `updated_by` = Wadek login
- [x] Daftarkan route

### T10.2 — Verifikator Controller (Manajemen Tim)

- [x] Buat `AssignVerifikatorRequest` — validasi: `user_id` exists di users, `prodi_id` exists di prodi
- [x] Buat `app/Http/Controllers/Wadek/VerifikatorController.php`
- [x] Method `index`: list semua verifikator aktif per prodi (join users + prodi)
- [x] Method `store`: assign dosen ke prodi (insert `verifikator_prodi`), auto-tambah role `verifikator` jika belum punya
- [x] Method `destroy`: cabut verifikator (set `is_active = 0`), jika tidak ada prodi lain, cabut role `verifikator`
- [x] Daftarkan route

### T10.3 — Bidang Mata Kuliah Controller

- [x] Buat `app/Http/Controllers/Wadek/BidangMataKuliahController.php`
- [x] Method `index`: list mapping bidang -> MK (bisa filter by bidang_id atau prodi_id)
- [x] Method `store`: tambah mapping baru
- [x] Method `destroy`: hapus mapping
- [x] Daftarkan route

### T10.4 — Testing Modul Wadek

- [x] Test update matriks — nilai baru tersimpan, `updated_by` terisi
- [x] Test pengajuan baru sesudah matriks diupdate — pakai matriks baru (snapshot saat submit)
- [x] Test pengajuan lama tidak berubah setelah matriks diupdate (snapshot sudah terkunci)
- [x] Test assign verifikator baru ke prodi
- [x] Test cabut verifikator
- [x] Test tambah & hapus mapping bidang -> MK

---

## PHASE 11 — Dashboard & Direktori Verifikator

### T11.1 — Dashboard Statistik

- [x] Buat `app/Http/Controllers/Shared/DashboardController.php`
- [x] Method `statistik`:
  - [x] Query: `SELECT prodi_id, COUNT(*) as total FROM pengajuan GROUP BY prodi_id`
  - [x] Hitung persentase: `(total per prodi / grand total) * 100`
  - [x] Return: `[{ prodi: "SI", total: 45, persentase: 60.0 }, ...]`
- [x] Daftarkan route `GET /api/dashboard/statistik`

### T11.2 — Direktori Verifikator

- [x] Buat `app/Http/Controllers/Shared/VerifikatorDirektoriController.php`
- [x] Method `index`: list verifikator aktif per prodi (grouped by prodi)
- [x] Daftarkan route `GET /api/direktori-verifikator`

### T11.3 — Testing

- [x] Test statistik — angka persentase benar sesuai data
- [x] Test direktori — semua verifikator aktif muncul, dikelompokkan per prodi

---

## PHASE 12 — Hardening & Final Testing

### T12.1 — Security & Edge Cases

- [x] Pastikan semua endpoint protected (tidak ada endpoint tanpa auth yang seharusnya protected)
- [x] Pastikan mahasiswa tidak bisa akses endpoint verifikator/tendik/wadek
- [x] Pastikan verifikator tidak bisa finalisasi (endpoint tendik)
- [x] Pastikan tendik tidak bisa update matriks (endpoint wadek)
- [x] Test `link_sertifikat` bukan format URL valid — return 422
- [x] Test `link_surat_tugas_mahasiswa` bukan URL valid (saat status Ada) — return 422
- [x] Test `link_surat_tugas_dosen` bukan URL valid (saat status Ada) — return 422
- [x] Test submit dengan `mata_kuliah_ids` dari prodi lain — return 422
- [x] Test submit dengan `mata_kuliah_ids` dari bidang berbeda — return 422

### T12.2 — Response Konsistensi

- [x] Cek semua response menggunakan format standar `{success, message, data}`
- [x] Cek semua error return `{success: false, message, errors}`
- [x] Cek HTTP status code sesuai tabel di `tech.md` bagian 4.3

### T12.3 — Data Integrity

- [x] Verifikasi snapshot matriks tersimpan benar di `pengajuan`
- [x] Verifikasi `pengajuan_mata_kuliah` terisi benar saat submit
- [x] Verifikasi `huruf_nilai` di `pengajuan_mata_kuliah` terisi setelah finalisasi Tendik
- [x] Verifikasi `verified_at` dan `processed_at` terisi dengan timestamp benar

---

## PHASE 13 — Dokumentasi API (Opsional tapi Disarankan)

- [x] Generate dokumentasi API (Postman Collection atau Swagger/L5-Swagger)
- [x] Sertakan contoh request body & response untuk setiap endpoint
- [x] Bagikan ke tim frontend

---

## Ringkasan Status Phase

| Phase | Nama | Status |
|---|---|---|
| 0 | Dokumen Spesifikasi | Selesai |
| 1 | Setup Project Laravel | Selesai |
| 2 | Database Migration | Selesai |
| 3 | Model Eloquent | Selesai |
| 4 | Seeder | Selesai |
| 5 | Auth & Middleware | Selesai |
| 6 | Service Classes | Selesai |
| 7 | Modul Mahasiswa | Selesai |
| 8 | Modul Verifikator | Selesai |
| 9 | Modul Tendik | Selesai |
| 10 | Modul Wadek | Selesai |
| 11 | Dashboard & Direktori | Selesai |
| 12 | Hardening & Testing | Selesai |
| 13 | Dokumentasi API | Selesai |
