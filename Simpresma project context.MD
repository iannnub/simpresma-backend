# SIMPRESMA — Sistem Informasi Manajemen Prestasi Mahasiswa
## Project Context Document (untuk dibaca & dipahami, BUKAN untuk langsung dieksekusi)

> **INSTRUKSI PENTING UNTUK AI AGENT (Antigravity):**
> Dokumen ini adalah **konteks proyek**, bukan perintah untuk mulai coding.
> Tugasmu sekarang hanya **membaca dan memahami** gambaran besar sistem ini —
> latar belakang, tujuan, aturan bisnis, dan batasan teknis.
> **Jangan membuat file, struktur folder, migration, atau kode apapun dulu.**
> Setelah dokumen ini, akan menyusul dokumen **alur (flow) detail** dari tiap
> fitur/proses. Baru setelah alur detail itu diberikan dan dikonfirmasi,
> eksekusi/pembuatan kode boleh dimulai — dan itupun bertahap sesuai instruksi
> yang akan diberikan satu per satu, bukan sekaligus membangun semuanya.

---

## 1. Latar Belakang

Fakultas Ilmu Komputer (FIK) — 3 program studi: **Sistem Informasi (SI)**,
**Teknologi Informasi (TI)**, dan **Informatika (IF)** — memiliki proses
pencatatan prestasi mahasiswa (mengikuti lomba/kompetisi) yang selama ini
dilakukan **manual menggunakan Excel**, terdiri dari 4 sheet:

1. **Rekap Data** — data pengajuan mahasiswa (identitas, lomba, tingkatan,
   tahapan, bukti, bidang, hasil SKS & nilai, mata kuliah pilihan, status,
   verifikator)
2. **SKS** — matriks konversi: tingkatan lomba × tahapan → besaran SKS
3. **Nilai** — matriks konversi: tingkatan lomba × tahapan → huruf nilai
4. **Bidang** — daftar bidang lomba (UI/UX, Programming, BMC, dst) → daftar
   mata kuliah yang bisa dikonversi, **berbeda-beda per program studi**

Sistem baru (SIMPRESMA) bertujuan mendigitalkan seluruh proses ini: dari
mahasiswa mengajukan prestasi, verifikasi berjenjang, sampai tercatat —
menggantikan pencatatan manual di Excel.

---

## 2. Strategi Pengembangan (PENTING)

Pengembangan dilakukan **bertahap**:

- **Tahap 1 — Bangun sistem inti dulu**, menggunakan **login dummy/mock**
  (akun tes dengan role yang di-hardcode/seed: Mahasiswa, Verifikator, Tendik,
  Wadek). Fokus di alur bisnis: pengajuan, verifikasi, konversi.
- **Tahap 2 — Integrasi SSO/CAS** dilakukan **belakangan**, setelah sistem
  inti stabil dan sudah disetujui. Login dummy akan diganti dengan login CAS
  asli tanpa mengubah struktur data/role yang sudah ada — karena struktur
  user & role sejak awal didesain mengikuti bentuk data yang nanti akan
  diberikan CAS (lihat bagian 6).

Jangan mendesain sistem seolah SSO sudah terintegrasi dari awal — tapi
desain tabel `users` dan `roles` harus tetap kompatibel untuk nanti diisi
dari CAS.

---

## 3. Tech Stack

- **Backend:** Laravel
- **Database:** MySQL (dikelola sendiri, terpisah dari sistem akademik kampus)
- Frontend/detail arsitektur lain: **belum difinalisasi**, menyusul di dokumen
  alur berikutnya.

---

## 4. Role Pengguna

| Role | Sifat | Catatan |
|---|---|---|
| **Mahasiswa** | Fixed, ditentukan identitas dari SSO nanti | Pengaju prestasi |
| **Tim Verifikator** | **Dinamis** — bisa lebih dari satu orang, siapa saja (kaprodi, dosen prodi, dll), di-assign/dicabut lewat panel admin internal SIMPRESMA | Bukan berasal dari data SSO — murni dikelola di dalam sistem |
| **Tim Tendik** | Fixed list, sesuai daftar resmi (referensi: ilkom.unej.ac.id/staf-dan-dosen/) | Tidak dipilih manual, tapi tetap dikelola sebagai tabel internal (bisa di-import admin) |
| **Wadek** | Ditentukan kampus, tapi jabatan bisa berganti per periode | Sebaiknya tetap tabel internal yang bisa di-update admin, bukan hardcode |

