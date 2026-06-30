
# Monitoring & Observability

> Panduan setup logging, error tracking, alerting untuk aplikasi production.

## 🎯 3 Pilar Observability

1. **Logs** — Apa yang terjadi (event-based)
2. **Metrics** — Berapa banyak (numeric, time-series)
3. **Traces** — Dari mana asal masalahnya (request flow)

---

## 📊 Logging

### Log Levels

```typescript
// lib/logger.ts
import pino from "pino";

export const logger = pino({
  level: process.env.LOG_LEVEL || "info",
  base: {
    env: process.env.NODE_ENV,
    service: "app-name",
  },
  redact: {
    paths: [
      "password",
      "token",
      "*.password",
      "*.token",
      "req.headers.authorization",
    ],
    censor: "[REDACTED]",
  },
});
```

**Levels:**

- `error` — sesuatu rusak
- `warn` — sesuatu nggak ideal
- `info` — event penting (login, register, deploy)
- `debug` — detail untuk debugging (dev only)
- `trace` — sangat detail (rare)

### Log Format

```typescript
// ✅ Bagus — structured, contextual
logger.info(
  {
    event: "user.login",
    userId: user.id,
    method: "email",
    ip: req.headers.get("x-forwarded-for"),
  },
  "User logged in",
);

// ❌ Buruk — unstructured
console.log("User logged in:", user);
```

### Yang WAJIB Di-Log

- ✅ Login/logout attempts (success & failure)
- ✅ Registration
- ✅ Password reset
- ✅ Sensitive actions (delete, role change, payment)
- ✅ API errors (5xx)
- ✅ Authorization failures (403)
- ✅ Rate limit hits

### Yang JANGAN Di-Log

- ❌ Password (plain atau hash)
- ❌ API keys / tokens
- ❌ Credit card / CVV
- ❌ Full PII (NIK, KTP) tanpa masking
- ❌ Session cookies

---

## 🐛 Error Tracking (Sentry)

### Setup

```typescript
// lib/sentry.ts
import * as Sentry from "@sentry/nextjs";

Sentry.init({
  dsn: process.env.SENTRY_DSN,
  environment: process.env.NODE_ENV,
  tracesSampleRate: process.env.NODE_ENV === "production" ? 0.1 : 1.0,
  beforeSend(event) {
    // Scrub sensitive data
    if (event.user) {
      delete event.user.ip_address;
    }
    return event;
  },
});
```

### Capture Errors

```typescript
try {
  await riskyOperation();
} catch (error) {
  logger.error({ err: error, context: "riskyOperation" }, "Operation failed");
  Sentry.captureException(error, {
    tags: { feature: "payment" },
    user: { id: session.userId },
  });
  throw error;
}
```

### Sentry Alerts Setup

Alert ketika:

- Error spike (> 10 errors dalam 5 menit)
- New error type muncul
- Error rate > 1%
- Specific feature error > 5

---

## 📈 Metrics

### Application Metrics

| Metric                  | Target  | Alert |
| ----------------------- | ------- | ----- |
| **Response time (p95)** | < 500ms | > 1s  |
| **Response time (p99)** | < 1s    | > 2s  |
| **Error rate**          | < 0.1%  | > 1%  |
| **Apdex score**         | > 0.9   | < 0.8 |
| **Uptime**              | > 99.5% | < 99% |

### Business Metrics

- Daily active users (DAU)
- Registration per hari
- Conversion rate
- Feature usage
- Retention rate

### Tools

- **Vercel Analytics** — performance, web vitals
- **Sentry** — errors, performance
- **Logtail** — log aggregation
- **Better Uptime** — uptime monitoring
- **PostHog / Plausible** — product analytics

---

## 🚨 Alerting

### Alert Channels

- **Critical:** Slack + Email + SMS (on-call)
- **Warning:** Slack
- **Info:** Dashboard only

### Alert Rules

```yaml
# Contoh alert configuration
alerts:
  - name: "High Error Rate"
    condition: error_rate > 5%
    duration: 5m
    severity: critical
    notify: [slack, email, sms]

  - name: "Slow Response"
    condition: p95_response_time > 2s
    duration: 10m
    severity: warning
    notify: [slack]

  - name: "DB Connection High"
    condition: db_connections > 80%
    duration: 5m
    severity: warning
    notify: [slack]

  - name: "Uptime Down"
    condition: uptime < 99%
    duration: 2m
    severity: critical
    notify: [slack, email, sms]
```

### On-Call Rotation

- 1 orang primary, 1 orang backup
- Rotate mingguan
- Response time SLA: 15 menit untuk critical

---

## 🏥 Health Checks

### Endpoint: `/api/health`

```typescript
// app/api/health/route.ts
import { prisma } from "@/lib/db";

export async function GET() {
  const checks = {
    status: "ok",
    timestamp: new Date().toISOString(),
    uptime: process.uptime(),
    database: "unknown",
    version: process.env.npm_package_version,
  };

  try {
    await prisma.$queryRaw`SELECT 1`;
    checks.database = "ok";
  } catch (error) {
    checks.status = "degraded";
    checks.database = "down";
  }

  const statusCode = checks.status === "ok" ? 200 : 503;
  return Response.json(checks, { status: statusCode });
}
```

---

## 📋 Monitoring Checklist

### Initial Setup

- [ ] Setup Sentry (staging & production)
- [ ] Setup Logtail (staging & production)
- [ ] Setup Better Uptime monitoring
- [ ] Setup Slack alerts integration
- [ ] Define alert rules
- [ ] Document on-call schedule
- [ ] Create runbook untuk common alerts

### Per-Sprint

- [ ] Review error trends
- [ ] Check alert noise (false positives)
- [ ] Update alert thresholds jika perlu
- [ ] Review slow query logs
- [ ] Archive old logs
