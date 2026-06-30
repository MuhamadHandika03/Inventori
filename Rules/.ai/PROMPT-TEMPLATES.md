
# Prompt Templates — Siap Pakai

> Copy-paste template ini sesuai task yang diberikan ke AI. Modifikasi bagian [bracket] sesuai kebutuhan.

---

## 🚀 Template 1: Mulai Sesi Baru

```
Halo, saya mau mulai sesi coding baru.

Tolong baca dulu file-file ini untuk konteks:
1. .ai/CONTEXT.md — konteks proyek
2. .ai/RULES.md — aturan coding
3. .ai/WORKFLOW.md — SOP kerja
4. .ai/SECURITY.md — security check
5. PROGRESS.md — status sprint

Setelah baca, konfirmasi pemahaman kamu dengan ringkasan:
- Tech stack: [sebutkan]
- Prinsip utama: [sebutkan]
- Sprint goal saat ini: [sebutkan]
```

---

## 🛠️ Template 2: Task Fitur Baru

```
Saya mau tambah fitur: [NAMA FITUR]

Deskripsi:
[2-3 kalimat jelaskan apa fitur ini dan untuk siapa]

Acceptance Criteria:
- [ ] User bisa [aksi 1]
- [ ] Sistem [proses 2]
- [ ] Hasil ditampilkan sebagai [output 3]

Konteks:
- Baca SPEC.md untuk requirements lengkap
- Cek PROGRESS.md untuk fitur existing yang serupa
- Ikuti pattern di [file referensi jika ada]

Sebelum coding, jalankan WORKFLOW.md Step 1 (Analyze) dulu.
Setelah selesai, jalankan CHECKLIST.md dan SECURITY.md.
```

---

## 🐛 Template 3: Bug Fix

```
Ada bug yang perlu di-fix.

Bug: [judul singkat]
Severity: [🔴 High / 🟡 Med / 🟢 Low]

Reproduce:
1. [Step 1]
2. [Step 2]
3. [Lihat error di X]

Expected: [apa yang seharusnya terjadi]
Actual: [apa yang terjadi]

Error message (jika ada):
[paste error]

File yang dicurigai: [path]
Sudah coba: [apa yang sudah dilakukan]

Jalankan WORKFLOW.md, lalu fix.
Setelah selesai, jelaskan root cause-nya.
```

---

## ♻️ Template 4: Refactor

```
Saya mau refactor [NAMA/KOMPONEN].

Alasan refactor:
- [Masalah 1: misal terlalu besar / repetitive / hard to test]

Scope:
- File: [path]
- Yang boleh diubah: [scope jelas]
- Yang JANGAN diubah: [scope yang harus dijaga]

Kriteria sukses:
- [ ] Tidak ada perubahan behavior/fungsi
- [ ] Tests masih passing
- [ ] Type-check passing
- [ ] Kode lebih [readable/maintainable/testable]

PENTING: Jangan ubah API contract atau behavior.
Jangan rename public function/class tanpa konfirmasi.
```

---

## 📝 Template 5: Code Review

```
Tolong review kode ini.

File: [path]
atau
PR: [URL]

Konteks: [apa yang dilakukan kode ini]

Tolong cek:
1. Apakah sesuai .ai/RULES.md?
2. Apakah ada masalah di .ai/SECURITY.md?
3. Apakah ada anti-pattern di .ai/DO-NOT.md?
4. Apakah ada cara yang lebih baik?

Format feedback:
- 🔴 Critical: [masalah yang harus diperbaiki]
- 🟡 Warning: [masalah yang sebaiknya diperbaiki]
- 🟢 Suggestion: [ide improvement]
- ✅ Good: [hal yang sudah bagus]
```

---

## 🧪 Template 6: Tambah Test

```
Saya mau tambah test untuk [KOMPONEN/FUNCTION].

File yang ditest: [path]

Test scenario:
- [Skenario 1: happy path]
- [Skenario 2: edge case]
- [Skenario 3: error case]

Framework test: [Jest / Vitest / Playwright]

Pattern:
- Unit test untuk logic/functions
- Integration test untuk API
- E2E test untuk user flow

Ikuti pattern test yang sudah ada di codebase.
Pastikan test bisa jalan: `npm run test`
```

---

## 📚 Template 7: Dokumentasi

```
Tolong update dokumentasi untuk [FITUR/PERUBAHAN].

Yang sudah diubah:
- [Perubahan 1]
- [Perubahan 2]

Update file:
- [ ] README.md (jika setup/proses berubah)
- [ ] SPEC.md (jika fitur baru)
- [ ] ARCHITECTURE.md (jika struktur berubah)
- [ ] CONVENTIONS.md (jika aturan berubah)
- [ ] PROGRESS.md (selalu update untuk sprint)
- [ ] API-STANDARDS.md (jika API berubah)
```

---

## 🚀 Template 8: Setup Environment

```
Saya mau setup [staging/production] environment.

Konteks:
- Tech stack: [sebutkan]
- Hosting: [Vercel / Railway / AWS]
- Database: [Neon / Supabase / RDS]

Yang perlu di-setup:
- [ ] Environment variables
- [ ] Database setup
- [ ] CI/CD pipeline
- [ ] Monitoring (Sentry/Logtail)
- [ ] Domain & DNS
- [ ] SSL certificate
- [ ] Backup strategy

Ikuti best practice di docs/ENVIRONMENT.md dan docs/DEPLOYMENT.md.
```

---

## 🔍 Template 9: Investigasi / Debugging

```
Saya ada masalah, butuh investigasi.

Gejala: [apa yang terjadi]
Mulai terjadi: [kapan, setelah perubahan apa]
Sudah cek: [apa yang sudah dicoba]

Tolong:
1. Identifikasi kemungkinan penyebab
2. Cek log/error terkait
3. Kasih saran langkah debug
4. Jika jelas, langsung fix

File terkait (jika ada): [path]
```

---

## 🤔 Template 10: Tanya Pendapat (Sebelum Coding)

```
Saya ada keputusan yang perlu dibuat.

Konteks: [penjelasan situasi]

Opsi:
A. [Opsi 1] — [pro]: [kelebihan], [con]: [kekurangan]
B. [Opsi 2] — [pro]: [kelebihan], [con]: [kekurangan]
C. [Opsi 3] — [pro]: [kelebihan], [con]: [kekurangan]

Trade-off utama: [performance vs DX / speed vs quality / dll]

Rekomendasi kamu apa? Kasih reasoning kenapa.
Setelah kita sepakat, baru mulai implement.
```

---

## 🛑 Template 11: Stop & Reset

```
STOP.

Saya mau reset arah. Kode yang baru kamu buat ada masalah:
- [Masalah 1]
- [Masalah 2]

Yang harus dilakukan:
1. Rollback perubahan [jika perlu]
2. Review apa yang salah
3. Plan ulang
4. Tunggu approval sebelum lanjut

Jangan lanjut coding sampai kita diskusi dulu.
```

---

## 💡 Template 12: Refinement (Tanya Klarifikasi)

```
Sebelum mulai, saya butuh klarifikasi:

1. [Pertanyaan 1]?
2. [Pertanyaan 2]?
3. [Pertanyaan 3]?

Ini penting supaya hasilnya sesuai yang diharapkan.
Setelah saya jawab, baru mulai.
```
