
# Release Management

> Panduan versioning, release notes, dan changelog untuk aplikasi production.

## 📌 Versioning (Semantic Versioning)

### Format: `MAJOR.MINOR.PATCH`

```
v1.4.2
│ │ │
│ │ └─ PATCH: bug fix, no breaking change
│ └─── MINOR: fitur baru, backward compatible
└───── MAJOR: breaking change
```

### Contoh

| Version | Perubahan                                           |
| ------- | --------------------------------------------------- |
| `1.0.0` | Initial release                                     |
| `1.1.0` | Tambah fitur dark mode (backward compatible)        |
| `1.1.1` | Fix bug tombol tidak responsive                     |
| `1.2.0` | Tambah fitur export CSV                             |
| `2.0.0` | Redesign API — breaking change, perlu update client |

### Kapan Bump Version?

**MAJOR (breaking):**

- API endpoint berubah atau dihapus
- Schema database yang breaking
- Auth flow berubah signifikan
- Response format berubah

**MINOR (feature):**

- Tambah fitur baru (backward compatible)
- Tambah endpoint baru
- Tambah field opsional di response

**PATCH (fix):**

- Bug fix
- Security fix (yang tidak breaking)
- Performance improvement
- Documentation update

---

## 📝 CHANGELOG.md

### Template

```markdown
# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/),
and this project adheres to [Semantic Versioning](https://semver.org/).

## [Unreleased]

### Added

- (akan datang)

## [1.2.0] - 2025-01-15

### Added

- Fitur export data ke CSV
- Filter berdasarkan tanggal di halaman dashboard
- Dark mode toggle di settings

### Changed

- Improved loading state di halaman profile
- Updated dependencies ke versi terbaru

### Fixed

- Bug upload avatar timeout untuk file > 2MB
- Email validation menerima spasi di awal/akhir

### Security

- Updated bcrypt cost factor dari 10 ke 12

## [1.1.0] - 2025-01-01

### Added

- Login dengan Google OAuth
- Two-factor authentication (TOTP)

### Changed

- Redesigned login page untuk better UX

## [1.0.0] - 2024-12-15

### Added

- Initial release
- User registration & login
- Basic dashboard
- CRUD operations
```

### Categories (Keep a Changelog)

- **Added** — fitur baru
- **Changed** — perubahan di fungsi existing
- **Deprecated** — fitur yang akan dihapus (warning)
- **Removed** — fitur yang dihapus
- **Fixed** — bug fix
- **Security** — security fix/vulnerability

### Best Practices

- ✅ **Tulis per release** — jangan tunggu-tunggu
- ✅ **Bahasa user-friendly** — jelaskan dampaknya untuk user
- ✅ **Link ke PR/Issue** — untuk traceability
- ✅ **Group by category** — Added/Changed/Fixed, dll
- ❌ **Jangan terlalu teknis** — fokus ke "apa" bukan "bagaimana"
- ❌ **Jangan skip release** — setiap release harus ada entry

---

## 🚀 Release Process

### Step 1: Feature Freeze

Di akhir sprint, semua story harus merged ke `develop`:

- Sprint Review ✅
- QA pass ✅
- All tests green ✅
- No blocker bugs ✅

### Step 2: Create Release Branch

```bash
# Dari develop
git checkout develop
git pull origin develop
git checkout -b release/1.2.0
```

### Step 3: Version Bump & Changelog

```bash
# Update package.json
npm version minor  # atau major / patch

# Update CHANGELOG.md
# Pindahkan [Unreleased] ke [1.2.0] dengan tanggal

# Commit
git add .
git commit -m "chore(release): bump version to 1.2.0"
```

### Step 4: Final Testing di Staging

- Full regression test
- Performance check
- Security check
- Smoke test critical flows

### Step 5: Merge ke Main

```bash
# PR: release/1.2.0 → main
# CI full suite + manual approval
```

### Step 6: Tag Release

```bash
git checkout main
git pull origin main
git tag -a v1.2.0 -m "Release version 1.2.0"
git push origin v1.2.0
```

### Step 7: Deploy ke Production

(Triggered otomatis oleh CI setelah merge ke main)

### Step 8: Cherry-pick ke Develop

