
# Code Examples — Good vs Bad

> Contoh kode yang benar vs salah sesuai standar. Referensi cepat saat coding.

---

## 1. TypeScript Types

### ❌ Bad

```typescript
function getUser(id: any) {
  return fetch(`/api/users/${id}`).then((res) => res.json());
}

const user = getUser("123");
user.name; // bisa apa aja, no type safety
```

### ✅ Good

```typescript
interface User {
  id: string;
  name: string;
  email: string;
}

async function getUser(id: string): Promise<User> {
  const res = await fetch(`/api/users/${id}`);
  if (!res.ok) throw new Error("Failed to fetch user");
  return res.json();
}

const user = await getUser("123");
user.name; // type-safe, auto-complete works
```

---

## 2. Component Structure

### ❌ Bad

```typescript
export default function UserProfile({ userId, showEmail, onUpdate }) {
  const [user, setUser] = useState();
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState();

  useEffect(() => {
    setLoading(true);
    fetch(`/api/users/${userId}`)
      .then(res => res.json())
      .then(data => setUser(data))
      .catch(err => setError(err))
      .finally(() => setLoading(false));
  }, [userId]);

  async function handleSave() {
    await fetch(`/api/users/${userId}`, { method: 'PUT', body: JSON.stringify(user) });
    onUpdate();
  }

  if (loading) return <div>Loading</div>;
  if (error) return <div>Error</div>;

  return (
    <div>
      <h1>{user.name</h1>
      {showEmail && <p>{user.email</p>}
      <button onClick={handleSave}>Save</button>
   </div>
  );
}
```

### ✅ Good

```typescript
// types
interface UserProfileProps {
  userId: string;
  showEmail?: boolean;
  onUpdate?: () => void;
}

interface User {
  id: string;
  name: string;
  email: string;
}

// service layer (terpisah)
async function fetchUser(id: string): Promise<User> {
  const res = await fetch(`/api/users/${id}`);
  if (!res.ok) throw new Error('Failed to fetch user');
  return res.json();
}

async function updateUser(id: string, data: Partial<User>): Promise<User> {
  const res = await fetch(`/api/users/${id}`, {
    method: 'PUT',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(data),
  });
  if (!res.ok) throw new Error('Failed to update user');
  return res.json();
}

// custom hook (terpisah)
function useUser(id: string) {
  return useQuery({
    queryKey: ['user', id],
    queryFn: () => fetchUser(id),
  });
}

// component
export const UserProfile = ({ userId, showEmail = false, onUpdate }: UserProfileProps) => {
  const { data: user, isLoading, error } = useUser(userId);
  const mutation = useMutation({
    mutationFn: (data: Partial<User>) => updateUser(userId, data),
    onSuccess: () => {
      onUpdate?.();
      queryClient.invalidateQueries(['user', userId]);
    },
  });

  if (isLoading) return <UserProfileSkeleton />;
  if (error) return <ErrorMessage error={error} />;
  if (!user) return <EmptyState />;

  return (
    <div className="user-profile">
      <h1>{user.name</h1>
      {showEmail && <p className="user-email">{user.email</p>}
      <Button onClick={() => mutation.mutate(user)} isLoading={mutation.isLoading}>
        Save
     </Button>
   </div>
  );
};
```

**Perbaikan:**

- ✅ Props punya interface
- ✅ Service layer terpisah (bukan fetch di component)
- ✅ Custom hook untuk data fetching
- ✅ Loading/error/empty state proper
- ✅ Type safety end-to-end

---

## 3. API Route Handler

### ❌ Bad

```typescript
export async function POST(req: Request) {
  const body = await req.json();
  const user = await db.user.create({ data: body });
  return Response.json(user);
}
```

**Masalah:**

- ❌ Tidak ada validasi input
- ❌ Tidak ada error handling
- ❌ Response format tidak konsisten
- ❌ Tidak cek authorization
- ❌ Bisa return password hash

### ✅ Good

```typescript
// validators/user.ts
import { z } from "zod";

export const createUserSchema = z.object({
  email: z.string().email(),
  password: z.string().min(8).max(100),
  name: z.string().min(1).max(100),
});

export type CreateUserInput = z.infer<typeof createUserSchema>;

// services/user.service.ts
import { hash } from "bcrypt";
import { prisma } from "@/lib/db";
import type { CreateUserInput } from "@/lib/validators/user";

export async function createUser(input: CreateUserInput) {
  const existingUser = await prisma.user.findUnique({
    where: { email: input.email },
  });
  if (existingUser) {
    throw new ConflictError("Email already registered");
  }

  const passwordHash = await hash(input.password, 12);
  const user = await prisma.user.create({
    data: {
      email: input.email,
      name: input.name,
      passwordHash,
    },
    select: { id: true, email: true, name: true, createdAt: true }, // JANGAN return passwordHash
  });

  return user;
}

// app/api/v1/users/route.ts
import { createUserSchema } from "@/lib/validators/user";
import { createUser } from "@/services/user.service";
import { handleApiError } from "@/lib/api-error";
import { requireAdmin } from "@/lib/auth";

export async function POST(req: Request) {
  try {
    // 1. Authorization
    await requireAdmin();

    // 2. Validation
    const body = await req.json();
    const input = createUserSchema.parse(body);

    // 3. Business logic
    const user = await createUser(input);

    // 4. Response
    return Response.json(
      {
        success: true,
        data: user,
        meta: { timestamp: new Date().toISOString() },
      },
      { status: 201 },
    );
  } catch (error) {
    return handleApiError(error);
  }
}
```

