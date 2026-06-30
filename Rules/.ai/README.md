
# .ai/ Folder — Panduan untuk AI

> Folder ini berisi "otak tambahan" untuk AI assistant.

## 📂 Isi Folder

| File              | Fungsi                                                   |
| ----------------- | -------------------------------------------------------- |
| `CONTEXT.md`      | Konteks proyek (WAJIB dibaca pertama)                    |
| `WORKFLOW.md`     | SOP kerja (Analyze → Plan → Implement → Verify → Report) |
| `RULES.md`        | Aturan coding (WAJIB dipatuhi)                           |
| `CHECKLIST.md`    | Quality gate sebelum/sesudah coding                      |
| `SECURITY.md`     | 7 security check wajib setiap fitur                      |
| `DO-NOT.md`       | Anti-patterns (larangan)                                 |
| `GIT-WORKFLOW.md` | Branch, commit, PR rules                                 |

## 🚀 Cara Pakai

### Sesi Baru

```
"Saya akan mulai sesi coding baru.
Tolong baca dulu:
1. .ai/CONTEXT.md
2. .ai/RULES.md
3. .ai/WORKFLOW.md
4. PROGRESS.md

Setelah itu, konfirmasi bahwa kamu sudah paham konteksnya."
```

### Task Baru

```
"Task: [deskripsi task]

Sebelum mulai:
1. Baca .ai/WORKFLOW.md dan jalankan Step 1 (Analyze)
2. Baca .ai/SECURITY.md untuk security considerations
3. Ikuti .ai/RULES.md untuk coding standards
4. Verify dengan .ai/CHECKLIST.md setelah selesai"
```

### Update Berkala

Setiap sprint:

- Update `PROGRESS.md`
- Update `.ai/CONTEXT.md` jika ada perubahan stack/arsitektur

## ⚙️ Maintenance

| Trigger                | Action                       |
| ---------------------- | ---------------------------- |
| Tambah library baru    | Update `.ai/CONTEXT.md`      |
| Ubah naming convention | Update `.ai/RULES.md`        |
| Tambah security rule   | Update `.ai/SECURITY.md`     |
| Ubah branch strategy   | Update `.ai/GIT-WORKFLOW.md` |
| Sprint baru            | Update `PROGRESS.md`         |
