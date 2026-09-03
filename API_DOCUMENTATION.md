# SIMPRESMA — Dokumentasi API Backend

Dokumentasi lengkap REST API **SIMPRESMA** (Sistem Informasi Manajemen Prestasi Mahasiswa) berbasis Laravel 11 dan Laravel Sanctum.

---

## 1. Panduan Cepat & Base URL

- **Base URL Lokal:** `http://localhost:8000/api`
- **Format Data:** JSON (`Content-Type: application/json` & `Accept: application/json`)
- **Autentikasi:** Bearer Token via header:
  ```http
  Authorization: Bearer <token_dari_login>
  ```
- **File Postman Collection:** [`SIMPRESMA_Postman_Collection.json`](file:///d:/iann%20Kuliah/1.Project/SIMPRESMA/SIMPRESMA_Postman_Collection.json)
- **File OpenAPI 3.0 Spec:** [`openapi.json`](file:///d:/iann%20Kuliah/1.Project/SIMPRESMA/openapi.json)

---

## 2. Akun Demo & Kredensial Pengujian

Semua akun dummy menggunakan password default: **`password`**

| Role | Email | Password | Scope / Keterangan |
|---|---|---|---|
| **Mahasiswa** | `mhs.si@test.com` | `password` | Mahasiswa Sistem Informasi (SI) |
| **Mahasiswa** | `mhs.ti@test.com` | `password` | Mahasiswa Teknologi Informasi (TI) |
| **Mahasiswa** | `mhs.if@test.com` | `password` | Mahasiswa Informatika (IF) |
| **Verifikator** | `verif.si@test.com` | `password` | Dosen Verifikator Scope SI |
| **Verifikator** | `verif.ti@test.com` | `password` | Dosen Verifikator Scope TI |
| **Verifikator** | `verif.if@test.com` | `password` | Dosen Verifikator Scope IF |
| **Tendik** | `tendik@test.com` | `password` | Staf Tendik (Seluruh Prodi) |
| **Wadek** | `wadek@test.com` | `password` | Wakil Dekan (Admin Master) |
| **Multi-role** | `multi@test.com` | `password` | Verifikator SI + Tendik |

---

## 3. Format Respons Standar

### 3.1 Response Sukses Single Object / Action (200 / 201)
```json
{
  "success": true,
  "message": "Pengajuan prestasi berhasil disubmit.",
  "data": { ... }
}
```

### 3.2 Response Sukses Paginated List (200)
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
      "total": 68
    }
  }
}
```

### 3.3 Response Error Validasi (422)
```json
{
  "message": "Satu atau lebih mata kuliah yang dipilih tidak sesuai dengan bidang lomba dan program studi mahasiswa.",
  "errors": {
    "mata_kuliah_ids": [
      "Satu atau lebih mata kuliah yang dipilih tidak sesuai dengan bidang lomba dan program studi mahasiswa."
    ]
  }
}
```

### 3.4 Response Error Autentikasi / Otorisasi (401 / 403)
```json
// 401 Unauthorized
{
  "success": false,
  "message": "Tidak terautentikasi. Silakan login terlebih dahulu."
}

