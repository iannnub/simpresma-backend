# SIMPRESMA Frontend — Requirements Document
## Sistem Informasi Manajemen Prestasi Mahasiswa — Frontend Specification

> **STATUS:** FINAL & LOCKED — Dokumen ini adalah spesifikasi lengkap untuk pembangunan frontend SIMPRESMA.
> Backend sudah production-ready (73 tests, 498 assertions, zero regressions).

---

## 1. Tech Stack (Modern 2026)

### Core Framework & Build Tool
| Technology | Version | Purpose |
|---|---|---|
| **React** | 18.3.x | UI library dengan Concurrent Features & Server Components support |
| **TypeScript** | 5.4.x | Type safety, autocomplete, prevent runtime errors |
| **Vite** | 5.2.x | Lightning-fast HMR (<50ms), optimized production build |

### Styling & UI Components
| Technology | Version | Purpose |
|---|---|---|
| **Tailwind CSS** | 3.4.x | Utility-first CSS framework, responsive utilities built-in |
| **Shadcn/ui** | Latest | Accessible, customizable component library (Radix UI + Tailwind) |
| **Lucide React** | Latest | Modern, consistent icon set (1000+ icons) |
| **clsx** + **tailwind-merge** | Latest | Dynamic className handling |

### Routing & Navigation
| Technology | Version | Purpose |
|---|---|---|
| **React Router** | 6.23.x | Client-side routing dengan nested routes & data loading |
| **React Router DOM** | 6.23.x | Browser history API integration |

### State Management
| Technology | Version | Purpose |
|---|---|---|
| **Zustand** | 4.5.x | Lightweight global state (auth, role switcher) |
| **React Context API** | Built-in | Lokalized state (theme, layout preferences) |
| **React Hook Form** | 7.51.x | Form state management dengan validation |
| **Zod** | 3.23.x | Schema validation (type-safe form validation) |

### HTTP Client & Data Fetching
| Technology | Version | Purpose |
|---|---|---|
| **Axios** | 1.6.x | HTTP client dengan interceptors untuk token injection |
| **TanStack Query (React Query)** | 5.32.x | Server state management, caching, auto-refetch |
| **openapi-typescript-codegen** | 0.29.x | Auto-generate TypeScript client dari openapi.json |

### Data Visualization
| Technology | Version | Purpose |
|---|---|---|
| **Recharts** | 2.12.x | React charts library (Pie, Bar, Line charts) |
| **date-fns** | 3.6.x | Modern date utility (format, parse, diff) |

### Form & Validation
| Technology | Version | Purpose |
|---|---|---|
| **React Hook Form** | 7.51.x | Performant form dengan minimal re-renders |
| **Zod** | 3.23.x | Runtime type validation |
| **React Select** | 5.8.x | Accessible, searchable select/dropdown |

### Notifications & Feedback
| Technology | Version | Purpose |
|---|---|---|
| **Sonner** | 1.4.x | Modern toast notifications (better than react-toastify) |
| **React Loading Skeleton** | 3.4.x | Skeleton screen saat loading |

### Development Tools
| Technology | Version | Purpose |
|---|---|---|
| **ESLint** | 8.57.x | Code linting |
| **Prettier** | 3.2.x | Code formatting |
| **Vitest** | 1.5.x | Unit testing (Vite-native, faster than Jest) |
| **Testing Library** | 15.0.x | Component testing (user-centric approach) |

---

## 2. Browser & Device Support

### Target Browsers (Modern Only)
```
✅ Chrome 120+
✅ Firefox 122+
✅ Safari 17+
✅ Edge 120+
❌ Internet Explorer (tidak didukung)
```

### Device Breakpoints (Tailwind Default)
| Breakpoint | Screen Size | Device Type | Layout Behavior |
|---|---|---|---|
| **xs** (default) | < 640px | Mobile portrait | Single column, bottom nav, hamburger menu |
| **sm** | ≥ 640px | Mobile landscape | Single column, refined spacing |
| **md** | ≥ 768px | Tablet portrait | Two columns (sidebar collapsible) |
| **lg** | ≥ 1024px | Tablet landscape / Small desktop | Persistent sidebar, multi-column forms |
| **xl** | ≥ 1280px | Desktop | Full sidebar, optimized spacing |
| **2xl** | ≥ 1536px | Large desktop | Max-width container, centered content |

### Responsive Strategy
- **Mobile-First Design:** Default styles untuk mobile, override dengan `md:`, `lg:`, `xl:`
- **Touch-Friendly:** Minimum tap target 44x44px (WCAG 2.5.5)
- **Keyboard Accessible:** Tab navigation, focus visible, ARIA labels
- **PWA-Ready:** Manifest, service worker (opsional fase 2)

---

## 3. Application Architecture

### Folder Structure (Feature-Based + Layered)
```
frontend/
├── public/
│   ├── logo-simpresma.svg
│   ├── logo-unej.png
│   └── favicon.ico
├── src/
│   ├── app/                          # Pages (route-based organization)
│   │   ├── (auth)/                   # Public routes (no layout)
│   │   │   └── login/
│   │   │       └── page.tsx
│   │   ├── (mahasiswa)/              # Protected routes (AppLayout)
│   │   │   ├── dashboard/
│   │   │   │   └── page.tsx
│   │   │   └── pengajuan/
│   │   │       ├── page.tsx          # List pengajuan (table)
│   │   │       ├── new/
│   │   │       │   └── page.tsx      # Form submit (kompleks!)
│   │   │       └── [id]/
│   │   │           └── page.tsx      # Detail pengajuan
│   │   ├── (verifikator)/
│   │   │   ├── dashboard/page.tsx
│   │   │   └── pengajuan/
│   │   │       ├── page.tsx
│   │   │       └── [id]/page.tsx
│   │   ├── (tendik)/
│   │   │   ├── dashboard/page.tsx
│   │   │   └── pengajuan/
│   │   │       ├── page.tsx
│   │   │       └── [id]/page.tsx
│   │   └── (wadek)/
│   │       ├── dashboard/page.tsx
│   │       ├── matriks/page.tsx
│   │       ├── verifikator/page.tsx
│   │       └── bidang-mk/page.tsx
│   ├── components/
│   │   ├── ui/                       # Shadcn/ui components (atomic)
│   │   │   ├── button.tsx
│   │   │   ├── input.tsx
│   │   │   ├── select.tsx
│   │   │   ├── textarea.tsx
│   │   │   ├── dialog.tsx
│   │   │   ├── dropdown-menu.tsx
│   │   │   ├── card.tsx
│   │   │   ├── table.tsx
│   │   │   ├── badge.tsx
│   │   │   ├── skeleton.tsx
│   │   │   └── toast.tsx
│   │   ├── layouts/                  # Layout components
│   │   │   ├── AppLayout.tsx         # Main wrapper (sidebar + header)
│   │   │   ├── Sidebar.tsx           # Desktop sidebar (persistent)
│   │   │   ├── MobileNav.tsx         # Bottom navigation (mobile)
│   │   │   ├── Header.tsx            # Top header (user profile, notifications)
│   │   │   └── ProtectedRoute.tsx    # Route guard component
│   │   ├── forms/                    # Complex form components
│   │   │   ├── PengajuanForm/
│   │   │   │   ├── index.tsx         # Main form
│   │   │   │   ├── StepLombaInfo.tsx # Step 1: Lomba info
│   │   │   │   ├── StepDokumen.tsx   # Step 2: Dokumen links
│   │   │   │   ├── StepMataKuliah.tsx# Step 3: MK selection
│   │   │   │   ├── StepPreview.tsx   # Step 4: Preview before submit
│   │   │   │   └── SKSCalculator.tsx # Real-time SKS calculator
│   │   │   ├── FinalisasiForm.tsx    # Tendik finalisasi form
│   │   │   └── MatriksForm.tsx       # Wadek edit matriks
│   │   ├── tables/                   # Reusable table components
│   │   │   ├── DataTable.tsx         # Generic data table
│   │   │   ├── PengajuanTable.tsx    # Pengajuan list table
│   │   │   └── Pagination.tsx        # Pagination component
│   │   ├── charts/                   # Data visualization
│   │   │   ├── StatistikChart.tsx    # Dashboard chart (pie + bar)
│   │   │   └── StatusBreakdown.tsx   # Status distribution chart
│   │   └── shared/                   # Shared components
│   │       ├── StatsCard.tsx         # Dashboard stats card
│   │       ├── StatusBadge.tsx       # Status badge (pending/diterima/dll)
│   │       ├── RoleGuard.tsx         # Role-based access wrapper
│   │       ├── EmptyState.tsx        # Empty state illustration
│   │       ├── ErrorBoundary.tsx     # Error boundary wrapper
│   │       └── LoadingSpinner.tsx    # Loading indicator
│   ├── lib/
│   │   ├── api/                      # API client & functions
│   │   │   ├── client.ts             # Axios instance dengan interceptors
│   │   │   ├── auth.api.ts           # Auth endpoints (login, logout, me)
│   │   │   ├── ref.api.ts            # Reference data (prodi, bidang, dll)
│   │   │   ├── mahasiswa.api.ts      # Mahasiswa endpoints
│   │   │   ├── verifikator.api.ts    # Verifikator endpoints
│   │   │   ├── tendik.api.ts         # Tendik endpoints
│   │   │   ├── wadek.api.ts          # Wadek endpoints
│   │   │   ├── shared.api.ts         # Dashboard, direktori
│   │   │   └── types.ts              # API response/request types
│   │   ├── hooks/                    # Custom React hooks
│   │   │   ├── useAuth.ts            # Auth hook (login, logout, user)
│   │   │   ├── useRole.ts            # Role management hook
│   │   │   ├── usePengajuan.ts       # Pengajuan CRUD hook
│   │   │   ├── useMatriks.ts         # Matriks lookup hook
│   │   │   └── useDebounce.ts        # Debounce utility hook
│   │   ├── utils/                    # Utility functions
│   │   │   ├── cn.ts                 # className merge utility
│   │   │   ├── format.ts             # Date, number formatting
│   │   │   ├── validation.ts         # Custom validators
│   │   │   └── constants.ts          # App constants
│   │   └── schemas/                  # Zod validation schemas
│   │       ├── pengajuan.schema.ts   # Pengajuan form validation
│   │       ├── finalisasi.schema.ts  # Finalisasi form validation
│   │       └── auth.schema.ts        # Login form validation
│   ├── stores/                       # Zustand stores (global state)
│   │   ├── authStore.ts              # Auth state (user, token, isAuth)
│   │   ├── roleStore.ts              # Multi-role switcher state
│   │   └── uiStore.ts                # UI state (sidebar open/close, theme)
│   ├── types/                        # TypeScript type definitions
│   │   ├── user.types.ts             # User, Role types
│   │   ├── pengajuan.types.ts        # Pengajuan, Status types
│   │   ├── matriks.types.ts          # Matriks, Bidang, MK types
│   │   └── api.types.ts              # API response wrapper types
│   ├── styles/
│   │   └── globals.css               # Tailwind imports + custom CSS
│   ├── config/
│   │   ├── routes.config.ts          # Route definitions
│   │   └── navigation.config.ts      # Sidebar menu config per role
│   ├── App.tsx                       # Root component
│   ├── main.tsx                      # Entry point
│   └── router.tsx                    # React Router setup
├── .env.example
├── .env.local
├── .eslintrc.json
├── .gitignore
├── index.html
├── package.json
├── postcss.config.js
├── tailwind.config.ts
├── tsconfig.json
├── tsconfig.node.json
└── vite.config.ts
```

