# SIMPRESMA — Structure Document
## Skema Database & Relasi Lengkap

> **STATUS:** FINAL — Mengacu pada `requirements.md`.
> Dokumen ini adalah sumber kebenaran untuk seluruh migration, model, dan query.

---

## 1. Ringkasan Tabel

| Tabel | Fungsi |
|---|---|
| `prodi` | Master program studi (SI/TI/IF) |
| `users` | Semua pengguna sistem |
| `roles` | Master role (mahasiswa/verifikator/tendik/wadek) |
| `user_roles` | Pivot many-to-many user-role |
| `verifikator_prodi` | Assignment verifikator ke prodi (many-to-many) |
| `tingkatan_lomba` | Master 6 tingkatan lomba |
| `tahapan_lomba` | Master 4 tahapan lomba |
| `matriks_konversi` | Matriks SKS & Nilai (tingkatan x tahapan) |
| `bidang_lomba` | Master bidang lomba (18 bidang) |
| `mata_kuliah` | Daftar mata kuliah per prodi |
| `bidang_mata_kuliah` | Pivot mapping bidang -> MK (per prodi via MK) |
| `pengajuan` | Pengajuan prestasi mahasiswa |
| `pengajuan_mata_kuliah` | MK yang dipilih + nilai yang ditetapkan Tendik |

---

## 2. Detail Skema Tiap Tabel

---

### 2.1 Tabel `prodi`

```sql
CREATE TABLE prodi (
    id          TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kode        VARCHAR(10)  NOT NULL UNIQUE, -- PSSI, PSTI, PSIF
    singkatan   VARCHAR(5)   NOT NULL UNIQUE, -- SI, TI, IF
    nama        VARCHAR(100) NOT NULL,
    created_at  TIMESTAMP NULL,
    updated_at  TIMESTAMP NULL
);
```

**Seed Data:**

| id | kode | singkatan | nama |
|---|---|---|---|
| 1 | PSSI | SI | Sistem Informasi |
| 2 | PSTI | TI | Teknologi Informasi |
| 3 | PSIF | IF | Informatika |

---

### 2.2 Tabel `users`

```sql
CREATE TABLE users (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nim_nip     VARCHAR(30)  NULL UNIQUE,     -- NIM mahasiswa / NIP dosen/tendik
    nama        VARCHAR(150) NOT NULL,
    email       VARCHAR(150) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,        -- bcrypt, untuk dummy auth
    prodi_id    TINYINT UNSIGNED NULL,        -- FK prodi, hanya diisi untuk mahasiswa
    no_whatsapp VARCHAR(20)  NULL,
    remember_token VARCHAR(100) NULL,
    created_at  TIMESTAMP NULL,
    updated_at  TIMESTAMP NULL,

    FOREIGN KEY (prodi_id) REFERENCES prodi(id)
);
```

**Catatan:**
- Kolom `nim_nip`, `prodi_id` dirancang agar kompatibel dengan response CAS nanti (Tahap 2).
- `prodi_id` NULL untuk user non-mahasiswa (verifikator, tendik, wadek).
- Password di-hash bcrypt. Untuk Tahap 1 (dummy), seed manual.

---

### 2.3 Tabel `roles`

```sql
CREATE TABLE roles (
    id          TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(30)  NOT NULL UNIQUE, -- mahasiswa, verifikator, tendik, wadek
    display_name VARCHAR(50) NOT NULL,
    created_at  TIMESTAMP NULL,
    updated_at  TIMESTAMP NULL
);
```

**Seed Data:**

| id | name | display_name |
|---|---|---|
| 1 | mahasiswa | Mahasiswa |
| 2 | verifikator | Tim Verifikator |
| 3 | tendik | Tenaga Kependidikan |
| 4 | wadek | Wakil Dekan |

---

### 2.4 Tabel `user_roles` (Pivot many-to-many)

```sql
CREATE TABLE user_roles (
    user_id     BIGINT UNSIGNED NOT NULL,
    role_id     TINYINT UNSIGNED NOT NULL,
    created_at  TIMESTAMP NULL,

    PRIMARY KEY (user_id, role_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
);
```

---

### 2.5 Tabel `verifikator_prodi` (Assignment verifikator per prodi)

