# SIMPRESMA Frontend — Structure Document
## Folder Structure, Component Hierarchy, & Organization Patterns

> **STATUS:** FINAL & LOCKED — Blueprint arsitektur frontend SIMPRESMA.
> Mengacu pada `frontend-requirements.md`.

---

## 1. Root Project Structure

```
simpresma-frontend/
├── public/                           # Static assets
│   ├── logo-simpresma.svg
│   ├── logo-unej.png
│   ├── favicon.ico
│   └── robots.txt
├── src/                              # Source code
│   ├── app/                          # Pages (route-based)
│   ├── components/                   # React components
│   ├── lib/                          # Utilities, API, hooks
│   ├── stores/                       # Zustand stores
│   ├── types/                        # TypeScript types
│   ├── styles/                       # Global styles
│   ├── config/                       # App configuration
│   ├── App.tsx                       # Root component
│   ├── main.tsx                      # Entry point
│   └── router.tsx                    # React Router config
├── .env.example                      # Environment variables template
├── .env.local                        # Local environment (gitignored)
├── .eslintrc.json                    # ESLint config
├── .gitignore
├── .prettierrc                       # Prettier config
├── index.html                        # HTML entry point
├── package.json
├── postcss.config.js                 # PostCSS config (Tailwind)
├── tailwind.config.ts                # Tailwind CSS config
├── tsconfig.json                     # TypeScript config
├── tsconfig.node.json                # TypeScript config for Vite
├── vite.config.ts                    # Vite build config
└── README.md
```

---

## 2. Detailed `src/` Structure

### 2.1 Pages (`src/app/`)

```
src/app/
├── (auth)/                           # Public routes (no auth required)
│   └── login/
│       └── page.tsx                  # Login page
│
├── (mahasiswa)/                      # Mahasiswa role routes
│   ├── dashboard/
│   │   └── page.tsx                  # Dashboard mahasiswa
│   └── pengajuan/
│       ├── page.tsx                  # List pengajuan (table/cards)
│       ├── new/
│       │   └── page.tsx              # Form submit pengajuan (multi-step)
│       └── [id]/
│           └── page.tsx              # Detail pengajuan (view only)
│
├── (verifikator)/                    # Verifikator role routes
│   ├── dashboard/
│   │   └── page.tsx                  # Dashboard verifikator
│   └── pengajuan/
│       ├── page.tsx                  # List pending (scope prodi)
│       └── [id]/
│           └── page.tsx              # Detail + terima/tolak actions
│
├── (tendik)/                         # Tendik role routes
│   ├── dashboard/
│   │   └── page.tsx                  # Dashboard tendik
│   └── pengajuan/
│       ├── page.tsx                  # List diterima (all prodi)
│       └── [id]/
│           └── page.tsx              # Detail + finalisasi form
│
├── (wadek)/                          # Wadek role routes
│   ├── dashboard/
│   │   └── page.tsx                  # Dashboard wadek
│   ├── matriks/
│   │   └── page.tsx                  # CRUD matriks konversi
│   ├── verifikator/
│   │   └── page.tsx                  # Manage tim verifikator
│   └── bidang-mk/
│       └── page.tsx                  # Manage mapping bidang-MK
│
└── (shared)/                         # Shared pages (all roles)
    └── direktori-verifikator/
        └── page.tsx                  # Read-only direktori verifikator
```

**Naming Convention:**
- Folder dengan `()` = route group (tidak muncul di URL path)
- `page.tsx` = route component
- `[id]` = dynamic route parameter

---

### 2.2 Components (`src/components/`)

