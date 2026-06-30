
# Anti-Patterns — JANGAN Lakukan Ini

> Daftar hal yang HARUS dihindari. Tidak ada exception.

## ❌ TypeScript

- ❌ **JANGAN** pakai `any` — gunakan `unknown` lalu type guard
- ❌ **JANGAN** pakai `@ts-ignore` — fix root cause-nya
- ❌ **JANGAN** disable strict mode
- ❌ **JANGAN** type cast paksa (`as` tanpa validasi)
- ❌ **JANGAN** return tanpa explicit type di public function

## ❌ Component & UI

- ❌ **JANGAN** business logic di component (pakai service)
- ❌ **JANGAN** fetch data di component langsung (pakai hook/SWR)
- ❌ **JANGAN** mutation langsung di onClick handler tanpa service
- ❌ **JANGAN** pakai index sebagai React key (`key={index}`)
- ❌ **JANGAN** mutate props langsung
- ❌ **JANGAN** taruh state di parent kalau bisa lokal
- ❌ **JANGAN** component > 300 baris (pecah)
- ❌ **JANGAN** inline function di list yang sering re-render
- ❌ **JANGAN** `useEffect` untuk derived state (pakai `useMemo`)

## ❌ State & Data

- ❌ **JANGAN** duplicate state (props + state untuk data sama)
- ❌ **JANGAN** simpan server data di global state (pakai TanStack Query)
- ❌ **JANGAN** fetch di useEffect tanpa cleanup function
- ❌ **JANGAN** simpan sensitive data di localStorage

## ❌ API & Backend

- ❌ **JANGAN** percaya input dari client (validate di server)
- ❌ **JANGAN** return password hash / token di response
- ❌ **JANGAN** catch error tanpa handle atau re-throw
- ❌ **JANGAN** hardcode URL / API key / secret
- ❌ **JANGAN** SQL string concatenation (pakai ORM/parameterized)
- ❌ **JANGAN** skip authorization check di endpoint
- ❌ **JANGAN** log data sensitif (password, token, PII tanpa masking)

## ❌ Async & Error

- ❌ **JANGAN** async function tanpa try-catch di caller
- ❌ **JANGAN** ignore promise rejection (`.catch()` atau await di try-catch)
- ❌ **JANGAN** render UI tanpa loading & error state
- ❌ **JANGAN** assume API selalu success

## ❌ Security

- ❌ **JANGAN** commit `.env`, secrets, API keys
- ❌ **JANGAN** disable CORS / security headers untuk "test cepat"
- ❌ **JANGAN** pakai `dangerouslySetInnerHTML` tanpa sanitize
- ❌ **JANGAN** eval() atau Function() constructor
- ❌ **JANGAN** cookie tanpa `httpOnly`, `secure`, `sameSite`
- ❌ **JANGAN** share session antar domain tanpa `sameSite=strict`

## ❌ Code Hygiene

- ❌ **JANGAN** kode di-comment (hapus saja, sudah di-git)
- ❌ **JANGAN** `console.log` tertinggal di production code
- ❌ **JANGAN** `TODO` tanpa owner & tanggal
- ❌ **JANGAN** dead code / unused import
- ❌ **JANGAN** magic number (extract ke constant)

## ❌ Git & Workflow

- ❌ **JANGAN** commit langsung ke `main` atau `develop`
- ❌ **JANGAN** commit message generic ("update", "fix", "perubahan")
- ❌ **JANGAN** push force ke shared branch
- ❌ **JANGAN** commit file besar yang tidak perlu (.DS_Store, node_modules, dll)
- ❌ **JANGAN** squash commit yang punya message informatif

## ❌ Dependency

- ❌ **JANGAN** tambah dependency tanpa cek dulu:
  - Bundle size impact
  - Maintenance status (last update, open issues)
  - License compatibility
  - Alternative yang lebih ringan
- ❌ **JANGAN** pakai library deprecated / unmaintained
- ❌ **JANGAN** import library besar untuk fungsi kecil

## ❌ Refactoring

- ❌ **JANGAN** refactor kode existing **TANPA DIMINTA**
- ❌ **JANGAN** ubah pattern yang sudah established tanpa diskusi
- ❌ **JANGAN** rename file/variable yang dipakai banyak tempat tanpa plan

---

## ✅ When in Doubt

Jika ragu apakah sesuatu boleh dilakukan:

1. **Cek RULES.md** — apakah ada aturannya?
2. **Cek codebase existing** — apakah ada pattern-nya?
3. **Tanya user** — lebih baik tanya daripada salah

> **Prinsip:** Consistency > Cleverness. Ikuti pattern yang ada, jangan coba-coba hal baru tanpa izin.
