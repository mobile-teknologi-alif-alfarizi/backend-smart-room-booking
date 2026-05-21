# User Management API Documentation

## Overview
API untuk manajemen user (CRUD operations). **Hanya role admin yang dapat mengakses semua endpoints ini.**

## Authentication
Semua endpoint memerlukan:
- **Header**: `Authorization: Bearer {token}`
- **Role**: Admin

## Base URL
```
http://localhost:8000/api/users
```

---

## Endpoints

### 1. Get All Users
**GET** `/api/users`

Mengambil daftar semua user.

#### Headers
```
Authorization: Bearer {token}
Content-Type: application/json
```

#### Response (Success - 200)
```json
{
  "success": true,
  "message": "Data user berhasil diambil",
  "data": [
    {
      "id": 1,
      "name": "John Doe",
      "nomor_induk": "123456789",
      "role": "admin",
      "fakultas": null,
      "program_studi": null,
      "created_at": "2024-01-15T10:30:00.000000Z",
      "updated_at": "2024-01-15T10:30:00.000000Z"
    },
    {
      "id": 2,
      "name": "Jane Smith",
      "nomor_induk": "987654321",
      "role": "mahasiswa",
      "fakultas": "Fakultas Ilmu Sosial & Bisnis",
      "program_studi": "Hubungan Internasional",
      "created_at": "2024-01-16T14:20:00.000000Z",
      "updated_at": "2024-01-16T14:20:00.000000Z"
    }
  ]
}
```

#### Response (Error - 403)
```json
{
  "success": false,
  "message": "Akses ditolak. Hanya admin yang dapat mengakses resource ini."
}
```

---

### 2. Get Single User
**GET** `/api/users/{id}`

Mengambil detail user berdasarkan ID.

#### Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| id | integer | Yes | User ID |

#### Headers
```
Authorization: Bearer {token}
Content-Type: application/json
```

#### Example
```
GET /api/users/1
```

#### Response (Success - 200)
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "John Doe",
    "nomor_induk": "123456789",
    "role": "admin",
    "fakultas": null,
    "program_studi": null,
    "created_at": "2024-01-15T10:30:00.000000Z",
    "updated_at": "2024-01-15T10:30:00.000000Z"
  }
}
```

#### Response (Error - 404)
```json
{
  "success": false,
  "message": "User tidak ditemukan"
}
```

---

### 3. Create User
**POST** `/api/users`

Membuat user baru.

#### Headers
```
Authorization: Bearer {token}
Content-Type: application/json
```

#### Request Body
```json
{
  "name": "New User",
  "nomor_induk": "111222333",
  "password": "password123",
  "password_confirmation": "password123",
  "role": "mahasiswa",
  "fakultas": "Fakultas Ilmu Sosial & Bisnis",
  "program_studi": "Hubungan Internasional"
}
```

#### Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| name | string | Yes | Nama user (max 255 char) |
| nomor_induk | string | Yes | Nomor induk (unique) |
| password | string | Yes | Password (min 6 char) |
| password_confirmation | string | Yes | Konfirmasi password (must match) |
| role | string | Yes | Role: `mahasiswa`, `dosen`, `admin` |
| fakultas | string | No | Fakultas (hanya untuk role mahasiswa) |
| program_studi | string | No | Program Studi (hanya untuk role mahasiswa) |

#### Response (Success - 201)
```json
{
  "success": true,
  "message": "User berhasil dibuat",
  "data": {
    "id": 3,
    "name": "New User",
    "nomor_induk": "111222333",
    "role": "mahasiswa",
    "fakultas": "Fakultas Ilmu Sosial & Bisnis",
    "program_studi": "Hubungan Internasional",
    "created_at": "2024-01-17T08:45:00.000000Z"
  }
}
```

#### Response (Error - 422 - Validation Failed)
```json
{
  "success": false,
  "message": "Validasi gagal",
  "errors": {
    "nomor_induk": [
      "The nomor induk has already been taken."
    ],
    "password": [
      "The passwords do not match."
    ]
  }
}
```

---

### 4. Update User
**PUT** `/api/users/{id}`

Memperbarui data user.

#### Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| id | integer | Yes | User ID |

#### Headers
```
Authorization: Bearer {token}
Content-Type: application/json
```

#### Request Body (all optional)
```json
{
  "name": "Updated Name",
  "nomor_induk": "999888777",
  "password": "newpassword123",
  "password_confirmation": "newpassword123",
  "role": "dosen",
  "fakultas": "Fakultas Sains & Teknologi",
  "program_studi": "Informatika"
}
```

#### Example
```
PUT /api/users/2
```

#### Response (Success - 200)
```json
{
  "success": true,
  "message": "User berhasil diupdate",
  "data": {
    "id": 2,
    "name": "Updated Name",
    "nomor_induk": "999888777",
    "role": "dosen",
    "fakultas": null,
    "program_studi": null,
    "created_at": "2024-01-16T14:20:00.000000Z",
    "updated_at": "2024-01-17T09:15:00.000000Z"
  }
}
```

#### Response (Error - 404)
```json
{
  "success": false,
  "message": "User tidak ditemukan"
}
```

---

### 5. Delete User
**DELETE** `/api/users/{id}`

Menghapus user.

#### Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| id | integer | Yes | User ID |

#### Headers
```
Authorization: Bearer {token}
Content-Type: application/json
```

#### Example
```
DELETE /api/users/3
```

#### Response (Success - 200)
```json
{
  "success": true,
  "message": "User berhasil dihapus"
}
```

#### Response (Error - 403 - Cannot delete yourself)
```json
{
  "success": false,
  "message": "Tidak dapat menghapus user sendiri"
}
```

#### Response (Error - 404)
```json
{
  "success": false,
  "message": "User tidak ditemukan"
}
```

---

## Error Responses

### 401 - Unauthorized
```json
{
  "success": false,
  "message": "Token invalid atau expired"
}
```

### 403 - Forbidden (Not Admin)
```json
{
  "success": false,
  "message": "Akses ditolak. Hanya admin yang dapat mengakses resource ini."
}
```

### 500 - Server Error
```json
{
  "success": false,
  "message": "Gagal {operation}: {error message}"
}
```

---

## Usage Examples

### Using cURL

#### Get All Users
```bash
curl -X GET http://localhost:8000/api/users \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json"
```

#### Create New User
```bash
curl -X POST http://localhost:8000/api/users \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Budi Santoso",
    "nomor_induk": "20240001",
    "password": "password123",
    "password_confirmation": "password123",
    "role": "mahasiswa"
  }'
```

#### Update User
```bash
curl -X PUT http://localhost:8000/api/users/2 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Budi Santoso Updated",
    "role": "dosen"
  }'
```

#### Delete User
```bash
curl -X DELETE http://localhost:8000/api/users/2 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json"
```

---

## Notes

1. **Admin Only**: Semua endpoint ini hanya dapat diakses oleh user dengan role `admin`
2. **Unique Nomor Induk**: Setiap nomor induk harus unik di database
3. **Password Hashing**: Password akan di-hash secara otomatis sebelum disimpan
4. **Self-Delete Protection**: User tidak dapat menghapus dirinya sendiri
5. **JWT Token**: Token diperoleh dari endpoint login `/api/auth/login`
6. **Token Expiry**: Token berlaku selama 12 jam tanpa aktivitas
