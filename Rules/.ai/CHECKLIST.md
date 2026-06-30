
# Quality Checklist

> WAJIB dijalankan sebelum & sesudah coding.

## 📋 PRE-TASK Checklist

Jawab pertanyaan ini **sebelum mulai coding**:

- [ ] Sudah baca `.ai/CONTEXT.md`?
- [ ] Sudah baca `.ai/RULES.md`?
- [ ] Sudah cek `PROGRESS.md` (fitur existing, tidak duplikat)?
- [ ] Tahu file mana yang akan dibuat/diubah?
- [ ] Sudah analisa dampak ke kode lain?
- [ ] Breaking change? (jika ya → tanya user dulu)

---

## 📋 POST-TASK Checklist

Setelah coding selesai, cek semua poin ini:

### TypeScript & Lint

- [ ] `npm run type-check` → 0 errors
- [ ] `npm run lint` → 0 errors (warnings boleh kalau sudah di-acknowledge)
- [ ] Tidak ada `any` di kode baru
- [ ] Semua function punya return type
- [ ] Semua component props punya interface

### Code Quality

- [ ] Naming convention sesuai (variable camelCase, component PascalCase, dll)
- [ ] Tidak ada `console.log` tertinggal
- [ ] Tidak ada kode di-comment (yang tidak perlu)
- [ ] Tidak ada `TODO` tanpa owner
- [ ] File < 300 baris (jika lebih → pecah)

### Functionality

- [ ] Ada error handling (try-catch atau .catch())
- [ ] Ada loading state (kalau async)
- [ ] Ada error state (kalau async)
- [ ] Ada empty state (kalau list/table)
- [ ] Ada validasi input (server-side)
- [ ] Ada authorization check (kalau perlu)

### Security (detail di .ai/SECURITY.md)

- [ ] Input divalidasi di server (bukan hanya client)
- [ ] Tidak ada password/token/secret di-log
- [ ] Tidak ada SQL raw string (pakai ORM/parameterized)
- [ ] Tidak ada `dangerouslySetInnerHTML` (atau sudah di-sanitize)
- [ ] Authorization dicek di endpoint

### Consistency

- [ ] Pattern sama dengan kode existing di codebase
- [ ] Tidak break kode lain (cek import/dependency)
- [ ] Tidak ada dependency baru yang tidak perlu
- [ ] API response format konsisten

### Testing

- [ ] Unit test untuk logic/functions
- [ ] Integration test untuk API endpoint
- [ ] E2E test untuk critical flow (jika user-facing)
- [ ] Manual test di local berhasil

### Documentation

- [ ] PROGRESS.md updated (jika ada perubahan scope)
- [ ] JSDoc untuk public function/component (jika kompleks)
- [ ] README updated (jika ada perubahan setup)

---

## 🎯 Definition of Done

Task dianggap selesai **JIKA** semua checklist di atas ✅.

Jika ada yang ❌ → **JANGAN** lapor selesai, fix dulu.