Satu user bisa punya lebih dari satu role sekaligus (contoh: dosen sebagai
Tendik sekaligus ditunjuk jadi Verifikator) → gunakan struktur **many-to-many**
(`users` – `roles` – pivot `user_roles`), bukan satu kolom role tunggal.

---

## 5. Aturan Bisnis Inti — Konversi Prestasi

### 5.1 Tahapan Lomba
Urutan status pengajuan seorang mahasiswa dalam satu lomba:

1. **Mendaftar**
2. **Lolos Pemberkasan** — *dicatat di sistem, TAPI TIDAK menghasilkan
   konversi SKS*
3. **Lolos Tahap Awal**
4. **Finalis**
5. **Pemenang** (Juara 1/2/3/Honorable Mention)

### 5.2 Tingkatan Lomba (kategori)
1. Internasional
2. Nasional Kementerian: Gemastik, LIDM, Satria Data, NUDC, KDMI, MTQMN
3. Nasional Kementerian: PKM, P2MW, PPK Ormawa, Pilmapres, Peksiminas
4. Nasional Non Kementerian/Mandiri
5. Wilayah/Regional/Provinsi
6. Promahadesa

### 5.3 Matriks Konversi (Tingkatan × Tahapan → SKS & Nilai)
Setiap kombinasi tingkatan lomba dan tahapan yang dicapai menghasilkan
**rentang SKS berbeda** dan **huruf nilai berbeda** (data lengkap sudah
tersedia dari sheet `SKS` dan `Nilai` Excel lama — akan disertakan sebagai
data referensi/seed terpisah, bukan di-hardcode di kode).

### 5.4 Bidang Lomba → Mata Kuliah
Setiap **bidang lomba** (misal: UI/UX, Programming, Software Development,
BMC, Karya Tulis Ilmiah, dll) memiliki **daftar mata kuliah pilihan
konversi yang berbeda untuk tiap program studi** (SI/TI/IF). Mahasiswa
memilih bidang lomba yang diikuti → sistem menampilkan daftar mata kuliah
yang relevan sesuai prodi mahasiswa tersebut → mata kuliah dipilih dari
daftar tersebut (kombinasi SKS harus sesuai rentang dari matriks konversi).

Data mapping bidang → mata kuliah ini adalah **kebijakan internal Tim
Rekognisi** (bukan data resmi kurikulum SIA), jadi harus dikelola sebagai
tabel referensi yang bisa diedit admin — bisa direvisi tiap semester.

### 5.5 Sifat Verifikasi Akhir
Ketika pengajuan disetujui oleh role terakhir (verifikator), sistem
**mencatat status "terverifikasi"** — tapi **TIDAK otomatis push/update**
ke Sistem Informasi Akademik (SIA) kampus. Input akhir ke SIA tetap
dilakukan **manual** oleh penerima role verifikasi akhir, di luar sistem
SIMPRESMA. SIMPRESMA hanya jadi alat bantu pencatatan & rekomendasi
otomatis (bidang → mata kuliah, tingkatan/tahapan → SKS & nilai).

---

## 6. Rencana Integrasi SSO (untuk Tahap 2 — belum dikerjakan sekarang)

- Kampus: **Universitas Jember (UNEJ)**
- Protokol: **CAS**, endpoint `https://sso.unej.ac.id/cas/login`
  (bagian dari ekosistem SISTER UNEJ)
- Mahasiswa login pakai NIM, dosen/staff pakai username masing-masing
- Yang akan diminta ke pihak kampus (Puskom/BTSI) nanti:
  1. Registrasi/whitelist service URL SIMPRESMA di server CAS
  2. Attribute release minimal: NIM/NIP, Nama, Program Studi, dan jika
     memungkinkan tipe akun/jabatan (mahasiswa/dosen/tendik)
- Selama Tahap 1, seluruh kebutuhan ini **disimulasikan** lewat data dummy —
  desain tabel `users` harus punya kolom yang sesuai (NIM/NIP, nama, prodi,
  role) agar nanti tinggal diisi dari response CAS tanpa migrasi ulang.

---

## 7. Status Dokumen Ini

Ini adalah **dokumen konteks awal**. Alur kerja (flow) detail tiap fitur
— mulai dari alur pengajuan mahasiswa, alur verifikasi berjenjang, sampai
wireframe/urutan halaman — akan **menyusul dikirim terpisah** oleh product
owner. **Tunggu dokumen alur tersebut sebelum mulai membangun apapun.**