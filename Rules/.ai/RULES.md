
# Coding Rules — WAJIB Dipatuhi

> Aturan keras untuk setiap kode yang ditulis. Tidak ada exception kecuali diminta explicit.

## 1. Naming Conventions

### Variabel & Function
- **Format:** `camelCase`
- **Contoh:** `userName`, `isLoading`, `getUserById`
- **Boolean:** prefix `is/has/should/can` → `isVisible`, `hasAccess`
- **Async function:** prefix `fetch/load/save` → `fetchUser`, `loadData`
- **Event handler:** prefix `handle/on` → `handleClick`, `onSubmit`

### Component & Class
- **Format:** `PascalCase`
- **Contoh:** `UserProfile`, `LoginForm`, `DataTable`

### File & Folder
- **Component files:** `PascalCase.tsx` → `UserProfile.tsx`
- **Hook files:** `camelCase.ts` → `useAuth.ts`
- **Utility files:** `kebab-case.ts` → `format-date.ts`
- **Service files:** `kebab-case.service.ts` → `auth.service.ts`
- **Folders:** `kebab-case`

### Constants
- **Format:** `UPPER_SNAKE_CASE`
- **Contoh:** `MAX_RETRY`, `API_TIMEOUT`, `DEFAULT_PAGE_SIZE`

## 2. TypeScript Rules

### WAJIB
- ✅ **Selalu** definisikan tipe untuk props, args, return value
- ✅ **strict mode** aktif (noImplicitAny, strictNullChecks)
- ✅ **Hindari `any`** — gunakan `unknown` + type guard
- ✅ **Definisikan return type** untuk public functions
- ✅ **Gunakan `readonly`** untuk immutable data
- ✅ **Gunakan `as const`** untuk literal arrays/objects

### Interface vs Type
```typescript
// ✅ Interface untuk object shape (bisa di-extend)
interface UserProfile {
  id: string;
  name: string;
  email: string;
}

// ✅ Type untuk union, intersection, utility
type Status = 'pending' | 'active' | 'suspended';
````

## 3. Struktur File Component

**WAJIB urutan ini:**

```typescript
// 1. Imports (external → internal → types → styles)
import { useState } from 'react';
import { useAuth } from '@/hooks/useAuth';
import type { User } from '@/types';

// 2. Types & Interfaces
interface ComponentProps { ... }

// 3. Component
export const Component = (props: ComponentProps) => {
  // a. Hooks
  // b. Derived state
  // c. Handlers
  // d. Effects
  // e. Render
};

// 4. Default export (opsional)
```

## 4. Service Layer Rules

- ✅ **WAJIB** pisahkan business logic dari component
- ✅ **WAJIB** taruh di `src/services/[nama].service.ts`
- ✅ **WAJIB** return typed result (no `any`)
- ✅ **WAJIB** throw custom error class (bukan generic Error)
- ❌ **JANGAN** query database langsung di component
- ❌ **JANGAN** business logic di component/handler

## 5. API Response Format

### Success

```typescript
{
  success: true,
  data: { ... },
  meta?: { page, perPage, total, timestamp }
}
```

### Error

```typescript
{
  success: false,
  error: {
    code: string,        // "VALIDATION_ERROR" | "AUTH_ERROR" | dll
    message: string,     // User-friendly message
    details?: object     // Field-level errors (jika ada)
  }
}
```

### Status Codes

- `200` OK, `201` Created, `204` No Content
- `400` Bad Request, `401` Unauthorized, `403` Forbidden
- `404` Not Found, `409` Conflict, `422` Unprocessable
- `429` Rate Limited, `500` Internal Error

## 6. Error Handling

### Component (Frontend)

```typescript
// ✅ Selalu handle 3 state: loading, error, success
const { data, isLoading, error } = useQuery(...);
if (isLoading) return <Loading />;
if (error) return <ErrorMessage error={error} />;
return <Display data={data} />;
```

### API Route (Backend)

```typescript
// ✅ Wrap dengan try-catch + handleApiError
export async function POST(req: Request) {
  try {
    const body = await req.json();
    const validated = schema.parse(body);
    const result = await service.create(validated);
    return NextResponse.json({ success: true, data: result }, { status: 201 });
  } catch (error) {
    return handleApiError(error);
  }
}
```

## 7. Validation Rules

- ✅ **WAJIB** validasi di SERVER (Zod schema)
- ✅ Client validation hanya untuk UX (bukan security)
- ✅ Schema didefinisikan di `src/lib/validators/`
- ✅ Share schema antara client-server (Zod inference)
- ❌ **JANGAN** percaya input dari client

## 8. Loading & Error State (Frontend)

Setiap async operation **WAJIB** punya:

- Loading state (skeleton/spinner)
- Error state (error message + retry button)
- Empty state (jika list kosong)
- Success state

## 9. Comments Guidelines

```typescript
// ✅ Bagus — komentar WHY (business reason)
// Bcrypt cost 12 untuk balance security vs performance per OWASP 2025
const BCRYPT_COST = 12;

// ❌ Buruk — komentar WHAT (sudah jelas dari kode)
const count = 10; // set count ke 10
```

- ✅ Komentar untuk **WHY**, bukan **WHAT**
- ✅ JSDoc untuk public API
- ❌ Jangan komentar kode self-explanatory
- ❌ Jangan kode yang di-comment out

## 10. Import Rules

```typescript
// ✅ Urutan: external → internal (@/) → types → styles
import { useState } from "react";
import { someLib } from "some-lib";

import { useAuth } from "@/hooks/useAuth";
import { Button } from "@/components/ui/Button";

import type { User } from "@/types";

import styles from "./Component.module.css";

// ❌ JANGAN import yang tidak dipakai
// ❌ JANGAN circular dependency
```