```sql
CREATE TABLE verifikator_prodi (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         BIGINT UNSIGNED NOT NULL,   -- dosen yang ditunjuk
    prodi_id        TINYINT UNSIGNED NOT NULL,
    assigned_by     BIGINT UNSIGNED NOT NULL,   -- FK ke user Wadek
    is_active       TINYINT(1) NOT NULL DEFAULT 1,
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,

    UNIQUE KEY uq_verifikator_prodi (user_id, prodi_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (prodi_id) REFERENCES prodi(id),
    FOREIGN KEY (assigned_by) REFERENCES users(id)
);
```

**Catatan:**
- Satu dosen bisa menjadi verifikator untuk lebih dari 1 prodi (UNIQUE pada pasangan, bukan user saja).
- `is_active = 0` artinya dicabut tapi historinya tetap ada.

---

### 2.6 Tabel `tingkatan_lomba`

```sql
CREATE TABLE tingkatan_lomba (
    id      TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama    VARCHAR(200) NOT NULL,
    urutan  TINYINT UNSIGNED NOT NULL  -- untuk sorting tampilan
);
```

**Seed Data:**

| id | urutan | nama |
|---|---|---|
| 1 | 1 | Internasional |
| 2 | 2 | Nasional Kementerian: Gemastik, LIDM, Satria Data, NUDC, KDMI, MTQMN |
| 3 | 3 | Nasional Kementerian: PKM, P2MW, PPK Ormawa, Pilmapres, Peksiminas |
| 4 | 4 | Nasional Non Kementerian / Mandiri |
| 5 | 5 | Wilayah / Regional / Provinsi |
| 6 | 6 | Promahadesa |

---

### 2.7 Tabel `tahapan_lomba`

```sql
CREATE TABLE tahapan_lomba (
    id      TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kode    VARCHAR(30)  NOT NULL UNIQUE, -- mendaftar, lolos_tahap_awal, finalis, pemenang
    nama    VARCHAR(100) NOT NULL,
    urutan  TINYINT UNSIGNED NOT NULL
);
```

**Seed Data:**

| id | kode | nama | urutan |
|---|---|---|---|
| 1 | mendaftar | Mendaftar | 1 |
| 2 | lolos_tahap_awal | Lolos Tahap Awal | 2 |
| 3 | finalis | Finalis | 3 |
| 4 | pemenang | Pemenang | 4 |

---

### 2.8 Tabel `matriks_konversi`

```sql
CREATE TABLE matriks_konversi (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tingkatan_id    TINYINT UNSIGNED NOT NULL,
    tahapan_id      TINYINT UNSIGNED NOT NULL,
    min_sks         TINYINT UNSIGNED NULL,   -- NULL = kombinasi ini tidak valid/tidak menghasilkan konversi
    max_sks         TINYINT UNSIGNED NULL,
    huruf_nilai     VARCHAR(5) NULL,         -- A, AB, B, dst. NULL jika tidak valid
    is_active       TINYINT(1) NOT NULL DEFAULT 1,
    updated_by      BIGINT UNSIGNED NULL,    -- FK users (Wadek yang terakhir ubah)
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,

    UNIQUE KEY uq_matriks (tingkatan_id, tahapan_id),
    FOREIGN KEY (tingkatan_id) REFERENCES tingkatan_lomba(id),
    FOREIGN KEY (tahapan_id)   REFERENCES tahapan_lomba(id),
    FOREIGN KEY (updated_by)   REFERENCES users(id)
);
```

**Seed Data (dari Excel SKS + Nilai):**