```
src/components/
├── ui/                               # Shadcn/ui base components (atomic)
│   ├── button.tsx
│   ├── input.tsx
│   ├── label.tsx
│   ├── select.tsx
│   ├── textarea.tsx
│   ├── checkbox.tsx
│   ├── radio-group.tsx
│   ├── switch.tsx
│   ├── dialog.tsx
│   ├── dropdown-menu.tsx
│   ├── popover.tsx
│   ├── tooltip.tsx
│   ├── card.tsx
│   ├── table.tsx
│   ├── badge.tsx
│   ├── alert.tsx
│   ├── skeleton.tsx
│   ├── toast.tsx
│   ├── tabs.tsx
│   ├── accordion.tsx
│   ├── separator.tsx
│   └── scroll-area.tsx
│
├── layouts/                          # Layout components
│   ├── AppLayout.tsx                 # Main wrapper (sidebar + header + content)
│   ├── Sidebar.tsx                   # Desktop sidebar (persistent, ≥1024px)
│   ├── MobileNav.tsx                 # Bottom navigation (mobile, <1024px)
│   ├── Header.tsx                    # Top header (user menu, notifications)
│   ├── ProtectedRoute.tsx            # Auth guard wrapper
│   └── RoleRoute.tsx                 # Role-specific route guard
│
├── forms/                            # Complex form components
│   ├── PengajuanForm/                # Multi-step form pengajuan mahasiswa
│   │   ├── index.tsx                 # Main form orchestrator
│   │   ├── StepLombaInfo.tsx         # Step 1: Lomba info + SKS calculator
│   │   ├── StepDokumen.tsx           # Step 2: Dokumen links (URL only)
│   │   ├── StepMataKuliah.tsx        # Step 3: MK selection (checkboxes)
│   │   ├── StepPreview.tsx           # Step 4: Preview before submit
│   │   ├── StepIndicator.tsx         # Visual step progress indicator
│   │   └── SKSCalculator.tsx         # Real-time SKS calculator widget
│   ├── FinalisasiForm.tsx            # Tendik finalisasi form (nilai per MK)
│   ├── MatriksForm.tsx               # Wadek edit matriks form
│   ├── VerifikatorForm.tsx           # Wadek assign verifikator form
│   └── BidangMKForm.tsx              # Wadek mapping bidang-MK form
│
├── tables/                           # Table components
│   ├── DataTable.tsx                 # Generic reusable data table
│   ├── PengajuanTable.tsx            # Specialized pengajuan table
│   ├── MatriksTable.tsx              # Matriks konversi table (inline edit)
│   ├── VerifikatorTable.tsx          # Verifikator list table
│   ├── Pagination.tsx                # Pagination controls
│   └── TableSkeleton.tsx             # Skeleton loader for tables
│
├── charts/                           # Data visualization components
│   ├── StatistikChart.tsx            # Dashboard pie chart (prodi distribution)
│   ├── StatusBreakdownChart.tsx      # Bar chart (status breakdown)
│   └── ChartSkeleton.tsx             # Skeleton loader for charts
│
└── shared/                           # Shared/common components
    ├── StatsCard.tsx                 # Dashboard stats card (reusable)
    ├── StatusBadge.tsx               # Status badge (pending/diterima/dll)
    ├── RoleGuard.tsx                 # Conditional render by role
    ├── RoleSwitcher.tsx              # Multi-role dropdown switcher
    ├── EmptyState.tsx                # Empty state illustration + message
    ├── ErrorBoundary.tsx             # Error boundary wrapper
    ├── LoadingSpinner.tsx            # Loading spinner
    ├── ConfirmDialog.tsx             # Reusable confirmation dialog
    └── PageHeader.tsx                # Page title + breadcrumb component
```

**Organization Pattern:**
- `ui/` = Atomic, unstyled base components (dari Shadcn/ui)
- `layouts/` = Layout structure components
- `forms/` = Business logic forms (complex, multi-field)
- `tables/` = Data display components
- `charts/` = Data visualization
- `shared/` = Cross-cutting concerns, reusable UI patterns

---

### 2.3 Library (`src/lib/`)

