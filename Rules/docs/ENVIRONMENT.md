
# Environment Strategy — Dev / Staging / Production

> Panduan setup & manage 3 environment secara profesional.

## 🎯 Tujuan

- **Isolasi:** Dev, staging, prod terpisah, tidak boleh tercampur
- **Mirroring:** Staging semirip mungkin dengan production
- **Safety:** Perubahan SELALU lewat staging sebelum production
- **Traceability:** Bisa trace perubahan dari PR → deploy → production

## 📊 Overview

```
┌─────────────────────────────────────────────────────┐
│              LOCAL DEVELOPMENT                       │
│                localhost:3000                         │
│  - Setiap developer setup sendiri                     │
│  - DB lokal (Docker atau install)                    │
│  - Bebas experiment                                  │
└──────────────────────┬──────────────────────────────┘
                       │ git push & PR ke develop
                       ↓
┌─────────────────────────────────────────────────────┐
│                   STAGING                            │
│           staging.example.com                        │
│  - Mirror production setup                           │
│  - Auto-deploy dari branch `develop`                 │
│  - Dipakai QA & demo                                 │
└──────────────────────┬──────────────────────────────┘
                       │ PR ke main + manual approval
                       ↓
┌─────────────────────────────────────────────────────┐
│                  PRODUCTION                          │
│              app.example.com                         │
│  - Real users, real data                             │
│  - Manual approval required                          │
│  - 24/7 monitoring                                   │
└─────────────────────────────────────────────────────┘
```

## 🛠️ Environment Specifications

### Development (Local)

**Tujuan:** Coding cepat, eksperimen, debug

**Setup:**

- Node.js 20 LTS
- PostgreSQL lokal (via Docker)
- File `.env.local` (di-gitignore)

**Karakteristics:**

- ✅ Hot reload, verbose logging
- ✅ Bebas install package
- ✅ Test data ada di DB lokal
- ❌ Tidak boleh diakses orang lain

**Commands:**

```bash
# Setup
docker run -d --name postgres-local -e POSTGRES_PASSWORD=dev -p 5432:5432 postgres:15
cp .env.example .env.local
npm install
npx prisma migrate dev
npm run dev
```

---

### Staging

**Tujuan:** Validasi sebelum production

**URL:** `https://staging.example.com`

**Setup:**

- Vercel project (staging branch)
- Neon **staging branch** (clone dari prod schema)
- Upstash Redis (small tier)
- Env: `.env.staging` di Vercel

**Karakteristik:**

- ✅ Auto-deploy dari `develop`
- ✅ Real-world-like data (anonymized)
- ✅ Same security headers
- ✅ Sentry project terpisah
- ⚠️ Public access (password-protect jika perlu)
- ❌ Tidak untuk production data asli

**Access:**

- Tim internal + QA
- Client (untuk UAT) — akses khusus

---

### Production

**Tujuan:** Serve real users

**URL:** `https://app.example.com`

**Setup:**

- Vercel (production deployment)
- Neon **production** (high-availability)
- Upstash Redis (production tier)
- Cloudflare R2 (file storage)
- Env: `.env.production` di Vercel (encrypted)

**Karakteristik:**

- ⚠️ Manual approval untuk deploy
- ✅ Auto-scaling
- ✅ CDN caching
- ✅ Error tracking & alerting
- ✅ Daily backup
- ✅ Strict — no experimental feature
- ❌ Tidak ada debug log

---

## 🌿 Branch → Environment Mapping

```
feature/*    ──→ Preview Deploy (per-PR URL)
    ↓ merged ke develop
develop      ──→ Staging (auto)
    ↓ PR + approval
main         ──→ Production (manual approval)
```

### Preview Deploys

Setiap PR otomatis dapat preview URL:

- Format: `[branch]-[hash].vercel.app`
- DB: shared preview branch atau isolated
- Berguna untuk: visual review, demo
- Auto-delete setelah PR closed

---

## 🔐 Environment Variables

### Naming Convention

```env
# Public (exposed ke client, WAJIB prefix NEXT_PUBLIC_)
NEXT_PUBLIC_APP_URL=https://app.example.com

# Private (server-side only)
DATABASE_URL=postgresql://...
JWT_SECRET=...
SENTRY_DSN=...
```

### Management

