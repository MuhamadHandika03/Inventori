
# Testing Strategy

> Panduan testing yang komprehensif: unit, integration, E2E.

## 🎯 Testing Pyramid

```
        ╱╲
       ╱  ╲         E2E Tests (sedikit)
      ╱ 10 ╲        - Critical user flows
     ╱______╲       - Slow, brittle
    ╱        ╲
   ╱   30-40  ╲     Integration Tests (sedang)
  ╱____________╲    - API endpoints
 ╱              ╲   - DB queries
╱     60-70      ╲  Unit Tests (banyak)
╱________________╲  - Pure functions
                     - Fast, isolated
```

**Coverage target:**

- Unit: 70%
- Integration: Key endpoints covered
- E2E: Critical user flows only

---

## 🧪 Unit Tests

### Tools

- **Framework:** Vitest (untuk Next.js) / Jest
- **Testing library:** @testing-library/react
- **Mocking:** MSW untuk API, vi.mock untuk module

### Apa yang Di-Test

- ✅ Pure functions (helpers, validators, formatters)
- ✅ Business logic (services)
- ✅ Custom hooks
- ✅ Component (render, interaction)
- ❌ Don't test: third-party library, framework code

### Contoh: Service Test

```typescript
// services/user.service.test.ts
import { describe, it, expect, beforeEach } from "vitest";
import { prismaMock } from "@/test/prisma-mock";
import { createUser } from "./user.service";
import { ConflictError } from "@/lib/errors";

describe("createUser", () => {
  beforeEach(() => {
    prismaMock.user.findUnique.mockReset();
    prismaMock.user.create.mockReset();
  });

  it("creates user with hashed password", async () => {
    prismaMock.user.findUnique.mockResolvedValue(null);
    prismaMock.user.create.mockResolvedValue({
      id: "1",
      email: "test@example.com",
      name: "Test",
      passwordHash: "hashed",
    });

    const user = await createUser({
      email: "test@example.com",
      password: "password123",
      name: "Test",
    });

    expect(user).toMatchObject({
      email: "test@example.com",
      name: "Test",
    });
    expect(user.passwordHash).not.toBe("password123"); // hashed
  });

  it("throws ConflictError if email exists", async () => {
    prismaMock.user.findUnique.mockResolvedValue({
      id: "1",
      email: "test@example.com",
    });

    await expect(
      createUser({
        email: "test@example.com",
        password: "password123",
        name: "Test",
      }),
    ).rejects.toThrow(ConflictError);
  });
});
```

### Contoh: Component Test

```typescript
// components/UserCard.test.tsx
import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { UserCard } from './UserCard';

describe('UserCard', () => {
  const mockUser = {
    id: '1',
    name: 'John Doe',
    email: 'john@example.com',
  };

  it('renders user name and email', () => {
    render(<UserCard user={mockUser} showEmail />);

    expect(screen.getByText('John Doe')).toBeInTheDocument();
    expect(screen.getByText('john@example.com')).toBeInTheDocument();
  });

  it('hides email when showEmail is false', () => {
    render(<UserCard user={mockUser} />);

    expect(screen.queryByText('john@example.com')).not.toBeInTheDocument();
  });

  it('calls onEdit when button clicked', async () => {
    const handleEdit = vi.fn();
    render(<UserCard user={mockUser} onEdit={handleEdit} />);

    await userEvent.click(screen.getByRole('button', { name: /edit/i }));

    expect(handleEdit).toHaveBeenCalledWith('1');
  });
});
```

---

## 🔌 Integration Tests

### Apa yang Di-Test

- ✅ API endpoints (full request-response cycle)
- ✅ Database queries (real DB atau test container)
- ✅ Authentication & authorization
- ✅ Validation logic
- ❌ Don't test: external services (mock them)

### Contoh: API Test

