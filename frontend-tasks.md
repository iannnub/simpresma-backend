
# SIMPRESMA Frontend — Tasks Document
## Phase-Based Development Checklist

> **STATUS:** AKTIF — Update status task saat pengerjaan dimulai/selesai.
> Referensi: `frontend-requirements.md`, `frontend-structure.md`.
>
> **Aturan Pengerjaan:**
> - [ ] = Belum dikerjakan
> - [/] = Sedang dikerjakan
> - [x] = Selesai & terverifikasi
>
> **JANGAN lanjut ke task berikutnya sebelum task sebelumnya selesai dan dikonfirmasi.**

---

## PHASE F0 — Dokumen Spesifikasi

- [x] **F0.1** Buat `frontend-requirements.md` (spec lengkap)
- [x] **F0.2** Buat `frontend-structure.md` (arsitektur & folder structure)
- [x] **F0.3** Buat `frontend-tasks.md` (checklist ini)

---

## PHASE F1 — Setup Project & Dependencies

### F1.1 — Inisialisasi Project

- [ ] Buat project Vite + React + TypeScript: `npm create vite@latest simpresma-frontend -- --template react-ts`
- [ ] Install dependencies dasar:
  ```bash
  cd simpresma-frontend
  npm install
  npm install react-router-dom@6 @tanstack/react-query@5 axios@1 zustand@4
  npm install react-hook-form@7 zod@3 @hookform/resolvers
  npm install date-fns@3 clsx tailwind-merge
  npm install lucide-react sonner
  ```
- [ ] Setup Tailwind CSS:
  ```bash
  npm install -D tailwindcss@3 postcss autoprefixer
  npx tailwindcss init -p
  ```
