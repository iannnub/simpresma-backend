# SIMPRESMA — Requirements Document
## Sistem Informasi Manajemen Prestasi Mahasiswa
### Fakultas Ilmu Komputer — Universitas Jember

> **STATUS:** FINAL & LOCKED — Semua keputusan di dokumen ini sudah dikonfirmasi oleh Product Owner.
> Dokumen ini menjadi acuan utama seluruh pengerjaan teknis.

---

## 1. Latar Belakang

Fakultas Ilmu Komputer (FIK) UNEJ — 3 program studi:
- **SI** = Sistem Informasi (kode prodi: PSSI)
- **TI** = Teknologi Informasi (kode prodi: PSTI)
- **IF** = Informatika (kode prodi: PSIF)

Proses pencatatan prestasi mahasiswa selama ini dilakukan manual di Excel (4 sheet: Rekap Data, SKS, Nilai, Bidang). SIMPRESMA mendigitalkan seluruh proses ini dari pengajuan hingga konversi SKS & Nilai.

---

## 2. Strategi Pengembangan

| Tahap | Deskripsi |
|---|---|
| **Tahap 1 (sekarang)** | Sistem inti + login dummy/mock (hardcode seed). Fokus alur bisnis. |
| **Tahap 2 (nanti)** | Integrasi SSO CAS UNEJ (`https://sso.unej.ac.id/cas/login`). |

**Aturan:**
- Desain tabel `users` harus kompatibel untuk nanti diisi dari CAS (kolom: NIM/NIP, nama, prodi) tanpa migrasi ulang.
- Backend Laravel sebagai **REST API (JSON)** — tidak ada Blade render.
- Frontend dikerjakan oleh tim terpisah, tidak terikat ke backend.

---

## 3. Role Pengguna

### 3.1 Tabel Role

| Role | Kode | Sifat | Keterangan |
|---|---|---|---|
| Mahasiswa | `mahasiswa` | Fixed dari SSO | Pengaju prestasi |
| Verifikator | `verifikator` | Dinamis, di-assign Wadek | Per prodi (SI/TI/IF), shared inbox |
| Tendik | `tendik` | Fixed list | Dari daftar resmi staf FIK |
| Wadek | `wadek` | Fixed jabatan | Bisa berganti per periode |

### 3.2 Multi-Role
- Satu user **bisa punya lebih dari 1 role** (contoh: dosen sekaligus Verifikator dan Tendik).
- Relasi: **Many-to-Many** (tabel `users` - `roles` - pivot `user_roles`).
- UI menggunakan **Role Switcher di navbar** untuk memilih konteks role yang sedang aktif.
- Backend: Token Sanctum membawa seluruh role aktif user.

### 3.3 Tim Verifikator
- Dikelompokkan **per prodi** (SI / TI / IF).
- Satu dosen bisa menjadi verifikator untuk **lebih dari 1 prodi** (relasi many-to-many: `user_id` - `prodi_id`).
- Di-assign / dicabut oleh **Wadek** melalui panel admin SIMPRESMA.
- **Shared inbox**: Semua anggota tim verifikator satu prodi dapat melihat dan memproses seluruh pengajuan dari prodi tersebut.
- Verifikator hanya melihat pengajuan dari **prodi yang menjadi scope-nya**.

---

## 4. Aturan Bisnis — Tahapan & Tingkatan

### 4.1 Tahapan Lomba (4 tahapan, diisi SEKALI di akhir)

| Kode | Label |
|---|---|
| `mendaftar` | Mendaftar |
| `lolos_tahap_awal` | Lolos Tahap Awal |
| `finalis` | Finalis |
| `pemenang` | Pemenang (Juara 1/2/3/Honorable Mention) |

**Aturan:**
- Diisi **satu kali setelah lomba selesai** — bukan update progresif.
- Tidak ada tahapan "Lolos Pemberkasan" (sudah dihapus dari versi terbaru).
- Juara 1 / 2 / 3 / Honorable Mention adalah **label detail** yang dicatat sebagai teks tambahan di pengajuan — **tidak memengaruhi nilai SKS maupun huruf nilai** di matriks.