```
src/lib/
├── api/                              # API client & endpoint functions
│   ├── client.ts                     # Axios instance + interceptors
│   ├── auth.api.ts                   # Auth endpoints (login, logout, me)
│   ├── ref.api.ts                    # Reference data endpoints
│   ├── mahasiswa.api.ts              # Mahasiswa endpoints
│   ├── verifikator.api.ts            # Verifikator endpoints
│   ├── tendik.api.ts                 # Tendik endpoints
│   ├── wadek.api.ts                  # Wadek endpoints
│   ├── shared.api.ts                 # Shared endpoints (dashboard, direktori)
│   └── types.ts                      # API request/response types
│
├── hooks/                            # Custom React hooks
│   ├── useAuth.ts                    # Auth hook (login, logout, user)
│   ├── useRole.ts                    # Role management hook
│   ├── usePengajuan.ts               # Pengajuan CRUD hooks (React Query)
│   ├── useMatriks.ts                 # Matriks lookup hook
│   ├── useRefData.ts                 # Reference data hooks (prodi, bidang, dll)
│   ├── useDebounce.ts                # Debounce utility hook
│   ├── useMediaQuery.ts              # Responsive breakpoint hook
│   └── useLocalStorage.ts            # LocalStorage sync hook
│
├── utils/                            # Utility functions
│   ├── cn.ts                         # className merge (clsx + tailwind-merge)
│   ├── format.ts                     # Date, number, currency formatting
│   ├── validation.ts                 # Custom validators (SKS range, URL, dll)
│   ├── constants.ts                  # App-wide constants
│   ├── logger.ts                     # Console logger (dev only)
│   └── helpers.ts                    # Misc helper functions
│
└── schemas/                          # Zod validation schemas
    ├── pengajuan.schema.ts           # Pengajuan form validation
    ├── finalisasi.schema.ts          # Finalisasi form validation
    ├── matriks.schema.ts             # Matriks form validation
    ├── verifikator.schema.ts         # Verifikator form validation
    ├── auth.schema.ts                # Login form validation
    └── common.schema.ts              # Shared/reusable schemas
```

**Organization Pattern:**
- `api/` = HTTP layer, endpoint definitions
- `hooks/` = Stateful logic, side effects
- `utils/` = Pure functions, no side effects
- `schemas/` = Data validation (Zod)

---

### 2.4 Stores (`src/stores/`)

```
src/stores/
├── authStore.ts                      # Auth state (user, token, login, logout)
├── roleStore.ts                      # Multi-role switcher state
└── uiStore.ts                        # UI state (sidebar, theme, notifications)
```

**Pattern:**
- Zustand store per domain
- Keep stores small and focused
- Persist only critical data (auth)

---

### 2.5 Types (`src/types/`)

```
src/types/
├── user.types.ts                     # User, Role, Prodi types
├── pengajuan.types.ts                # Pengajuan, Status types
├── matriks.types.ts                  # Matriks, Tingkatan, Tahapan, Bidang types
├── mata-kuliah.types.ts              # MataKuliah, BidangMataKuliah types
├── api.types.ts                      # Generic API response wrappers
└── index.ts                          # Barrel export
```

**Naming Convention:**
- `*.types.ts` = Type definitions
- `index.ts` = Re-export all types for convenience

---

### 2.6 Styles (`src/styles/`)

```
src/styles/
└── globals.css                       # Tailwind imports + custom global CSS
```

**Content:**
```css
@tailwind base;
@tailwind components;
@tailwind utilities;

/* Custom global styles */
@layer base {
  * {
    @apply border-border;
  }
  body {
    @apply bg-background text-foreground;
  }
}

@layer utilities {
  /* Custom utility classes */
}
```

---

### 2.7 Config (`src/config/`)

```
src/config/
├── routes.config.ts                  # Route path definitions
└── navigation.config.ts              # Sidebar menu config per role
```

**Example `routes.config.ts`:**
```typescript
export const routes = {
  public: {
    login: '/login',
  },
  mahasiswa: {
    dashboard: '/mahasiswa/dashboard',
    pengajuan: {
      list: '/mahasiswa/pengajuan',
      new: '/mahasiswa/pengajuan/new',
      detail: (id: number) => `/mahasiswa/pengajuan/${id}`,
    },
  },
  // ... other roles
} as const;
```

---

## 3. Component Hierarchy

### 3.1 App Component Tree

