
# Deployment & CI/CD Guide

> Panduan pipeline deployment yang aman, automated, dan bisa di-trace.

## 🔄 Pipeline Overview

```
┌─────────────┐
│  Git Push   │
└──────┬──────┘
       ↓
┌──────────────────────┐
│ GitHub Actions (CI)  │
│ - Lint               │
│ - Type Check         │
│ - Unit Test          │
│ - Build              │
└──────┬───────────────┘
       ↓
   ┌───┴────┐
   ↓        ↓
[Pass]   [Fail] → Block merge
   ↓
┌──────────────────────┐
│   PR Created         │
└──────┬───────────────┘
       ↓
┌──────────────────────┐
│  Preview Deploy      │ (Vercel auto, per-PR)
└──────┬───────────────┘
       ↓ (PR merged)
┌──────────────────────┐
│ Auto Deploy Staging  │ (from develop)
└──────┬───────────────┘
       ↓
┌──────────────────────┐
│  QA + E2E Tests      │
└──────┬───────────────┘
       ↓ (PR develop → main)
┌──────────────────────┐
│  Manual Approval     │
└──────┬───────────────┘
       ↓
┌──────────────────────┐
│ Deploy Production    │
└──────┬───────────────┘
       ↓
┌──────────────────────┐
│ Post-Deploy Check    │
│ - Smoke tests        │
│ - Monitor 30 min     │
└──────────────────────┘
```

---

## 🤖 GitHub Actions Workflows

### File: `.github/workflows/ci.yml`

```yaml
name: CI

on:
  pull_request:
    branches: [main, develop]
  push:
    branches: [develop]

jobs:
  lint:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: actions/setup-node@v4
        with:
          node-version: 20
          cache: "npm"
      - run: npm ci
      - run: npm run lint
      - run: npm run format:check

  type-check:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: actions/setup-node@v4
        with:
          node-version: 20
          cache: "npm"
      - run: npm ci
      - run: npm run type-check

  test:
    runs-on: ubuntu-latest
    services:
      postgres:
        image: postgres:15
        env:
          POSTGRES_USER: test
          POSTGRES_PASSWORD: test
          POSTGRES_DB: test_db
        ports: ["5432:5432"]
    steps:
      - uses: actions/checkout@v4
      - uses: actions/setup-node@v4
        with:
          node-version: 20
          cache: "npm"
      - run: npm ci
      - run: npx prisma migrate deploy
        env:
          DATABASE_URL: postgresql://test:test@localhost:5432/test_db
      - run: npm run test:unit
      - run: npm run test:integration
      - uses: codecov/codecov-action@v3

  build:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: actions/setup-node@v4
        with:
          node-version: 20
          cache: "npm"
      - run: npm ci
      - run: npm run build
```

### File: `.github/workflows/deploy-production.yml`

```yaml
name: Deploy to Production

on:
  pull_request:
    branches: [main]
    types: [closed]

jobs:
  deploy:
    if: github.event.pull_request.merged == true
    runs-on: ubuntu-latest
    environment:
      name: production
      url: https://app.example.com
    steps:
      - uses: actions/checkout@v4
      - uses: amondnet/vercel-action@v25
        with:
          vercel-token: ${{ secrets.VERCEL_TOKEN }}
          vercel-org-id: ${{ secrets.VERCEL_ORG_ID }}
          vercel-project-id: ${{ secrets.VERCEL_PROJECT_ID }}
          vercel-args: "--prod"
      - run: npm run test:smoke
        env:
          APP_URL: https://app.example.com
      - uses: 8398a7/action-slack@v3
        with:
          status: ${{ job.status }}
          text: "Production deploy ${{ job.status }}"
        env:
          SLACK_WEBHOOK_URL: ${{ secrets.SLACK_WEBHOOK }}
        if: always()
```

---

## ⚙️ Vercel Configuration

### File: `vercel.json`