---

## 4. Authentication & Authorization

### Authentication Flow
```
1. User visits app
   ↓
2. Check token in localStorage
   ├─ Token exists → Validate with API (GET /api/auth/me)
   │  ├─ Valid → Redirect to dashboard based on role
   │  └─ Invalid → Clear token, redirect to /login
   └─ No token → Redirect to /login

3. Login page (/login)
   ↓
4. Submit email + password → POST /api/auth/login
   ├─ Success (200):
   │  ├─ Store token in localStorage
   │  ├─ Store user data in Zustand authStore
   │  └─ Redirect to role-based dashboard:
   │     • Mahasiswa → /mahasiswa/dashboard
   │     • Verifikator → /verifikator/dashboard
   │     • Tendik → /tendik/dashboard
   │     • Wadek → /wadek/dashboard
   └─ Error (422/401):
      └─ Show error toast, stay on /login

5. All protected routes check:
   - Token exists? → Continue
   - No token? → Redirect to /login
   - Wrong role? → Show 403 Forbidden page
```

### Token Management
```typescript
// localStorage keys
const TOKEN_KEY = 'simpresma_token';
const USER_KEY = 'simpresma_user';

// Axios interceptor (auto-inject token)
axios.interceptors.request.use((config) => {
  const token = localStorage.getItem(TOKEN_KEY);
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  config.headers.Accept = 'application/json';
  return config;
});

// Axios interceptor (handle 401 = logout)
axios.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem(TOKEN_KEY);
      localStorage.removeItem(USER_KEY);
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);
```

### Role-Based Access Control (RBAC)

#### Route Protection Matrix
| Route Path | Allowed Roles | Redirect if Unauthorized |
|---|---|---|
| `/login` | Public (unauthenticated only) | → `/[role]/dashboard` if already logged in |
| `/mahasiswa/*` | `mahasiswa` | → `/login` (no token) or 403 (wrong role) |
| `/verifikator/*` | `verifikator` | → `/login` (no token) or 403 (wrong role) |
| `/tendik/*` | `tendik` | → `/login` (no token) or 403 (wrong role) |
| `/wadek/*` | `wadek` | → `/login` (no token) or 403 (wrong role) |

#### Multi-Role User Handling
```typescript
// User dengan multiple roles (contoh: multi@test.com)
const user = {
  roles: ['verifikator', 'tendik']
};

// UI: Role switcher dropdown di header
<RoleSwitcher 
  roles={user.roles}
  currentRole="verifikator"
  onSwitch={(role) => {
    // Update active role in store
    // Navigate to new role dashboard
    navigate(`/${role}/dashboard`);
  }}
/>
```

---

## 5. Routing Structure

### Route Hierarchy
```typescript
// routes.config.ts
export const routes = {
  public: {
    login: '/login',
  },
  mahasiswa: {
    dashboard: '/mahasiswa/dashboard',
    pengajuan: {
      list: '/mahasiswa/pengajuan',
      new: '/mahasiswa/pengajuan/new',
      detail: '/mahasiswa/pengajuan/:id',
    },
  },
  verifikator: {
    dashboard: '/verifikator/dashboard',
    pengajuan: {
      list: '/verifikator/pengajuan',
      detail: '/verifikator/pengajuan/:id',
    },
  },
  tendik: {
    dashboard: '/tendik/dashboard',
    pengajuan: {
      list: '/tendik/pengajuan',
      detail: '/tendik/pengajuan/:id',
    },
  },
  wadek: {
    dashboard: '/wadek/dashboard',
    matriks: '/wadek/matriks',
    verifikator: '/wadek/verifikator',
    bidangMK: '/wadek/bidang-mk',
  },
};
```

### Navigation Menu Configuration
```typescript
// navigation.config.ts
export const navigationConfig = {
  mahasiswa: [
    { 
      label: 'Dashboard', 
      href: '/mahasiswa/dashboard', 
      icon: LayoutDashboard 
    },
    { 
      label: 'Pengajuan Saya', 
      href: '/mahasiswa/pengajuan', 
      icon: FileText,
      badge: 'pending_count' // Dynamic badge
    },
    { 
      label: 'Ajukan Prestasi', 
      href: '/mahasiswa/pengajuan/new', 
      icon: PlusCircle,
      highlight: true // Primary action
    },
  ],
  verifikator: [
    { 
      label: 'Dashboard', 
      href: '/verifikator/dashboard', 
      icon: LayoutDashboard 
    },
    { 
      label: 'Pengajuan Pending', 
      href: '/verifikator/pengajuan', 
      icon: ClipboardList,
      badge: 'pending_count'
    },
    { 
      label: 'Direktori Verifikator', 
      href: '/direktori-verifikator', 
      icon: Users 
    },
  ],
  tendik: [
    { 
      label: 'Dashboard', 
      href: '/tendik/dashboard', 
      icon: LayoutDashboard 
    },
    { 
      label: 'Pengajuan Diterima', 
      href: '/tendik/pengajuan', 
      icon: CheckCircle,
      badge: 'diterima_count'
    },
  ],
  wadek: [
    { 
      label: 'Dashboard', 
      href: '/wadek/dashboard', 
      icon: LayoutDashboard 
    },
    { 
      label: 'Kelola Matriks', 
      href: '/wadek/matriks', 
      icon: Grid3x3 
    },
    { 
      label: 'Tim Verifikator', 
      href: '/wadek/verifikator', 
      icon: UserCog 
    },
    { 
      label: 'Mapping Bidang-MK', 
      href: '/wadek/bidang-mk', 
      icon: Link 
    },
  ],
};
```

---

## 6. Core Features Specification

### 6.1 Dashboard (All Roles)

#### Layout Components
```typescript
// Desktop (≥1024px)
<div className="flex h-screen">
  <Sidebar /> {/* Fixed width 256px, persistent */}
  <div className="flex-1 flex flex-col">
    <Header /> {/* Height 64px, sticky top */}
    <main className="flex-1 overflow-y-auto p-6">
      {children} {/* Page content */}
    </main>
  </div>
</div>

// Mobile (<1024px)
<div className="flex flex-col h-screen">
  <Header /> {/* With hamburger menu */}
  <main className="flex-1 overflow-y-auto p-4">
    {children}
  </main>
  <MobileNav /> {/* Bottom navigation bar, height 64px */}
</div>
```

