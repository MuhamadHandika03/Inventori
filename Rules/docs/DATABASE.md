
# Database Management Guide

> Panduan manage database: schema, migration, performance, backup, recovery.

## 🗄️ Schema Design Principles

1. **Normalize** — minimalkan duplikasi data
2. **Index wisely** — index untuk query yang sering, jangan over-index
3. **Soft delete** — jangan hard delete data penting (gunakan `deletedAt`)
4. **Audit fields** — selalu ada `createdAt`, `updatedAt`, `createdBy`
5. **Use UUID** — bukan auto-increment, untuk security & scalability
6. **Use enum** — untuk nilai tetap, daripada string

---

## 📐 Schema Patterns

### Standard Fields (WAJIB di semua table)

```prisma
model BaseModel {
  id        String   @id @default(uuid())
  createdAt DateTime @default(now())
  updatedAt DateTime @updatedAt
  createdBy String?
  updatedBy String?
  deletedAt DateTime?  // soft delete

  @@map("base_models")
}
```

### Naming Convention

- **Model:** PascalCase, singular → `User`, `OrderItem`
- **Field:** camelCase → `firstName`, `createdAt`
- **Table:** snake_case, plural → `users`, `order_items`
- **Enum:** PascalCase untuk nama, UPPER_SNAKE untuk value

---

## 🔍 Indexing Strategy

### Kapan Perlu Index?

✅ **Index untuk:**

- Foreign key (otomatis di Prisma)
- Field yang sering di-WHERE clause (`email`, `slug`)
- Field yang sering di-ORDER BY (`createdAt`)
- Composite untuk query multi-field

❌ **Jangan index untuk:**

- Table kecil (< 1000 rows)
- Field yang jarang di-query
- Field dengan cardinality rendah (boolean, enum)

### Contoh Index

```prisma
model User {
  id        String   @id @default(uuid())
  email     String   @unique  // index untuk login lookup
  status    UserStatus
  createdAt DateTime @default(now())

  @@index([status, createdAt])  // composite untuk filter + sort
  @@index([email])  // explicit (sudah unique tapi explicit lebih jelas)

  @@map("users")
}

enum UserStatus {
  ACTIVE
  SUSPENDED
  DELETED
}
```

---

## 🔄 Migration Strategy

### Workflow

```bash
# 1. Buat migration
npx prisma migrate dev --name add_user_avatar

# Ini generate:
# - prisma/migrations/[timestamp]_add_user_avatar/migration.sql
# - Apply ke local DB
# - Regenerate Prisma Client

# 2. Review SQL yang di-generate
cat prisma/migrations/[timestamp]_add_user_avatar/migration.sql

# 3. Test di local
npm run dev

# 4. Commit
git add prisma/migrations
git commit -m "feat(db): add avatar_url to users"

# 5. Push → CI validate
# 6. Merge ke develop → migrate di staging
# 7. PR ke main → manual approval → migrate di production
```

### ⚠️ Migration Safety Rules

- ✅ **SELALU** tulis reversible migration
- ✅ **Test di staging** sebelum production
- ✅ **Backup DB** sebelum destructive migration
- ✅ **Backward-compatible** — kode lama harus tetap jalan dengan schema baru
- ❌ **JANGAN** rename column langsung (akan break existing data)
- ❌ **JANGAN** drop column langsung (2-step: add new → migrate data → drop old)
- ❌ **JANGAN** ubah tipe data yang bisa kehilangan data

### Zero-Downtime Migration Pattern

Untuk perubahan schema besar, pakai pattern 3-step:

```
Step 1: ADD new column (backward compatible)
Step 2: Migrate data & deploy code yang pakai new column
Step 3: DROP old column (di release berikutnya)
```

Contoh:

```sql
-- Step 1: Add new column
ALTER TABLE users ADD COLUMN full_name VARCHAR(255);

-- Step 2: Backfill data (bisa di background)
UPDATE users SET full_name = CONCAT(first_name, ' ', last_name) WHERE full_name IS NULL;

-- Step 3: (di release berikutnya, setelah code pakai full_name)
ALTER TABLE users DROP COLUMN first_name;
ALTER TABLE users DROP COLUMN last_name;
```

---

## 💾 Backup & Recovery

### Backup Strategy

| Env        | Frequency               | Retention | Storage   |
| ---------- | ----------------------- | --------- | --------- |
| Local      | ❌                      | -         | -         |
| Staging    | Daily                   | 7 hari    | Neon auto |
| Production | **Hourly** + Daily full | 30 hari   | Neon + S3 |

### Automated Backup (Neon)

Neon punya built-in backup:

- ✅ Point-in-time recovery (PITR)
- ✅ Daily full backup
- ✅ Retained per plan

### Manual Backup (On-Demand)

```bash
# Export production data (sebelum migration besar)
pg_dump $DATABASE_URL > backup_$(date +%Y%m%d_%H%M%S).sql

# Restore dari backup
psql $DATABASE_URL < backup_20250115_103000.sql
```

⚠️ **JANGAN** dump production ke local tanpa anonymization!

---

## 🚨 Disaster Recovery

### RTO (Recovery Time Objective): 1 jam

### RPO (Recovery Point Objective): 1 jam (via hourly backup)

