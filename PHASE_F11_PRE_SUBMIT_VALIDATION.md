# Phase F11: Enhancement — Pre-Submit Validation & Mandatory Documents

**Versi**: 1.0
**Tujuan**: Mencegah kesalahan input data prestasi dan memastikan kelengkapan dokumen pendukung.

---

## 1. Requirement Utama

### A. Modal Konfirmasi Sebelum Submit
1. **Trigger**: Saat tombol "Submit" ditekan pada form input prestasi.
2. **Behavior**: Munculkan Modal/Alert konfirmasi.
3. **Isi Modal**: 
   - Peringatan: "Pastikan data yang Anda masukkan sudah benar. Setelah dikirim, data tidak dapat diubah kembali."
   - Ringkasan data (Opsional: Tampilkan Nama Lomba & Bidang Lomba sebagai pengingat).
4. **Tombol**: "Batal" (kembali ke form) dan "Ya, Kirim Sekarang" (lanjut ke proses submit).

### B. Validasi Dokumen Wajib (Mandatory)
1. **Fields**: `Link Sertifikat` dan `Link SK` (tugas/pembimbing).
2. **Behavior**: 
   - Ubah status field menjadi `required` pada validasi frontend (React/Zod/Formik).
   - Pastikan backend (`Request` validation di Laravel) juga menerapkan `'required'` pada field tersebut.
   - Berikan tanda bintang merah (*) pada label field di form.

---

## 2. Instruksi Teknis

### Backend (Laravel)
1. Update `FormRequest` pada controller atau request class yang menangani penyimpanan data (`StorePengajuanRequest`).
2. Pastikan rule validasi untuk sertifikat dan SK dokumen adalah: `required|url`.

### Frontend (React + TypeScript)
1. Update skema validasi (Zod schema atau Yup) di file form input:
   - `sertifikat_url: z.string().url().nonempty()`
   - `sk_url: z.string().url().nonempty()`
2. Tambahkan komponen `<ConfirmationDialog>` atau `window.confirm` sebelum fungsi `submit` dipanggil.
3. Pastikan UI memberikan feedback visual jika field wajib belum diisi saat tombol submit ditekan.

---

## 3. Daftar File yang Perlu Diperiksa
- `app/Http/Requests/StorePengajuanRequest.php` (atau file Request terkait)
- `src/components/forms/PrestasiForm.tsx` (atau file form mahasiswa)
- `src/lib/schemas/prestasiSchema.ts` (jika ada file skema)