#### Dashboard Content (Common Pattern)
```typescript
// Stats Cards Row (4 cards)
<div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
  <StatsCard 
    title="Total Pengajuan" 
    value={totalPengajuan} 
    icon={FileText}
    trend={{ value: 12, direction: 'up' }}
  />
  <StatsCard 
    title="Pending" 
    value={pendingCount} 
    icon={Clock}
    color="warning"
  />
  <StatsCard 
    title="Diterima" 
    value={diterimaCount} 
    icon={CheckCircle}
    color="success"
  />
  <StatsCard 
    title="Selesai" 
    value={selesaiCount} 
    icon={Award}
    color="info"
  />
</div>

// Charts Row (2 charts)
<div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
  <Card>
    <CardHeader>
      <CardTitle>Distribusi per Prodi</CardTitle>
    </CardHeader>
    <CardContent>
      <PieChart data={statistikProdi} />
    </CardContent>
  </Card>
  <Card>
    <CardHeader>
      <CardTitle>Status Breakdown</CardTitle>
    </CardHeader>
    <CardContent>
      <BarChart data={statusBreakdown} />
    </CardContent>
  </Card>
</div>

// Recent Activity Table
<Card>
  <CardHeader>
    <CardTitle>Aktivitas Terbaru</CardTitle>
  </CardHeader>
  <CardContent>
    <DataTable 
      columns={activityColumns}
      data={recentActivity}
      pageSize={5}
    />
  </CardContent>
</Card>
```

---

### 6.2 Form Pengajuan Mahasiswa (COMPLEX!)

#### Multi-Step Form (4 Steps)

**Step 1: Informasi Lomba**
```typescript
// Fields:
- nama_lomba (text, required, max 200)
- nama_tim (text, optional, max 150)
- no_whatsapp (text, required, format: 08xxxxxxxx)
- bidang_id (select, required, dari API /api/ref/bidang)
- tingkatan_id (select, required, dari API /api/ref/tingkatan)
- tahapan_id (select, required, dari API /api/ref/tahapan)
- detail_juara (text, conditional: jika tahapan = 'pemenang')

// Real-time SKS Calculator
useEffect(() => {
  if (tingkatan_id && tahapan_id) {
    fetchMatriks(tingkatan_id, tahapan_id).then((matriks) => {
      if (!matriks || !matriks.min_sks) {
        toast.error('Kombinasi tingkatan dan tahapan tidak valid!');
        return;
      }
      setSKSRange({
        min: matriks.min_sks,
        max: matriks.max_sks,
        huruf_nilai: matriks.huruf_nilai
      });
    });
  }
}, [tingkatan_id, tahapan_id]);

// Display SKS Badge
{sksRange && (
  <Alert>
    <AlertTitle>Rentang Konversi SKS</AlertTitle>
    <AlertDescription>
      Anda dapat memilih mata kuliah dengan total SKS antara{' '}
      <strong>{sksRange.min} - {sksRange.max} SKS</strong>. 
      Prediksi nilai: <Badge variant="success">{sksRange.huruf_nilai}</Badge>
    </AlertDescription>
  </Alert>
)}
```

**Step 2: Dokumen & Bukti**
```typescript
// Fields (all URL, no file upload):
- link_sertifikat (url, required, max 500)
- status_surat_tugas_mahasiswa (boolean, required)
- link_surat_tugas_mahasiswa (url, conditional required if status = true)
- status_surat_tugas_dosen (boolean, required)
- link_surat_tugas_dosen (url, conditional required if status = true)
- link_poster (url, optional, max 500)
- link_sosmed (url, optional, max 500)
- keterangan (textarea, optional)

// Validation with Zod
const stepDokumenSchema = z.object({
  link_sertifikat: z.string().url('URL tidak valid').max(500),
  status_surat_tugas_mahasiswa: z.boolean(),
  link_surat_tugas_mahasiswa: z.string().url().max(500).optional()
    .refine((val, ctx) => {
      if (ctx.parent.status_surat_tugas_mahasiswa && !val) {
        return false;
      }
      return true;
    }, 'Link wajib diisi jika status Ada'),
  // ... similar for surat tugas dosen
});
```

**Step 3: Pilih Mata Kuliah**
```typescript
// Dynamic MK selection
const [availableMK, setAvailableMK] = useState([]);
const [selectedMK, setSelectedMK] = useState([]);
const [totalSKS, setTotalSKS] = useState(0);

// Fetch MK based on bidang + prodi
useEffect(() => {
  if (bidang_id && user.prodi_id) {
    fetchMataKuliah(bidang_id, user.prodi_id).then(setAvailableMK);
  }
}, [bidang_id, user.prodi_id]);

// Real-time SKS counter
useEffect(() => {
  const total = selectedMK.reduce((sum, mk) => sum + mk.sks, 0);
  setTotalSKS(total);
}, [selectedMK]);

// Validation
const isValidSKS = totalSKS >= sksRange.min && totalSKS <= sksRange.max;

// UI
<Card>
  <CardHeader>
    <CardTitle>Pilih Mata Kuliah</CardTitle>
    <CardDescription>
      Total SKS terpilih: <strong>{totalSKS} / {sksRange.max} SKS</strong>
      {isValidSKS ? (
        <Badge variant="success">Valid ✓</Badge>
      ) : (
        <Badge variant="destructive">Rentang tidak sesuai!</Badge>
      )}
    </CardDescription>
  </CardHeader>
  <CardContent>
    <div className="space-y-2">
      {availableMK.map((mk) => (
        <label key={mk.id} className="flex items-center space-x-3">
          <Checkbox
            checked={selectedMK.some((m) => m.id === mk.id)}
            onCheckedChange={(checked) => {
              if (checked) {
                setSelectedMK([...selectedMK, mk]);
              } else {
                setSelectedMK(selectedMK.filter((m) => m.id !== mk.id));
              }
            }}
          />
          <span>{mk.nama_mk}</span>
          <Badge variant="outline">{mk.sks} SKS</Badge>
        </label>
      ))}
    </div>
  </CardContent>
</Card>
```

**Step 4: Preview & Submit**
```typescript
// Display all data for review
<Card>
  <CardHeader>
    <CardTitle>Preview Pengajuan</CardTitle>
    <CardDescription>
      Periksa kembali data sebelum submit. Data tidak dapat diubah setelah disubmit.
    </CardDescription>
  </CardHeader>
  <CardContent>
    <dl className="space-y-2">
      <div>
        <dt className="font-semibold">Nama Lomba:</dt>
        <dd>{formData.nama_lomba}</dd>
      </div>
      <div>
        <dt className="font-semibold">Bidang:</dt>
        <dd>{bidangName}</dd>
      </div>
      {/* ... semua field */}
      <div>
        <dt className="font-semibold">Mata Kuliah Dipilih:</dt>
        <dd>
          <ul className="list-disc list-inside">
            {selectedMK.map((mk) => (
              <li key={mk.id}>{mk.nama_mk} ({mk.sks} SKS)</li>
            ))}
          </ul>
          <p className="mt-2">
            Total: <strong>{totalSKS} SKS</strong> | 
            Prediksi Nilai: <Badge>{sksRange.huruf_nilai}</Badge>
          </p>
        </dd>
      </div>
    </dl>
  </CardContent>
  <CardFooter>
    <Button onClick={handleSubmit} disabled={isSubmitting}>
      {isSubmitting ? <Spinner /> : 'Submit Pengajuan'}
    </Button>
  </CardFooter>
</Card>

// Submit handler
const handleSubmit = async () => {
  try {
    setIsSubmitting(true);
    const payload = {
      ...formData,
      mata_kuliah_ids: selectedMK.map((mk) => mk.id),
    };
    await api.mahasiswa.submitPengajuan(payload);
    toast.success('Pengajuan berhasil disubmit!');
    navigate('/mahasiswa/pengajuan');
  } catch (error) {
    if (error.response?.status === 422) {
      // Validation errors
      const errors = error.response.data.errors;
      Object.keys(errors).forEach((key) => {
        toast.error(errors[key][0]);
      });
    } else {
      toast.error('Terjadi kesalahan. Silakan coba lagi.');
    }
  } finally {
    setIsSubmitting(false);
  }
};
```

---

### 6.3 Verifikator Module

#### Pengajuan List (Scope Prodi)
```typescript
// Filter by scope prodi (backend handles this)
const { data, isLoading } = useQuery({
  queryKey: ['verifikator-pengajuan', filters],
  queryFn: () => api.verifikator.getPengajuanList(filters),
});

// Table columns
const columns = [
  { key: 'id', label: 'ID', sortable: true },
  { key: 'user.nama', label: 'Mahasiswa' },
  { key: 'nama_lomba', label: 'Lomba' },
  { key: 'tingkatan.nama', label: 'Tingkatan' },
  { key: 'tahapan.nama', label: 'Tahapan' },
  { key: 'created_at', label: 'Tanggal Ajukan', sortable: true },
  { key: 'actions', label: 'Aksi', render: (row) => (
    <Button size="sm" onClick={() => navigate(`/verifikator/pengajuan/${row.id}`)}>
      Lihat Detail
    </Button>
  )},
];
```