```
<App>
  ├── <Router>
  │   ├── <ProtectedRoute>                 # Auth guard
  │   │   ├── <RoleRoute role="mahasiswa"> # Role guard
  │   │   │   └── <AppLayout>              # Layout wrapper
  │   │   │       ├── <Sidebar />          # Desktop (≥1024px)
  │   │   │       ├── <MobileNav />        # Mobile (<1024px)
  │   │   │       ├── <Header />
  │   │   │       └── <main>
  │   │   │           └── {Page Component}
  │   │   │
  │   │   ├── <RoleRoute role="verifikator">
  │   │   │   └── <AppLayout>
  │   │   │       └── ...
  │   │   │
  │   │   └── ... (other roles)
  │   │
  │   └── <Route path="/login">
  │       └── <LoginPage />               # Public route
  │
  └── <Toaster />                          # Global toast notifications
```

---

### 3.2 Page Component Pattern

**Standard Page Structure:**
```tsx
// src/app/(mahasiswa)/dashboard/page.tsx

export default function MahasiswaDashboardPage() {
  return (
    <div className="space-y-6">
      <PageHeader
        title="Dashboard Mahasiswa"
        description="Ringkasan pengajuan prestasi Anda"
      />

      {/* Stats Cards */}
      <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
        <StatsCard title="Total Pengajuan" value={10} icon={FileText} />
        {/* ... more cards */}
      </div>

      {/* Charts */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <Card>
          <CardHeader>
            <CardTitle>Distribusi Status</CardTitle>
          </CardHeader>
          <CardContent>
            <StatusBreakdownChart data={statusData} />
          </CardContent>
        </Card>
      </div>

      {/* Recent Activity */}
      <Card>
        <CardHeader>
          <CardTitle>Aktivitas Terbaru</CardTitle>
        </CardHeader>
        <CardContent>
          <PengajuanTable data={recentPengajuan} />
        </CardContent>
      </Card>
    </div>
  );
}
```

---

### 3.3 Form Component Pattern

**Multi-Step Form Structure (Pengajuan):**
```tsx
// src/components/forms/PengajuanForm/index.tsx

export function PengajuanForm() {
  const [currentStep, setCurrentStep] = useState(1);
  const [formData, setFormData] = useState<PengajuanFormData>({});

  return (
    <div className="space-y-6">
      <StepIndicator currentStep={currentStep} totalSteps={4} />

      {currentStep === 1 && (
        <StepLombaInfo
          data={formData}
          onNext={(data) => {
            setFormData({ ...formData, ...data });
            setCurrentStep(2);
          }}
        />
      )}

      {currentStep === 2 && (
        <StepDokumen
          data={formData}
          onNext={(data) => {
            setFormData({ ...formData, ...data });
            setCurrentStep(3);
          }}
          onBack={() => setCurrentStep(1)}
        />
      )}

      {/* ... steps 3 & 4 */}
    </div>
  );
}
```

---

## 4. Import Path Rules

### 4.1 Path Aliases (tsconfig.json)

```json
{
  "compilerOptions": {
    "baseUrl": ".",
    "paths": {
      "@/*": ["src/*"],
      "@/components/*": ["src/components/*"],
      "@/lib/*": ["src/lib/*"],
      "@/types/*": ["src/types/*"],
      "@/stores/*": ["src/stores/*"],
      "@/config/*": ["src/config/*"],
      "@/styles/*": ["src/styles/*"]
    }
  }
}
```

### 4.2 Import Order Convention

```typescript
// 1. React & external libraries
import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { useQuery } from '@tanstack/react-query';

// 2. Internal absolute imports (alias)
import { Button } from '@/components/ui/button';
import { useAuth } from '@/lib/hooks/useAuth';
import type { User } from '@/types/user.types';

// 3. Relative imports (same directory)
import { StepIndicator } from './StepIndicator';

// 4. Styles (if any)
import './styles.css';
```

### 4.3 Barrel Exports (index.ts)

**Use barrel exports for:**
- `src/types/index.ts` (re-export all types)
- `src/lib/api/index.ts` (re-export all API functions)
- `src/components/ui/index.ts` (optional, for convenience)

**Example:**
```typescript
// src/types/index.ts
export * from './user.types';
export * from './pengajuan.types';
export * from './matriks.types';
export * from './mata-kuliah.types';
export * from './api.types';
```