| tingkatan_id | tahapan_id | min_sks | max_sks | huruf_nilai |
|---|---|---|---|---|
| 1 | 1 | NULL | NULL | NULL |
| 1 | 2 | 2 | 3 | AB |
| 1 | 3 | 4 | 6 | A |
| 1 | 4 | 8 | 12 | A |
| 2 | 1 | NULL | NULL | NULL |
| 2 | 2 | NULL | NULL | NULL |
| 2 | 3 | 4 | 6 | AB |
| 2 | 4 | 6 | 9 | A |
| 3 | 1 | NULL | NULL | NULL |
| 3 | 2 | 4 | 6 | A |
| 3 | 3 | 6 | 9 | A |
| 3 | 4 | 8 | 12 | A |
| 4 | 1 | NULL | NULL | NULL |
| 4 | 2 | NULL | NULL | NULL |
| 4 | 3 | 2 | 3 | AB |
| 4 | 4 | 4 | 6 | A |
| 5 | 1 | NULL | NULL | NULL |
| 5 | 2 | NULL | NULL | NULL |
| 5 | 3 | 2 | 3 | B |
| 5 | 4 | 4 | 6 | AB |
| 6 | 1 | NULL | NULL | NULL |
| 6 | 2 | 2 | 3 | A |
| 6 | 3 | NULL | NULL | NULL |
| 6 | 4 | NULL | NULL | NULL |

**Aturan:**
- Baris dengan `min_sks IS NULL` berarti kombinasi tidak valid (tidak bisa dipilih mahasiswa).
- Perubahan oleh Wadek hanya mengupdate baris yang ada (tidak insert baru), dan pengajuan lama sudah pakai snapshot.

---

### 2.9 Tabel `bidang_lomba`

```sql
CREATE TABLE bidang_lomba (
    id          SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama        VARCHAR(100) NOT NULL,
    keterangan  TEXT NULL,
    is_active   TINYINT(1) NOT NULL DEFAULT 1,
    created_at  TIMESTAMP NULL,
    updated_at  TIMESTAMP NULL
);
```

**Seed Data (18 bidang):**
Kewirausahaan, Graphic Design, Desain Poster, VGK, UI/UX, Programming, Software Development, Karya Tulis Ilmiah, Matematika Komputasi, Non Akademik, Immersive Development, KKN, Embedded dan IOT, Jaringan dan Sekuritas, PPK Ormawa, BMC, Data Science, Data Analytics.

---

### 2.10 Tabel `mata_kuliah`

```sql
CREATE TABLE mata_kuliah (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    prodi_id    TINYINT UNSIGNED NOT NULL,
    kode_mk     VARCHAR(20)  NOT NULL,
    nama_mk     VARCHAR(200) NOT NULL,
    sks         TINYINT UNSIGNED NOT NULL,
    semester    TINYINT UNSIGNED NULL,
    is_active   TINYINT(1) NOT NULL DEFAULT 1,
    created_at  TIMESTAMP NULL,
    updated_at  TIMESTAMP NULL,

    UNIQUE KEY uq_mk_prodi (kode_mk, prodi_id),
    FOREIGN KEY (prodi_id) REFERENCES prodi(id)
);
```

**Catatan:**
- Satu `kode_mk` bisa muncul di lebih dari 1 prodi (ada MK lintas prodi seperti KSU1101).
- Unique constraint pada kombinasi `(kode_mk, prodi_id)`.
- Data di-seed dari sheet `Bidang` Excel (sudah ada nama + kode + SKS tiap MK).

---

### 2.11 Tabel `bidang_mata_kuliah` (Pivot Mapping Bidang -> MK)

```sql
CREATE TABLE bidang_mata_kuliah (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    bidang_id       SMALLINT UNSIGNED NOT NULL,
    mata_kuliah_id  BIGINT UNSIGNED NOT NULL,
    is_active       TINYINT(1) NOT NULL DEFAULT 1,
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,

    UNIQUE KEY uq_bidang_mk (bidang_id, mata_kuliah_id),
    FOREIGN KEY (bidang_id)      REFERENCES bidang_lomba(id) ON DELETE CASCADE,
    FOREIGN KEY (mata_kuliah_id) REFERENCES mata_kuliah(id) ON DELETE CASCADE
);
```

**Catatan:**
- Prodi sudah terwakili oleh `mata_kuliah.prodi_id`.
- Query: "Tampilkan MK untuk Bidang X + Prodi Y" = `bidang_mata_kuliah JOIN mata_kuliah WHERE bidang_id = X AND mata_kuliah.prodi_id = Y`.
- Dikelola Wadek via panel admin (CRUD).

---

### 2.12 Tabel `pengajuan` (Inti sistem)