**Perbaikan:**

- ✅ Validasi dengan Zod schema
- ✅ Service layer terpisah
- ✅ Authorization check (admin only)
- ✅ Error handling centralized
- ✅ Response format konsisten
- ✅ Password di-hash, tidak di-return

---

## 4. Error Handling

### ❌ Bad

```typescript
async function getData() {
  const res = await fetch("/api/data");
  const data = await res.json();
  return data;
}

// Di component
const data = await getData(); // bisa crash
```

### ✅ Good

```typescript
async function getData(): Promise<Data> {
  try {
    const res = await fetch('/api/data');
    if (!res.ok) {
      throw new ApiError(`HTTP ${res.status}`, res.status);
    }
    const data = await res.json();
    return data;
  } catch (error) {
    logger.error('Failed to fetch data', { error, url: '/api/data' });
    throw error; // re-throw, biar caller handle
  }
}

// Di component
function MyComponent() {
  const { data, isLoading, error } = useQuery({ queryKey: ['data'], queryFn: getData });

  if (isLoading) return <Loading />;
  if (error) return <ErrorMessage error={error} onRetry={refetch} />;
  if (!data) return <EmptyState />;

  return <Display data={data} />;
}
```

---

## 5. Naming Conventions

### ❌ Bad

```typescript
const x = 5;
const data = fetchUser(); // terlalu generic
function process() { ... }
function DoStuff() { ... } // bukan camelCase
function getUsers() { ... } // tapi tidak ada type
```

### ✅ Good

```typescript
const MAX_RETRY_COUNT = 5;
const userProfile = fetchUserProfile(); // deskriptif
function processPayment() { ... }
function doStuff() { ... } // verb + noun
async function getUsers(): Promise<User[]> { ... }
```

---

## 6. Security

### ❌ Bad

```typescript
// SQL injection risk
const query = `SELECT * FROM users WHERE email = '${email}'`;
await db.execute(query);

// XSS risk
<div dangerouslySetInnerHTML={{ __html: userInput }} />

// Password di-log
console.log('User login:', { email, password });

// Hardcoded secret
const API_KEY = 'sk_live_abc123xyz';

// No auth check
export async function DELETE(req: Request) {
  await db.user.delete({ where: { id: req.params.id } });
}
```

### ✅ Good

```typescript
// Parameterized query (atau pakai ORM)
const user = await prisma.user.findUnique({ where: { email } });

// Escape output (React default)
// atau sanitize HTML
<div dangerouslySetInnerHTML={{ __html: DOMPurify.sanitize(userInput) }} />

// Password di-hash & tidak di-log
const passwordHash = await bcrypt.hash(password, 12);
logger.info('User registered', { userId, email: maskEmail(email) });

// Env var
const apiKey = process.env.API_KEY;
if (!apiKey) throw new Error('API_KEY not set');

// Auth check
export async function DELETE(req: Request) {
  const session = await requireAuth();
  await requireOwnership(session.userId, req.params.id);
  await db.user.delete({ where: { id: req.params.id } });
}
```

---

## 7. Loading & Error State

### ❌ Bad

```typescript
function UserList() {
  const [users, setUsers] = useState([]);

  useEffect(() => {
    fetch('/api/users').then(res => res.json()).then(setUsers);
  }, []);

  return (
    <ul>
      {users.map(u => <li key={u.id}>{u.name</li>)}
   </ul>
  );
}
```

**Masalah:** Tidak ada loading state, error state, empty state.

### ✅ Good

```typescript
function UserList() {
  const { data: users, isLoading, error } = useUsers();

  if (isLoading) return <UserListSkeleton />;
  if (error) return <ErrorMessage error={error} />;
  if (users.length === 0) return <EmptyState message="No users yet" />;

  return (
    <ul>
      {users.map(u => <UserListItem key={u.id} user={u} />)}
   </ul>
  );
}
```

---

## 8. Git Commit Messages

### ❌ Bad

```bash
git commit -m "update"
git commit -m "fix"
git commit -m "perubahan"
git commit -m "add feature"  // bahasa campur
git commit -m "feat: tambah" // terlalu pendek, tidak deskriptif
```

### ✅ Good

```bash
git commit -m "feat(auth): tambah Google OAuth login

- Implement OAuth 2.0 flow
- Tambah callback handler di /api/auth/callback/google
- Update schema User dengan googleId field

Refs: PROJ-123"

git commit -m "fix(profile): perbaiki upload avatar timeout

File upload gagal untuk file > 2MB karena timeout.
Fix: tambah chunked upload dan increase timeout ke 30s.

Closes: PROJ-456"
```

---

## 🎯 Cheat Sheet

| Mau bikin...     | Pattern                                               |
| ---------------- | ----------------------------------------------------- |
| Async data fetch | Hook + service layer, handle 3 state                  |
| API endpoint     | Validate (Zod) → Auth → Service → Response            |
| Form             | RHF + Zod, submit via service, show loading/error     |
| List/table       | Loading skeleton + empty state + error retry          |
| Error class      | Custom class per error type, centralized handler      |
| Component        | Interface props → hooks → handlers → render           |
| Service          | Pure function, typed input/output, throw custom error |