```typescript
// app/api/v1/users/route.test.ts
import { POST } from "./route";
import { prisma } from "@/lib/db";

describe("POST /api/v1/users", () => {
  beforeEach(async () => {
    await prisma.user.deleteMany();
  });

  it("creates user with valid input", async () => {
    const req = new Request("http://localhost", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        email: "test@example.com",
        password: "password123",
        name: "Test",
      }),
    });

    const res = await POST(req);
    const json = await res.json();

    expect(res.status).toBe(201);
    expect(json.success).toBe(true);
    expect(json.data.email).toBe("test@example.com");
    expect(json.data.passwordHash).toBeUndefined();
  });

  it("returns 400 for invalid email", async () => {
    const req = new Request("http://localhost", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        email: "invalid-email",
        password: "password123",
        name: "Test",
      }),
    });

    const res = await POST(req);
    const json = await res.json();

    expect(res.status).toBe(400);
    expect(json.success).toBe(false);
    expect(json.error.code).toBe("VALIDATION_ERROR");
  });

  it("returns 409 for duplicate email", async () => {
    await prisma.user.create({
      data: { email: "test@example.com", name: "Test", passwordHash: "hash" },
    });

    const req = new Request("http://localhost", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        email: "test@example.com",
        password: "password123",
        name: "Test",
      }),
    });

    const res = await POST(req);
    expect(res.status).toBe(409);
  });
});
```

---

## 🌐 E2E Tests (Playwright)

### Apa yang Di-Test

- ✅ Critical user flows (login, register, checkout)
- ✅ Cross-browser compatibility
- ✅ Happy path only (jangan terlalu detail)
- ❌ Don't test: edge cases (cukup di integration)

### Contoh: Login Flow

```typescript
// e2e/login.spec.ts
import { test, expect } from "@playwright/test";

test("user can login with valid credentials", async ({ page }) => {
  await page.goto("/login");

  await page.fill('input[name="email"]', "test@example.com");
  await page.fill('input[name="password"]', "password123");
  await page.click('button[type="submit"]');

  await expect(page).toHaveURL("/dashboard");
  await expect(page.locator("h1")).toContainText("Welcome");
});

test("user sees error with invalid credentials", async ({ page }) => {
  await page.goto("/login");

  await page.fill('input[name="email"]', "test@example.com");
  await page.fill('input[name="password"]', "wrong-password");
  await page.click('button[type="submit"]');

  await expect(page.locator('[role="alert"]')).toContainText(
    "Invalid credentials",
  );
});
```

---

## 🎯 Best Practices

### General

- ✅ **Test behavior, bukan implementation**
- ✅ **Descriptive test names** — `it('returns 400 when email is invalid')`
- ✅ **AAA pattern** — Arrange, Act, Assert
- ✅ **One assertion focus per test** (bisa multiple tapi related)
- ✅ **Independent tests** — tidak boleh bergantung urutan
- ❌ **Jangan test private methods**
- ❌ **Jangan copy-paste test setup** — pakai helper

### Test Data

- ✅ **Pakai factory** untuk generate test data
- ✅ **Reset DB** sebelum each test
- ✅ **Use realistic data** (jangan `test@test.com` saja)

### Mocking

- ✅ **Mock external services** (email, payment, storage)
- ✅ **Mock time** (untuk test yang time-sensitive)
- ❌ **Jangan over-mock** — kalau terlalu banyak mock, integration test lebih bermakna

---

## 📋 Testing Checklist

### Per Feature

- [ ] Unit test untuk service functions
- [ ] Component test untuk UI
- [ ] Integration test untuk API endpoint
- [ ] E2E test untuk critical flow
- [ ] Test untuk error cases
- [ ] Test untuk loading/empty state

### Per PR

- [ ] All tests passing
- [ ] Coverage tidak turun
- [ ] Tidak ada test yang di-skip (.skip) tanpa alasan
- [ ] New feature punya minimal 1 test

### Per Sprint

- [ ] Review test coverage report
- [ ] Refactor test yang flaky
- [ ] Remove obsolete tests
- [ ] Update test data factories
