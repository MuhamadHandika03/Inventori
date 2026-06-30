
# Security Checklist — WAJIB Setiap Fitur

> Setiap kali membuat/mengubah fitur, **WAJIB** jawab 7 pertanyaan ini.

## 🔒 7 Security Questions

### 1. Input Validation ✅

**Pertanyaan:** Apakah input dari user divalidasi di SERVER?

```typescript
// ✅ Bagus — pakai Zod schema di API route
const schema = z.object({
  email: z.string().email(),
  password: z.string().min(8),
});
const validated = schema.parse(input);

// ❌ Buruk — hanya validasi di client
if (!email.includes("@")) {
  /* ... */
} // bisa di-bypass
```

- [ ] Validasi di API route, bukan hanya form
- [ ] Tipe data sesuai (string, number, dll)
- [ ] Format sesuai (email, URL, dll)
- [ ] Length min/max dicek
- [ ] Special characters di-escape

---

### 2. Authorization ✅

**Pertanyaan:** Apakah dicek SIAPA yang boleh akses?

- [ ] Ada authentication check (sudah login?)
- [ ] Ada authorization check (role/permission sesuai?)
- [ ] Resource ownership dicek (user hanya bisa akses data miliknya)
- [ ] Default deny (jika tidak jelas → tolak)

```typescript
// ✅ Bagus
if (session.user.id !== resource.ownerId) {
  return NextResponse.json({ error: "Forbidden" }, { status: 403 });
}

// ❌ Buruk — langsung return data tanpa cek ownership
const data = await db.item.findUnique({ where: { id } });
return NextResponse.json(data);
```

---

### 3. Sensitive Data Logging ✅

**Pertanyaan:** Apakah ada data sensitif di-log?

**JANGAN PERNAH log:**

- ❌ Password (plain atau hash)
- ❌ API keys / tokens
- ❌ Session tokens / JWT
- ❌ Credit card / payment info
- ❌ PII (NIK, KTP, full address, phone) tanpa masking
- ❌ Cookie value

```typescript
// ✅ Bagus
logger.info("User registered", {
  userId: user.id,
  email: maskEmail(user.email),
});

// ❌ Buruk
logger.info("User registered", { email: user.email, password: user.password });
```

---

### 4. SQL Injection ✅

**Pertanyaan:** Apakah query aman dari SQL injection?

- [ ] Pakai ORM (Prisma, Drizzle) — auto parameterized
- [ ] JANGAN pakai raw SQL string concatenation
- [ ] JANGAN pakai `prisma.$queryRaw` tanpa parameterization

```typescript
// ✅ Bagus — Prisma auto-escape
await prisma.user.findMany({ where: { email: userInput } });

// ✅ Bagus — parameterized raw query
await prisma.$queryRaw`SELECT * FROM users WHERE email = ${userInput}`;

// ❌ Buruk — SQL injection risk
await prisma.$queryRawUnsafe(
  `SELECT * FROM users WHERE email = '${userInput}'`,
);
```

---

### 5. XSS (Cross-Site Scripting) ✅

**Pertanyaan:** Apakah output aman dari XSS?

- [ ] React auto-escape → aman secara default
- [ ] JANGAN pakai `dangerouslySetInnerHTML` tanpa sanitization
- [ ] Pakai DOMPurify jika memang perlu render HTML

```typescript
// ✅ Bagus — React auto-escape
<div>{userInput</div>

// ❌ Buruk — XSS risk
<div dangerouslySetInnerHTML={{ __html: userInput }} />

// ✅ Kalau terpaksa — sanitize dulu
<div dangerouslySetInnerHTML={{ __html: DOMPurify.sanitize(userInput) }} />
```

---

### 6. Rate Limiting ✅

**Pertanyaan:** Apakah endpoint perlu rate limiting?

**WAJIB rate limit untuk:**

- 🔴 Login (max 5 attempts / 15 min / IP)
- 🔴 Register (max 3 / jam / IP)
- 🔴 Forgot password (max 3 / jam / email)
- 🔴 OTP verification (max 5 / 15 min)
- 🟡 API endpoints yang berat
- 🟡 File upload

```typescript
// Contoh dengan Upstash Ratelimit
import { Ratelimit } from "@upstash/ratelimit";

const ratelimit = new Ratelimit({
  redis,
  limiter: Ratelimit.slidingWindow(5, "15 m"),
});

const { success } = await ratelimit.limit(ip);
if (!success) return new Response("Too Many Requests", { status: 429 });
```

---

### 7. CSRF (Cross-Site Request Forgery) ✅

**Pertanyaan:** Apakah mutation request aman dari CSRF?

**Mitigasi:**

- ✅ SameSite cookies (`SameSite=Strict` atau `Lax`)
- ✅ CSRF token untuk form (jika pakai cookie-based auth)
- ✅ Cek `Origin` header di API route
- ✅ Bearer token di header (tidak rentan CSRF)

```typescript
// ✅ SameSite cookies
cookies().set("session", token, {
  httpOnly: true,
  secure: true,
  sameSite: "strict",
});
```

---

## 🛡️ Security Headers (Set di Next.js/Vercel)

Pastikan headers ini aktif:

```
X-Content-Type-Options: nosniff
X-Frame-Options: DENY
X-XSS-Protection: 1; mode=block
Referrer-Policy: strict-origin-when-cross-origin
Strict-Transport-Security: max-age=31536000; includeSubDomains; preload
Content-Security-Policy: default-src 'self'; ...
```

---

## 📋 Sensitive Data yang Harus Dilindungi

| Data          | Protection                                 |
| ------------- | ------------------------------------------ |
| Password      | bcrypt/argon2 hash, never plain, never log |
| API keys      | Environment variable, never commit         |
| JWT secret    | Environment variable, strong (min 32 char) |
| Database URL  | Environment variable, never public         |
| PII           | Encrypt at rest, mask in logs              |
| Session token | httpOnly + secure + sameSite cookie        |
| File upload   | Validate type, size, scan virus            |

---

## 🚨 Security Incident? (Lihat juga INCIDENT_RESPONSE.md)

Jika обнаружил vulnerability atau breach:

1. **STOP** — jangan lanjut coding
2. **ROTATE** secrets jika terkompromi
3. **REPORT** ke tech lead immediately
4. **DOCUMENT** insiden & fix