```bash
git checkout develop
git merge main  # atau cherry-pick release commits
```

### Step 9: Communicate Release

- Update status page
- Notify internal team
- (Optional) Notify users jika ada perubahan user-facing
- Update release notes di GitHub

### Step 10: Monitor

- Monitor 24 jam pertama setelah release
- Watch error rate, performance metrics
- Siap rollback jika ada masalah

---

## 📋 Release Notes Template

### Public Release Notes (untuk user)

```markdown
# 🚀 Release 1.2.0 — 15 Januari 2025

## ✨ What's New

### Export Data ke CSV

Sekarang kamu bisa export data dari dashboard ke file CSV. Klik tombol "Export" di kanan atas tabel.

### Dark Mode

Aktifkan dark mode dari Settings → Appearance. Otomatis follow system preference juga bisa.

### Filter Tanggal

Filter data berdasarkan rentang tanggal. Tersedia di dashboard.

## 🔧 Improvements

- Loading 2x lebih cepat di halaman profile
- Dashboard lebih responsive di mobile

## 🐛 Bug Fixes

- Upload avatar sekarang support file sampai 5MB
- Email typo sebelumnya diterima, sekarang di-validate ketat

## 📝 Notes

- Pastikan kamu sudah login untuk akses fitur baru
- Butuh help? Hubungi support@example.com
```

### Internal Release Notes (untuk team)

```markdown
# Release 1.2.0 — Internal Notes

## Deployment

- **Tanggal:** 2025-01-15 10:00 WIB
- **PIC Deploy:** @[nama]
- **Downtime:** None (zero-downtime deploy)

## Changes

- PROJ-201: Export CSV feature
- PROJ-202: Dark mode
- PROJ-203: Date range filter
- BUG-101: Fix upload timeout
- BUG-102: Fix email validation

## Database Changes

- Migration: add `dark_mode` column ke `users` table
- Migration: add `export_logs` table
- **Reversible:** Yes

## Breaking Changes

- None

## Rollback Plan

- Vercel: instant rollback via dashboard
- DB: `npx prisma migrate resolve --rolled-back`

## Monitoring

- Watch for 24 jam: error rate, response time, signup flow
- Sentry tag: `release-1.2.0`

## Known Issues

- Dark mode tidak support di Safari < 14 (graceful degradation)
```

---

## 🔖 Git Tagging Strategy

### Format

```
v[MAJOR].[MINOR].[PATCH]
v1.2.0
v1.2.1
v2.0.0
```

### Commands

```bash
# Create tag
git tag -a v1.2.0 -m "Release version 1.2.0"

# Push tag
git push origin v1.2.0

# List tags
git tag -l

# Checkout specific version
git checkout v1.2.0

# Delete tag (jika salah)
git tag -d v1.2.0
git push origin :refs/tags/v1.2.0
```

### Tag Annotations

Tag harus annotated (bukan lightweight) agar include metadata:

- Tanggal release
- Release notes summary
- Who released

---

## 📊 Release Metrics

Track per release:

| Metric                    | Target          |
| ------------------------- | --------------- |
| Time to deploy            | < 30 menit      |
| Failed deploy rate        | < 5%            |
| Rollback rate             | < 2%            |
| Post-release bugs (P0/P1) | < 1 per release |
| Time to first feedback    | < 24 jam        |

---

## 📋 Release Checklist

### Pre-Release

- [ ] All sprint stories merged ke develop
- [ ] Sprint review passed
- [ ] QA passed
- [ ] All tests green
- [ ] No P0/P1 bugs open
- [ ] Changelog updated
- [ ] Version bumped di package.json
- [ ] Migration tested di staging
- [ ] Release notes drafted

### Release Day

- [ ] Final test di staging
- [ ] PR release → main approved
- [ ] Merge to main
- [ ] Tag release
- [ ] Deploy triggered
- [ ] Smoke test di production
- [ ] Monitor 30 menit pertama
- [ ] Communicate release
- [ ] Update status page

### Post-Release

- [ ] Monitor 24 jam
- [ ] Track any issues
- [ ] Close release milestone
- [ ] Retrospective (kalau ada issue besar)
- [ ] Update docs jika perlu