### 4.2 Tingkatan Lomba (6 kategori)

| No | Label |
|---|---|
| 1 | Internasional |
| 2 | Nasional Kementerian: Gemastik, LIDM, Satria Data, NUDC, KDMI, MTQMN |
| 3 | Nasional Kementerian: PKM, P2MW, PPK Ormawa, Pilmapres, Peksiminas |
| 4 | Nasional Non Kementerian / Mandiri |
| 5 | Wilayah / Regional / Provinsi |
| 6 | Promahadesa |

---

## 5. Matriks Konversi

### 5.1 Matriks SKS (dari sheet SKS Excel)

| Tingkatan | Mendaftar | Lolos Tahap Awal | Finalis | Pemenang |
|---|---|---|---|---|
| Internasional | - | 2-3 SKS | 4-6 SKS | 8-12 SKS |
| Nasional Kementerian (Gemastik dll) | - | - | 4-6 SKS | 6-9 SKS |
| Nasional Kementerian (PKM dll) | - | 4-6 SKS | 6-9 SKS | 8-12 SKS |
| Nasional Non Kementerian/Mandiri | - | - | 2-3 SKS | 4-6 SKS |
| Wilayah/Regional/Provinsi | - | - | 2-3 SKS | 4-6 SKS |
| Promahadesa | - | 2-3 SKS | - | - |

### 5.2 Matriks Nilai (dari sheet Nilai Excel)

| Tingkatan | Mendaftar | Lolos Tahap Awal | Finalis | Pemenang |
|---|---|---|---|---|
| Internasional | - | AB | A | A |
| Nasional Kementerian (Gemastik dll) | - | - | AB | A |
| Nasional Kementerian (PKM dll) | - | A | A | A |
| Nasional Non Kementerian/Mandiri | - | - | AB | A |
| Wilayah/Regional/Provinsi | - | - | B | AB |
| Promahadesa | - | A | - | - |

### 5.3 Aturan Matriks

- **Tabel database:** 1 tabel terpadu `matriks_konversi` dengan kolom `(tingkatan_id, tahapan_id, min_sks, max_sks, huruf_nilai)`.
- Nilai `-` (dash) = kombinasi tingkatan x tahapan ini **tidak menghasilkan konversi**. Mahasiswa tidak bisa memilih kombinasi ini.
- **Snapshot saat Submit:** Nilai `min_sks`, `max_sks`, `huruf_nilai` dari matriks **disimpan sebagai snapshot** ke tabel `pengajuan` saat mahasiswa submit. Jika Wadek mengubah matriks di kemudian hari, pengajuan lama tidak berubah.
- **Timing snapshot:** Lookup matriks dilakukan saat **server menerima request POST /pengajuan** (bukan saat form dibuka di frontend).
- Verifikator dan Tendik **wajib mengikuti matriks**. Tidak ada override manual nilai SKS oleh verifikator.

---

## 6. Bidang Lomba dan Mata Kuliah

### 6.1 Daftar Bidang Lomba (Universal untuk semua prodi)

| No | Bidang |
|---|---|
| 1 | Kewirausahaan |
| 2 | Graphic Design |
| 3 | Desain Poster |
| 4 | VGK |
| 5 | UI/UX |
| 6 | Programming |
| 7 | Software Development |
| 8 | Karya Tulis Ilmiah |
| 9 | Matematika Komputasi |
| 10 | Non Akademik |
| 11 | Immersive Development |
| 12 | KKN |
| 13 | Embedded dan IOT |
| 14 | Jaringan dan Sekuritas |
| 15 | PPK Ormawa |
| 16 | BMC |
| 17 | Data Science |
| 18 | Data Analytics |

Catatan: Graphic Design, Desain Poster, Non Akademik saat ini diakomodir di "Fasilkom Credit Point" — daftar MK konversinya kosong untuk tahap ini.

### 6.2 Mapping Bidang ke Mata Kuliah

- Daftar mata kuliah berbeda untuk setiap prodi (SI / TI / IF).
- Dikelola sebagai tabel referensi yang bisa diedit oleh Wadek.
- Keputusan isi dari Tim Rekognisi, dieksekusi lewat akun Wadek.
- Bisa direvisi tiap semester.