```sql
CREATE TABLE pengajuan (
    id                              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    -- Identitas Mahasiswa (snapshot saat submit)
    user_id                         BIGINT UNSIGNED NOT NULL,
    prodi_id                        TINYINT UNSIGNED NOT NULL,   -- snapshot prodi mahasiswa

    -- Data Tim & Kontak
    nama_tim                        VARCHAR(150) NULL,
    no_whatsapp                     VARCHAR(20)  NOT NULL,

    -- Data Lomba
    nama_lomba                      VARCHAR(200) NOT NULL,
    bidang_id                       SMALLINT UNSIGNED NOT NULL,
    tingkatan_id                    TINYINT UNSIGNED NOT NULL,
    tahapan_id                      TINYINT UNSIGNED NOT NULL,
    detail_juara                    VARCHAR(50) NULL,  -- "Juara 1", "Juara 2", "HM", dll

    -- Snapshot Matriks (diambil saat POST submit, tidak berubah walau matriks diupdate Wadek)
    snapshot_min_sks                TINYINT UNSIGNED NULL,
    snapshot_max_sks                TINYINT UNSIGNED NULL,
    snapshot_huruf_nilai            VARCHAR(5) NULL,

    -- Dokumen — Semua berupa Link/URL (tidak ada upload file)
    link_sertifikat                 VARCHAR(500) NOT NULL,        -- link/URL wajib
    status_surat_tugas_mahasiswa    TINYINT(1) NOT NULL DEFAULT 0,
    link_surat_tugas_mahasiswa      VARCHAR(500) NULL,            -- link jika ada
    status_surat_tugas_dosen        TINYINT(1) NOT NULL DEFAULT 0,
    link_surat_tugas_dosen          VARCHAR(500) NULL,            -- link jika ada
    link_poster                     VARCHAR(500) NULL,
    link_sosmed                     VARCHAR(500) NULL,
    keterangan                      TEXT NULL,

    -- Status & Alur
    status                          ENUM('pending','diterima','ditolak','selesai')
                                    NOT NULL DEFAULT 'pending',

    -- Verifikator
    feedback_verifikator            TEXT NULL,    -- wajib diisi jika status = ditolak
    verifikator_id                  BIGINT UNSIGNED NULL,
    verified_at                     TIMESTAMP NULL,

    -- Tendik
    link_sk_konversi                VARCHAR(500) NULL,    -- link/URL opsional, diisi Tendik
    tendik_id                       BIGINT UNSIGNED NULL,
    processed_at                    TIMESTAMP NULL,

    created_at                      TIMESTAMP NULL,
    updated_at                      TIMESTAMP NULL,

    FOREIGN KEY (user_id)         REFERENCES users(id),
    FOREIGN KEY (prodi_id)        REFERENCES prodi(id),
    FOREIGN KEY (bidang_id)       REFERENCES bidang_lomba(id),
    FOREIGN KEY (tingkatan_id)    REFERENCES tingkatan_lomba(id),
    FOREIGN KEY (tahapan_id)      REFERENCES tahapan_lomba(id),
    FOREIGN KEY (verifikator_id)  REFERENCES users(id),
    FOREIGN KEY (tendik_id)       REFERENCES users(id)
);
```

**Aturan penting:**
- `snapshot_*` diisi server dari query `matriks_konversi` saat POST diterima — frontend hanya menampilkan, tidak mengirim nilai matriks.
- `link_sk_konversi`, `tendik_id`, `processed_at` hanya diisi saat Tendik memfinalisasi.
- `verifikator_id`, `verified_at` diisi saat Verifikator memproses.
- Validasi duplikasi aktif: Tidak boleh ada 2 baris dengan `user_id` + `nama_lomba` yang sama dan status `IN ('pending', 'diterima')`.
- Semua kolom `link_*` menyimpan URL string (VARCHAR 500) — tidak ada path file lokal.

---

### 2.13 Tabel `pengajuan_mata_kuliah` (Pivot MK yang dipilih + nilai Tendik)

```sql
CREATE TABLE pengajuan_mata_kuliah (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pengajuan_id    BIGINT UNSIGNED NOT NULL,
    mata_kuliah_id  BIGINT UNSIGNED NOT NULL,
    sks_snapshot    TINYINT UNSIGNED NOT NULL,  -- snapshot SKS MK saat mahasiswa memilih
    huruf_nilai     VARCHAR(5) NULL,             -- ditetapkan Tendik saat finalisasi (nullable sampai status selesai)

    UNIQUE KEY uq_pengajuan_mk (pengajuan_id, mata_kuliah_id),
    FOREIGN KEY (pengajuan_id)   REFERENCES pengajuan(id) ON DELETE CASCADE,
    FOREIGN KEY (mata_kuliah_id) REFERENCES mata_kuliah(id)
);
```