**Usage:**
```typescript
// Instead of multiple imports
import { User } from '@/types/user.types';
import { Pengajuan } from '@/types/pengajuan.types';

// Use barrel export
import { User, Pengajuan } from '@/types';
```

---

## 5. File Naming Conventions

### 5.1 React Components

| Pattern | Example | Usage |
|---|---|---|
| PascalCase | `Button.tsx` | UI components |
| PascalCase | `DataTable.tsx` | Feature components |
| PascalCase | `AppLayout.tsx` | Layout components |
| PascalCase | `PengajuanForm/index.tsx` | Multi-file component (folder) |

### 5.2 Hooks

| Pattern | Example | Usage |
|---|---|---|
| camelCase + use prefix | `useAuth.ts` | Custom hooks |
| camelCase + use prefix | `usePengajuan.ts` | Feature hooks |

### 5.3 Utilities & Config

| Pattern | Example | Usage |
|---|---|---|
| camelCase | `format.ts` | Utility functions |
| camelCase | `validation.ts` | Validators |
| kebab-case | `routes.config.ts` | Config files |

### 5.4 Types

| Pattern | Example | Usage |
|---|---|---|
| kebab-case + .types | `user.types.ts` | Type definitions |
| kebab-case + .types | `pengajuan.types.ts` | Feature types |

### 5.5 Stores

| Pattern | Example | Usage |
|---|---|---|
| camelCase + Store suffix | `authStore.ts` | Zustand stores |
| camelCase + Store suffix | `roleStore.ts` | Feature stores |

### 5.6 API Files

| Pattern | Example | Usage |
|---|---|---|
| kebab-case + .api | `auth.api.ts` | API endpoint functions |
| kebab-case + .api | `mahasiswa.api.ts` | Feature API |

### 5.7 Schemas

| Pattern | Example | Usage |
|---|---|---|
| kebab-case + .schema | `pengajuan.schema.ts` | Zod schemas |
| kebab-case + .schema | `auth.schema.ts` | Validation schemas |

---

## 6. Component Composition Patterns

### 6.1 Compound Components

**For complex UI with internal state sharing:**

```typescript
// ✅ Good: Compound component pattern
<Tabs defaultValue="SI">
  <TabsList>
    <TabsTrigger value="SI">Sistem Informasi</TabsTrigger>
    <TabsTrigger value="TI">Teknologi Informasi</TabsTrigger>
  </TabsList>
  <TabsContent value="SI">
    <VerifikatorList prodiId={1} />
  </TabsContent>
  <TabsContent value="TI">
    <VerifikatorList prodiId={2} />
  </TabsContent>
</Tabs>

// ❌ Bad: Prop drilling
<Tabs
  defaultValue="SI"
  tabs={[
    { value: 'SI', label: 'Sistem Informasi', content: <VerifikatorList prodiId={1} /> },
    { value: 'TI', label: 'Teknologi Informasi', content: <VerifikatorList prodiId={2} /> },
  ]}
/>
```

### 6.2 Render Props

**For flexible rendering logic:**

```typescript
// ✅ Good: Render props for custom rendering
<DataTable
  data={pengajuanList}
  columns={[
    { key: 'nama_lomba', label: 'Lomba' },
    {
      key: 'status',
      label: 'Status',
      render: (value) => <StatusBadge status={value} />,
    },
  ]}
/>
```

### 6.3 Component Slots

**For layout flexibility:**

```typescript
// ✅ Good: Slots pattern
<Card>
  <CardHeader>
    <CardTitle>Pengajuan Saya</CardTitle>
    <CardDescription>Total: {totalPengajuan}</CardDescription>
  </CardHeader>
  <CardContent>
    <PengajuanTable data={data} />
  </CardContent>
  <CardFooter>
    <Button>Load More</Button>
  </CardFooter>
</Card>
```

---

## 7. State Management Strategy

### 7.1 State Location Decision Tree

```
User interacts with component
  ↓
Is state needed by multiple routes?
  ├─ YES → Zustand store (global state)
  └─ NO → Is state needed by sibling components?
      ├─ YES → React Context (localized global)
      └─ NO → Is state server data?
          ├─ YES → React Query (server state cache)
          └─ NO → useState/useReducer (local state)
```

