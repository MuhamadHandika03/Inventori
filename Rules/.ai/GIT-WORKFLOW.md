
# Git Workflow Rules

> Aturan branch, commit, PR. WAJIB dipatuhi oleh AI.

## 🌳 Branch Strategy

```
main              ← production (protected, butuh PR + approval)
  │
develop           ← staging (protected, auto-deploy)
  │
  ├── feature/*   ← fitur baru (dari develop)
  ├── fix/*       ← bug fix (dari develop)
  ├── hotfix/*    ← urgent production fix (dari main)
  └── chore/*     ← maintenance (dari develop)
```

## 📝 Branch Naming

```bash
feature/[TICKET-ID]-[short-desc]
fix/[TICKET-ID]-[short-desc]
hotfix/[short-desc]
chore/[short-desc]

# Contoh
feature/PROJ-123-user-profile
fix/PROJ-456-login-error
hotfix/critical-payment-bug
chore/update-dependencies
```

## 💬 Commit Message (Conventional Commits)

### Format

```
<type>(<scope>): <subject>

<body (opsional)>

<footer (opsional)>
```

### Type

- `feat` — fitur baru
- `fix` — bug fix
- `docs` — dokumentasi
- `style` — formatting (no logic change)
- `refactor` — restructure code (no behavior change)
- `perf` — performance improvement
- `test` — tambah test
- `chore` — maintenance (deps, build)
- `ci` — CI/CD changes

### Contoh

```
feat(auth): tambah social login dengan Google

- Implement OAuth 2.0 flow
- Tambah callback handler di /api/auth/callback/google
- Update schema dengan social_account_id

Refs: PROJ-123
```

```
fix(profile): perbaiki error upload avatar

File upload gagal karena Content-Type salah.
Fix: validasi Content-Type sebelum upload.

Closes: PROJ-456
```

### Rules

- ✅ Subject max 72 karakter
- ✅ Subject dalam Bahasa Indonesia atau Inggris (konsisten)
- ✅ Pakai imperative mood ("tambah", bukan "ditambahkan")
- ❌ Jangan generic message: "update", "fix", "perubahan"
- ❌ Jangan multi-topic dalam 1 commit

## 🔀 Pull Request Rules

- ✅ Minimal 1 reviewer approval (2 untuk critical changes)
- ✅ CI harus passing (lint, type-check, test)
- ✅ Squash & merge untuk feature branches
- ❌ Jangan force push ke `main` atau `develop`
- ❌ Jangan merge PR sendiri tanpa review

### PR Template (auto-filled)

```markdown
## Apa yang berubah

- [bullet 1]
- [bullet 2]

## Mengapa

[Penjelasan business reason]

## Testing

- [ ] Unit test added/updated
- [ ] Manual test di local
- [ ] Test di staging

## Screenshots (jika UI change)

[screenshots]

## Checklist

- [ ] Lint passing
- [ ] Type-check passing
- [ ] Test passing
- [ ] PROGRESS.md updated (jika perlu)
```

## 🚨 Hotfix Workflow (Production Emergency)

```bash
# 1. Buat branch dari main
git checkout main
git checkout -b hotfix/critical-bug

# 2. Fix
# ... fix bug ...

# 3. PR ke main (skip develop dulu)
git push origin hotfix/critical-bug
# Buat PR: hotfix/* → main

# 4. Setelah merge → auto deploy ke production

# 5. Cherry-pick ke develop
git checkout develop
git cherry-pick <commit-hash>
```

## 📦 Commit Frequency

- ✅ Commit sering dengan logical chunks
- ✅ 1 commit = 1 logical change
- ✅ Bisa commit walaupun belum perfect (akan di-review)
- ❌ Jangan 1 commit untuk 1 hari penuh
