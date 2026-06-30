
# API Design Standards

> Panduan desain API yang konsisten, RESTful, dan developer-friendly.

## 🎯 Prinsip Desain

1. **Consistency** — semua endpoint pakai pola yang sama
2. **Predictability** — developer bisa tebak response tanpa baca docs
3. **Security** — by default, secure
4. **Versioning** — perubahan tidak break existing client
5. **Documentation** — self-documenting lewat naming & structure

---

## 📍 URL Structure

### Base URL

```
Dev:       http://localhost:3000/api/v1
Staging:   https://staging.example.com/api/v1
Production: https://app.example.com/api/v1
```

### Resource Naming

✅ **DO:**

- Pakai noun (bukan verb) → `/users`, `/orders`, `/items`
- Plural untuk collection → `/users`, bukan `/user`
- Kebab-case untuk multi-word → `/order-items`, bukan `/orderItems`
- Hierarchical untuk relasi → `/users/{id}/orders`

❌ **DON'T:**

- Verb di URL → `/getUsers`, `/createOrder`
- CamelCase → `/orderItems`
- Singular untuk collection → `/user/123`

### Contoh Struktur

```
GET    /api/v1/users              # List users
POST   /api/v1/users              # Create user
GET    /api/v1/users/{id}         # Get user by ID
PUT    /api/v1/users/{id}         # Update user (full)
PATCH  /api/v1/users/{id}         # Update user (partial)
DELETE /api/v1/users/{id}         # Delete user

GET    /api/v1/users/{id}/orders  # Get user's orders
POST   /api/v1/users/{id}/orders  # Create order for user

GET    /api/v1/orders             # List orders (with filter)
GET    /api/v1/orders/{id}        # Get order

POST   /api/v1/auth/login         # Auth endpoint (verb OK karena action)
POST   /api/v1/auth/logout
POST   /api/v1/auth/refresh
```

---

## 📊 HTTP Methods

| Method     | Use Case                  | Idempotent | Safe   |
| ---------- | ------------------------- | ---------- | ------ |
| **GET**    | Read resource             | ✅ Yes     | ✅ Yes |
| **POST**   | Create resource / action  | ❌ No      | ❌ No  |
| **PUT**    | Replace resource (full)   | ✅ Yes     | ❌ No  |
| **PATCH**  | Update resource (partial) | ❌ No      | ❌ No  |
| **DELETE** | Remove resource           | ✅ Yes     | ❌ No  |

---

## 📬 Request Format

### Headers (WAJIB)

```http
Content-Type: application/json
Authorization: Bearer {token}        # untuk protected endpoint
Accept: application/json
X-Request-ID: {uuid}                # untuk tracing
```

### Body (POST/PUT/PATCH)

```json
{
  "email": "user@example.com",
  "name": "John Doe",
  "password": "secure-password"
}
```

### Query Parameters (GET)

```
GET /api/v1/users?page=1&perPage=20&sort=createdAt:desc&status=active
```

**Common params:**

- `page` — halaman (default: 1)
- `perPage` — jumlah per halaman (default: 20, max: 100)
- `sort` — field:direction (contoh: `createdAt:desc`)
- `filter[field]` — filter berdasarkan field
- `search` — full-text search
- `include` — relasi yang mau di-include

---

## 📤 Response Format

### Success Response

```json
{
  "success": true,
  "data": {
    "id": "uuid",
    "email": "user@example.com",
    "name": "John Doe",
    "createdAt": "2025-01-15T10:00:00Z"
  },
  "meta": {
    "timestamp": "2025-01-15T10:00:00Z"
  }
}
```

### List Response (dengan pagination)

```json
{
  "success": true,
  "data": [
    { "id": "1", "name": "User 1" },
    { "id": "2", "name": "User 2" }
  ],
  "meta": {
    "page": 1,
    "perPage": 20,
    "total": 150,
    "totalPages": 8,
    "timestamp": "2025-01-15T10:00:00Z"
  }
}
```

### Error Response

```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Email tidak valid",
    "details": {
      "field": "email",
      "reason": "format"
    }
  }
}
```

### Validation Error (Multiple Fields)

```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Input tidak valid",
    "details": [
      { "field": "email", "message": "Format email tidak valid" },
      { "field": "password", "message": "Password minimal 8 karakter" }
    ]
  }
}
```

---

## 🔢 HTTP Status Codes

### Success (2xx)

| Code  | Use Case                                    |
| ----- | ------------------------------------------- |
| `200` | OK — GET, PUT, PATCH success                |
| `201` | Created — POST success, return new resource |
| `204` | No Content — DELETE success, no body        |

### Client Error (4xx)

| Code  | Use Case                                                             |
| ----- | -------------------------------------------------------------------- |
| `400` | Bad Request — malformed syntax                                       |
| `401` | Unauthorized — not authenticated                                     |
| `403` | Forbidden — authenticated tapi no permission                         |
| `404` | Not Found — resource tidak ada                                       |
| `409` | Conflict — duplicate, state conflict                                 |
| `422` | Unprocessable Entity — validation passed tapi business rule violated |
| `429` | Too Many Requests — rate limit exceeded                              |

### Server Error (5xx)

| Code  | Use Case                                    |
| ----- | ------------------------------------------- |
| `500` | Internal Server Error — unexpected error    |
| `502` | Bad Gateway — upstream service error        |
| `503` | Service Unavailable — maintenance, overload |
| `504` | Gateway Timeout — upstream timeout          |

---

## 🔐 Authentication & Authorization

### Authentication

```http
GET /api/v1/users/me
Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
```

### Authorization Rules

- **Public:** tidak perlu auth (register, login, health check)
- **Authenticated:** perlu valid token
- **Owner:** user hanya bisa akses resource miliknya
- **Admin:** khusus admin role

