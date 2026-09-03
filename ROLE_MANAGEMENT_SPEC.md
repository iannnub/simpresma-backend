# Role Management System Specification

**Versi**: 1.0  
**Tanggal**: 3 September 2026

---

## 1. Overview
Sistem internal untuk kelola role karena SSO tidak kirim data jabatan.
- **Admin**: Role baru untuk manajemen user.
- **Multi-Role**: Satu user bisa banyak role (Verifikator + Dosen).
- **Audit Trail**: Catat perubahan di `role_history`.

---

## 2. Database Schema

### 2.1 Table `users`
- Ubah kolom `role` dari string ke **JSON**.
- Format data: `["mahasiswa", "verifikator"]`.

### 2.2 Table `role_history`
- Kolom: `id`, `user_id`, `action` (assign/revoke), `role_name`, `changed_by` (admin_id), `notes`, `created_at`.

---

## 3. Backend Implementation

### 3.1 Migration
1. Update `users`: Backup role lama -> ubah tipe ke JSON -> migrate data ke array JSON.
2. Create `role_history`: Tabel audit trail.

### 3.2 Logic
- **Model User**: Tambah helper `hasRole()`, `assignRole()`, `revokeRole()`.
- **Middleware**: Update `RoleMiddleware` agar support pengecekan di array JSON.
- **AdminController**: 
  - `index()`: List user + filter.
  - `assignRole()`: Tambah role ke array + catat history.
  - `revokeRole()`: Hapus role dari array + catat history.
  - `roleHistory()`: Get log per user.

---

## 4. Frontend Implementation

### 4.1 Page: `src/app/(admin)/kelola-role/page.tsx`
- Tabel list user + Badge role multi-warna.
- Tombol "Tambah Role" (Dialog).
- Icon "History" (Dialog riwayat).
- Fitur Revoke: Klik tanda `x` pada badge role.

---

## 5. Verification Plan

### 5.1 Test Flow
1. [ ] **Seed Admin**: Jalankan `AdminSeeder` -> Login admin@simpresma.unej.ac.id.
2. [ ] **Multi-Role**: Cari user Dosen -> Tambah role "Verifikator" -> Cek user punya 2 role.
3. [ ] **Toggle/Revoke**: Klik `x` pada role "Dosen" -> Pastikan role terhapus tapi role "Verifikator" tetap ada.
4. [ ] **Audit Trail**: Klik icon History -> Cek log (siapa yang tambah/hapus, kapan, dan catatannya).
5. [ ] **Validation**: Coba hapus role terakhir user -> Harus error (user minimal punya 1 role).
6. [ ] **Security**: Login Mahasiswa -> Tembak API `/admin/users` -> Harus 403 Forbidden.

---

## 6. Implementation Notes
- Gunakan JSON database function Laravel: `whereJsonContains`.
- Jangan hapus user, gunakan toggle role jika jabatan selesai (Wadek lama -> revoke role wadek).