### 6.3 Aturan Pemilihan Mata Kuliah

- Mahasiswa memilih mata kuliah via **checkbox** (boleh pilih 1 atau lebih).
- **Total SKS yang dipilih harus berada dalam rentang min_sks s/d max_sks** dari matriks.
- Sistem menampilkan rentang SKS yang berlaku sebagai panduan (read-only).

---

## 7. Alur Pengajuan Mahasiswa

### 7.1 Field Form Pengajuan

| Field | Sumber | Tipe | Wajib |
|---|---|---|---|
| Nama | Otomatis dari login | read-only | Ya |
| Program Studi | Otomatis dari login | read-only | Ya |
| NIM | Otomatis dari login | read-only | Ya |
| Nama Tim | Input bebas (kosong jika perorangan) | teks | Tidak |
| No. WhatsApp | Input mahasiswa | teks | Ya |
| Nama Lomba | Input mahasiswa | teks | Ya |
| Bidang Lomba | Pilih dari daftar | select | Ya |
| Tingkatan Lomba | Pilih dari 6 kategori | select | Ya |
| Tahapan Saat Ini | Pilih dari 4 tahapan | select | Ya |
| Detail Juara | Input jika tahapan = Pemenang | teks | Tidak |
| Rentang SKS | Otomatis dari matriks | display read-only | - |
| Huruf Nilai | Otomatis dari matriks | display read-only | - |
| Mata Kuliah Pilihan | Checkbox dari daftar (Bidang x Prodi) | checkbox | Ya |
| Sertifikat / Bukti Prestasi | Link/URL dari mahasiswa | url | Ya |
| Surat Tugas Mahasiswa | Ada/Tidak + link jika Ada | boolean + url | Ya |
| Surat Tugas Dosen Pembimbing | Ada/Tidak + link jika Ada | boolean + url | Ya |
| Poster Lomba | URL/link | url | Tidak |
| Link Sosmed Lomba | URL/link | url | Tidak |
| Keterangan | Teks bebas | teks | Tidak |

### 7.2 Aturan Nama Tim
- Tim 5 orang: tiap anggota submit pengajuan **masing-masing secara individual**.
- Nama tim diketik manual oleh tiap anggota.
- Sistem **tidak menggabungkan** pengajuan berdasarkan nama tim.

### 7.3 Setelah Submit
- Status otomatis jadi `pending`.
- Snapshot matriks (SKS & Nilai) disimpan ke pengajuan saat POST diterima server.

### 7.4 Pengajuan Ditolak
- Berstatus final, tidak ada revisi untuk entri yang sama.
- Mahasiswa **bebas membuat pengajuan baru** (tidak diblokir).
- Validasi: Tidak boleh ada 2 pengajuan berstatus `pending` atau `diterima` untuk mahasiswa yang sama dengan lomba yang sama.

---

## 8. Alur Verifikator

1. Login -> dashboard: pengajuan berstatus `pending` dari prodi scope-nya.
2. Periksa kelengkapan dan kevalidan data.
3. Keputusan:
   - **Terima** -> status `diterima`. SKS & Nilai ikut matriks, tidak bisa override.
   - **Tolak** -> status `ditolak`. **Wajib isi feedback.**
4. Proses per pengajuan individu (bukan per tim).
5. Status `diterima` -> otomatis muncul di dashboard Tendik.

---

## 9. Alur Tendik

1. Login -> dashboard: pengajuan berstatus `diterima`.
2. Lihat daftar mata kuliah yang dipilih mahasiswa.
3. Input **huruf nilai per mata kuliah** secara manual satu per satu. Nilai yang diinput **wajib persis sama dengan `snapshot_huruf_nilai` dari matriks** — tidak boleh berbeda. Sistem memvalidasi server-side: jika nilai yang diinput tidak sama dengan snapshot, request ditolak (422).
4. Input **link Surat Keterangan Konversi** (opsional — hanya jika lomba menerbitkan SK).
5. Finalisasi -> status `selesai`.
6. Mahasiswa langsung bisa lihat hasil konversi.
7. Input ke SIA dilakukan manual Tendik di luar SIMPRESMA — tidak ada push/sync otomatis.

