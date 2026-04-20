# JWT Authentication REST API Documentation

Dokumentasi lengkap untuk REST API authentication dengan JWT.

## Struktur Database

Tabel `users` memiliki struktur berikut:

```sql
- id (Primary Key)
- name (string)
- nomor_induk (string, unique) - Username untuk login
- password (string, hashed)
- role (enum: 'admin', 'dosen', 'mahasiswa') - Default: 'mahasiswa'
- created_at (timestamp)
- updated_at (timestamp)
```

## Informasi Penting

### Token TTL (Time To Live)
- **Token berlaku selama 12 jam** setelah login
- Jika token tidak digunakan dalam 12 jam, token akan **otomatis expired**
- User harus login kembali untuk mendapatkan token baru
- User dapat **refresh token** sebelum expired (berlaku hingga 24 jam setelah login pertama)

### Blacklist & Logout
- Saat logout, token akan di-**blacklist** (tidak bisa digunakan lagi)
- Blacklist digunakan untuk memastikan token tidak bisa digunakan setelah logout
- Data blacklist disimpan di database tabel `jwt_blacklist`

## Endpoints

### 1. Login
**POST** `/api/auth/login`

Login menggunakan nomor_induk dan password untuk mendapatkan JWT token.

**Request Body:**
```json
{
  "nomor_induk": "123456789",
  "password": "password123"
}
```

**Response Success (200):**
```json
{
  "success": true,
  "message": "Login berhasil",
  "info": "Token berlaku selama 12 jam. Jika tidak digunakan, token akan otomatis dihapus dan Anda harus login kembali.",
  "data": {
    "user": {
      "id": 1,
      "name": "Nama User",
      "nomor_induk": "123456789",
      "role": "mahasiswa"
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "token_type": "Bearer",
    "expires_in": 43200,
    "expires_at": "2024-04-21T10:30:00Z"
  }
}
```

**Response Error (401):**
```json
{
  "success": false,
  "message": "Nomor Induk atau password salah"
}
```

**Response Validation Error (422):**
```json
{
  "success": false,
  "message": "Validasi gagal",
  "errors": {
    "nomor_induk": ["The nomor induk field is required."],
    "password": ["The password field is required."]
  }
}
```

---

### 2. Get User Profile
**GET** `/api/auth/me`

Mendapatkan profil user yang sedang login (memerlukan token).

**Headers:**
```
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
```

**Response Success (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Nama User",
    "nomor_induk": "123456789",
    "role": "mahasiswa",
    "created_at": "2024-01-15T10:30:00.000000Z"
  }
}
```

**Response Error - Invalid Token (401):**
```json
{
  "success": false,
  "message": "Token invalid atau expired"
}
```

---

### 3. Logout
**POST** `/api/auth/logout`

Logout dan invalidate token yang digunakan.

**Headers:**
```
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
```

**Response Success (200):**
```json
{
  "success": true,
  "message": "Logout berhasil"
}
```

---

### 4. Refresh Token
**POST** `/api/auth/refresh`

Memperbarui token yang masih valid (sebelum token expired).

**Headers:**
```
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
```

**Response Success (200):**
```json
{
  "success": true,
  "message": "Token berhasil diperbarui",
  "info": "Token akan berlaku selama 12 jam dari sekarang.",
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "token_type": "Bearer",
    "expires_in": 43200,
    "expires_at": "2024-04-21T10:30:00Z"
  }
}
```

---

## Contoh Penggunaan cURL

### Login
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "nomor_induk": "123456789",
    "password": "password123"
  }'
```

### Get Profile
```bash
curl -X GET http://localhost:8000/api/auth/me \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### Logout
```bash
curl -X POST http://localhost:8000/api/auth/logout \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### Refresh Token
```bash
curl -X POST http://localhost:8000/api/auth/refresh \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

---

## Contoh Penggunaan JavaScript/Fetch API

### Login
```javascript
fetch('/api/auth/login', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    nomor_induk: '123456789',
    password: 'password123'
  })
})
.then(response => response.json())
.then(data => {
  if (data.success) {
    console.log('Token:', data.data.token);
    localStorage.setItem('token', data.data.token);
  }
});
```

### Get Profile (dengan token yang tersimpan)
```javascript
fetch('/api/auth/me', {
  headers: {
    'Authorization': `Bearer ${localStorage.getItem('token')}`
  }
})
.then(response => response.json())
.then(data => console.log(data));
```

---

## Konfigurasi

### File: `config/jwt.php`
Konfigurasi JWT sudah dipublikasi ke `config/jwt.php`. Setting penting:

- **TTL (Time To Live)**: 720 menit = **12 jam**
  - Token akan expire 12 jam setelah dibuat
  - Request dengan token yang sudah expired akan ditolak (401)
  - Client harus login kembali untuk mendapatkan token baru

- **Refresh TTL**: 1440 menit = **24 jam**
  - User dapat refresh token dalam 24 jam setelah login pertama
  - Setelah 24 jam, user harus login kembali

- **Blacklist Enabled**: true
  - Token yang sudah logout akan disimpan di blacklist
  - Token yang di-blacklist tidak dapat digunakan lagi
  - Data tersimpan di table `jwt_blacklist`

### File: `config/auth.php`
Guard API sudah dikonfigurasi menggunakan driver JWT:
```php
'api' => [
    'driver' => 'jwt',
    'provider' => 'users',
]
```

### File: `.env`
```
JWT_SECRET=...              # Secret key untuk sign token
JWT_TTL=720                 # Token berlaku 12 jam (dalam menit)
JWT_REFRESH_TTL=1440        # Bisa refresh dalam 24 jam (dalam menit)
JWT_BLACKLIST_ENABLED=true  # Enable blacklist untuk logout
```

---

## Catatan Penting

1. **JWT Secret**: Sudah ter-generate otomatis di file `.env` saat menjalankan `php artisan jwt:secret`

2. **Token Expiration**: 
   - Token berlaku **12 jam** setelah dibuat
   - Jika token sudah expired, request akan mengembalikan status **401 Unauthorized**
   - Client harus handle error ini dengan melakukan re-login

3. **Auto Logout**: 
   - Token akan otomatis invalid setelah **12 jam**
   - User tidak perlu melakukan logout manual untuk auto-logout
   - Token juga di-blacklist saat user melakukan logout manual

4. **Token Refresh**:
   - User dapat refresh token sebelum expired
   - Token baru akan berlaku 12 jam lagi dari waktu refresh
   - Refresh hanya bisa dilakukan dalam 24 jam sejak login pertama

5. **Username**: Menggunakan field `nomor_induk` bukan `email`

6. **Role**: Untuk role-based access control, dapat ditambahkan middleware authorization di kemudian hari

7. **Password**: Secara otomatis di-hash menggunakan Laravel's Bcrypt hashing

8. **Blacklist**: Disimpan di table `jwt_blacklist` untuk track token yang sudah logout

---

## Testing API

Anda dapat menggunakan tools seperti:
- Postman
- Insomnia
- REST Client (VS Code Extension)
- Thunder Client (VS Code Extension)

---

## Error Handling

| HTTP Status | Meaning |
|--|--|
| 200 | OK - Request berhasil |
| 401 | Unauthorized - Token invalid/expired/diperlukan |
| 404 | Not Found - User tidak ditemukan |
| 422 | Unprocessable Entity - Validasi gagal |
| 500 | Internal Server Error - Kesalahan server |

