# SIMPRESMA — Sistem Informasi Manajemen Prestasi Mahasiswa
## Project Context Document (untuk dibaca & dipahami, BUKAN untuk langsung dieksekusi)

> **INSTRUKSI PENTING UNTUK AI AGENT (Antigravity):**
> Dokumen ini adalah **konteks proyek**, bukan perintah untuk mulai coding.
> Tugasmu sekarang hanya **membaca dan memahami** gambaran besar sistem ini —
> latar belakang, tujuan, aturan bisnis, dan batasan teknis.
> **Jangan membuat file, struktur folder, migration, atau kode apapun dulu.**
> Setelah dokumen ini, akan menyusul dokumen **alur (flow) detail**,
> **structure.md** (skema database), dan **tech.md** (stack & konvensi).
> Baru setelah semua dokumen itu diberikan dan dikonfirmasi, eksekusi/
> pembuatan kode boleh dimulai — dan itupun bertahap sesuai instruksi yang
> akan diberikan satu per satu, bukan sekaligus membangun semuanya.

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

Pengerjaan modul dilakukan **satu per satu secara berurutan** (bukan
sekaligus semua fitur): database → autentikasi dummy → seed data referensi →
modul Mahasiswa → modul Verifikator → modul Tendik → modul Wadek →
dashboard statistik → hardening & testing → baru integrasi SSO.

---

## 3. Tech Stack

- **Backend:** Laravel, dibangun sebagai **REST API (JSON)**, bukan
  render halaman (Blade) — karena frontend dikerjakan **tim terpisah**
  dengan stack yang belum ditentukan tim backend. Backend tidak boleh
  terikat ke pilihan frontend apapun; kontraknya cukup lewat HTTP + JSON.
- **Database:** MySQL (dikelola sendiri, terpisah dari sistem akademik kampus)
- **Autentikasi API:** Laravel Sanctum (token-based). Tahap dummy: seed user
  + token manual. Tahap SSO: setelah validasi CAS server-side sukses,
  backend keluarkan Sanctum token seperti biasa.
- Detail struktur folder, konvensi, dan kontrak API — lihat `tech.md`.

---

## 4. Role Pengguna

| Role | Sifat | Catatan |
|---|---|---|
| **Mahasiswa** | Fixed, ditentukan identitas dari SSO nanti | Pengaju prestasi |
| **Tim Verifikator** | **Dinamis**, di-assign/dicabut oleh **Wadek** lewat panel admin internal SIMPRESMA. **Dikelompokkan per prodi** (contoh: dosen A, B, C untuk SI; dosen D, E, F untuk TI, dst) — bisa lebih dari satu orang per prodi. Di dalam scope prodinya, siapa saja anggota tim itu boleh memproses pengajuan mahasiswa prodi tersebut (shared inbox per-prodi) | Bukan berasal dari data SSO — murni dikelola di dalam sistem |
| **Tim Tendik** | Fixed list, sesuai daftar resmi (referensi: ilkom.unej.ac.id/staf-dan-dosen/) | Tidak dipilih manual, tapi tetap dikelola sebagai tabel internal (bisa di-import admin) |
| **Wadek** | Ditentukan kampus, tapi jabatan bisa berganti per periode | Sebaiknya tetap tabel internal yang bisa di-update admin, bukan hardcode. Selain kelola matriks & tim verifikator, Wadek juga menginput perubahan **mapping Bidang → Mata Kuliah** — tapi keputusan isinya berasal dari **Tim Rekognisi**, yang **tidak punya akun/role sendiri** di SIMPRESMA (kerja mereka di luar sistem, hasilnya dieksekusi lewat akun Wadek) |

Satu user bisa punya lebih dari satu role sekaligus (contoh: dosen sebagai
Tendik sekaligus ditunjuk jadi Verifikator) → gunakan struktur **many-to-many**
(`users` – `roles` – pivot `user_roles`), bukan satu kolom role tunggal.

---

## 5. Aturan Bisnis Inti — Konversi Prestasi

### 5.1 Tahapan Lomba
Urutan status pengajuan seorang mahasiswa dalam satu lomba — **4 tahapan
saja**, tanpa "Lolos Pemberkasan":

1. **Mendaftar**
2. **Lolos Tahap Awal**
3. **Finalis**
4. **Pemenang** (Juara 1/2/3/Honorable Mention)

Tahapan ini diisi **sekali di akhir**, setelah lomba selesai — tidak ada
update progresif per tahapan di tengah jalan.

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
data referensi/seed terpisah, bukan di-hardcode di kode). Angka ini
**disimpan sebagai snapshot** pada tiap pengajuan saat submit — bukan
live-lookup — supaya kalau matriks diubah Wadek di kemudian hari,
pengajuan lama tidak ikut berubah.

Verifikator dan Tendik **wajib strict mengikuti angka dari matriks apa
adanya** — tidak boleh override/input bebas.

### 5.4 Bidang Lomba → Mata Kuliah
Setiap **bidang lomba** (misal: UI/UX, Programming, Software Development,
BMC, Karya Tulis Ilmiah, dll) memiliki **daftar mata kuliah pilihan
konversi yang berbeda untuk tiap program studi** (SI/TI/IF). Mahasiswa
memilih bidang lomba yang diikuti → sistem menampilkan daftar mata kuliah
yang relevan sesuai prodi mahasiswa tersebut → mata kuliah dipilih dari
daftar tersebut (kombinasi SKS harus sesuai rentang dari matriks konversi).

Data mapping bidang → mata kuliah ini adalah **kebijakan internal Tim
Rekognisi** (bukan data resmi kurikulum SIA), jadi harus dikelola sebagai
tabel referensi yang bisa diedit lewat akun Wadek — bisa direvisi tiap
semester.

### 5.5 Sifat Verifikasi & Konversi
Alurnya dua tahap, dengan role berbeda:

- **Verifikator** memeriksa kelengkapan pengajuan → terima (status
  "Diterima") atau tolak (status "Ditolak", wajib feedback, **final, tidak
  bisa direvisi**).
- **Tendik** memproses pengajuan yang sudah "Diterima" → memberi nilai
  (strict ikut matriks) → status jadi **"Selesai"**.

**Role terakhir yang melakukan input manual ke SIA (Sistem Informasi
Akademik) kampus adalah Tendik, bukan Verifikator.** Proses ini **tidak
otomatis push/update** ke SIA — input akhir tetap manual oleh Tendik di
luar sistem SIMPRESMA. SIMPRESMA hanya jadi alat bantu pencatatan &
rekomendasi otomatis (bidang → mata kuliah, tingkatan/tahapan → SKS & nilai).

### 5.6 Dashboard Statistik
Semua role bisa melihat **persentase pengajuan per prodi** — dihitung dari
proporsi pengajuan yang sudah masuk dibagi per prodi (SI/TI/IF), langsung
dari data pengajuan internal (`COUNT(pengajuan) GROUP BY prodi`). **Tidak
perlu data eksternal** (bukan dari total mahasiswa aktif per prodi).

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

Ini adalah **dokumen konteks proyek versi terbaru** (revisi dari versi awal
— sudah sinkron dengan hasil klarifikasi alur & keputusan teknis terbaru).
Dokumen pendamping yang sudah ada: `alur_final.md` (alur detail per role),
`structure.md` (skema database), `tech.md` (stack & konvensi),
`simpresma_schema.dbml` (kode database siap import ke dbdiagram.io).