# SIMPRESMA — Alur (Flow) Final per Role

> **Untuk AI Agent (Antigravity):** Dokumen ini adalah **lanjutan** dari
> `project_context.md`. Ini berisi alur detail tiap role hasil klarifikasi
> dengan product owner. Setelah membaca dokumen ini (beserta `structure.md`
> dan `tech.md`), kamu boleh mulai **mengusulkan rencana teknis** — tapi
> **tunggu konfirmasi eksplisit** sebelum mulai menulis kode/migration
> sungguhan. Semua data pada tahap ini menggunakan **login dummy/mock**
> (lihat konteks di dokumen sebelumnya), belum SSO asli.

---

## 1. Alur Mahasiswa

1. Login (dummy dulu, nanti SSO) → masuk dashboard SIMPRESMA.
2. Klik "Ajukan Prestasi" → isi form pengajuan:
   - **Nama** — otomatis dari identitas login (read-only)
   - **Program Studi** — otomatis dari identitas login (read-only)
   - **Nama Tim** — teks bebas, diisi sendiri (kosongkan jika perorangan). Kalau
     tim beranggota 5 orang, tiap orang submit pengajuan masing-masing dengan
     nama tim yang sama diketik manual (tidak divalidasi ketat/tidak digabung
     otomatis oleh sistem)
   - **No. WhatsApp**
   - **Nama Lomba**
   - **Bidang Lomba** — pilih dari daftar bidang yang tersedia untuk prodinya
   - **Tingkatan Lomba** — pilih salah satu dari 6 kategori:
     Internasional / Nasional Kementerian (Gemastik, LIDM, Satria Data, NUDC,
     KDMI, MTQMN) / Nasional Kementerian (PKM, P2MW, PPK Ormawa, Pilmapres,
     Peksiminas) / Nasional Non Kementerian-Mandiri / Wilayah-Regional-Provinsi /
     Promahadesa
   - **Tahapan Saat Ini** — pilih salah satu: Mendaftar / Lolos Tahap Awal /
     Finalis / Pemenang (Juara 1/2/3/Honorable Mention). Diisi **sekali di
     akhir**, setelah lomba benar-benar selesai — bukan diupdate progresif
   - Sistem otomatis menampilkan (read-only, hasil lookup matriks):
     **Rentang SKS** dan **Huruf Nilai** sesuai kombinasi Tingkatan + Tahapan
     — nilai ini **disnapshot** ke pengajuan saat submit
   - **Mata Kuliah Pilihan** — muncul daftar checkbox mata kuliah sesuai
     kombinasi Bidang + Prodi mahasiswa. Mahasiswa pilih satu/lebih mata
     kuliah sampai total SKS-nya sesuai rentang yang muncul di atas
   - Upload: **Sertifikat/bukti tahapan saat ini** (wajib), **Surat Tugas
     Mahasiswa** (ada/tidak), **Surat Tugas Dosen Pembimbing** (ada/tidak),
     **Poster Lomba** (link), **Link Sosmed Lomba**
   - **Keterangan** — teks bebas opsional
3. Submit → status otomatis **"Pending"**.
4. Mahasiswa bisa lihat status pengajuannya sendiri: **Pending → Diterima →
   Selesai**, atau **Pending → Ditolak**.
5. Kalau **Ditolak** → mahasiswa bisa lihat **feedback** dari verifikator.
   Pengajuan bersifat **final, tidak bisa direvisi/submit ulang** untuk
   entri yang sama.
6. Setelah status **"Selesai"** (sudah diproses Tendik) → mahasiswa bisa
   lihat: mata kuliah yang dikonversi + nilai yang didapat.
7. Ada halaman terpisah (read-only, semua role bisa akses): **Direktori Tim
   Verifikator per Prodi** — daftar dosen yang jadi verifikator untuk SI,
   TI, dan Informatika.
8. Dashboard menampilkan **persentase pengajuan per prodi** (lihat bagian 5).

---

## 2. Alur Verifikator

1. Login → masuk dashboard sesuai **scope prodinya** (verifikator SI hanya
   melihat pengajuan dari mahasiswa SI; berlaku sama untuk TI & IF).
2. Tim verifikator per prodi terdiri dari beberapa dosen (contoh: SI = dosen
   A, B, C). **Shared inbox**: semua pengajuan dari prodi itu terlihat oleh
   seluruh anggota tim, siapa saja boleh memproses duluan.
3. Untuk tiap pengajuan yang masuk, verifikator mengecek kelengkapan &
   kevalidan data (bukti, surat tugas, dll), lalu:
   - **Terima** → status jadi "Diterima". SKS & Nilai **wajib mengikuti
     angka dari matriks apa adanya — tidak boleh diubah/override manual**.
   - **Tolak** → status jadi "Ditolak", **wajib isi feedback** alasan
     penolakan (feedback ini yang akan dilihat mahasiswa). **Final, tidak
     ada revisi.**
