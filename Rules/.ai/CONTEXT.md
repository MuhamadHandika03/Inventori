
# Project Context — Untuk AI Assistant

> File ini WAJIB di-load pertama di setiap sesi coding dengan AI.

## 📋 Ringkasan Proyek

[Nama Proyek] adalah [jenis aplikasi: web app / API / SaaS / dll] yang digunakan oleh [target user] untuk [tujuan utama]. Aplikasi ini [value proposition — apa masalah yang diselesaikan].

**Status:** [Development / Staging / Production]
**Metode:** Agile Scrum (sprint 2 minggu)
**Completion:** [X]%

## 🛠️ Tech Stack

### Frontend
- **Framework:** [Next.js 14 / React 18 / Vue 3]
- **Bahasa:** TypeScript (strict mode aktif)
- **UI:** [Tailwind CSS / shadcn/ui / MUI]
- **Forms:** [React Hook Form + Zod]
- **State:** [TanStack Query / Zustand / Redux]

### Backend
- **Runtime:** [Node.js 20 / Bun / Deno]
- **API:** [Next.js API Routes / Express / Hono]
- **ORM:** [Prisma / Drizzle / TypeORM]
- **Auth:** [NextAuth.js / custom JWT]
- **Validation:** Zod (shared client-server)

### Database & Storage
- **Primary DB:** [PostgreSQL 15 / MySQL 8 / MongoDB]
- **Cache:** [Redis / Upstash]
- **File Storage:** [S3 / R2 / Supabase Storage]

### DevOps
- **Hosting:** [Vercel / Railway / AWS / VPS]
- **CI/CD:** [GitHub Actions]
- **Monitoring:** [Sentry / Logtail]
- **Testing:** [Vitest / Jest + Playwright]

## 🌐 Environments

| Env | URL | Branch | Auto Deploy |
|-----|-----|--------|-------------|
| Development | localhost:3000 | feature/* | ❌ Manual |
| Staging | staging.example.com | develop | ✅ Auto |
| Production | app.example.com | main | ⚠️ Manual approval |

## 📐 Prinsip Arsitektur

1. **Server-first** — Default Server Component, Client hanya jika perlu interaksi
2. **Service Layer Pattern** — Business logic di `src/services/`, component hanya UI
3. **Single Source of Truth** — DB adalah source of truth, state UI derived dari server state
4. **Type Safety End-to-End** — Zod untuk validasi, TypeScript untuk types
5. **API Versioning** — Semua API di `/api/v1/*`
6. **Mobile-First Responsive** — Design dari mobile ke desktop

## 📂 Struktur Folder Penting

```

src/
├── app/ # [Next.js App Router / pages router]
├── components/
│ ├── ui/ # Atomic (Button, Input, Card)
│ └── features/ # Feature-specific
├── services/ # Business logic — WAJIB lewat sini
├── lib/ # Utilities (db, auth, logger, validators)
├── hooks/ # Custom hooks
└── types/ # Shared types

prisma/schema.prisma # Database schema

````

## 🎯 Goal Saat Ini

**Sprint Goal:** "[satu kalimat outcome]"

**Yang harus difokuskan:**
- [Story 1]
- [Story 2]
- [Story 3]

Lihat detail di `PROGRESS.md`.