#### Pengajuan Detail + Actions
```typescript
// Detail page
const { data: pengajuan } = useQuery({
  queryKey: ['pengajuan', id],
  queryFn: () => api.verifikator.getPengajuanDetail(id),
});

// Action buttons
<Card>
  <CardFooter className="flex gap-2">
    <Dialog>
      <DialogTrigger asChild>
        <Button variant="success">
          <CheckCircle className="mr-2 h-4 w-4" />
          Terima
        </Button>
      </DialogTrigger>
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Konfirmasi Terima Pengajuan</DialogTitle>
          <DialogDescription>
            Anda yakin akan menerima pengajuan ini? SKS dan nilai akan dikunci 
            sesuai matriks yang berlaku saat ini.
          </DialogDescription>
        </DialogHeader>
        <DialogFooter>
          <Button variant="outline" onClick={() => setDialogOpen(false)}>
            Batal
          </Button>
          <Button onClick={handleTerima}>
            Ya, Terima
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <Dialog>
      <DialogTrigger asChild>
        <Button variant="destructive">
          <XCircle className="mr-2 h-4 w-4" />
          Tolak
        </Button>
      </DialogTrigger>
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Tolak Pengajuan</DialogTitle>
          <DialogDescription>
            Silakan berikan alasan penolakan (wajib diisi).
          </DialogDescription>
        </DialogHeader>
        <Textarea
          placeholder="Contoh: Sertifikat tidak sesuai dengan tahapan yang diklaim..."
          value={feedback}
          onChange={(e) => setFeedback(e.target.value)}
          minLength={10}
          required
        />
        <DialogFooter>
          <Button variant="outline" onClick={() => setDialogOpen(false)}>
            Batal
          </Button>
          <Button 
            variant="destructive" 
            onClick={handleTolak}
            disabled={feedback.length < 10}
          >
            Tolak dengan Feedback
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </CardFooter>
</Card>

// Handler
const handleTerima = async () => {
  try {
    await api.verifikator.terimaPengajuan(pengajuan.id);
    toast.success('Pengajuan berhasil diterima!');
    queryClient.invalidateQueries(['verifikator-pengajuan']);
    navigate('/verifikator/pengajuan');
  } catch (error) {
    toast.error('Gagal menerima pengajuan.');
  }
};

const handleTolak = async () => {
  try {
    await api.verifikator.tolakPengajuan(pengajuan.id, { 
      feedback_verifikator: feedback 
    });
    toast.success('Pengajuan ditolak dengan feedback.');
    queryClient.invalidateQueries(['verifikator-pengajuan']);
    navigate('/verifikator/pengajuan');
  } catch (error) {
    if (error.response?.status === 422) {
      toast.error('Feedback minimal 10 karakter.');
    } else {
      toast.error('Gagal menolak pengajuan.');
    }
  }
};
```

---

### 6.4 Tendik Module

#### Finalisasi Form (CRITICAL — Strict Validation!)
```typescript
// Fetch pengajuan detail
const { data: pengajuan } = useQuery({
  queryKey: ['pengajuan', id],
  queryFn: () => api.tendik.getPengajuanDetail(id),
});

// Pre-fill nilai per MK dengan snapshot_huruf_nilai
const initialNilai = pengajuan.mata_kuliahs.map((mk) => ({
  mk_id: mk.id,
  huruf_nilai: pengajuan.snapshot_huruf_nilai, // Default = snapshot
}));

const [nilaiPerMK, setNilaiPerMK] = useState(initialNilai);
const [linkSK, setLinkSK] = useState('');

// Form UI
<Card>
  <CardHeader>
    <CardTitle>Finalisasi Konversi Nilai</CardTitle>
    <CardDescription>
      <Alert variant="warning">
        <AlertCircle className="h-4 w-4" />
        <AlertTitle>Perhatian!</AlertTitle>
        <AlertDescription>
          Nilai yang diinput <strong>WAJIB</strong> sama dengan nilai matriks:{' '}
          <Badge variant="destructive">{pengajuan.snapshot_huruf_nilai}</Badge>
        </AlertDescription>
      </Alert>
    </CardDescription>
  </CardHeader>
  <CardContent>
    <Table>
      <TableHeader>
        <TableRow>
          <TableHead>Mata Kuliah</TableHead>
          <TableHead>SKS</TableHead>
          <TableHead>Huruf Nilai (Wajib: {pengajuan.snapshot_huruf_nilai})</TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        {pengajuan.mata_kuliahs.map((mk, index) => (
          <TableRow key={mk.id}>
            <TableCell>{mk.nama_mk}</TableCell>
            <TableCell>{mk.pivot.sks_snapshot} SKS</TableCell>
            <TableCell>
              <Select
                value={nilaiPerMK[index].huruf_nilai}
                onValueChange={(value) => {
                  const updated = [...nilaiPerMK];
                  updated[index].huruf_nilai = value;
                  setNilaiPerMK(updated);
                }}
              >
                <SelectTrigger>
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  {/* HANYA OPSI YANG SAMA DENGAN SNAPSHOT! */}
                  <SelectItem value={pengajuan.snapshot_huruf_nilai}>
                    {pengajuan.snapshot_huruf_nilai}
                  </SelectItem>
                </SelectContent>
              </Select>
              {nilaiPerMK[index].huruf_nilai !== pengajuan.snapshot_huruf_nilai && (
                <p className="text-sm text-destructive mt-1">
                  ⚠️ Nilai harus sama dengan snapshot!
                </p>
              )}
            </TableCell>
          </TableRow>
        ))}
      </TableBody>
    </Table>

    <div className="mt-4">
      <Label>Link Surat Keterangan Konversi (Opsional)</Label>
      <Input
        type="url"
        placeholder="https://drive.google.com/file/..."
        value={linkSK}
        onChange={(e) => setLinkSK(e.target.value)}
      />
    </div>
  </CardContent>
  <CardFooter>
    <Button onClick={handleFinalisasi} disabled={!isValidNilai}>
      Finalisasi Konversi
    </Button>
  </CardFooter>
</Card>

// Validation
const isValidNilai = nilaiPerMK.every(
  (item) => item.huruf_nilai === pengajuan.snapshot_huruf_nilai
);

// Submit handler
const handleFinalisasi = async () => {
  try {
    await api.tendik.finalisasiPengajuan(pengajuan.id, {
      nilai_per_mk: nilaiPerMK.map((item) => ({
        mk_id: item.mk_id,
        huruf_nilai: item.huruf_nilai,
      })),
      link_sk_konversi: linkSK || null,
    });
    toast.success('Pengajuan berhasil difinalisasi!');
    queryClient.invalidateQueries(['tendik-pengajuan']);
    navigate('/tendik/pengajuan');
  } catch (error) {
    if (error.response?.status === 422) {
      const errors = error.response.data.errors;
      if (errors.nilai_per_mk) {
        toast.error(errors.nilai_per_mk[0]); 
        // "Huruf nilai wajib sama dengan nilai matriks: A"
      }
    } else {
      toast.error('Gagal finalisasi pengajuan.');
    }
  }
};
```

---

### 6.5 Wadek Module

#### CRUD Matriks Konversi
```typescript
// Table with inline edit
<DataTable
  columns={[
    { key: 'tingkatan.nama', label: 'Tingkatan' },
    { key: 'tahapan.nama', label: 'Tahapan' },
    { key: 'min_sks', label: 'Min SKS', editable: true },
    { key: 'max_sks', label: 'Max SKS', editable: true },
    { key: 'huruf_nilai', label: 'Huruf Nilai', editable: true },
    { key: 'updated_by', label: 'Terakhir Diubah' },
    { key: 'actions', label: 'Aksi', render: (row) => (
      <Dialog>
        <DialogTrigger asChild>
          <Button size="sm" variant="outline">
            <Edit className="h-4 w-4" />
          </Button>
        </DialogTrigger>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Edit Matriks Konversi</DialogTitle>
          </DialogHeader>
          <MatriksForm matriks={row} onSubmit={handleUpdate} />
        </DialogContent>
      </Dialog>
    )},
  ]}
  data={matriksList}
/>

// Form
const MatriksForm = ({ matriks, onSubmit }) => {
  const form = useForm({
    defaultValues: matriks,
    resolver: zodResolver(matriksSchema),
  });

  return (
    <Form {...form}>
      <FormField name="min_sks" label="Min SKS" type="number" />
      <FormField name="max_sks" label="Max SKS" type="number" />
      <FormField name="huruf_nilai" label="Huruf Nilai" />
      <Button onClick={form.handleSubmit(onSubmit)}>
        Simpan Perubahan
      </Button>
    </Form>
  );
};

// Update handler
const handleUpdate = async (data) => {
  try {
    await api.wadek.updateMatriks(matriks.id, data);
    toast.success('Matriks berhasil diperbarui!');
    queryClient.invalidateQueries(['matriks']);
  } catch (error) {
    toast.error('Gagal memperbarui matriks.');
  }
};
```