### Error Response untuk Auth Issues

```json
// 401 — belum login
{ "success": false, "error": { "code": "UNAUTHORIZED", "message": "Silakan login terlebih dahulu" } }

// 403 — tidak punya akses
{ "success": false, "error": { "code": "FORBIDDEN", "message": "Anda tidak punya akses ke resource ini" } }
```

---

## 📋 Pagination

### Offset-based (Recommended untuk admin/UI)

```http
GET /api/v1/users?page=1&perPage=20
```

**Response:**

```json
{
  "data": [...],
  "meta": {
    "page": 1,
    "perPage": 20,
    "total": 150,
    "totalPages": 8,
    "hasNext": true,
    "hasPrev": false
  }
}
```

### Cursor-based (Untuk infinite scroll / mobile)

```http
GET /api/v1/feed?cursor=eyJpZCI6MTIzfQ&limit=20
```

**Response:**

```json
{
  "data": [...],
  "meta": {
    "nextCursor": "eyJpZCI6MTQzfQ",
    "hasMore": true
  }
}
```

---

## 🔍 Filtering, Sorting, Search

### Filter

```http
GET /api/v1/users?filter[status]=active&filter[role]=admin
```

### Sort

```http
GET /api/v1/users?sort=createdAt:desc
GET /api/v1/users?sort=name:asc,createdAt:desc
```

### Search (Full-text)

```http
GET /api/v1/users?search=john
```

### Field Selection (Sparse Fieldsets)

```http
GET /api/v1/users?fields=id,name,email
```

---

## 📦 Versioning

### Strategy: URL Path Versioning

```
/api/v1/users
/api/v2/users
```

### Rules

- ✅ **Increment major version** untuk breaking change
- ✅ **Support old version** minimal 6 bulan setelah deprecation
- ✅ **Announce deprecation** di response header & docs
- ✅ **New version** bisa coexist dengan old
- ❌ **Jangan hapus old version** mendadak

### Deprecation Header

```http
Sunset: Sat, 31 Dec 2025 23:59:59 GMT
Deprecation: true
Link:</api/v2/users>; rel="successor-version"
```

---

## 🛡️ Security Standards

### WAJIB di Setiap Endpoint

- ✅ **Input validation** (Zod schema)
- ✅ **Authorization check** (siapa yang boleh)
- ✅ **Rate limiting** (untuk endpoint sensitif)
- ✅ **HTTPS only** (production)
- ✅ **No sensitive data in response** (no password hash, no token)

### Rate Limiting Headers

```http
X-RateLimit-Limit: 100
X-RateLimit-Remaining: 95
X-RateLimit-Reset: 1640995200
```

### Error Response — Jangan Leak Detail

```json
// ❌ Buruk — leak detail internal
{ "error": "PrismaClientKnownRequestError: Invalid `prisma.user.create()` invocation: ..." }

// ✅ Bagus — user-friendly
{ "success": false, "error": { "code": "INTERNAL_ERROR", "message": "Terjadi kesalahan. Silakan coba lagi." } }
```

**Detail error hanya di log server (untuk debugging), bukan di response.**

---

## 🧪 Testing API

### Tools

- **Unit/Integration:** Vitest + Supertest
- **E2E:** Playwright
- **Manual:** Postman / Insomnia / Thunder Client
- **Auto-docs:** OpenAPI / Swagger

### Contoh Integration Test

```typescript
// app/api/v1/users/route.test.ts
import { POST } from "./route";

describe("POST /api/v1/users", () => {
  it("creates user with valid input", async () => {
    const req = new Request("http://localhost/api/v1/users", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        email: "test@example.com",
        name: "Test User",
        password: "password123",
      }),
    });

    const res = await POST(req);
    const json = await res.json();

    expect(res.status).toBe(201);
    expect(json.success).toBe(true);
    expect(json.data.email).toBe("test@example.com");
    expect(json.data.passwordHash).toBeUndefined();
    expect(json.data.password).toBeUndefined();
  });

  it("returns 400 for invalid email", async () => {
    const req = new Request("http://localhost/api/v1/users", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        email: "not-an-email",
        name: "Test",
        password: "password123",
      }),
    });

    const res = await POST(req);
    expect(res.status).toBe(400);
  });
});
```

---

## 📚 API Documentation

### OpenAPI / Swagger

Generate docs otomatis dari Zod schema atau TypeScript types:

```typescript
// lib/openapi.ts
import { extendZodWithOpenApi } from "@asteasolutions/zod-to-openapi";
import { z } from "zod";

extendZodWithOpenApi(z);

export const UserSchema = z
  .object({
    id: z
      .string()
      .uuid()
      .openapi({ example: "123e4567-e89b-12d3-a456-426614174000" }),
    email: z.string().email().openapi({ example: "user@example.com" }),
    name: z.string().openapi({ example: "John Doe" }),
  })
  .openapi("User");
```

### Docs URL

```
Development: http://localhost:3000/api/docs
Staging:     https://staging.example.com/api/docs
Production:  https://app.example.com/api/docs (opsional public)
```

---

## 📋 API Design Checklist

### Per Endpoint

- [ ] URL RESTful (noun, plural, kebab-case)
- [ ] HTTP method sesuai (GET/POST/PUT/PATCH/DELETE)
- [ ] Input validation (Zod schema)
- [ ] Authorization check
- [ ] Rate limiting (jika perlu)
- [ ] Response format konsisten (success/error)
- [ ] Status code sesuai
- [ ] No sensitive data di response
- [ ] No internal error detail di response
- [ ] Pagination untuk list
- [ ] Filter/sort untuk list
- [ ] Test (unit + integration)
- [ ] Docs updated