| Env        | Cara Set               | Cara Pakai               |
| ---------- | ---------------------- | ------------------------ |
| Local      | `.env.local` (manual)  | Di-load otomatis Next.js |
| Staging    | Vercel dashboard / CLI | Auto-injected saat build |
| Production | Vercel dashboard / CLI | Auto-injected saat build |

### Commands

```bash
# Set env di Vercel
vercel env add DATABASE_URL production
vercel env add JWT_SECRET production

# Pull ke local (untuk debugging)
vercel env pull .env.local
```

### ⚠️ PENTING

- **JANGAN** commit `.env` atau `.env.local` ke Git
- **JANGAN** share secrets antar environment
- **JANGAN** pakai production secret di staging
- **WAJIB** rotate secrets secara berkala (quarterly)

---

## 🗄️ Database Management

### Per Environment

| Env        | DB                        | Refresh           | Backup               |
| ---------- | ------------------------- | ----------------- | -------------------- |
| Local      | PostgreSQL lokal / Docker | Bebas             | ❌                   |
| Staging    | Neon staging branch       | Per awal sprint   | ✅ Daily             |
| Production | Neon production           | **JANGAN PERNAH** | ✅ Daily + on-demand |

### Migration Flow

```bash
# 1. Buat migration di local
npx prisma migrate dev --name add_user_avatar

# 2. Test di local
npm run dev

# 3. Commit migration files
git add prisma/migrations
git commit -m "feat(db): add avatar_url to users"

# 4. Push → CI jalan di test DB
# 5. Merge ke develop → auto migrate di staging
# 6. PR ke main → manual approval → migrate di production
```

### ⚠️ Production Migration Safety

- ✅ Migration SELALU reversible (tulis down script)
- ✅ Test migration di staging DULU
- ✅ Backup DB sebelum migrate
- ✅ Jalankan di low-traffic time (jam 2-4 pagi)
- ✅ Zero-downtime migration untuk schema besar
- ❌ JANGAN destructive migration langsung (ALTER TABLE DROP COLUMN, dst)

---

## 🚀 Deployment Workflow

### Step-by-Step

```
1. LOCAL
   Developer bikin feature/* branch, coding & test

2. PUSH & PR
   Push ke GitHub, buka PR: feature/* → develop

3. CI VALIDATION (GitHub Actions)
   ✅ Lint, type-check, unit test, build

4. PREVIEW DEPLOY
   Vercel auto-deploy preview URL

5. CODE REVIEW
   Min 1 approver

6. MERGE TO DEVELOP
   Auto-deploy ke STAGING

7. QA TESTING
   Manual test + E2E automated test

8. PR DEVELOP → MAIN
   Full CI suite + manual approval

9. PRODUCTION DEPLOY
   Auto-deploy + post-deploy smoke tests

10. MONITORING
    Pantau 30 menit pertama (error rate, response time)
```

### Hotfix (Production Emergency)

```bash
# 1. Branch dari main
git checkout main
git checkout -b hotfix/critical-bug

# 2. Fix & test
# ...

# 3. PR langsung ke main (skip develop)
git push origin hotfix/critical-bug

# 4. Setelah merge → deploy ke production

# 5. Cherry-pick ke develop
git checkout develop
git cherry-pick <commit-hash>
```

---

## ⏮️ Rollback Strategy

### Otomatis (Recommended)

- **Vercel:** keep last 10 deployments, instant rollback via dashboard/CLI
- **DB:** semua migration reversible

### Manual Process

```bash
# Rollback code
vercel rollback

# Rollback DB (jika migration gagal)
npx prisma migrate resolve --rolled-back [migration-name]
```

### Kapan Rollback?

- 🔴 Error rate > 5% setelah deploy
- 🔴 Critical functionality broken
- 🔴 Security vulnerability ditemukan
- 🔴 Performance degradation signifikan

---

## 📋 Environment Checklist

### Initial Setup

- [ ] Setup Vercel project (production + preview)
- [ ] Setup Neon DB (3 branches: dev/staging/production)
- [ ] Setup Upstash Redis
- [ ] Setup Sentry (2 projects: staging & production)
- [ ] Setup Logtail (2 sources)
- [ ] Setup Cloudflare R2 (bucket)
- [ ] Setup domain + DNS
- [ ] Setup SSL (auto via Vercel)
- [ ] Configure environment variables
- [ ] Setup GitHub branch protection

### Per-Sprint Routine

- [ ] Reset staging DB jika perlu
- [ ] Verify env vars masih valid
- [ ] Check backup ter-verifikasi
- [ ] Update Sentry release tags