#### Manage Tim Verifikator
```typescript
// List + Assign/Cabut
<Card>
  <CardHeader>
    <CardTitle>Tim Verifikator per Prodi</CardTitle>
    <CardDescription>
      Kelola dosen yang ditunjuk sebagai verifikator untuk setiap prodi.
    </CardDescription>
  </CardHeader>
  <CardContent>
    <Tabs defaultValue="SI">
      <TabsList>
        <TabsTrigger value="SI">Sistem Informasi</TabsTrigger>
        <TabsTrigger value="TI">Teknologi Informasi</TabsTrigger>
        <TabsTrigger value="IF">Informatika</TabsTrigger>
      </TabsList>
      <TabsContent value="SI">
        <VerifikatorList prodiId={1} />
      </TabsContent>
      {/* ... TI, IF */}
    </Tabs>
  </CardContent>
</Card>

// Verifikator List Component
const VerifikatorList = ({ prodiId }) => {
  const { data: verifikators } = useQuery({
    queryKey: ['verifikator', prodiId],
    queryFn: () => api.wadek.getVerifikatorByProdi(prodiId),
  });

  return (
    <>
      <div className="flex justify-between mb-4">
        <h3>Verifikator Aktif</h3>
        <Dialog>
          <DialogTrigger asChild>
            <Button size="sm">
              <Plus className="mr-2 h-4 w-4" />
              Assign Verifikator
            </Button>
          </DialogTrigger>
          <DialogContent>
            <AssignVerifikatorForm prodiId={prodiId} />
          </DialogContent>
        </Dialog>
      </div>
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead>Nama Dosen</TableHead>
            <TableHead>NIP</TableHead>
            <TableHead>Status</TableHead>
            <TableHead>Aksi</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          {verifikators.map((v) => (
            <TableRow key={v.id}>
              <TableCell>{v.user.nama}</TableCell>
              <TableCell>{v.user.nim_nip}</TableCell>
              <TableCell>
                <Badge variant={v.is_active ? 'success' : 'secondary'}>
                  {v.is_active ? 'Aktif' : 'Nonaktif'}
                </Badge>
              </TableCell>
              <TableCell>
                {v.is_active && (
                  <Button
                    size="sm"
                    variant="destructive"
                    onClick={() => handleCabut(v.id)}
                  >
                    Cabut
                  </Button>
                )}
              </TableCell>
            </TableRow>
          ))}
        </TableBody>
      </Table>
    </>
  );
};

// Assign form
const AssignVerifikatorForm = ({ prodiId }) => {
  const [selectedDosen, setSelectedDosen] = useState(null);

  const { data: dosenList } = useQuery({
    queryKey: ['dosen-list'],
    queryFn: api.wadek.getDosenList,
  });

  const handleAssign = async () => {
    try {
      await api.wadek.assignVerifikator({
        user_id: selectedDosen,
        prodi_id: prodiId,
      });
      toast.success('Verifikator berhasil ditambahkan!');
      queryClient.invalidateQueries(['verifikator', prodiId]);
    } catch (error) {
      toast.error('Gagal assign verifikator.');
    }
  };

  return (
    <div className="space-y-4">
      <Label>Pilih Dosen</Label>
      <Select value={selectedDosen} onValueChange={setSelectedDosen}>
        <SelectTrigger>
          <SelectValue placeholder="Pilih dosen..." />
        </SelectTrigger>
        <SelectContent>
          {dosenList?.map((dosen) => (
            <SelectItem key={dosen.id} value={dosen.id}>
              {dosen.nama} ({dosen.nim_nip})
            </SelectItem>
          ))}
        </SelectContent>
      </Select>
      <Button onClick={handleAssign} disabled={!selectedDosen}>
        Assign
      </Button>
    </div>
  );
};
```

---

## 7. API Integration

### Base Configuration
```typescript
// lib/api/client.ts
import axios from 'axios';

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api';

export const apiClient = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  timeout: 30000, // 30 seconds
});

// Request interceptor (inject token)
apiClient.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('simpresma_token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => Promise.reject(error)
);

// Response interceptor (handle errors)
apiClient.interceptors.response.use(
  (response) => response.data, // Return data directly
  (error) => {
    if (error.response?.status === 401) {
      // Token expired or invalid
      localStorage.removeItem('simpresma_token');
      localStorage.removeItem('simpresma_user');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);
```

### API Functions (Type-Safe)
```typescript
// lib/api/auth.api.ts
import { apiClient } from './client';
import type { User, LoginRequest, LoginResponse } from '@/types';

export const authApi = {
  login: async (credentials: LoginRequest): Promise<LoginResponse> => {
    const response = await apiClient.post('/auth/login', credentials);
    return response.data;
  },

  logout: async (): Promise<void> => {
    await apiClient.post('/auth/logout');
  },

  me: async (): Promise<User> => {
    const response = await apiClient.get('/auth/me');
    return response.data;
  },
};

// lib/api/mahasiswa.api.ts
export const mahasiswaApi = {
  getPengajuanList: async (params?: PaginationParams) => {
    const response = await apiClient.get('/mahasiswa/pengajuan', { params });
    return response.data;
  },

  submitPengajuan: async (data: PengajuanFormData) => {
    const response = await apiClient.post('/mahasiswa/pengajuan', data);
    return response.data;
  },

  getPengajuanDetail: async (id: number) => {
    const response = await apiClient.get(`/mahasiswa/pengajuan/${id}`);
    return response.data;
  },
};

// ... similar for verifikator.api.ts, tendik.api.ts, wadek.api.ts
```

### React Query Integration
```typescript
// lib/hooks/usePengajuan.ts
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { mahasiswaApi } from '@/lib/api';
import { toast } from 'sonner';

export const usePengajuanList = (filters?: any) => {
  return useQuery({
    queryKey: ['pengajuan-list', filters],
    queryFn: () => mahasiswaApi.getPengajuanList(filters),
    staleTime: 5 * 60 * 1000, // 5 minutes
  });
};

export const useSubmitPengajuan = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: mahasiswaApi.submitPengajuan,
    onSuccess: () => {
      toast.success('Pengajuan berhasil disubmit!');
      queryClient.invalidateQueries(['pengajuan-list']);
    },
    onError: (error: any) => {
      if (error.response?.status === 422) {
        const errors = error.response.data.errors;
        Object.values(errors).flat().forEach((msg) => {
          toast.error(msg as string);
        });
      } else {
        toast.error('Terjadi kesalahan. Silakan coba lagi.');
      }
    },
  });
};
```

---

## 8. State Management

### Zustand Stores

#### Auth Store
```typescript
// stores/authStore.ts
import { create } from 'zustand';
import { persist } from 'zustand/middleware';
import type { User } from '@/types';

interface AuthState {
  user: User | null;
  token: string | null;
  isAuthenticated: boolean;
  login: (user: User, token: string) => void;
  logout: () => void;
  updateUser: (user: Partial<User>) => void;
}

export const useAuthStore = create<AuthState>()(
  persist(
    (set) => ({
      user: null,
      token: null,
      isAuthenticated: false,

      login: (user, token) => {
        localStorage.setItem('simpresma_token', token);
        set({ user, token, isAuthenticated: true });
      },

      logout: () => {
        localStorage.removeItem('simpresma_token');
        localStorage.removeItem('simpresma_user');
        set({ user: null, token: null, isAuthenticated: false });
      },

      updateUser: (updatedUser) => {
        set((state) => ({
          user: state.user ? { ...state.user, ...updatedUser } : null,
        }));
      },
    }),
    {
      name: 'simpresma_user',
      partialize: (state) => ({ user: state.user }), // Only persist user
    }
  )
);
```

#### Role Store (Multi-Role Switcher)
```typescript
// stores/roleStore.ts
import { create } from 'zustand';

interface RoleState {
  currentRole: string | null;
  availableRoles: string[];
  setCurrentRole: (role: string) => void;
  setAvailableRoles: (roles: string[]) => void;
}

export const useRoleStore = create<RoleState>((set) => ({
  currentRole: null,
  availableRoles: [],

  setCurrentRole: (role) => set({ currentRole: role }),

  setAvailableRoles: (roles) => {
    set({ 
      availableRoles: roles,
      currentRole: roles[0] || null, // Default to first role
    });
  },
}));
```

#### UI Store
```typescript
// stores/uiStore.ts
import { create } from 'zustand';

interface UIState {
  sidebarOpen: boolean;
  theme: 'light' | 'dark';
  toggleSidebar: () => void;
  setSidebarOpen: (open: boolean) => void;
  toggleTheme: () => void;
}

export const useUIStore = create<UIState>((set) => ({
  sidebarOpen: true,
  theme: 'light',

  toggleSidebar: () => set((state) => ({ sidebarOpen: !state.sidebarOpen })),
  
  setSidebarOpen: (open) => set({ sidebarOpen: open }),

  toggleTheme: () => set((state) => ({ 
    theme: state.theme === 'light' ? 'dark' : 'light' 
  })),
}));
```

