# 📦 Stockify Pro — API Documentation

**Base URL:** `http://localhost:8080` (atau `https://uneven-pyrotechnically-orion.ngrok-free.dev`)

**Auth:** Session-based (cookie). Login dulu → session otomatis, CSRF token dikasih.

---

## 1. Login
**`POST /auth.php`**

Request body:
```json
{
  "username": "admin",
  "password": "admin123"
}
```

Response (200):
```json
{
  "status": "success",
  "message": "Autentikasi Berhasil",
  "user": {
    "username": "admin",
    "role": "admin"
  },
  "csrf_token": "abc123..."
}
```

> **Postman:** Setelah ini, ambil `csrf_token` & set sebagai variable `csrf_token`. Cookie session otomatis.

---

## 2. Lihat Barang
**`GET /api.php`**

No params. Response: array of barang (non-deleted).

---

## 3. Lihat Kategori
**`GET /api.php?action=kategori`**

Response: array of kategori `[{id, name, code_prefix, created_at}]`.

---

## 4. Riwayat Transaksi
**`GET /api.php?action=riwayat`**

Response: array transaksi stok (join barang).

---

## 5. Tempat Sampah (Soft-deleted)
**`GET /api.php?action=trash`**

---

## 6. Tambah Barang (Admin only)
**`POST /api.php`**

Headers: `X-CSRF-Token: {{csrf_token}}`

```json
{
  "kategori_id": 1,
  "name": "Monitor LG 24\"",
  "stock": 10,
  "unit": "pcs",
  "price": 1500000
}
```

Auto-generate code (prefix-NNN).

---

## 7. Edit Barang (Admin only)
**`PUT /api.php?id={barang_id}`**

Headers: `X-CSRF-Token: {{csrf_token}}`

Body sama kayak POST (`kategori_id, name, stock, unit, price`).

---

## 8. Hapus Barang (Soft delete, Admin only)
**`DELETE /api.php?id={barang_id}`**

Headers: `X-CSRF-Token: {{csrf_token}}`

---

## 9. Hapus Permanen (Admin only)
**`DELETE /api.php?action=force&id={barang_id}`**

Headers: `X-CSRF-Token: {{csrf_token}}`

---

## 10. Pulihkan Barang (Admin only)
**`PUT /api.php?action=restore&id={barang_id}`**

Headers: `X-CSRF-Token: {{csrf_token}}`

---

## 11. Tambah Kategori (Admin only)
**`POST /api.php?action=kategori`**

Headers: `X-CSRF-Token: {{csrf_token}}`

```json
{
  "name": "Mouse",
  "code_prefix": "MOU"
}
```

---

## 12. Mutasi Stok (Masuk/Keluar)
**`POST /api.php?action=transaksi`**

Headers: `X-CSRF-Token: {{csrf_token}}`

```json
{
  "barang_id": 1,
  "jenis_transaksi": "masuk",
  "jumlah": 5,
  "keterangan": "Restock dari supplier"
}
```

`jenis_transaksi`: `"masuk"` atau `"keluar"`

---

## Flow Test di Postman

1. **Login** → simpan `csrf_token` sebagai variable
2. **GET /api.php** → lihat barang
3. **POST + action=transaksi** → mutasi stok (kirim CSRF)
4. **GET /api.php?action=riwayat** → cek log

Semua POST/PUT/DELETE butuh **X-CSRF-Token** header.