### Recovery Process

1. **Detect** — monitoring alert triggered
2. **Assess** — seberapa parah kerusakan
3. **Communicate** — notify stakeholders
4. **Activate backup DB** — restore dari backup terakhir
5. **Verify** — cek data integrity
6. **Resume** — switch traffic ke recovered DB
7. **Post-mortem** — dalam 48 jam

### Commands

```bash
# Point-in-time recovery (Neon)
# Via dashboard: Project → Restore → pilih timestamp

# Restore dari SQL dump
psql $DATABASE_URL < backup_latest.sql

# Verify data
psql $DATABASE_URL -c "SELECT COUNT(*) FROM users;"
```

---

## ⚡ Performance Optimization

### Query Optimization

```typescript
// ❌ Bad — N+1 query
const orders = await prisma.order.findMany();
for (const order of orders) {
  order.user = await prisma.user.findUnique({ where: { id: order.userId } });
}

// ✅ Good — pakai include/select
const orders = await prisma.order.findMany({
  include: { user: true },
});
```

### Connection Pooling

```typescript
// lib/db.ts
import { PrismaClient } from "@prisma/client";

const globalForPrisma = global as unknown as { prisma: PrismaClient };

export const prisma =
  globalForPrisma.prisma ||
  new PrismaClient({
    log:
      process.env.NODE_ENV === "development"
        ? ["query", "warn", "error"]
        : ["error"],
  });

if (process.env.NODE_ENV !== "production") globalForPrisma.prisma = prisma;
```

### Slow Query Monitoring

```typescript
// Enable slow query log
const prisma = new PrismaClient({
  log: [{ emit: "event", level: "query" }],
});

prisma.$on("query", (e) => {
  if (e.duration > 1000) {
    // > 1 detik
    logger.warn("Slow query detected", {
      duration: e.duration,
      query: e.query,
    });
  }
});
```

---

## 🧹 Data Lifecycle

### Soft Delete

```prisma
model User {
  id        String    @id @default(uuid())
  email     String
  deletedAt DateTime?

  @@index([deletedAt])
}
```

```typescript
// Soft delete
await prisma.user.update({
  where: { id },
  data: { deletedAt: new Date() },
});

// Query exclude soft deleted
await prisma.user.findMany({
  where: { deletedAt: null },
});

// Hard delete (only for compliance / GDPR)
await prisma.user.delete({ where: { id } });
```

### Archive Strategy

Data lama (> 1 tahun) di-archive ke table terpisah atau cold storage:

```typescript
// Archive users yang sudah dihapus > 90 hari
const cutoffDate = new Date();
cutoffDate.setDate(cutoffDate.getDate() - 90);

await prisma.$transaction([
  prisma.userArchive.createMany({
    data: await prisma.user.findMany({
      where: { deletedAt: { lt: cutoffDate } },
    }),
  }),
  prisma.user.deleteMany({
    where: { deletedAt: { lt: cutoffDate } },
  }),
]);
```

### Data Retention Policy

| Data                 | Retention | Action After            |
| -------------------- | --------- | ----------------------- |
| User account aktif   | Selamanya | -                       |
| User account deleted | 30 hari   | Hard delete             |
| Audit log            | 1 tahun   | Archive to cold storage |
| Activity log         | 90 hari   | Hard delete             |
| Session              | 7 hari    | Auto expire             |
| Backup               | 30 hari   | Auto delete             |

---

## 🔒 Security Best Practices

- ✅ **Parameterized queries** (Prisma default)
- ✅ **Encrypted connections** (SSL/TLS)
- ✅ **Least privilege DB user** (no superuser di app)
- ✅ **Rotate DB credentials** setiap 90 hari
- ✅ **Encrypt sensitive fields** (PII) di application level
- ❌ **JANGAN** simpan password plain text
- ❌ **JANGAN** log query dengan data sensitif
- ❌ **JANGAN** expose DB port ke public

```typescript
// Encrypt PII field
import { encrypt, decrypt } from "@/lib/crypto";

// Save
await prisma.user.create({
  data: {
    email: user.email,
    phone: encrypt(user.phone),
  },
});

// Read
const user = await prisma.user.findUnique({ where: { id } });
const phone = user.phone ? decrypt(user.phone) : null;
```

---

## 📊 Database Monitoring

Metrics yang harus dimonitor:

- **Connection count** — jangan > 80% dari max
- **Query duration** — p95, p99
- **Slow queries** — > 1 detik
- **Lock contention** — deadlock
- **Disk usage** — jangan > 80%
- **Replication lag** — kalau pakai replica
- **Backup success** — daily

Tools:

- Neon dashboard (built-in)
- Sentry (slow query tracking)
- Custom logging

---

## 📋 Database Checklist

### Initial Setup

- [ ] Setup DB per environment (dev/staging/production)
- [ ] Define schema dengan Prisma
- [ ] Create initial migration
- [ ] Setup seed data untuk staging
- [ ] Setup backup automation
- [ ] Setup monitoring
- [ ] Document schema di diagram

### Per-Sprint

- [ ] Review migration baru sebelum merge
- [ ] Test migration di staging
- [ ] Verify backup masih jalan
- [ ] Check slow query log
- [ ] Cleanup unused data