### 7.2 State Examples

**Local State (Component-Only):**
```typescript
// ✅ Use useState for UI-only state
const [isOpen, setIsOpen] = useState(false);
const [currentStep, setCurrentStep] = useState(1);
```

**Server State (API Data):**
```typescript
// ✅ Use React Query for server data
const { data: pengajuanList, isLoading } = useQuery({
  queryKey: ['pengajuan-list'],
  queryFn: mahasiswaApi.getPengajuanList,
});
```

**Global State (Cross-Route):**
```typescript
// ✅ Use Zustand for app-wide state
const { user, logout } = useAuthStore();
const { currentRole, setCurrentRole } = useRoleStore();
```

**Form State:**
```typescript
// ✅ Use React Hook Form for complex forms
const form = useForm<PengajuanFormData>({
  resolver: zodResolver(pengajuanSchema),
  defaultValues: { ... },
});
```

---

## 8. Type Organization

### 8.1 Type Definition Locations

| Type | Location | Example |
|---|---|---|
| Component Props | Same file as component | `ButtonProps` in `Button.tsx` |
| API Request/Response | `lib/api/types.ts` | `LoginRequest`, `LoginResponse` |
| Domain Models | `types/*.types.ts` | `User`, `Pengajuan`, `Matriks` |
| Utility Types | `types/api.types.ts` | `ApiResponse<T>`, `PaginatedData<T>` |

### 8.2 Type Naming Conventions

```typescript
// ✅ Good: Descriptive, specific names
type User = { ... };
type PengajuanFormData = { ... };
type ApiResponse<T> = { success: boolean; data: T };

// ❌ Bad: Generic, ambiguous names
type Data = { ... };
type Props = { ... };
type Response = { ... };
```

### 8.3 Type Exports

```typescript
// ✅ Good: Export types explicitly
export type User = { ... };
export type UserRole = 'mahasiswa' | 'verifikator' | 'tendik' | 'wadek';

// ✅ Good: Use type-only imports
import type { User, UserRole } from '@/types';

// ❌ Bad: Mix value and type imports
import { User, UserRole, someFunction } from '@/types';
```

---

## 9. Testing File Structure

```
src/
├── components/
│   ├── ui/
│   │   ├── button.tsx
│   │   └── button.test.tsx          # Unit test (co-located)
│   ├── forms/
│   │   ├── PengajuanForm/
│   │   │   ├── index.tsx
│   │   │   ├── index.test.tsx       # Integration test
│   │   │   └── __mocks__/           # Mocks for testing
│   │   │       └── mockFormData.ts
├── lib/
│   ├── utils/
│   │   ├── validation.ts
│   │   └── validation.test.ts       # Unit test (co-located)
└── __tests__/                        # E2E tests (separate folder)
    └── e2e/
        ├── login-flow.test.tsx
        └── pengajuan-flow.test.tsx
```

**Convention:**
- Unit tests: co-located with source file (`.test.tsx`)
- Integration tests: co-located with feature component
- E2E tests: separate `__tests__/e2e/` folder

---

## 10. Documentation Structure

```
frontend-docs/
├── frontend-requirements.md          # This file (already created)
├── frontend-structure.md             # This file (current)
├── frontend-tasks.md                 # Next file (checklist)
├── frontend-code-patterns.md         # Optional (code snippets reference)
└── README.md                         # Quick start guide
```

---

## 11. Git & Version Control

### 11.1 .gitignore

```
# Dependencies
node_modules/

# Production build
dist/
build/

# Environment variables
.env.local
.env.*.local

# IDE
.vscode/
.idea/

# OS
.DS_Store
Thumbs.db

# Logs
*.log
npm-debug.log*

# Testing
coverage/

# Misc
.cache/
```

### 11.2 Branch Naming

```
main                                  # Production
develop                               # Development
feature/F1-setup-project              # Feature branch (follows task ID)
feature/F4-mahasiswa-form             # Feature branch
bugfix/fix-sks-calculator             # Bug fix
hotfix/critical-auth-bug              # Hotfix for production
```

