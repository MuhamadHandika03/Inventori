
# Workflow untuk AI Assistant

> SOP kerja yang WAJIB diikuti setiap kali ada task. Bertujuan memastikan hasil production-grade.

## 🎯 Prinsip Utama

> **"Berpikir dulu, baru coding. Quality > Speed."**

---

## STEP 1: ANALYZE (WAJIB — Skip = Tolak)

Sebelum mulai coding, **WAJIB** jawab pertanyaan ini:

### 1.1 — Pemahaman Task

- Apa yang diminta? (fitur / fix / refactor / docs)
- Apa tujuan akhirnya? (mengapa perlu dibuat?)
- Siapa penggunanya?
- Apa acceptance criteria-nya?

### 1.2 — Konteks Proyek

- Baca `.ai/CONTEXT.md` → cek tech stack
- Baca `PROGRESS.md` → cek fitur existing, jangan duplikat
- Baca `.ai/RULES.md` → ingat aturan coding
- Baca `.ai/SECURITY.md` → ingat security check

### 1.3 — Dampak

- File mana yang akan dibuat/diubah?
- Apakah ada kode existing yang bergantung?
- Apakah ada schema database yang perlu berubah?
- Apakah ada API contract yang berubah?
- Apakah ini breaking change?

**Output Step 1:**

```
ANALISIS:
- Task: [deskripsi]
- File terpengaruh: [list]
- Breaking change: [ya/tidak + penjelasan]
- Risiko: [low/medium/high]
```

---

## STEP 2: PLAN (WAJIB untuk task medium/high complexity)

Buat rencana **sebelum** coding:

### 2.1 — Breakdown

Pecah task jadi sub-task kecil:

```
1. [Sub-task 1] — [file/path]
2. [Sub-task 2] — [file/path]
3. [Sub-task 3] — [file/path]
```

### 2.2 — Dependency

- Mana yang harus dibuat duluan?
- Apakah perlu migration database?
- Apakah perlu dependency baru?

### 2.3 — Testing Plan

- Unit test: apa yang harus ditest?
- Integration test: flow apa?
- E2E test: scenario apa?

**Output Step 2:**

```
PLAN:
1. Buat [file 1] — untuk [tujuan]
2. Buat [file 2] — untuk [tujuan]
3. Test [scenario]
4. Verify dengan [checklist]
```

---

## STEP 3: IMPLEMENT

Saat coding, **WAJIB** patuhi:

- ✅ `.ai/RULES.md` — coding style & patterns
- ✅ `.ai/SECURITY.md` — 7 security check
- ✅ `.ai/DO-NOT.md` — anti-pattern
- ✅ Konsisten dengan kode existing (cek style & pattern)

### Catatan Penting

- **Jangan refactor** kode existing kecuali diminta
- **Jangan tambah** dependency baru tanpa tanya
- **Jangan ubah** schema DB tanpa plan migration
- **Jangan skip** validasi karena "simple case"
- **Jangan lupa** error handling dengan asumsi "tidak akan error"

---

## STEP 4: VERIFY (WAJIB — Tidak Boleh Skip)

Setelah coding, **WAJIB** jalankan checklist di `.ai/CHECKLIST.md`:

### 4.1 — Pre-merge Checklist

- [ ] TypeScript: `npm run type-check` pass
- [ ] Lint: `npm run lint` pass
- [ ] Test: `npm run test` pass
- [ ] Build: `npm run build` pass

### 4.2 — Code Quality

- [ ] Tidak ada `any`
- [ ] Tidak ada `console.log` tertinggal
- [ ] Tidak ada kode di-comment
- [ ] Naming convention sesuai
- [ ] Ada loading/error state (kalau async)
- [ ] Ada error handling
- [ ] Ada validasi input

### 4.3 — Security (dari .ai/SECURITY.md)

- [ ] Input divalidasi di server
- [ ] Authorization dicek
- [ ] Data sensitif tidak di-log
- [ ] SQL injection safe
- [ ] XSS safe
- [ ] Rate limiting (jika perlu)
- [ ] CSRF protection (jika perlu)

### 4.4 — Consistency

- [ ] Pattern sama dengan kode existing
- [ ] Tidak break kode lain (cek yang import file ini)
- [ ] Tidak ada dependency baru yang tidak perlu

---

## STEP 5: REPORT (WAJIB)

Setelah selesai, **WAJIB** lapor ke user dengan format:

```markdown
## ✅ Selesai: [Nama Task]

### File yang dibuat/diubah:

- `path/file.ts` — [deskripsi singkat]
- `path/file.tsx` — [deskripsi singkat]

### Penjelasan:

- [Kenapa pakai approach A bukan B]
- [Trade-off yang diambil]
- [Pattern yang diikuti]

### Yang perlu dicek user:

- [Asumsi yang dibuat]
- [Limitasi yang ada]
- [Yang mungkin perlu disesuaikan]

### Testing yang dilakukan:

- [Test yang sudah dijalanin / belum]
```

---

## 🚨 Escalation Rules

**TANYA ke user** (jangan langsung coding) jika:

| Situasi                     | Contoh                                                 |
| --------------------------- | ------------------------------------------------------ |
| Pilihan library baru        | "Pakai library A atau B?"                              |
| Schema DB berubah           | "Ini perlu migration, approve?"                        |
| API breaking change         | "Ini akan break existing client, lanjut?"              |
| Ada 2+ approach valid       | "Ada 2 cara, mau yang mana?"                           |
| Trade-off performance vs DX | "Trade-off X, pilih mana?"                             |
| Refactor kode existing      | "Saya lihat kode ini bisa di-improve, refactor boleh?" |

**LANGSUNG coding** jika:

- Task jelas & tidak ambigu
- Pattern sudah established di codebase
- Tidak ada breaking change
- Tidak ada dependency baru

---

## 📋 Decision Tree

```
Task masuk
    ↓
Bisa langsung? (jelas, established pattern, no breaking change)
    ├─ Ya → Langsung coding (Step 3)
    └─ Tidak → Analyze (Step 1)
                    ↓
                Complex? (multi-file, multi-system)
                    ├─ Ya → Plan (Step 2)
                    └─ Tidak → Langsung coding (Step 3)
                                    ↓
                                Implement (Step 3)
                                    ↓
                                Verify (Step 4)
                                    ↓
                                Report (Step 5)
```
