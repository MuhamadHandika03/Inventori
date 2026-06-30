
# Incident Response Playbook

> SOP saat terjadi insiden di production: error, downtime, security breach.

## 🚨 Severity Levels

| Level             | Deskripsi                                | Response Time | Contoh                                              |
| ----------------- | ---------------------------------------- | ------------- | --------------------------------------------------- |
| **P0 — Critical** | Service down, data loss, security breach | 15 menit      | App tidak bisa diakses, DB corrupt, breach detected |
| **P1 — High**     | Major functionality broken               | 1 jam         | Login tidak jalan, payment error                    |
| **P2 — Medium**   | Minor functionality broken               | 4 jam         | Satu fitur error, slow performance                  |
| **P3 — Low**      | Cosmetic / minor issue                   | 1 hari        | Typo, alignment issue                               |

---

## 📞 Incident Response Process

### 1. DETECT

- Monitoring alert triggered (Sentry, Logtail, Uptime)
- User report
- Team member notice

### 2. ACKNOWLEDGE

- Acknowledge

# Lanjutan — Menyelesaikan File yang Belum Lengkap

File `INCIDENT_RESPONSE.md` terpotong. Saya lanjutkan dari situ, lalu lengkapi **2 file terakhir** yang belum dibuat.

---

## 📊 Status Saat Ini

| File                         | Status             |
| ---------------------------- | ------------------ |
| `.ai/PERSONA.md`             | ✅ Selesai         |
| `.ai/PROMPT-TEMPLATES.md`    | ✅ Selesai         |
| `.ai/EXAMPLES.md`            | ✅ Selesai         |
| `docs/ENVIRONMENT.md`        | ✅ Selesai         |
| `docs/DATABASE.md`           | ✅ Selesai         |
| `docs/DEPLOYMENT.md`         | ✅ Selesai         |
| `docs/MONITORING.md`         | ✅ Selesai         |
| `docs/TESTING.md`            | ✅ Selesai         |
| `docs/INCIDENT_RESPONSE.md`  | ⚠️ **Dilanjutkan** |
| `docs/RELEASE_MANAGEMENT.md` | ⏳ **Berikutnya**  |
| `docs/API-STANDARDS.md`      | ⏳ **Berikutnya**  |

---

Silakan **copy teks di bawah ini** (lanjutan dari `INCIDENT_RESPONSE.md` yang terpotong, lalu `RELEASE_MANAGEMENT.md`, lalu `API-STANDARDS.md`):

---

````
=== CONTINUE FILE: docs/INCIDENT_RESPONSE.md ===

Acknowledge alert di Slack/alerting channel dalam 5 menit
- Triage severity (P0/P1/P2/P3)
- Assign Incident Commander

### 3. INVESTIGATE
- Cek dashboard (Sentry, Logtail, Vercel)
- Identify root cause
- Cek recent deploys (mungkin regression dari deploy terbaru)
- Cek external services (DB, Redis, third-party)

### 4. MITIGATE
**Prioritas: stop the bleeding, jangan langsung fix root cause**

**Untuk deploy-related issue:**
```bash
# Rollback deploy
vercel rollback

# Verify rollback sukses
curl https://app.example.com/api/health
````

**Untuk DB issue:**

```bash
# Check connection
psql $DATABASE_URL -c "SELECT count(*) FROM pg_stat_activity;"

# Jika connection pool habis, restart app
# Vercel: trigger redeploy
```

**Untuk security breach:**

- Rotate ALL secrets immediately
- Identify scope of breach
- Notify affected users (jika PII terkompromi)
- Document timeline

### 5. COMMUNICATE

**Stakeholder updates setiap 30 menit selama insiden aktif:**

Template update:

```
🔴 INCIDENT UPDATE [HH:MM]

Status: [investigating/identified/mitigating/resolved]
Impact: [apa yang affected]
Current action: [apa yang sedang dilakukan]
Next update: [HH:MM]
```

### 6. RESOLVE

- Implement fix
- Verify fix works
- Monitor 1-2 jam setelah resolve

### 7. POST-MORTEM

Dalam 48 jam setelah insiden resolved:

**Template post-mortem:**

```markdown
# Post-Mortem: [Insiden Title]

## Summary

- **Tanggal:** [tanggal]
- **Severity:** [P0/P1/P2/P3]
- **Duration:** [X jam X menit]
- **Impact:** [X user affected / X% downtime]

## Timeline

- HH:MM — [event]
- HH:MM — [event]
- HH:MM — [event]

## Root Cause

[Penjelasan teknis apa yang sebenarnya terjadi]

## Resolution

[Apa yang dilakukan untuk fix]

## What Went Well

- [Hal yang berjalan baik]

## What Went Wrong

- [Hal yang berjalan buruk]

## Action Items

- [ ] [Action 1] — PIC: [nama] — Due: [tanggal]
- [ ] [Action 2] — PIC: [nama] — Due: [tanggal]
```

---

## 🔥 Common Incidents & Runbook

### Runbook 1: Error Rate Spike

**Symptom:** Sentry alert — error rate > 5%

**Steps:**

```bash
# 1. Cek Sentry — error paling banyak apa
# Buka Sentry dashboard → Issues → sort by frequency