---

## 9. Responsive Design Guidelines

### Breakpoint Strategy
```css
/* Mobile First (default: < 640px) */
.container {
  padding: 1rem; /* 16px */
  width: 100%;
}

/* Tablet (≥ 768px) */
@media (min-width: 768px) {
  .container {
    padding: 1.5rem; /* 24px */
    max-width: 768px;
  }
}

/* Desktop (≥ 1024px) */
@media (min-width: 1024px) {
  .container {
    padding: 2rem; /* 32px */
    max-width: 1280px;
  }
}
```

### Component Responsive Patterns

#### Sidebar → Bottom Nav
```tsx
// Desktop: Persistent sidebar (left)
// Mobile: Bottom navigation bar

<div className="
  hidden               // Hide on mobile
  lg:flex              // Show on desktop (≥1024px)
  lg:w-64              // Width 256px
  lg:flex-col 
  lg:fixed 
  lg:inset-y-0
">
  <Sidebar />
</div>

<div className="
  fixed 
  bottom-0 
  left-0 
  right-0 
  lg:hidden            // Hide on desktop
  h-16 
  bg-white 
  border-t
">
  <MobileNav />
</div>
```

#### Form Layout
```tsx
// Mobile: Single column
// Desktop: Two columns

<div className="
  grid 
  grid-cols-1          // 1 column on mobile
  lg:grid-cols-2       // 2 columns on desktop
  gap-4
">
  <FormField name="nama_lomba" />
  <FormField name="nama_tim" />
  <FormField name="bidang_id" />
  <FormField name="tingkatan_id" />
</div>
```

#### Table → Cards
```tsx
// Desktop: Data table
// Mobile: Card list

{/* Desktop Table */}
<div className="hidden lg:block">
  <DataTable columns={columns} data={data} />
</div>

{/* Mobile Cards */}
<div className="lg:hidden space-y-4">
  {data.map((item) => (
    <Card key={item.id}>
      <CardHeader>
        <CardTitle>{item.nama_lomba}</CardTitle>
        <CardDescription>{item.user.nama}</CardDescription>
      </CardHeader>
      <CardContent>
        <dl className="space-y-1">
          <div>
            <dt className="text-sm text-muted-foreground">Tingkatan:</dt>
            <dd>{item.tingkatan.nama}</dd>
          </div>
          <div>
            <dt className="text-sm text-muted-foreground">Status:</dt>
            <dd><StatusBadge status={item.status} /></dd>
          </div>
        </dl>
      </CardContent>
      <CardFooter>
        <Button onClick={() => navigate(`/pengajuan/${item.id}`)}>
          Lihat Detail
        </Button>
      </CardFooter>
    </Card>
  ))}
</div>
```

---

## 10. Performance Optimization

### Code Splitting (Lazy Loading)
```typescript
// router.tsx
import { lazy, Suspense } from 'react';
import { LoadingSpinner } from '@/components/shared';

const LoginPage = lazy(() => import('@/app/(auth)/login/page'));
const MahasiswaDashboard = lazy(() => import('@/app/(mahasiswa)/dashboard/page'));
const PengajuanForm = lazy(() => import('@/app/(mahasiswa)/pengajuan/new/page'));

// Wrap with Suspense
<Suspense fallback={<LoadingSpinner />}>
  <Routes>
    <Route path="/login" element={<LoginPage />} />
    <Route path="/mahasiswa/dashboard" element={<MahasiswaDashboard />} />
    {/* ... */}
  </Routes>
</Suspense>
```

### Image Optimization
```typescript
// Use WebP format, lazy loading, srcset for responsive
<img
  src="/logo.webp"
  srcSet="/logo-sm.webp 480w, /logo-md.webp 768w, /logo-lg.webp 1024w"
  sizes="(max-width: 480px) 100vw, (max-width: 768px) 50vw, 33vw"
  alt="SIMPRESMA Logo"
  loading="lazy"
  decoding="async"
/>
```

### Memoization
```typescript
// Expensive calculations
const sksCalculation = useMemo(() => {
  return selectedMK.reduce((sum, mk) => sum + mk.sks, 0);
}, [selectedMK]);

// Callbacks
const handleMKToggle = useCallback((mk) => {
  setSelectedMK((prev) => 
    prev.some((m) => m.id === mk.id)
      ? prev.filter((m) => m.id !== mk.id)
      : [...prev, mk]
  );
}, []);
```

### Virtualization (Large Lists)
```typescript
// For tables with 100+ rows
import { useVirtualizer } from '@tanstack/react-virtual';

const VirtualTable = ({ data }) => {
  const parentRef = useRef(null);
  
  const virtualizer = useVirtualizer({
    count: data.length,
    getScrollElement: () => parentRef.current,
    estimateSize: () => 60, // Row height
  });

  return (
    <div ref={parentRef} style={{ height: '600px', overflow: 'auto' }}>
      <div style={{ height: `${virtualizer.getTotalSize()}px` }}>
        {virtualizer.getVirtualItems().map((virtualRow) => (
          <TableRow key={virtualRow.index}>
            {/* Render row */}
          </TableRow>
        ))}
      </div>
    </div>
  );
};
```

---

## 11. Error Handling & User Feedback

### Error Boundary
```typescript
// components/shared/ErrorBoundary.tsx
class ErrorBoundary extends React.Component {
  state = { hasError: false };

  static getDerivedStateFromError(error) {
    return { hasError: true };
  }

  componentDidCatch(error, errorInfo) {
    console.error('Error caught by boundary:', error, errorInfo);
  }

  render() {
    if (this.state.hasError) {
      return (
        <div className="flex flex-col items-center justify-center h-screen">
          <h1 className="text-2xl font-bold mb-4">Oops! Terjadi kesalahan.</h1>
          <p className="text-muted-foreground mb-4">
            Silakan refresh halaman atau hubungi admin jika masalah berlanjut.
          </p>
          <Button onClick={() => window.location.reload()}>
            Refresh Halaman
          </Button>
        </div>
      );
    }

    return this.props.children;
  }
}
```

### Toast Notifications (Sonner)
```typescript
// Success
toast.success('Pengajuan berhasil disubmit!', {
  description: 'Silakan cek status di halaman Pengajuan Saya.',
  duration: 5000,
});

// Error
toast.error('Gagal submit pengajuan', {
  description: 'Total SKS harus antara 2-3 SKS.',
  action: {
    label: 'Perbaiki',
    onClick: () => navigate('/mahasiswa/pengajuan/new'),
  },
});

// Warning
toast.warning('Sertifikat belum diupload', {
  description: 'Link sertifikat wajib diisi.',
});

// Info
toast.info('Verifikasi sedang diproses', {
  description: 'Estimasi waktu: 1-2 hari kerja.',
});

// Loading (promise)
toast.promise(
  submitPengajuan(data),
  {
    loading: 'Mengirim pengajuan...',
    success: 'Pengajuan berhasil disubmit!',
    error: 'Gagal submit pengajuan.',
  }
);
```

### Loading States
```typescript
// Skeleton loading (better than spinner for perceived performance)
import { Skeleton } from '@/components/ui/skeleton';

{isLoading ? (
  <div className="space-y-4">
    <Skeleton className="h-12 w-full" />
    <Skeleton className="h-12 w-full" />
    <Skeleton className="h-12 w-full" />
  </div>
) : (
  <DataTable data={data} />
)}

// Button loading state
<Button disabled={isSubmitting}>
  {isSubmitting && <Loader2 className="mr-2 h-4 w-4 animate-spin" />}
  {isSubmitting ? 'Mengirim...' : 'Submit'}
</Button>
```

### HTTP Error Handling
```typescript
// Custom hook for API errors
const useApiError = (error: any) => {
  useEffect(() => {
    if (!error) return;

    const status = error.response?.status;
    const message = error.response?.data?.message;

    switch (status) {
      case 400:
        toast.error('Permintaan tidak valid.');
        break;
      case 401:
        toast.error('Sesi telah berakhir. Silakan login kembali.');
        // Auto logout handled by axios interceptor
        break;
      case 403:
        toast.error('Anda tidak memiliki akses ke resource ini.');
        navigate('/');
        break;
      case 404:
        toast.error('Data tidak ditemukan.');
        break;
      case 422:
        // Validation errors
        const errors = error.response?.data?.errors;
        if (errors) {
          Object.values(errors).flat().forEach((msg) => {
            toast.error(msg as string);
          });
        }
        break;
      case 500:
        toast.error('Terjadi kesalahan server. Silakan coba lagi nanti.');
        break;
      default:
        toast.error(message || 'Terjadi kesalahan. Silakan coba lagi.');
    }
  }, [error, navigate]);
};
```

---

## 12. Accessibility (WCAG 2.1 Level AA)