---

## 12. Build & Bundle Structure

### 12.1 Production Build Output (`dist/`)

```
dist/
├── assets/
│   ├── index-[hash].js               # Main bundle
│   ├── index-[hash].css              # Styles bundle
│   ├── react-vendor-[hash].js        # React chunk
│   ├── ui-vendor-[hash].js           # UI components chunk
│   ├── chart-vendor-[hash].js        # Charts chunk
│   └── logo-simpresma-[hash].svg
├── index.html                        # Entry HTML
└── robots.txt
```

### 12.2 Bundle Splitting Strategy

```typescript
// vite.config.ts
export default defineConfig({
  build: {
    rollupOptions: {
      output: {
        manualChunks: {
          'react-vendor': ['react', 'react-dom', 'react-router-dom'],
          'ui-vendor': ['@radix-ui/react-dialog', '@radix-ui/react-dropdown-menu'],
          'chart-vendor': ['recharts'],
          'form-vendor': ['react-hook-form', 'zod'],
          'query-vendor': ['@tanstack/react-query'],
        },
      },
    },
  },
});
```

**Goal:**
- Initial bundle < 200 KB (gzipped)
- Lazy load routes (code splitting per page)
- Vendor chunks cached separately

---

## 13. Accessibility File Organization

### 13.1 ARIA Labels & Screen Reader Text

**Pattern:**
- Co-locate screen reader text with component
- Use semantic HTML first, ARIA as supplement

```typescript
// ✅ Good: Co-located accessibility
<button
  onClick={handleTerima}
  aria-label="Terima pengajuan dari Mahasiswa SI dengan nama lomba Gemastik XVII"
>
  <CheckCircle className="h-4 w-4" />
  <span className="sr-only">Terima</span> {/* Screen reader only */}
</button>
```

### 13.2 Keyboard Navigation

**Focus management:**
- Trap focus inside modals/dialogs
- Restore focus after modal close
- Skip links for keyboard users

```typescript
// ✅ Good: Focus trap in dialog
<Dialog onOpenChange={(open) => {
  if (!open) {
    // Restore focus to trigger element
    triggerRef.current?.focus();
  }
}}>
  {/* Dialog content */}
</Dialog>
```

---

## 14. Performance Optimization Structure

### 14.1 Code Splitting Locations

```
src/app/
├── (mahasiswa)/
│   └── pengajuan/
│       └── new/
│           └── page.tsx              # Lazy load (heavy form)
└── (wadek)/
    └── matriks/
        └── page.tsx                  # Lazy load (heavy table)
```

**Pattern:**
```typescript
// router.tsx
const PengajuanFormPage = lazy(() => import('@/app/(mahasiswa)/pengajuan/new/page'));

<Route
  path="/mahasiswa/pengajuan/new"
  element={
    <Suspense fallback={<LoadingSpinner />}>
      <PengajuanFormPage />
    </Suspense>
  }
/>
```

### 14.2 Asset Optimization

```
public/
├── logo-simpresma.svg                # SVG for vector logos
├── logo-unej.webp                    # WebP for raster images
└── favicon.ico                       # Small, no optimization needed
```

---

## 15. Environment-Specific Structure

### 15.1 Environment Files

```
.env.example                          # Template (committed)
.env.local                            # Development (gitignored)
.env.staging                          # Staging (gitignored)
.env.production                       # Production (gitignored)
```

### 15.2 Config Files per Environment

```typescript
// src/config/env.ts
export const env = {
  apiBaseUrl: import.meta.env.VITE_API_BASE_URL,
  appName: import.meta.env.VITE_APP_NAME,
  isDev: import.meta.env.DEV,
  isProd: import.meta.env.PROD,
};

// Usage
import { env } from '@/config/env';
console.log(env.apiBaseUrl);
```

---

## STATUS DOKUMEN

- **Version:** 1.0.0
- **Last Updated:** 2026-09-01
- **Status:** FINAL & LOCKED
- **Next Steps:** Create `frontend-tasks.md` (phase-based checklist)

---

**END OF DOCUMENT**