```json
{
  "buildCommand": "prisma generate && next build",
  "framework": "nextjs",
  "regions": ["sin1"],
  "headers": [
    {
      "source": "/(.*)",
      "headers": [
        { "key": "X-Content-Type-Options", "value": "nosniff" },
        { "key": "X-Frame-Options", "value": "DENY" },
        { "key": "X-XSS-Protection", "value": "1; mode=block" },
        {
          "key": "Referrer-Policy",
          "value": "strict-origin-when-cross-origin"
        },
        {
          "key": "Strict-Transport-Security",
          "value": "max-age=31536000; includeSubDomains; preload"
        },
        {
          "key": "Content-Security-Policy",
          "value": "default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' https: data:; connect-src 'self'"
        }
      ]
    }
  ]
}
```

---

## ✅ Pre-Deploy Checklist

Sebelum merge ke `main`:

### Code

- [ ] Lint passing
- [ ] Type-check passing
- [ ] Unit tests passing
- [ ] Integration tests passing
- [ ] E2E tests passing (kalau ada)
- [ ] Build successful
- [ ] Self-reviewed
- [ ] Approved by reviewer

### Database

- [ ] Migration tested di staging
- [ ] Migration reversible
- [ ] No data loss risk
- [ ] Indexes added untuk query baru

### Documentation

- [ ] PROGRESS.md updated
- [ ] API docs updated (jika API berubah)
- [ ] CHANGELOG updated

### Operational

- [ ] Rollback plan ready
- [ ] Backup DB (kalau ada migration besar)
- [ ] Stakeholders notified

---

## 🧪 Post-Deploy Verification

### Otomatis (Smoke Tests)

```typescript
// scripts/smoke-test.ts
async function runSmokeTests() {
  const baseUrl = process.env.APP_URL!;

  const tests = [
    { name: "Health check", url: "/api/health", expected: 200 },
    { name: "Home page", url: "/", expected: 200 },
    { name: "API version", url: "/api/v1", expected: 200 },
  ];

  for (const test of tests) {
    const res = await fetch(`${baseUrl}${test.url}`);
    const status = res.status === test.expected ? "✅" : "❌";
    console.log(`${status} ${test.name}: ${res.status}`);

    if (res.status !== test.expected) {
      throw new Error(`Smoke test failed: ${test.name}`);
    }
  }
}
```

### Manual Check (30 menit pertama)

- [ ] Sentry — no error spike
- [ ] Logtail — no error log spike
- [ ] Vercel Analytics — response time normal
- [ ] DB CPU < 60%
- [ ] Critical user flow works

---

## ⏮️ Rollback

### Instant Rollback (Vercel)

```bash
# Via CLI
vercel rollback

# Via Dashboard
# Deployments → pilih previous → Promote to Production
```

### DB Rollback

```bash
# Jika migration gagal
npx prisma migrate resolve --rolled-back [migration-name]
```

### Decision: Kapan Rollback?

- 🔴 Error rate > 5%
- 🔴 Critical functionality broken
- 🔴 Security vulnerability post-deploy
- 🔴 Performance drop signifikan

---

## 📅 Deployment Schedule

### Best Time to Deploy

- ✅ **Selasa-Kamis, 10:00-15:00 WIB**
- ✅ Tim masih fresh, bisa monitor
- ❌ **Jangan** Jumat sore atau weekend

### Planned Downtime

- Announce minimal **48 jam sebelumnya**
- Update status page
- Pilih low-traffic time (02:00-04:00 WIB)

---

## 🛠️ Tools

| Kategori       | Tool                             |
| -------------- | -------------------------------- |
| Hosting        | Vercel                           |
| DB             | Neon                             |
| Cache          | Upstash Redis                    |
| CI/CD          | GitHub Actions                   |
| Error Tracking | Sentry                           |
| Logs           | Logtail                          |
| Monitoring     | Vercel Analytics + Better Uptime |