4. Approve/reject dilakukan **per pengajuan individu (per orang)** — kalau
   satu tim ada 5 pengajuan terpisah, hasilnya boleh berbeda-beda per orang
   (misal 4 diterima, 1 ditolak).
5. Begitu status "Diterima", pengajuan otomatis muncul di dashboard role
   **Tendik** untuk diproses lebih lanjut.

---

## 3. Alur Tendik

1. Login → masuk dashboard, melihat daftar pengajuan berstatus **"Diterima"**
   (hasil approve dari Verifikator).
2. Untuk tiap pengajuan: Tendik melihat mata kuliah yang dipilih mahasiswa
   (hasil pilihan dari daftar Bidang → Mata Kuliah), lalu memberikan
   **Nilai** — nilai ini **wajib mengacu ketat ke huruf nilai dari matriks
   (sheet Nilai)**, tidak bebas input sembarangan.
3. Upload **Surat Keterangan** — **opsional**, hanya diupload kalau memang
   lomba tersebut memberikan SK. Kalau tidak ada SK, mata kuliah tetap
   dikonversi tanpa lampiran SK.
4. Setelah diproses, status pengajuan berubah menjadi **"Selesai"** — beda
   dari status "Diterima" biasa, supaya jelas mana yang sudah tuntas
   diproses Tendik dan mana yang baru di-ACC verifikator tapi belum diproses.
5. Mahasiswa yang bersangkutan langsung bisa lihat mata kuliah yang
   dikonversi + nilai yang didapat di dashboard-nya.
6. **Tendik adalah role terakhir dalam alur SIMPRESMA.** Setelah status
   "Selesai", input akhir ke SIA (Sistem Informasi Akademik kampus) tetap
   dilakukan **manual oleh Tendik di luar sistem SIMPRESMA** — proses ini
   **tidak otomatis mengubah data di SIA**. SIMPRESMA hanya jadi alat bantu
   pencatatan & rekomendasi.

---

## 4. Alur Wadek

1. Login → masuk dashboard dengan akses penuh untuk mengelola:
   - **Matriks Konversi SKS & Nilai** — bisa ubah rentang SKS/huruf Nilai
     untuk tiap kombinasi Tingkatan Lomba × Tahapan (contoh: tahun ini
     "Lolos Tahap Awal - Nasional" = 4-6 SKS, tahun depan Wadek ubah jadi
     2-3 SKS). Perubahan ini berlaku untuk pengajuan baru ke depannya —
     pengajuan lama tidak berubah (data lama sudah disnapshot).
   - **Tim Verifikator per Prodi** — assign/cabut dosen yang jadi anggota
     tim verifikator untuk masing-masing prodi (SI/TI/IF), bisa lebih dari
     satu orang per prodi.
   - **Mapping Bidang → Mata Kuliah** — Wadek yang menginput perubahan ke
     sistem, tapi **keputusan isinya berasal dari Tim Rekognisi** (Tim
     Rekognisi tidak punya akun/role sendiri di SIMPRESMA — kerja mereka di
     luar sistem, hasilnya dieksekusi/diinput lewat akun Wadek).
2. Dashboard Wadek juga menampilkan statistik persentase pengajuan per
   prodi (sama seperti yang dilihat role lain).

---

## 5. Dashboard Bersama (semua role)

- Menampilkan **persentase pengajuan yang sudah masuk, dipecah per prodi**
  (Sistem Informasi, Teknologi Informasi, Informatika) — misal 60% SI, 30%
  TI, 10% Informatika.
- Dihitung **langsung dari data internal** (`COUNT(pengajuan) GROUP BY
  prodi`, dibagi total seluruh pengajuan yang sudah masuk) — **bukan** dari
  data total mahasiswa aktif per prodi, jadi **tidak butuh API/data
  eksternal apapun** untuk fitur ini.

---

## 6. Status Pengajuan (ringkasan)

Urutan status yang mungkin dialami satu pengajuan:

`Pending` → `Diterima` (oleh Verifikator) → `Selesai` (oleh Tendik)

atau

`Pending` → `Ditolak` (oleh Verifikator, wajib ada feedback) — **final, tidak
ada revisi**

---

## 7. Status Dokumen Ini

Semua poin yang sebelumnya berstatus "belum diputuskan" sudah final:
- Sumber data dashboard persentase → dari data internal, tidak perlu API eksternal.
- Nama status akhir setelah diproses Tendik → **"Selesai"**.

Dokumen ini sudah sinkron dengan `project_context.md`, `structure.md`,
`tech.md`, dan `simpresma_schema.dbml`.