---

## 10. Alur Wadek

1. Login -> akses panel admin penuh.
2. Kelola **Matriks Konversi**: ubah min_sks, max_sks, huruf_nilai per kombinasi Tingkatan x Tahapan.
3. Kelola **Tim Verifikator per Prodi**: assign/cabut dosen untuk SI/TI/IF.
4. Kelola **Mapping Bidang -> Mata Kuliah**: CRUD MK pilihan per bidang per prodi.
5. Lihat dashboard statistik.

---

## 11. Status Pengajuan

```
pending -> diterima -> selesai
        -> ditolak (final)
```

| Status | Diubah oleh | Keterangan |
|---|---|---|
| `pending` | Sistem | Otomatis saat mahasiswa submit |
| `diterima` | Verifikator | Pengajuan valid, lanjut ke Tendik |
| `ditolak` | Verifikator | Final. Wajib ada feedback. |
| `selesai` | Tendik | Konversi selesai. Mahasiswa bisa lihat hasil. |

---

## 12. Dashboard Statistik (Semua Role)

- Persentase jumlah pengajuan per prodi (SI / TI / IF).
- Rumus: `COUNT(pengajuan) GROUP BY prodi / total pengajuan x 100%`.
- Tidak butuh data total mahasiswa aktif & tidak butuh API eksternal.
- Dapat dilihat semua role.

---

## 13. Halaman Direktori Verifikator

- Read-only, bisa diakses semua role.
- Menampilkan daftar dosen verifikator per prodi (SI / TI / IF).

---

## 14. Aturan Dokumen — Semua Berbasis Link (Tidak Ada Upload File)

**Keputusan Final:** Tidak ada upload file sama sekali di sistem. Semua bukti/dokumen disimpan sebagai URL/link yang diinput manual.

| Dokumen | Diisi oleh | Wajib | Keterangan |
|---|---|---|---|
| Sertifikat / Bukti Prestasi | Mahasiswa | Ya | Link/URL ke file (Google Drive, dll) |
| Surat Tugas Mahasiswa | Mahasiswa | Jika status Ada | Ada/Tidak. Jika Ada, wajib isi link. |
| Surat Tugas Dosen Pembimbing | Mahasiswa | Jika status Ada | Ada/Tidak. Jika Ada, wajib isi link. |
| Surat Keterangan Konversi | Tendik | Tidak | Link/URL (opsional, saat finalisasi) |

---

## 15. Batasan Implementasi Tahap 1

- Login dummy/mock (seed manual + Sanctum token).
- Tidak ada integrasi SSO CAS.
- Tidak ada push/sync otomatis ke SIA.
- Tim Rekognisi tidak punya role/akun di SIMPRESMA.
- Urutan pengerjaan: Database -> Auth -> Seed -> Mahasiswa -> Verifikator -> Tendik -> Wadek -> Dashboard.

---

## 16. Keputusan Teknis Final

| Keputusan | Hasil |
|---|---|
| Struktur matriks di DB | 1 tabel terpadu `matriks_konversi` |
| Juara 1/2/3/HM vs matriks | Hanya label, tidak memengaruhi SKS/Nilai |
| Timing snapshot matriks | Saat server terima POST submit |
| Nilai konversi per MK | Tendik input manual per MK, WAJIB sama persis dengan snapshot matriks (validasi server-side) |
| Pengajuan ditolak | Tidak diblokir, boleh buat pengajuan baru |
| UI Multi-Role | Role Switcher di navbar |
| Verifikator multi-prodi | Didukung (many-to-many pivot) |
| Struktur bidang lomba | Bidang universal, mapping MK per prodi |
| Dokumen/bukti | Semua berupa link/URL — tidak ada upload file |
| Surat tugas | Boolean Ada/Tidak + link URL jika Ada |
| SK Konversi Tendik | Link/URL opsional (bukan upload file) |
| Status akhir Tendik | `selesai` |
| Dashboard sumber data | Internal DB (COUNT GROUP BY prodi) |