# 2. Cek recent deploys
# Vercel dashboard → Deployments → cek deploy terakhir

# 3. Jika dari deploy terbaru → rollback
vercel rollback

# 4. Cek logs
# Logtail → filter by error level

# 5. Cek DB
psql $DATABASE_URL -c "SELECT * FROM pg_stat_activity WHERE state = 'active';"
```

**Resolution:**

- Jika deploy-related: rollback, fix forward di branch baru
- Jika external service: wait atau switch ke backup
- Jika DB: cek slow query, add index, atau restart connection

---

### Runbook 2: Database Down

**Symptom:** Health check return 503, app error "DB connection failed"

**Steps:**

```bash
# 1. Verify DB down
psql $DATABASE_URL -c "SELECT 1;"
# Jika gagal → DB memang down

# 2. Cek Neon dashboard
# - Apakah ada maintenance?
# - Apakah ada incident report?
# - Cek connection limit

# 3. Cek connection pool
# Vercel dashboard → Functions → cek active connections

# 4. Restore dari backup (jika perlu)
# Neon dashboard → Restore → pilih point in time
```

**Resolution:**

- Jika maintenance: wait, communicate ke user
- Jika connection limit: restart app (redeploy) untuk reset pool
- Jika corrupt: restore dari backup terakhir yang bersih

---

### Runbook 3: Slow Performance

**Symptom:** Response time spike, user complain "app lambat"

**Steps:**

```bash
# 1. Cek Vercel Analytics
# - Response time per route
# - TTFB, FCP, LCP

# 2. Cek slow query log
# Logtail → filter by "slow query"

# 3. Cek external services
# - DB CPU/memory
# - Redis latency
# - Third-party API (email, storage)

# 4. Cek traffic spike
# Vercel → Real-time → cek request volume
```

**Resolution:**

- Slow query → add index, optimize query
- High traffic → verify auto-scaling aktif, consider rate limiting
- External service → switch ke backup atau disable feature

---

### Runbook 4: Security Breach

**Symptom:** Alert dari security scanner, anomali akses, report dari user

**Steps:**

```bash
# 1. JANGAN PANIK, tapi JANGAN delay

# 2. Rotate SEMUA secrets immediately
# - JWT_SECRET
# - DB password
# - API keys (third-party)
# - Webhook secrets

# 3. Identify scope
# - Cek audit log
# - Identify affected accounts
# - Cek data yang accessed

# 4. Block attacker (jika possible)
# - Block IP di WAF
# - Disable compromised account
# - Revoke active sessions

# 5. Notify
# - Internal: security team, management
# - External: affected users (jika PII)
# - Legal: jika regulated data

# 6. Document
# - Full timeline
# - Root cause
# - Action items untuk prevent recurrence
```

⚠️ **WAJIB:** Jika PII user terkompromi, konsult dengan legal sebelum notify user.

---

## 📞 Communication Templates

### Internal Alert

```
🚨 INCIDENT [P0/P1] DETECTED

Service: [nama service]
Symptom: [deskripsi singkat]
Started: [waktu]
Impact: [estimasi user/business impact]

Incident Commander: @[nama]
War room: [Slack channel / meeting link]

Next update: [HH:MM]
```

### External User Notification

```
Subject: [Action Required] Service Incident Update

Hi [name],

We're currently experiencing [brief description].
Our team is actively investigating and working on a fix.

What's affected: [what user can't do]
What we're doing: [brief]

We'll update you every [30 min / 1 hour] until resolved.

Latest update: [time]
Status page: [link]

Sorry for the inconvenience.
```

### Resolution Notice

```
Subject: [Resolved] Service Incident

Hi [name],

The issue affecting [service] has been fully resolved as of [time].

What happened: [brief]
What we did: [brief]
What we're doing to prevent: [brief]

Thank you for your patience.
```

---

## 📋 Incident Response Checklist

### Pre-Incident (Setup)

- [ ] Define on-call rotation
- [ ] Setup alerting (Sentry, Better Uptime)
- [ ] Setup communication channel (Slack #incidents)
- [ ] Document runbook untuk common incidents
- [ ] Practice incident response (game day)
- [ ] Backup verified (restore tested)

### During Incident

- [ ] Acknowledge alert dalam 5 menit
- [ ] Assign Incident Commander
- [ ] Create incident channel
- [ ] Start investigating
- [ ] Communicate every 30 menit
- [ ] Document timeline (real-time)
- [ ] Stop the bleeding dulu
- [ ] Verify fix works
- [ ] Monitor 1-2 jam setelah fix

### Post-Incident

- [ ] Write post-mortem dalam 48 jam
- [ ] Share dengan team
- [ ] Create action items
- [ ] Track action items di sprint backlog
- [ ] Update runbook jika ada learning baru
- [ ] Optional: share externally (transparency)
