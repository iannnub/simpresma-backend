# Phase F10: Post-MVP Enhancement — Export & Telegram Notification

**Versi**: 1.0  
**Tanggal**: 3 September 2026  
**Status**: Ready for Implementation

---

## 1. Overview
Fitur:
1. **Data Export Engine**: Excel/CSV untuk laporan.
2. **Telegram Bot Notification**: Notif real-time status pengajuan.

---

## 2. Feature 1: Data Export (Excel/CSV)

### 2.1 Requirement
- **Wadek**: Semua prodi.
- **Tendik**: Status "Diterima" & "Selesai".
- **Format**: .xlsx & .csv.

### 2.2 Kolom (17 Kolom)
No, NIM, Nama, Prodi, Nama Lomba, Bidang, Tingkatan, Capaian, Tgl Pengajuan, Status, SKS, MK, Nilai, Link Sertifikat, Link SK, Verifikator, Tgl Verifikasi.

### 2.3 Backend
- Install: `composer require maatwebsite/excel`
- Class: `app/Exports/PengajuanExport.php` (Gunakan FromCollection, WithHeadings, WithMapping)
- Routes: `/api/wadek/export` & `/api/tendik/export`
- Controller: `export()` di `WadekController` & `TendikController`.

### 2.4 Frontend
- Component: `src/components/shared/ExportButton.tsx` (Dropdown format)
- Integrasi: Dashboard Wadek & Tendik.

---

## 3. Feature 2: Telegram Bot Notification

### 3.1 Requirement
- **Mhs**: Notif "Diterima", "Ditolak", "Selesai".
- **Verifikator**: Notif pengajuan baru (status pending).
- **Tendik**: Notif pengajuan siap finalisasi (status diterima).

### 3.2 Setup
- Bot: `@simpresma_unej_bot`
- Token: `8860236314:AAGBuT-B334-gmW0bxNaB4L2v3oc2Si0h1s`
- Env: `TELEGRAM_BOT_TOKEN`, `TELEGRAM_BOT_USERNAME`, `APP_URL=http://localhost:8000`

### 3.3 Database
- Migration: Tambah `telegram_chat_id` di tabel `users`.

### 3.4 Logic
- Service: `app/Services/TelegramService.php` (Method `sendMessage`)
- Event: `app/Events/PengajuanStatusChanged.php`
- Listener: `app/Listeners/SendTelegramNotification.php`
- Webhook: `app/Http/Controllers/TelegramWebhookController.php` (Handle `/start` -> reply Chat ID).

### 3.5 Frontend
- Component: `src/components/shared/TelegramConnect.tsx` (Input Chat ID & Status).
- Page: `src/app/(shared)/profile/page.tsx` (Integrasi koneksi).

---

## 4. Verification Plan

### 4.1 Export
- [ ] Login Wadek -> Klik Ekspor Excel -> File terunduh -> Cek 17 kolom & data.
- [ ] Login Wadek -> Klik Ekspor CSV -> File terunduh -> Cek delimiter.
- [ ] Login Tendik -> Ekspor -> Pastikan hanya data status diterima/selesai.

### 4.2 Telegram
- [ ] Mhs buka Bot -> Klik /start -> Dapat Chat ID.
- [ ] Mhs input Chat ID di Profil -> Klik Hubungkan -> Bot kirim konfirmasi sukses.
- [ ] Verifikator approve pengajuan -> Mhs dapat notif "Diterima".
- [ ] Verifikator tolak pengajuan -> Mhs dapat notif "Ditolak" + feedback.
- [ ] Mhs ajukan baru -> Verifikator prodi terkait dapat notif pengajuan baru.
- [ ] Verifikator approve -> Tendik dapat notif siap finalisasi.

---