**Aturan:**
- Baris dibuat saat mahasiswa submit (`huruf_nilai` masih NULL).
- Tendik mengisi `huruf_nilai` per baris saat finalisasi secara manual (satu per satu).
- Nilai yang diinput Tendik **wajib persis sama dengan `snapshot_huruf_nilai`** di tabel `pengajuan`. Jika berbeda, server menolak request (422).
- Tidak ada nilai bebas/override — sistem memvalidasi ketat server-side.

---

## 3. Diagram Relasi (ERD Summary)

```
prodi (1) ─────────────────────────── (N) users
prodi (1) ─────────────────────────── (N) mata_kuliah
prodi (1) ─────────────────────────── (N) verifikator_prodi
users (1) ─────────────────────────── (N) user_roles ─── (1) roles
users (1) ─────────────────────────── (N) verifikator_prodi
users (1) ─────────────────────────── (N) pengajuan [sebagai mahasiswa]
users (1) ─────────────────────────── (N) pengajuan [sebagai verifikator_id]
users (1) ─────────────────────────── (N) pengajuan [sebagai tendik_id]
tingkatan_lomba (1) ───────────────── (N) matriks_konversi
tahapan_lomba (1) ─────────────────── (N) matriks_konversi
tingkatan_lomba (1) ───────────────── (N) pengajuan
tahapan_lomba (1) ─────────────────── (N) pengajuan
bidang_lomba (1) ──────────────────── (N) bidang_mata_kuliah ─── (1) mata_kuliah
bidang_lomba (1) ──────────────────── (N) pengajuan
pengajuan (1) ─────────────────────── (N) pengajuan_mata_kuliah ─── (1) mata_kuliah
```

---

## 4. Index yang Direkomendasikan

```sql
-- Untuk filter pengajuan per prodi (Verifikator)
CREATE INDEX idx_pengajuan_prodi_status ON pengajuan(prodi_id, status);

-- Untuk filter pengajuan per mahasiswa
CREATE INDEX idx_pengajuan_user ON pengajuan(user_id);

-- Untuk lookup matriks
CREATE INDEX idx_matriks_kombo ON matriks_konversi(tingkatan_id, tahapan_id);

-- Untuk lookup bidang-MK per prodi
CREATE INDEX idx_mk_prodi ON mata_kuliah(prodi_id);
```

---

## 5. Konvensi Penamaan

| Konvensi | Contoh |
|---|---|
| Tabel | snake_case plural | `pengajuan_mata_kuliah` |
| Primary Key | `id` (auto increment) | `id` |
| Foreign Key | `{tabel_singular}_id` | `pengajuan_id`, `prodi_id` |
| Boolean | `is_*` atau `status_*` | `is_active`, `status_surat_tugas_mahasiswa` |
| Timestamp khusus | `*_at` | `verified_at`, `processed_at` |
| Enum | lowercase | `'pending'`, `'diterima'` |
| Snapshot kolom | `snapshot_*` | `snapshot_min_sks`, `snapshot_huruf_nilai` |

---

## 6. Catatan Migrasi Laravel

- Urutan migration: `prodi` -> `roles` -> `users` -> `user_roles` -> `verifikator_prodi` -> `tingkatan_lomba` -> `tahapan_lomba` -> `matriks_konversi` -> `bidang_lomba` -> `mata_kuliah` -> `bidang_mata_kuliah` -> `pengajuan` -> `pengajuan_mata_kuliah`.
- Gunakan `unsignedBigInteger()` untuk semua FK ke `users.id`.
- Gunakan `unsignedTinyInteger()` untuk FK ke tabel master kecil (prodi, roles, tingkatan, tahapan).
- Semua FK gunakan `constrained()` di Laravel migration.
- Tabel `pengajuan.status` gunakan `$table->enum('status', ['pending','diterima','ditolak','selesai'])->default('pending')`.