- [ ] Setup Shadcn/ui:
  ```bash
  npx shadcn-ui@latest init
  ```
  (Pilih: TypeScript, Tailwind CSS, src directory, import alias @/*)
- [ ] Verifikasi `npm run dev` berjalan normal (http://localhost:5173)

### F1.2 — Konfigurasi TypeScript

- [ ] Update `tsconfig.json`:
  ```json
  {
    "compilerOptions": {
      "target": "ES2020",
      "useDefineForClassFields": true,
      "lib": ["ES2020", "DOM", "DOM.Iterable"],
      "module": "ESNext",
      "skipLibCheck": true,
      "moduleResolution": "bundler",
      "allowImportingTsExtensions": true,
      "resolveJsonModule": true,
      "isolatedModules": true,
      "noEmit": true,
      "jsx": "react-jsx",
      "strict": true,
      "noUnusedLocals": true,
      "noUnusedParameters": true,
      "noFallthroughCasesInSwitch": true,
      "baseUrl": ".",
      "paths": {
        "@/*": ["src/*"]
      }
    },
    "include": ["src"],
    "references": [{ "path": "./tsconfig.node.json" }]
  }
  ```
- [ ] Update `vite.config.ts` untuk path alias:
  ```typescript
  import path from 'path';
  export default defineConfig({
    resolve: {
      alias: {
        '@': path.resolve(__dirname, './src'),
      },
    },
  });
  ```

### F1.3 — Konfigurasi Tailwind CSS

- [ ] Update `tailwind.config.ts`:
  ```typescript
  export default {
    darkMode: ['class'],
    content: [
      './index.html',
      './src/**/*.{ts,tsx}',
    ],
    theme: {
      extend: {
        colors: {
          border: 'hsl(var(--border))',
          input: 'hsl(var(--input))',
          ring: 'hsl(var(--ring))',
          background: 'hsl(var(--background))',
          foreground: 'hsl(var(--foreground))',
          primary: {
            DEFAULT: 'hsl(var(--primary))',
            foreground: 'hsl(var(--primary-foreground))',
          },
          // ... (Shadcn default colors)
        },
      },
    },
    plugins: [require('tailwindcss-animate')],
  };
  ```
- [ ] Update `src/styles/globals.css` dengan Tailwind imports + CSS variables

### F1.4 — Install Shadcn/ui Components (Base)

- [ ] Install komponen UI yang sering dipakai:
  ```bash
  npx shadcn-ui@latest add button
  npx shadcn-ui@latest add input
  npx shadcn-ui@latest add label
  npx shadcn-ui@latest add card
  npx shadcn-ui@latest add dialog
  npx shadcn-ui@latest add dropdown-menu
  npx shadcn-ui@latest add select
  npx shadcn-ui@latest add textarea
  npx shadcn-ui@latest add checkbox
  npx shadcn-ui@latest add badge
  npx shadcn-ui@latest add table
  npx shadcn-ui@latest add alert
  npx shadcn-ui@latest add skeleton
  npx shadcn-ui@latest add tabs
  npx shadcn-ui@latest add toast
  ```
- [ ] Verifikasi semua komponen muncul di `src/components/ui/`

### F1.5 — Setup Environment Variables

- [ ] Buat `.env.example`:
  ```bash
  VITE_API_BASE_URL=http://localhost:8000/api
  VITE_APP_NAME=SIMPRESMA
  ```
- [ ] Buat `.env.local` (copy dari `.env.example`)
- [ ] Update `.gitignore`:
  ```
  .env.local
  .env.*.local
  ```

### F1.6 — Struktur Folder Awal

- [ ] Buat folder structure sesuai `frontend-structure.md`:
  ```bash
  mkdir -p src/{app,components,lib,stores,types,styles,config}
  mkdir -p src/app/{auth,mahasiswa,verifikator,tendik,wadek,shared}
  mkdir -p src/components/{layouts,forms,tables,charts,shared}
  mkdir -p src/lib/{api,hooks,utils,schemas}
  ```
- [ ] Hapus boilerplate tidak terpakai:
  ```bash
  rm src/App.css
  rm src/index.css (diganti globals.css)
  ```

### F1.7 — ESLint & Prettier

- [ ] Install ESLint & Prettier:
  ```bash
  npm install -D eslint prettier eslint-config-prettier
  npm install -D @typescript-eslint/eslint-plugin @typescript-eslint/parser
  ```
- [ ] Buat `.eslintrc.json`:
  ```json
  {
    "extends": [
      "eslint:recommended",
      "plugin:@typescript-eslint/recommended",
      "plugin:react-hooks/recommended",
      "prettier"
    ]
  }
  ```
- [ ] Buat `.prettierrc`:
  ```json
  {
    "semi": true,
    "singleQuote": true,
    "tabWidth": 2,
    "trailingComma": "es5"
  }
  ```

---

## PHASE F2 — Authentication & Routing

### F2.1 — Setup React Router

- [ ] Buat `src/router.tsx`:
  - Setup `BrowserRouter`
  - Define route structure (public + protected)
  - Placeholder routes untuk semua role
- [ ] Buat `src/App.tsx`:
  - Import `router`
  - Render `<RouterProvider router={router} />`
  - Wrap dengan `<QueryClientProvider>` (React Query)
  - Wrap dengan `<Toaster />` (Sonner)
- [ ] Update `src/main.tsx`:
  - Import `globals.css`
  - Render `<App />`

### F2.2 — API Client Setup

- [ ] Buat `src/lib/api/client.ts`:
  - Setup Axios instance dengan `baseURL` dari env
  - Request interceptor: inject token dari localStorage
  - Response interceptor: handle 401 (logout + redirect)
- [ ] Buat `src/lib/api/types.ts`:
  - Type `ApiResponse<T>`
  - Type `PaginatedResponse<T>`
  - Type `ApiError`

### F2.3 — Type Definitions

- [ ] Buat `src/types/user.types.ts`:
  ```typescript
  export type UserRole = 'mahasiswa' | 'verifikator' | 'tendik' | 'wadek';
  
  export interface User {
    id: number;
    nim_nip: string | null;
    nama: string;
    email: string;
    no_whatsapp: string | null;
    prodi: Prodi | null;
    roles: UserRole[];
  }
  
  export interface Prodi {
    id: number;
    kode: string;
    singkatan: string;
    nama: string;
  }
  ```
- [ ] Buat `src/types/api.types.ts`:
  ```typescript
  export interface ApiResponse<T> {
    success: boolean;
    message: string;
    data: T;
  }
  
  export interface PaginatedData<T> {
    items: T[];
    meta: {
      current_page: number;
      last_page: number;
      per_page: number;
      total: number;
    };
  }
  ```

### F2.4 — Auth Store (Zustand)

- [ ] Buat `src/stores/authStore.ts`:
  - State: `user`, `token`, `isAuthenticated`
  - Actions: `login(user, token)`, `logout()`, `updateUser(user)`
  - Persist user data ke localStorage
- [ ] Buat `src/stores/roleStore.ts`:
  - State: `currentRole`, `availableRoles`
  - Actions: `setCurrentRole(role)`, `setAvailableRoles(roles)`

### F2.5 — Auth API & Hooks

- [ ] Buat `src/lib/api/auth.api.ts`:
  ```typescript
  export const authApi = {
    login: async (credentials: LoginRequest) => { ... },
    logout: async () => { ... },
    me: async () => { ... },
  };
  ```
- [ ] Buat `src/lib/hooks/useAuth.ts`:
  - Hook `useAuth()` yang expose `login`, `logout`, `user`, `isAuthenticated`
  - Integrasi dengan `authStore`

### F2.6 — Login Page

- [ ] Buat `src/app/(auth)/login/page.tsx`:
  - Form: email + password
  - Validasi dengan Zod schema
  - Submit → call `authApi.login()`
  - Success → store token + user → redirect ke dashboard sesuai role
  - Error → tampilkan toast error
- [ ] Buat `src/lib/schemas/auth.schema.ts`:
  ```typescript
  export const loginSchema = z.object({
    email: z.string().email('Email tidak valid'),
    password: z.string().min(1, 'Password wajib diisi'),
  });
  ```
- [ ] Test login dengan akun dummy: `mhs.si@test.com` / `password`

### F2.7 — Protected Route Component

- [ ] Buat `src/components/layouts/ProtectedRoute.tsx`:
  - Cek `isAuthenticated` dari `authStore`
  - Jika tidak auth → redirect ke `/login`
  - Jika auth → render children
- [ ] Buat `src/components/layouts/RoleRoute.tsx`:
  - Cek user punya role yang dibutuhkan
  - Jika tidak punya → tampilkan 403 Forbidden page
  - Jika punya → render children

### F2.8 — Testing Auth Flow

- [ ] Test login → dashboard redirect (mahasiswa)
- [ ] Test logout → kembali ke login
- [ ] Test akses protected route tanpa login → redirect login
- [ ] Test akses route dengan role salah → 403 page

---

## PHASE F3 — Layout & Navigation

### F3.1 — App Layout Component

- [ ] Buat `src/components/layouts/AppLayout.tsx`:
  - Desktop (≥1024px): Sidebar + Header + Main content
  - Mobile (<1024px): Header + Main content + Bottom nav
  - Responsive behavior dengan Tailwind breakpoints

### F3.2 — Sidebar (Desktop)

- [ ] Buat `src/components/layouts/Sidebar.tsx`:
  - Fixed width 256px
  - Logo SIMPRESMA di atas
  - Navigation menu dari `navigationConfig`
  - Active state untuk current route
  - Role switcher dropdown (jika multi-role)
  - Logout button di bawah

### F3.3 — Mobile Navigation (Bottom Nav)

- [ ] Buat `src/components/layouts/MobileNav.tsx`:
  - Fixed bottom, height 64px
  - Icon + label untuk 4-5 menu utama
  - Active state untuk current route
  - Hide pada desktop (≥1024px)

### F3.4 — Header Component

- [ ] Buat `src/components/layouts/Header.tsx`:
  - Breadcrumb navigation (opsional)
  - User profile dropdown (nama + email + logout)
  - Hamburger menu button (mobile only)
  - Height 64px, sticky top

### F3.5 — Navigation Configuration

- [ ] Buat `src/config/navigation.config.ts`:
  - Define menu per role (mahasiswa, verifikator, tendik, wadek)
  - Icon dari Lucide React
  - Badge untuk notification count (opsional, bisa null)
- [ ] Buat `src/config/routes.config.ts`:
  - Define semua route path sebagai constants
  - Type-safe route helpers

### F3.6 — Role Switcher Component

- [ ] Buat `src/components/shared/RoleSwitcher.tsx`:
  - Dropdown menu untuk user dengan multiple roles
  - Current role di-highlight
  - Switch role → navigate ke dashboard role baru
  - Update `roleStore.currentRole`

### F3.7 — Testing Layout

- [ ] Test responsive behavior (desktop ↔ mobile)
- [ ] Test navigation link active state
- [ ] Test role switcher (pakai akun `multi@test.com`)
- [ ] Test logout dari sidebar/header

---

## PHASE F4 — Modul Mahasiswa

### F4.1 — Dashboard Mahasiswa

- [ ] Buat `src/app/(mahasiswa)/dashboard/page.tsx`:
  - Stats cards: Total pengajuan, Pending, Diterima, Selesai
  - Chart: Distribusi status (pie chart)
  - Tabel: 5 pengajuan terbaru
- [ ] Buat `src/components/shared/StatsCard.tsx`:
  - Reusable card untuk stats (icon + title + value + trend)
- [ ] Buat API hook `src/lib/hooks/useDashboard.ts`:
  - Fetch statistik dari `/api/dashboard/statistik`

### F4.2 — List Pengajuan Mahasiswa

- [ ] Buat `src/app/(mahasiswa)/pengajuan/page.tsx`:
  - Desktop: Data table dengan kolom (ID, Nama Lomba, Tingkatan, Tahapan, Status, Tanggal, Aksi)
  - Mobile: Card list
  - Pagination (15 items per page)
  - Filter by status (dropdown)
- [ ] Buat `src/components/tables/PengajuanTable.tsx`:
  - Reusable table component
  - Sort by column (sortable)
  - Action column: Link ke detail
- [ ] Buat `src/components/shared/StatusBadge.tsx`:
  - Badge dengan warna sesuai status:
    - pending → yellow
    - diterima → blue
    - ditolak → red
    - selesai → green

### F4.3 — Detail Pengajuan Mahasiswa

- [ ] Buat `src/app/(mahasiswa)/pengajuan/[id]/page.tsx`:
  - Readonly view semua data pengajuan
  - Section: Info Lomba
  - Section: Dokumen (link clickable, buka new tab)
  - Section: Mata Kuliah Dipilih (list + total SKS)
  - Section: Status & Feedback (jika ditolak)
  - Section: Hasil Konversi (jika selesai: nilai per MK)

### F4.4 — Reference Data Hooks

- [ ] Buat `src/lib/api/ref.api.ts`:
  ```typescript
  export const refApi = {
    getProdi: async () => { ... },
    getTingkatan: async () => { ... },
    getTahapan: async () => { ... },
    getBidang: async () => { ... },
    getMatriks: async (tingkatanId, tahapanId) => { ... },
    getMataKuliah: async (bidangId, prodiId) => { ... },
  };
  ```
- [ ] Buat `src/lib/hooks/useRefData.ts`:
  - `useProdi()`, `useTingkatan()`, `useTahapan()`, `useBidang()`
  - Cache dengan React Query (staleTime: 10 minutes)

### F4.5 — Form Pengajuan (Multi-Step) — Step 1

- [ ] Buat `src/components/forms/PengajuanForm/index.tsx`:
  - State: `currentStep` (1-4), `formData`
  - Step indicator component
  - Navigation: Back/Next buttons
- [ ] Buat `src/components/forms/PengajuanForm/StepIndicator.tsx`:
  - Visual progress (4 circles: 1 → 2 → 3 → 4)
  - Completed steps: green checkmark
  - Current step: blue, bold
- [ ] Buat `src/components/forms/PengajuanForm/StepLombaInfo.tsx`:
  - Fields: nama_lomba, nama_tim, no_whatsapp, bidang, tingkatan, tahapan, detail_juara
  - Real-time SKS calculator:
    - useEffect: saat tingkatan + tahapan berubah → fetch matriks
    - Display: Rentang SKS (min-max) + Huruf Nilai
    - Alert jika kombinasi tidak valid (min_sks NULL)
  - Validation: React Hook Form + Zod
  - Button: Lanjut (disabled jika invalid)

### F4.6 — Form Pengajuan — Step 2

- [ ] Buat `src/components/forms/PengajuanForm/StepDokumen.tsx`:
  - Fields: link_sertifikat (URL), status_surat_tugas_mahasiswa (checkbox), link_surat_tugas_mahasiswa (URL, conditional), status_surat_tugas_dosen (checkbox), link_surat_tugas_dosen (URL, conditional), link_poster (URL), link_sosmed (URL), keterangan (textarea)
  - Validation: URL format, conditional required
  - Helper text: "📎 Upload file ke Google Drive, paste link di sini"
  - Buttons: Kembali, Lanjut

### F4.7 — Form Pengajuan — Step 3

- [ ] Buat `src/components/forms/PengajuanForm/StepMataKuliah.tsx`:
  - Fetch MK: `useMataKuliah(bidangId, prodiId)` dari step 1
  - Display: List checkbox MK (nama + SKS badge)
  - Real-time SKS counter:
    - `totalSKS = sum(selectedMK.map(mk => mk.sks))`
    - Display: "Total SKS: X / Y SKS"
    - Badge: Valid ✓ (green) atau Invalid ✗ (red)
  - Validation: totalSKS harus dalam rentang min-max dari step 1
  - Buttons: Kembali, Lanjut

### F4.8 — Form Pengajuan — Step 4

- [ ] Buat `src/components/forms/PengajuanForm/StepPreview.tsx`:
  - Display semua data (readonly)
  - Section breakdown: Lomba, Dokumen, Mata Kuliah
  - Summary: Total SKS, Prediksi Nilai
  - Warning: "Data tidak dapat diubah setelah submit"
  - Buttons: Kembali, Submit Pengajuan (loading state)
- [ ] Submit handler:
  - `mahasiswaApi.submitPengajuan(payload)`
  - Success: toast + redirect ke `/mahasiswa/pengajuan`
  - Error: toast error (422 → display validation errors)

### F4.9 — Pengajuan API & Hooks

- [ ] Buat `src/lib/api/mahasiswa.api.ts`:
  ```typescript
  export const mahasiswaApi = {
    getPengajuanList: async (filters) => { ... },
    getPengajuanDetail: async (id) => { ... },
    submitPengajuan: async (data) => { ... },
  };
  ```
- [ ] Buat `src/lib/hooks/usePengajuan.ts`:
  - `usePengajuanList(filters)` (React Query)
  - `usePengajuanDetail(id)` (React Query)
  - `useSubmitPengajuan()` (useMutation)

### F4.10 — Validation Schemas

- [ ] Buat `src/lib/schemas/pengajuan.schema.ts`:
  - Schema per step (stepLombaInfoSchema, stepDokumenSchema, stepMataKuliahSchema)
  - Full schema untuk submit (gabungan semua step)
  - Custom validators: URL format, SKS range, conditional required

### F4.11 — Testing Modul Mahasiswa

- [ ] Test dashboard: stats + chart load
- [ ] Test list pengajuan: pagination, filter, sort
- [ ] Test detail pengajuan: semua data tampil
- [ ] Test form pengajuan:
  - Step 1: SKS calculator update saat tingkatan/tahapan berubah
  - Step 1: Alert jika kombinasi invalid
  - Step 2: Conditional required (surat tugas)
  - Step 2: URL validation
  - Step 3: Real-time SKS counter
  - Step 3: Validation SKS range
  - Step 4: Preview benar
  - Submit: success + redirect
  - Submit: error 422 → toast validation errors

---

## PHASE F5 — Modul Verifikator

### F5.1 — Dashboard Verifikator

- [ ] Buat `src/app/(verifikator)/dashboard/page.tsx`:
  - Stats cards: Pending (scope prodi), Total Diterima, Total Ditolak
  - Chart: Status breakdown (pie chart)
  - Tabel: 10 pengajuan pending terbaru

### F5.2 — List Pengajuan Pending (Verifikator)

- [ ] Buat `src/app/(verifikator)/pengajuan/page.tsx`:
  - Filter: otomatis scope prodi verifikator (backend handle)
  - Desktop: Table (ID, Mahasiswa, Lomba, Tingkatan, Tahapan, Tanggal, Aksi)
  - Mobile: Cards
  - Pagination
  - Action: Link ke detail

### F5.3 — Detail Pengajuan (Verifikator)

- [ ] Buat `src/app/(verifikator)/pengajuan/[id]/page.tsx`:
  - Display semua data pengajuan (readonly)
  - Section: Dokumen (link preview button)
  - Action buttons (bottom):
    - Button "Terima" (green) → confirmation dialog
    - Button "Tolak" (red) → input feedback dialog

### F5.4 — Terima Pengajuan Dialog

- [ ] Buat confirmation dialog:
  - Title: "Konfirmasi Terima Pengajuan"
  - Description: "SKS dan nilai akan dikunci sesuai matriks saat ini"
  - Buttons: Batal, Ya Terima
  - Submit: `verifikatorApi.terimaPengajuan(id)`
  - Success: toast + invalidate query + navigate back

### F5.5 — Tolak Pengajuan Dialog

- [ ] Buat input dialog:
  - Title: "Tolak Pengajuan"
  - Field: Textarea `feedback_verifikator` (required, min 10 char)
  - Validation: Zod schema
  - Buttons: Batal, Tolak dengan Feedback
  - Submit: `verifikatorApi.tolakPengajuan(id, { feedback_verifikator })`
  - Success: toast + invalidate query + navigate back

### F5.6 — Verifikator API & Hooks

- [ ] Buat `src/lib/api/verifikator.api.ts`:
  ```typescript
  export const verifikatorApi = {
    getPengajuanList: async (filters) => { ... },
    getPengajuanDetail: async (id) => { ... },
    terimaPengajuan: async (id) => { ... },
    tolakPengajuan: async (id, data) => { ... },
  };
  ```
- [ ] Buat hooks di `src/lib/hooks/usePengajuan.ts` (extend):
  - `useVerifikatorPengajuanList()`
  - `useTerimaPengajuan()` (useMutation)
  - `useTolakPengajuan()` (useMutation)

### F5.7 — Testing Modul Verifikator

- [ ] Test dashboard: stats load
- [ ] Test list: hanya tampil pengajuan prodi scope verifikator
- [ ] Test detail: semua data + dokumen link tampil
- [ ] Test terima:
  - Dialog confirmation muncul
  - Submit success → toast + navigate back
- [ ] Test tolak:
  - Dialog input muncul
  - Validation: feedback wajib diisi min 10 char
  - Submit success → toast + navigate back
- [ ] Test 403: verifikator SI tidak bisa akses pengajuan TI

---

## PHASE F6 — Modul Tendik

### F6.1 — Dashboard Tendik

- [ ] Buat `src/app/(tendik)/dashboard/page.tsx`:
  - Stats cards: Total Diterima (belum diproses), Total Selesai (sudah diproses)
  - Chart: Distribusi per prodi
  - Tabel: 10 pengajuan diterima terbaru

### F6.2 — List Pengajuan Diterima (Tendik)

- [ ] Buat `src/app/(tendik)/pengajuan/page.tsx`:
  - Filter: semua prodi (tidak ada scope)
  - Desktop: Table (ID, Mahasiswa, Lomba, Prodi, Tanggal Diterima, Aksi)
  - Mobile: Cards
  - Pagination
  - Action: Link ke detail + finalisasi

### F6.3 — Detail Pengajuan (Tendik)

- [ ] Buat `src/app/(tendik)/pengajuan/[id]/page.tsx`:
  - Display data pengajuan (readonly)
  - Section: Mata Kuliah Dipilih (list + SKS snapshot)
  - Alert: "Nilai yang diinput WAJIB sama dengan nilai matriks: {snapshot_huruf_nilai}"
  - Form: Finalisasi konversi

### F6.4 — Finalisasi Form (CRITICAL!)

- [ ] Buat `src/components/forms/FinalisasiForm.tsx`:
  - Table: Mata Kuliah | SKS | Huruf Nilai (dropdown)
  - Dropdown options: HANYA `snapshot_huruf_nilai` (single option!)
  - Validation: setiap MK nilai harus = snapshot (client-side + server-side)
  - Field: link_sk_konversi (URL, optional)
  - Button: Finalisasi Konversi (disabled jika nilai tidak valid)
  - Submit: `tendikApi.finalisasiPengajuan(id, data)`
  - Success: toast + invalidate query + navigate back
  - Error 422: toast error validation

### F6.5 — Tendik API & Hooks

- [ ] Buat `src/lib/api/tendik.api.ts`:
  ```typescript
  export const tendikApi = {
    getPengajuanList: async (filters) => { ... },
    getPengajuanDetail: async (id) => { ... },
    finalisasiPengajuan: async (id, data) => { ... },
  };
  ```
- [ ] Buat hooks di `src/lib/hooks/usePengajuan.ts` (extend):
  - `useTendikPengajuanList()`
  - `useFinalisasiPengajuan()` (useMutation)

### F6.6 — Validation Schema

- [ ] Buat `src/lib/schemas/finalisasi.schema.ts`:
  ```typescript
  export const finalisasiSchema = z.object({
    nilai_per_mk: z.array(
      z.object({
        mk_id: z.number(),
        huruf_nilai: z.string().min(1),
      })
    ).min(1),
    link_sk_konversi: z.string().url().optional().or(z.literal('')),
  });
  ```

### F6.7 — Testing Modul Tendik

- [ ] Test dashboard: stats load
- [ ] Test list: tampil semua prodi (tidak ada filter scope)
- [ ] Test detail: mata kuliah list + snapshot nilai tampil
- [ ] Test finalisasi form:
  - Dropdown hanya ada 1 opsi (snapshot nilai)
  - Client-side validation: nilai harus = snapshot
  - Submit dengan nilai benar → success
  - Submit dengan nilai salah → error 422 (tidak mungkin jika dropdown benar)
  - Link SK opsional: bisa kosong, bisa diisi URL
  - Link SK invalid URL → error validation

---

## PHASE F7 — Modul Wadek

### F7.1 — Dashboard Wadek

- [ ] Buat `src/app/(wadek)/dashboard/page.tsx`:
  - Stats cards: Total Pengajuan (all prodi), Total Verifikator Aktif, Total Matriks Valid
  - Chart: Distribusi per prodi + per status
  - Quick actions: Link ke Kelola Matriks, Kelola Verifikator

### F7.2 — Kelola Matriks Konversi

- [ ] Buat `src/app/(wadek)/matriks/page.tsx`:
  - Table: Tingkatan | Tahapan | Min SKS | Max SKS | Huruf Nilai | Terakhir Diubah | Aksi
  - Inline edit atau edit dialog (pilih salah satu)
  - Action: Button Edit (icon) → open dialog
- [ ] Buat `src/components/forms/MatriksForm.tsx`:
  - Fields: min_sks (number), max_sks (number), huruf_nilai (text)
  - Validation: max_sks >= min_sks
  - Submit: `wadekApi.updateMatriks(id, data)`
  - Success: toast + invalidate query

### F7.3 — Kelola Tim Verifikator

- [ ] Buat `src/app/(wadek)/verifikator/page.tsx`:
  - Tabs: SI | TI | IF (per prodi)
  - Per tab:
    - Table: Nama Dosen | NIP | Status (Aktif/Nonaktif) | Aksi
    - Button: + Assign Verifikator (open dialog)
    - Action per row: Button Cabut (jika aktif)
- [ ] Buat `src/components/forms/VerifikatorForm.tsx` (Assign):
  - Field: Dropdown pilih dosen (fetch dari API dosen list)
  - Submit: `wadekApi.assignVerifikator({ user_id, prodi_id })`
  - Success: toast + invalidate query
- [ ] Cabut verifikator:
  - Confirmation dialog
  - Submit: `wadekApi.cabutVerifikator(id)`
  - Success: toast + invalidate query

### F7.4 — Kelola Mapping Bidang-MK

- [ ] Buat `src/app/(wadek)/bidang-mk/page.tsx`:
  - Filter: Dropdown Bidang, Dropdown Prodi
  - Table: Bidang | Mata Kuliah | Prodi | Aksi
  - Button: + Tambah Mapping (open dialog)
  - Action per row: Button Hapus (confirmation)
- [ ] Buat `src/components/forms/BidangMKForm.tsx`:
  - Field: Dropdown Bidang, Dropdown Mata Kuliah (filtered by prodi)
  - Submit: `wadekApi.addBidangMK({ bidang_id, mata_kuliah_id })`
  - Success: toast + invalidate query
- [ ] Hapus mapping:
  - Confirmation dialog
  - Submit: `wadekApi.deleteBidangMK(id)`
  - Success: toast + invalidate query

### F7.5 — Wadek API & Hooks

- [ ] Buat `src/lib/api/wadek.api.ts`:
  ```typescript
  export const wadekApi = {
    // Matriks
    getMatriksList: async () => { ... },
    updateMatriks: async (id, data) => { ... },
    
    // Verifikator
    getVerifikatorList: async () => { ... },
    getDosenList: async () => { ... },
    assignVerifikator: async (data) => { ... },
    cabutVerifikator: async (id) => { ... },
    
    // Bidang-MK
    getBidangMKList: async (filters) => { ... },
    addBidangMK: async (data) => { ... },
    deleteBidangMK: async (id) => { ... },
  };
  ```
- [ ] Buat hooks:
  - `useMatriksList()`
  - `useUpdateMatriks()` (useMutation)
  - `useVerifikatorList()`
  - `useAssignVerifikator()` (useMutation)
  - `useCabutVerifikator()` (useMutation)
  - `useBidangMKList()`
  - `useAddBidangMK()` (useMutation)
  - `useDeleteBidangMK()` (useMutation)

### F7.6 — Validation Schemas

- [ ] Buat `src/lib/schemas/matriks.schema.ts`:
  ```typescript
  export const matriksSchema = z.object({
    min_sks: z.number().min(0).nullable(),
    max_sks: z.number().min(0).nullable(),
    huruf_nilai: z.string().max(5).nullable(),
  }).refine(data => !data.max_sks || !data.min_sks || data.max_sks >= data.min_sks, {
    message: 'Max SKS harus >= Min SKS',
  });
  ```
- [ ] Buat `src/lib/schemas/verifikator.schema.ts`:
  ```typescript
  export const assignVerifikatorSchema = z.object({
    user_id: z.number(),
    prodi_id: z.number(),
  });
  ```

### F7.7 — Testing Modul Wadek

- [ ] Test dashboard: stats + quick actions
- [ ] Test matriks:
  - Edit matriks → update success
  - Validation: max_sks < min_sks → error
- [ ] Test verifikator:
  - Tabs per prodi bekerja
  - Assign verifikator → success
  - Cabut verifikator → success
  - Verifikator muncul di direktori shared
- [ ] Test bidang-MK:
  - Filter by bidang/prodi bekerja
  - Tambah mapping → success
  - Hapus mapping → success

---

## PHASE F8 — Dashboard & Charts

### F8.1 — Dashboard Statistik (Shared)

- [ ] Buat `src/app/(shared)/direktori-verifikator/page.tsx`:
  - Read-only page
  - Grouping per prodi: SI, TI, IF
  - Display: Nama Dosen, NIP, Status (Aktif)

### F8.2 — Chart Components

- [ ] Install Recharts: `npm install recharts`
- [ ] Buat `src/components/charts/StatistikChart.tsx`:
  - Pie chart: distribusi pengajuan per prodi
  - Data dari `/api/dashboard/statistik`
  - Legend: SI (60%), TI (30%), IF (10%)
  - Responsive: width 100%
- [ ] Buat `src/components/charts/StatusBreakdownChart.tsx`:
  - Bar chart: breakdown per status (pending, diterima, ditolak, selesai)
  - Grouped by prodi (optional)
  - Responsive

### F8.3 — Shared API & Hooks

- [ ] Buat `src/lib/api/shared.api.ts`:
  ```typescript
  export const sharedApi = {
    getStatistik: async () => { ... },
    getDirektoriVerifikator: async () => { ... },
  };
  ```
- [ ] Buat `src/lib/hooks/useDashboard.ts`:
  - `useStatistik()` (React Query, cache 5 minutes)
  - `useDirektoriVerifikator()` (React Query, cache 10 minutes)

### F8.4 — Testing Dashboard

- [ ] Test statistik: chart render dengan data benar
- [ ] Test direktori: verifikator dikelompokkan per prodi
- [ ] Test semua role bisa akses dashboard & direktori

---

## PHASE F9 — Polish, Testing & Optimization

### F9.1 — Error Handling Global

- [ ] Buat `src/components/shared/ErrorBoundary.tsx`:
  - Catch component errors
  - Display fallback UI: "Oops! Terjadi kesalahan."
  - Button: Refresh Halaman
- [ ] Wrap `<App>` dengan `<ErrorBoundary>`

### F9.2 — Loading States

- [ ] Buat `src/components/shared/LoadingSpinner.tsx`:
  - Reusable loading spinner (Lucide `Loader2` icon)
  - Center screen variant
  - Inline variant
- [ ] Tambahkan skeleton loaders:
  - Table: `<TableSkeleton />` (rows of Skeleton)
  - Card: `<CardSkeleton />`
  - Stats: `<StatsCardSkeleton />`
- [ ] Gunakan Suspense + lazy loading untuk heavy pages:
  - Form pengajuan mahasiswa
  - Matriks wadek (heavy table)

### F9.3 — Empty States

- [ ] Buat `src/components/shared/EmptyState.tsx`:
  - Reusable component (icon + title + description + action button)
  - Variants: "Belum ada pengajuan", "Tidak ada data", "404 Not Found"
- [ ] Gunakan di:
  - List pengajuan kosong
  - Detail pengajuan 404
  - Matriks kosong (tidak mungkin, tapi safety)

### F9.4 — Confirmation Dialogs

- [ ] Buat `src/components/shared/ConfirmDialog.tsx`:
  - Reusable confirmation dialog (title + description + cancel/confirm buttons)
  - Variants: destructive (red), default (blue)
  - Promise-based API untuk async confirmation

### F9.5 — Toast Notifications

- [ ] Standardize toast messages:
  - Success: `toast.success(message, { description })`
  - Error: `toast.error(message, { description })`
  - Warning: `toast.warning(message)`
  - Info: `toast.info(message)`
- [ ] Add toast untuk semua mutations:
  - Submit pengajuan
  - Terima/tolak (verifikator)
  - Finalisasi (tendik)
  - Update matriks (wadek)
  - CRUD verifikator/bidang-MK

### F9.6 — Responsive Testing

- [ ] Test di Chrome DevTools responsive mode:
  - Mobile: 375px, 414px
  - Tablet: 768px, 1024px
  - Desktop: 1280px, 1920px
- [ ] Cek breakpoints:
  - Sidebar → bottom nav (mobile)
  - Table → cards (mobile)
  - Form 2-column → 1-column (mobile)
  - Dashboard charts stack vertically (mobile)

### F9.7 — Accessibility Audit

- [ ] Install Axe DevTools extension
- [ ] Audit semua pages:
  - Login page
  - Dashboard per role
  - Form pengajuan
  - List pengajuan
  - Detail pengajuan
- [ ] Fix issues:
  - Missing ARIA labels
  - Color contrast < 4.5:1
  - Keyboard navigation broken
  - Focus trap di modals

### F9.8 — Performance Optimization

- [ ] Lazy load heavy components:
  ```typescript
  const PengajuanFormPage = lazy(() => import('@/app/(mahasiswa)/pengajuan/new/page'));
  ```
- [ ] Add React Query staleTime:
  - Reference data: 10 minutes
  - Dashboard stats: 5 minutes
  - List data: 2 minutes
- [ ] Add React.memo untuk component yang sering re-render:
  - StatsCard
  - StatusBadge
  - DataTable rows
- [ ] Image optimization:
  - Convert PNG → WebP
  - Use `loading="lazy"` untuk images

### F9.9 — Code Quality

- [ ] Run ESLint: `npm run lint`
- [ ] Fix all warnings & errors
- [ ] Run Prettier: `npm run format`
- [ ] Remove console.log di production:
  ```typescript
  // lib/utils/logger.ts
  const isDev = import.meta.env.DEV;
  export const logger = {
    log: (...args) => isDev && console.log(...args),
    error: (...args) => console.error(...args), // Always log errors
  };
  ```

### F9.10 — Build & Deploy Testing

- [ ] Test production build:
  ```bash
  npm run build
  npm run preview
  ```
- [ ] Cek bundle size:
  - Total bundle < 500 KB (gzipped)
  - Initial bundle < 200 KB (gzipped)
- [ ] Test di production mode:
  - All routes accessible
  - API calls work dengan production backend URL
  - No console errors
- [ ] Setup `.env.production`:
  ```bash
  VITE_API_BASE_URL=https://api-simpresma.fik.unej.ac.id/api
  VITE_APP_NAME=SIMPRESMA
  ```

### F9.11 — Documentation

- [ ] Update `README.md`:
  - Project description
  - Tech stack
  - Installation steps
  - Development commands
  - Environment variables
  - Deployment guide
- [ ] Buat `CONTRIBUTING.md` (opsional):
  - Code style guide
  - Commit message format
  - Pull request process

### F9.12 — Final Testing Checklist

- [ ] Login flow: semua role bisa login + logout
- [ ] Role-based access: setiap role hanya bisa akses route-nya
- [ ] Multi-role switcher: user dengan multiple roles bisa switch
- [ ] Mahasiswa:
  - Dashboard load
  - List + detail pengajuan
  - Submit form pengajuan (4 steps) → success
- [ ] Verifikator:
  - Dashboard load
  - List pengajuan (scope prodi)
  - Terima pengajuan → success
  - Tolak pengajuan (dengan feedback) → success
- [ ] Tendik:
  - Dashboard load
  - List pengajuan (all prodi)
  - Finalisasi pengajuan (nilai strict) → success
- [ ] Wadek:
  - Dashboard load
  - Edit matriks → success
  - Assign/cabut verifikator → success
  - CRUD bidang-MK → success
- [ ] Shared:
  - Dashboard statistik tampil di semua role
  - Direktori verifikator accessible
- [ ] Responsive: semua pages responsive di mobile/tablet/desktop
- [ ] Error handling: error 401/403/404/422/500 handled dengan toast/page
- [ ] Loading states: skeleton/spinner tampil saat loading
- [ ] Empty states: tampil saat data kosong

---

## Ringkasan Status Phase

| Phase | Nama | Status |
|---|---|---|
| F0 | Dokumen Spesifikasi | Selesai |
| F1 | Setup Project | Belum Mulai |
| F2 | Auth & Routing | Belum Mulai |
| F3 | Layout & Navigation | Belum Mulai |
| F4 | Modul Mahasiswa | Belum Mulai |
| F5 | Modul Verifikator | Belum Mulai |
| F6 | Modul Tendik | Belum Mulai |
| F7 | Modul Wadek | Belum Mulai |
| F8 | Dashboard & Charts | Belum Mulai |
| F9 | Polish & Testing | Belum Mulai |

---

## Estimasi Waktu

| Phase | Estimasi Waktu | Kompleksitas |
|---|---|---|
| F1 | 2-3 jam | Medium (banyak setup) |
| F2 | 3-4 jam | High (auth flow critical) |
| F3 | 2-3 jam | Medium (layout responsive) |
| F4 | 6-8 jam | Very High (form 4-step kompleks) |
| F5 | 3-4 jam | Medium (CRUD + dialogs) |
| F6 | 3-4 jam | High (validation strict nilai) |
| F7 | 4-5 jam | High (3 sub-modul CRUD) |
| F8 | 2-3 jam | Low (charts straightforward) |
| F9 | 4-6 jam | Medium (polish + testing comprehensive) |
| **TOTAL** | **29-40 jam** | **~1 minggu full-time atau 2-3 minggu part-time** |

---

## Catatan Penting

1. **Jangan skip testing per phase** — bug compound jika tidak ditest bertahap
2. **Backend harus running** — frontend butuh API backend (localhost:8000)
3. **Multi-step form (F4.5-F4.8)** adalah bagian paling kompleks — alokasikan waktu cukup
4. **Nilai Tendik strict (F6.4)** — pastikan validation benar (client + server)
5. **Responsive testing (F9.6)** — test di device sebenarnya, bukan hanya DevTools

---

## STATUS DOKUMEN

- **Version:** 1.0.0
- **Last Updated:** 2026-09-01
- **Status:** FINAL & LOCKED
- **Next Steps:** Mulai eksekusi Phase F1

---

**END OF DOCUMENT**