// 403 Forbidden
{
  "success": false,
  "message": "Akses ditolak. Role yang diperlukan: wadek"
}
```

---

## 4. Matriks Akses Endpoint Berdasarkan Role

| Method | Endpoint | Mahasiswa | Verifikator | Tendik | Wadek | Keterangan |
|---|---|:---:|:---:|:---:|:---:|---|
| `POST` | `/api/auth/login` | ✅ | ✅ | ✅ | ✅ | Public |
| `POST` | `/api/auth/logout` | ✅ | ✅ | ✅ | ✅ | Revoke token |
| `GET` | `/api/auth/me` | ✅ | ✅ | ✅ | ✅ | Info profil user aktif |
| `GET` | `/api/ref/*` | ✅ | ✅ | ✅ | ✅ | Lookup prodi, tingkatan, tahapan, bidang, matriks, MK |
| `GET` | `/api/mahasiswa/pengajuan` | ✅ | ❌ | ❌ | ❌ | List pengajuan mahasiswa login |
| `POST` | `/api/mahasiswa/pengajuan` | ✅ | ❌ | ❌ | ❌ | Submit pengajuan konversi SKS |
| `GET` | `/api/mahasiswa/pengajuan/{id}` | ✅ (Milik sendiri) | ❌ | ❌ | ❌ | Detail pengajuan mahasiswa login |
| `GET` | `/api/verifikator/pengajuan` | ❌ | ✅ | ❌ | ❌ | List pengajuan `pending` scope prodi verifikator |
| `GET` | `/api/verifikator/pengajuan/{id}` | ❌ | ✅ (Scope prodi) | ❌ | ❌ | Detail dokumen verifikasi |
| `POST` | `/api/verifikator/pengajuan/{id}/terima` | ❌ | ✅ (Scope prodi) | ❌ | ❌ | Ubah status `pending` -> `diterima` |
| `POST` | `/api/verifikator/pengajuan/{id}/tolak` | ❌ | ✅ (Scope prodi) | ❌ | ❌ | Ubah status `pending` -> `ditolak` (wajib feedback) |
| `GET` | `/api/tendik/pengajuan` | ❌ | ❌ | ✅ | ❌ | List pengajuan `diterima` dari semua prodi |
| `GET` | `/api/tendik/pengajuan/{id}` | ❌ | ❌ | ✅ | ❌ | Detail pengajuan + list MK + snapshot nilai |
| `POST` | `/api/tendik/pengajuan/{id}/finalisasi` | ❌ | ❌ | ✅ | ❌ | Finalisasi nilai konversi (strict snapshot check) |
| `GET` | `/api/wadek/matriks` | ❌ | ❌ | ❌ | ✅ | List semua 24 baris matriks konversi |
| `PUT` | `/api/wadek/matriks/{id}` | ❌ | ❌ | ❌ | ✅ | Update batas SKS & nilai matriks |
| `GET` | `/api/wadek/verifikator` | ❌ | ❌ | ❌ | ✅ | List verifikator aktif per prodi |
| `POST` | `/api/wadek/verifikator` | ❌ | ❌ | ❌ | ✅ | Assign dosen verifikator ke prodi |
| `DELETE`| `/api/wadek/verifikator/{id}` | ❌ | ❌ | ❌ | ✅ | Cabut verifikator |
| `GET` | `/api/wadek/bidang-mata-kuliah` | ❌ | ❌ | ❌ | ✅ | List mapping bidang -> MK |
| `POST` | `/api/wadek/bidang-mata-kuliah` | ❌ | ❌ | ❌ | ✅ | Tambah mapping bidang -> MK |
| `DELETE`| `/api/wadek/bidang-mata-kuliah/{id}`| ❌ | ❌ | ❌ | ✅ | Hapus mapping bidang -> MK |
| `GET` | `/api/dashboard/statistik` | ✅ | ✅ | ✅ | ✅ | Statistik pengajuan per prodi |
| `GET` | `/api/direktori-verifikator` | ✅ | ✅ | ✅ | ✅ | Direktori verifikator aktif per prodi |

---

## 5. Rincian Endpoint per Modul

### 5.1 Modul Autentikasi (`/api/auth`)

#### `POST /api/auth/login`
- **Headers:** `Accept: application/json`, `Content-Type: application/json`
- **Request Body:**
  ```json
  {
    "email": "mhs.si@test.com",
    "password": "password"
  }
  ```
- **Response 200:**
  ```json
  {
    "success": true,
    "message": "Login berhasil.",
    "data": {
      "token": "1|nB4vK2Z2qGkL...",
      "user": {
        "id": 1,
        "nim_nip": "220210101001",
        "nama": "Mahasiswa SI",
        "email": "mhs.si@test.com",
        "no_whatsapp": "08111110001",
        "prodi": {
          "id": 1,
          "kode": "24",
          "singkatan": "SI",
          "nama": "Sistem Informasi"
        },
        "roles": [
          "mahasiswa"
        ]
      }
    }
  }
  ```

#### `GET /api/auth/me`
- **Headers:** `Authorization: Bearer <token>`, `Accept: application/json`
- **Response 200:** Mengembalikan data user aktif beserta roles dan info prodi.

#### `POST /api/auth/logout`
- **Headers:** `Authorization: Bearer <token>`, `Accept: application/json`
- **Response 200:** Token saat ini di-revoke dari database.

---

### 5.2 Modul Data Referensi (`/api/ref`)

Digunakan untuk mengisi dropdown/pilihan pada form pengajuan prestasi.

- `GET /api/ref/prodi` — Daftar 3 Program Studi (SI, TI, IF).
- `GET /api/ref/tingkatan` — Daftar 6 Tingkatan Lomba (Internasional, Nasional, dll).
- `GET /api/ref/tahapan` — Daftar 4 Tahapan Lomba (Mendaftar, Lolos Tahap Awal, Finalis, Juara).
- `GET /api/ref/bidang` — Daftar 18 Bidang Lomba aktif.
- `GET /api/ref/matriks?tingkatan_id=1&tahapan_id=2` — Mengambil range SKS (`min_sks`, `max_sks`) dan `huruf_nilai` untuk kombinasi tingkatan & tahapan.
- `GET /api/ref/mata-kuliah?bidang_id=6&prodi_id=1` — Daftar MK yang dapat dipilih berdasarkan bidang lomba dan prodi mahasiswa.

---

### 5.3 Modul Mahasiswa (`/api/mahasiswa`)

#### `GET /api/mahasiswa/pengajuan`
- **Deskripsi:** List pengajuan milik mahasiswa yang sedang login (paginated).
- **Query Params:** `page` (default: 1)

#### `POST /api/mahasiswa/pengajuan`
- **Deskripsi:** Submit pengajuan prestasi baru. Seluruh bukti dokumen berupa tautan URL (Zero file upload).
- **Request Body:**
  ```json
  {
    "nama_tim": "Tim Hackathon SI",
    "no_whatsapp": "081234567890",
    "nama_lomba": "Gemastik XVII 2026",
    "bidang_id": 6,
    "tingkatan_id": 1,
    "tahapan_id": 2,
    "detail_juara": "Juara 3",
    "mata_kuliah_ids": [1],
    "link_sertifikat": "https://drive.google.com/file/d/sertifikat-gemastik.pdf",
    "status_surat_tugas_mahasiswa": 1,
    "link_surat_tugas_mahasiswa": "https://drive.google.com/file/d/st-mhs.pdf",
    "status_surat_tugas_dosen": 1,
    "link_surat_tugas_dosen": "https://drive.google.com/file/d/st-dosen.pdf",
    "link_poster": "https://drive.google.com/file/d/poster.jpg",
    "link_sosmed": "https://instagram.com/p/gemastik2026",
    "keterangan": "Pengajuan konversi SKS lomba Gemastik"
  }
  ```
- **Response 201:** Data pengajuan tersimpan beserta snapshot nilai dan pivot MK.

#### `GET /api/mahasiswa/pengajuan/{id}`
- **Deskripsi:** Detail lengkap pengajuan milik sendiri. Jika sudah berstatus `selesai`, nilai konversi per mata kuliah akan tampil di `pengajuan_mata_kuliahs`.

---

### 5.4 Modul Verifikator (`/api/verifikator`)

#### `GET /api/verifikator/pengajuan`
- **Deskripsi:** Menampilkan antrean pengajuan berstatus `pending` khusus dari program studi yang berada dalam wewenang dosen verifikator aktif.

#### `GET /api/verifikator/pengajuan/{id}`
- **Deskripsi:** Melihat detail dokumen pengajuan mahasiswa untuk diverifikasi. Mengembalikan `403` jika pengajuan berasal dari luar prodi verifikator.

#### `POST /api/verifikator/pengajuan/{id}/terima`
- **Deskripsi:** Menerima pengajuan prestasi. Status berubah menjadi `diterima` dan otomatis muncul di antrean Tendik.

#### `POST /api/verifikator/pengajuan/{id}/tolak`
- **Deskripsi:** Menolak pengajuan. Status berubah menjadi `ditolak`.
- **Request Body:**
  ```json
  {
    "feedback_verifikator": "Dokumen surat tugas tidak ditandatangani oleh pimpinan. Silakan perbaiki dan submit ulang."
  }
  ```

---

### 5.5 Modul Tendik (`/api/tendik`)

#### `GET /api/tendik/pengajuan`
- **Deskripsi:** Menampilkan antrean pengajuan berstatus `diterima` dari **seluruh program studi**.

#### `GET /api/tendik/pengajuan/{id}`
- **Deskripsi:** Melihat detail pengajuan, list mata kuliah yang dipilih, serta referensi wajib `snapshot_huruf_nilai`.

#### `POST /api/tendik/pengajuan/{id}/finalisasi`
- **Deskripsi:** Finalisasi konversi SKS & penginputan nilai. Status berubah menjadi `selesai`.
- **Aturan:** Huruf nilai pada `nilai_per_mk` **wajib sama persis** dengan `snapshot_huruf_nilai` pada pengajuan (jika berbeda akan ditolak dengan error 422).
- **Request Body:**
  ```json
  {
    "nilai_per_mk": [
      {
        "mk_id": 1,
        "huruf_nilai": "AB"
      }
    ],
    "link_sk_konversi": "https://siakad.univ.ac.id/sk/2026/sk-konversi-12345.pdf"
  }
  ```

---

### 5.6 Modul Wadek (`/api/wadek`)

#### `GET /api/wadek/matriks` & `PUT /api/wadek/matriks/{id}`
- **Deskripsi:** Menampilkan dan memperbarui aturan batas SKS & huruf nilai pada baris matriks konversi.
- **Request Body Update:**
  ```json
  {
    "min_sks": 3,
    "max_sks": 4,
    "huruf_nilai": "A"
  }
  ```

#### `GET /api/wadek/verifikator` & `POST /api/wadek/verifikator` & `DELETE /api/wadek/verifikator/{id}`
- **Deskripsi:** Manajemen penugasan dosen verifikator per program studi.
- **Request Body Assign (`POST`):**
  ```json
  {
    "user_id": 4,
    "prodi_id": 1
  }
  ```

#### `GET /api/wadek/bidang-mata-kuliah` & `POST /api/wadek/bidang-mata-kuliah` & `DELETE /api/wadek/bidang-mata-kuliah/{id}`
- **Deskripsi:** Pengaturan relasi antara bidang lomba dengan mata kuliah yang diakui.
- **Request Body Tambah Mapping (`POST`):**
  ```json
  {
    "bidang_id": 6,
    "mata_kuliah_id": 1
  }
  ```

---

### 5.7 Modul Shared & Dashboard

Dapat diakses oleh seluruh pengguna yang telah login (Mahasiswa, Verifikator, Tendik, Wadek).

#### `GET /api/dashboard/statistik`
- **Response 200:**
  ```json
  {
    "success": true,
    "message": "OK",
    "data": {
      "grand_total": 45,
      "per_prodi": [
        {
          "prodi_id": 1,
          "prodi": "SI",
          "nama_prodi": "Sistem Informasi",
          "total": 25,
          "persentase": 55.56,
          "by_status": {
            "pending": 5,
            "diterima": 10,
            "ditolak": 2,
            "selesai": 8
          }
        },
        {
          "prodi_id": 2,
          "prodi": "TI",
          "nama_prodi": "Teknologi Informasi",
          "total": 12,
          "persentase": 26.67,
          "by_status": {
            "pending": 2,
            "diterima": 5,
            "ditolak": 1,
            "selesai": 4
          }
        },
        {
          "prodi_id": 3,
          "prodi": "IF",
          "nama_prodi": "Informatika",
          "total": 8,
          "persentase": 17.78,
          "by_status": {
            "pending": 1,
            "diterima": 3,
            "ditolak": 1,
            "selesai": 3
          }
        }
      ]
    }
  }
  ```

#### `GET /api/direktori-verifikator`
- **Response 200:** Menampilkan daftar dosen verifikator aktif yang dikelompokkan per Program Studi (SI, TI, IF).