### Keyboard Navigation
```typescript
// All interactive elements must be keyboard accessible
<Button 
  onClick={handleSubmit}
  onKeyDown={(e) => {
    if (e.key === 'Enter' || e.key === ' ') {
      handleSubmit();
    }
  }}
>
  Submit
</Button>

// Focus visible
/* globals.css */
*:focus-visible {
  outline: 2px solid hsl(var(--primary));
  outline-offset: 2px;
}
```

### ARIA Labels
```typescript
// Screen reader support
<button 
  aria-label="Terima pengajuan dari Mahasiswa SI"
  onClick={handleTerima}
>
  <CheckCircle className="h-4 w-4" />
</button>

// Form labels
<Label htmlFor="nama_lomba">
  Nama Lomba <span aria-label="wajib diisi">*</span>
</Label>
<Input 
  id="nama_lomba" 
  aria-required="true"
  aria-describedby="nama_lomba_error"
/>
<span id="nama_lomba_error" role="alert" className="text-sm text-destructive">
  {errors.nama_lomba?.message}
</span>

// Loading states
<div role="status" aria-live="polite">
  {isLoading ? 'Memuat data...' : `${data.length} pengajuan ditemukan`}
</div>
```

### Color Contrast
```typescript
// Ensure minimum contrast ratio 4.5:1 for text
// Tailwind config (tailwind.config.ts)
export default {
  theme: {
    extend: {
      colors: {
        primary: {
          DEFAULT: 'hsl(221, 83%, 53%)', // #2563eb (contrast ratio: 4.6:1)
          foreground: 'hsl(0, 0%, 100%)', // white text on primary
        },
        destructive: {
          DEFAULT: 'hsl(0, 84%, 60%)', // #ef4444 (contrast ratio: 4.5:1)
          foreground: 'hsl(0, 0%, 100%)',
        },
      },
    },
  },
};
```

### Touch Targets
```typescript
// Minimum 44x44px tap target (WCAG 2.5.5)
<Button size="icon" className="h-11 w-11"> {/* 44px */}
  <TrashIcon className="h-4 w-4" />
</Button>

// Mobile: Increase tap area
<button className="
  p-3            // 12px padding
  min-h-[44px]   // Minimum height
  min-w-[44px]   // Minimum width
">
  Delete
</button>
```

---

## 13. Testing Strategy

### Unit Tests (Vitest)
```typescript
// lib/utils/validation.test.ts
import { describe, it, expect } from 'vitest';
import { isValidSKS, formatDate } from './validation';

describe('isValidSKS', () => {
  it('should return true for valid SKS range', () => {
    expect(isValidSKS(3, 2, 4)).toBe(true);
  });

  it('should return false for SKS below range', () => {
    expect(isValidSKS(1, 2, 4)).toBe(false);
  });

  it('should return false for SKS above range', () => {
    expect(isValidSKS(5, 2, 4)).toBe(false);
  });
});
```

### Component Tests (Testing Library)
```typescript
// components/forms/PengajuanForm.test.tsx
import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import { PengajuanForm } from './PengajuanForm';

describe('PengajuanForm', () => {
  it('should display SKS calculator when tingkatan and tahapan selected', async () => {
    render(<PengajuanForm />);
    
    const tingkatanSelect = screen.getByLabelText('Tingkatan Lomba');
    const tahapanSelect = screen.getByLabelText('Tahapan');
    
    fireEvent.change(tingkatanSelect, { target: { value: '1' } });
    fireEvent.change(tahapanSelect, { target: { value: '2' } });
    
    await waitFor(() => {
      expect(screen.getByText(/Rentang Konversi SKS/i)).toBeInTheDocument();
      expect(screen.getByText(/2 - 3 SKS/i)).toBeInTheDocument();
    });
  });

  it('should show validation error for invalid URL', async () => {
    render(<PengajuanForm />);
    
    const linkInput = screen.getByLabelText('Link Sertifikat');
    fireEvent.change(linkInput, { target: { value: 'not-a-url' } });
    fireEvent.blur(linkInput);
    
    await waitFor(() => {
      expect(screen.getByText(/URL tidak valid/i)).toBeInTheDocument();
    });
  });
});
```

### Integration Tests
```typescript
// e2e/pengajuan-flow.test.tsx
import { describe, it, expect } from 'vitest';
import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import { App } from '@/App';

describe('Pengajuan Flow (E2E)', () => {
  it('should complete full pengajuan submission flow', async () => {
    render(<App />);
    
    // Login
    fireEvent.change(screen.getByLabelText('Email'), {
      target: { value: 'mhs.si@test.com' },
    });
    fireEvent.change(screen.getByLabelText('Password'), {
      target: { value: 'password' },
    });
    fireEvent.click(screen.getByText('Login'));
    
    await waitFor(() => {
      expect(screen.getByText('Dashboard')).toBeInTheDocument();
    });
    
    // Navigate to form
    fireEvent.click(screen.getByText('Ajukan Prestasi'));
    
    // Fill form (Step 1)
    fireEvent.change(screen.getByLabelText('Nama Lomba'), {
      target: { value: 'Gemastik XVII' },
    });
    // ... fill all fields
    
    // Submit
    fireEvent.click(screen.getByText('Submit Pengajuan'));
    
    await waitFor(() => {
      expect(screen.getByText(/berhasil disubmit/i)).toBeInTheDocument();
    });
  });
});
```

---

## 14. Environment Variables

### .env.example
```bash
# API Configuration
VITE_API_BASE_URL=http://localhost:8000/api

# App Configuration
VITE_APP_NAME=SIMPRESMA
VITE_APP_VERSION=1.0.0

# Feature Flags
VITE_ENABLE_DARK_MODE=true
VITE_ENABLE_PWA=false

# Analytics (optional)
VITE_GA_MEASUREMENT_ID=
```

### .env.local (development)
```bash
VITE_API_BASE_URL=http://localhost:8000/api
VITE_APP_NAME=SIMPRESMA (Dev)
VITE_ENABLE_DARK_MODE=true
```

### .env.production
```bash
VITE_API_BASE_URL=https://api-simpresma.fik.unej.ac.id/api
VITE_APP_NAME=SIMPRESMA
VITE_ENABLE_DARK_MODE=true
VITE_ENABLE_PWA=true
```

---

## 15. Deployment

### Build Configuration
```typescript
// vite.config.ts
import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import path from 'path';

export default defineConfig({
  plugins: [react()],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './src'),
    },
  },
  build: {
    outDir: 'dist',
    sourcemap: true,
    rollupOptions: {
      output: {
        manualChunks: {
          'react-vendor': ['react', 'react-dom', 'react-router-dom'],
          'ui-vendor': ['@radix-ui/react-dialog', '@radix-ui/react-dropdown-menu'],
          'chart-vendor': ['recharts'],
        },
      },
    },
  },
  server: {
    port: 5173,
    proxy: {
      '/api': {
        target: 'http://localhost:8000',
        changeOrigin: true,
      },
    },
  },
});
```

### Build Command
```bash
# Development
npm run dev

# Production build
npm run build

# Preview production build
npm run preview
```

### Deployment Targets
```bash
# Vercel (recommended for frontend)
vercel --prod

# Netlify
netlify deploy --prod

# Cloudflare Pages
wrangler pages publish dist

# Static hosting (Nginx)
npm run build
# Copy dist/ to /var/www/simpresma-frontend/
```

---

## 16. Browser DevTools & Debugging

### React DevTools Extension
- Install React DevTools Chrome/Firefox extension
- Component tree inspector
- Props/state viewer
- Performance profiler

### Redux DevTools (for Zustand)
```typescript
// stores/authStore.ts
import { devtools } from 'zustand/middleware';

export const useAuthStore = create<AuthState>()(
  devtools(
    persist(
      (set) => ({
        // ... store implementation
      }),
      { name: 'simpresma_user' }
    ),
    { name: 'AuthStore' } // Shows in Redux DevTools
  )
);
```

### Console Logging Strategy
```typescript
// lib/utils/logger.ts
const isDev = import.meta.env.DEV;

export const logger = {
  info: (...args: any[]) => isDev && console.log('[INFO]', ...args),
  warn: (...args: any[]) => isDev && console.warn('[WARN]', ...args),
  error: (...args: any[]) => console.error('[ERROR]', ...args), // Always log errors
  api: (method: string, url: string, data?: any) => 
    isDev && console.log(`[API ${method}]`, url, data),
};

// Usage
logger.api('POST', '/api/mahasiswa/pengajuan', formData);
logger.error('Failed to submit', error);
```

---

## 17. Documentation & Comments

### Code Comments Policy
```typescript
// ✅ Good: Explain WHY, not WHAT
// Lock nilai to snapshot karena requirement bisnis: 
// Tendik tidak boleh mengubah nilai dari matriks yang sudah di-snapshot
const isValidNilai = nilaiPerMK.every(
  (item) => item.huruf_nilai === pengajuan.snapshot_huruf_nilai
);

// ❌ Bad: Redundant comment
// Loop through nilaiPerMK array
nilaiPerMK.forEach((item) => {
  // ...
});

// ✅ Good: Complex logic explanation
// Matriks lookup harus dilakukan setiap kali tingkatan/tahapan berubah
// karena kombinasi tertentu tidak valid (min_sks = NULL)
// dan mahasiswa harus tahu rentang SKS sebelum memilih mata kuliah
useEffect(() => {
  if (tingkatan_id && tahapan_id) {
    fetchMatriks(tingkatan_id, tahapan_id);
  }
}, [tingkatan_id, tahapan_id]);
```

### JSDoc for Complex Functions
```typescript
/**
 * Validates if total SKS from selected MK is within matrix range.
 * 
 * @param selectedMK - Array of selected mata kuliah objects
 * @param minSKS - Minimum SKS from matriks (snapshot)
 * @param maxSKS - Maximum SKS from matriks (snapshot)
 * @returns Object with validation result and error message
 * 
 * @example
 * ```ts
 * const result = validateSKSRange(
 *   [{ sks: 2 }, { sks: 3 }],
 *   2,
 *   4
 * );
 * // result = { isValid: false, error: 'Total SKS (5) melebihi maksimum (4)' }
 * ```
 */
export function validateSKSRange(
  selectedMK: MataKuliah[],
  minSKS: number,
  maxSKS: number
): { isValid: boolean; error?: string } {
  const totalSKS = selectedMK.reduce((sum, mk) => sum + mk.sks, 0);
  
  if (totalSKS < minSKS) {
    return { 
      isValid: false, 
      error: `Total SKS (${totalSKS}) kurang dari minimum (${minSKS})` 
    };
  }
  
  if (totalSKS > maxSKS) {
    return { 
      isValid: false, 
      error: `Total SKS (${totalSKS}) melebihi maksimum (${maxSKS})` 
    };
  }
  
  return { isValid: true };
}
```

---

## 18. Security Considerations

### XSS Prevention
```typescript
// Never use dangerouslySetInnerHTML without sanitization
import DOMPurify from 'dompurify';

const SafeHTML = ({ html }: { html: string }) => {
  const sanitized = DOMPurify.sanitize(html);
  return <div dangerouslySetInnerHTML={{ __html: sanitized }} />;
};

// Use text content instead when possible
<div>{userInput}</div> // Safe (React escapes by default)
```

### Token Security
```typescript
// Store token in localStorage (acceptable for SPA with short token lifetime)
// DO NOT store in cookies without HttpOnly flag

// Token expiry check (optional, backend handles via 401)
const isTokenExpired = (token: string) => {
  try {
    const payload = JSON.parse(atob(token.split('.')[1]));
    return payload.exp * 1000 < Date.now();
  } catch {
    return true;
  }
};

// Logout on token expiry
useEffect(() => {
  const token = localStorage.getItem('simpresma_token');
  if (token && isTokenExpired(token)) {
    useAuthStore.getState().logout();
    navigate('/login');
  }
}, [navigate]);
```

### CSRF Protection
```typescript
// Not needed for Sanctum token-based auth (no cookies)
// Backend Laravel already handles CSRF for session-based auth
```

### Content Security Policy (CSP)
```html
<!-- index.html -->
<meta http-equiv="Content-Security-Policy" content="
  default-src 'self';
  script-src 'self' 'unsafe-inline';
  style-src 'self' 'unsafe-inline';
  img-src 'self' data: https:;
  font-src 'self' data:;
  connect-src 'self' http://localhost:8000 https://api-simpresma.fik.unej.ac.id;
">
```

---

## 19. Monitoring & Analytics (Optional — Phase 2)

### Error Tracking (Sentry)
```typescript
// main.tsx
import * as Sentry from '@sentry/react';

if (import.meta.env.PROD) {
  Sentry.init({
    dsn: 'YOUR_SENTRY_DSN',
    integrations: [
      new Sentry.BrowserTracing(),
      new Sentry.Replay(),
    ],
    tracesSampleRate: 0.1,
    replaysSessionSampleRate: 0.1,
    replaysOnErrorSampleRate: 1.0,
  });
}
```

### Performance Monitoring (Web Vitals)
```typescript
// lib/utils/web-vitals.ts
import { onCLS, onFID, onLCP } from 'web-vitals';

function sendToAnalytics({ name, delta, id }) {
  console.log(`${name} (${id}): ${delta}ms`);
  // Send to analytics service (Google Analytics, etc)
}

onCLS(sendToAnalytics);
onFID(sendToAnalytics);
onLCP(sendToAnalytics);
```

---

## 20. Future Enhancements (Post-MVP)

### Phase 2 Features
- [ ] Dark mode toggle
- [ ] Export pengajuan to PDF/Excel
- [ ] Advanced filtering & search (fuzzy search)
- [ ] Notification center (in-app notifications)
- [ ] Email notifications (backend integration)
- [ ] File preview modal (for link_sertifikat, etc)
- [ ] Bulk actions (Verifikator: terima/tolak multiple)
- [ ] Audit log viewer (Wadek)
- [ ] User profile page (edit WhatsApp, email)
- [ ] Password change (when SSO not active)

### Phase 3 Features (PWA)
- [ ] Offline mode (service worker)
- [ ] Push notifications
- [ ] Install as app (mobile + desktop)
- [ ] Background sync

---

## APPENDIX A: Demo Accounts (from Backend Seeder)

| Role | Email | Password | Prodi | Notes |
|---|---|---|---|---|
| Mahasiswa SI | mhs.si@test.com | password | SI | - |
| Mahasiswa TI | mhs.ti@test.com | password | TI | - |
| Mahasiswa IF | mhs.if@test.com | password | IF | - |
| Verifikator SI | verif.si@test.com | password | - | Scope: SI only |
| Verifikator TI | verif.ti@test.com | password | - | Scope: TI only |
| Verifikator IF | verif.if@test.com | password | - | Scope: IF only |
| Tendik | tendik@test.com | password | - | All prodi |
| Wadek | wadek@test.com | password | - | Admin panel |
| Multi-role | multi@test.com | password | - | Roles: Verifikator SI + Tendik |

---

## APPENDIX B: API Endpoint Quick Reference

| Method | Endpoint | Auth | Role | Description |
|---|---|---|---|---|
| POST | /api/auth/login | ❌ | Public | Login |
| POST | /api/auth/logout | ✅ | All | Logout |
| GET | /api/auth/me | ✅ | All | Get user profile |
| GET | /api/ref/prodi | ✅ | All | List prodi |
| GET | /api/ref/tingkatan | ✅ | All | List tingkatan |
| GET | /api/ref/tahapan | ✅ | All | List tahapan |
| GET | /api/ref/bidang | ✅ | All | List bidang |
| GET | /api/ref/matriks | ✅ | All | Lookup matriks |
| GET | /api/ref/mata-kuliah | ✅ | All | List MK (filtered) |
| GET | /api/mahasiswa/pengajuan | ✅ | Mahasiswa | List pengajuan |
| POST | /api/mahasiswa/pengajuan | ✅ | Mahasiswa | Submit pengajuan |
| GET | /api/mahasiswa/pengajuan/:id | ✅ | Mahasiswa | Detail pengajuan |
| GET | /api/verifikator/pengajuan | ✅ | Verifikator | List pending (scope prodi) |
| GET | /api/verifikator/pengajuan/:id | ✅ | Verifikator | Detail pengajuan |
| POST | /api/verifikator/pengajuan/:id/terima | ✅ | Verifikator | Terima pengajuan |
| POST | /api/verifikator/pengajuan/:id/tolak | ✅ | Verifikator | Tolak pengajuan |
| GET | /api/tendik/pengajuan | ✅ | Tendik | List diterima (all prodi) |
| GET | /api/tendik/pengajuan/:id | ✅ | Tendik | Detail pengajuan |
| POST | /api/tendik/pengajuan/:id/finalisasi | ✅ | Tendik | Finalisasi konversi |
| GET | /api/wadek/matriks | ✅ | Wadek | List matriks |
| PUT | /api/wadek/matriks/:id | ✅ | Wadek | Update matriks |
| GET | /api/wadek/verifikator | ✅ | Wadek | List verifikator |
| POST | /api/wadek/verifikator | ✅ | Wadek | Assign verifikator |
| DELETE | /api/wadek/verifikator/:id | ✅ | Wadek | Cabut verifikator |
| GET | /api/wadek/bidang-mk | ✅ | Wadek | List mapping bidang-MK |
| POST | /api/wadek/bidang-mk | ✅ | Wadek | Add mapping |
| DELETE | /api/wadek/bidang-mk/:id | ✅ | Wadek | Delete mapping |
| GET | /api/dashboard/statistik | ✅ | All | Dashboard stats |
| GET | /api/direktori-verifikator | ✅ | All | List verifikator directory |

---

## STATUS DOKUMEN

- **Version:** 1.0.0
- **Last Updated:** 2026-09-01
- **Status:** FINAL & LOCKED
- **Next Steps:** Create `frontend-structure.md` and `frontend-tasks.md`

---

**END OF DOCUMENT**