# Chat/Message API Documentation

## Base URL
```
/api/messages
```

## Authentication
Semua endpoints memerlukan JWT token dalam header:
```
Authorization: Bearer {token}
```

## Endpoints

### 1. Get All Conversations
**GET** `/`

Mendapatkan daftar semua percakapan untuk user yang authenticated.

**Response:**
```json
{
  "success": true,
  "message": "Data percakapan berhasil diambil",
  "data": [
    {
      "user_id": 2,
      "user": {
        "id": 2,
        "name": "Dosen Ahmad",
        "nomor_induk": "N001",
        "role": "dosen",
        "fakultas": "Teknik",
        "program_studi": "Informatika"
      },
      "last_message": "Terima kasih",
      "last_message_at": "2026-06-07T10:30:00.000000Z",
      "unread_count": 2,
      "last_sender_id": 1
    }
  ]
}
```

---

### 2. Get Unread Message Count
**GET** `/unread-count`

Mendapatkan jumlah pesan yang belum dibaca.

**Response:**
```json
{
  "success": true,
  "data": {
    "unread_count": 5
  }
}
```

---

### 3. Get Follow-Up Messages
**GET** `/follow-up`

Mendapatkan pesan yang belum dibaca dan sudah lebih dari 10 menit.

**Response:**
```json
{
  "success": true,
  "message": "Data pesan yang memerlukan follow-up berhasil diambil",
  "data": [
    {
      "uuid": "550e8400-e29b-41d4-a716-446655440000",
      "sent_id": 2,
      "receive_id": 1,
      "message": "Kapan bisa bertemu?",
      "status_seen": false,
      "seen_at": null,
      "created_by": 2,
      "created_at": "2026-06-07T10:00:00.000000Z",
      "updated_at": "2026-06-07T10:00:00.000000Z",
      "sender": { ... },
      "receiver": { ... },
      "creator": { ... }
    }
  ]
}
```

---

### 4. Get Conversation with Specific User
**GET** `/conversation/{userId}`

Mendapatkan semua pesan dalam percakapan dengan user tertentu.

**Path Parameters:**
- `userId` (required): ID dari user yang ingin diambil percakapannya

**Response:**
```json
{
  "success": true,
  "message": "Data percakapan berhasil diambil",
  "data": [
    {
      "uuid": "550e8400-e29b-41d4-a716-446655440000",
      "sent_id": 1,
      "receive_id": 2,
      "message": "Halo, ada yang bisa dibantu?",
      "status_seen": true,
      "seen_at": "2026-06-07T10:15:00.000000Z",
      "created_by": 1,
      "created_at": "2026-06-07T10:10:00.000000Z",
      "updated_at": "2026-06-07T10:10:00.000000Z",
      "sender": { ... },
      "receiver": { ... },
      "creator": { ... }
    }
  ]
}
```

---

### 5. Send Message
**POST** `/send`

Mengirim pesan baru ke user tertentu.

**Request Body:**
```json
{
  "receive_id": 2,
  "message": "Halo, saya ingin booking ruangan"
}
```

**Request Validation:**
- `receive_id` (required): ID user penerima, harus exist di tabel users, dan berbeda dengan user yang login
- `message` (required): String, min 1 karakter, max 5000 karakter

**Response (201):**
```json
{
  "success": true,
  "message": "Pesan berhasil dikirim",
  "data": {
    "uuid": "550e8400-e29b-41d4-a716-446655440001",
    "sent_id": 1,
    "receive_id": 2,
    "message": "Halo, saya ingin booking ruangan",
    "status_seen": false,
    "seen_at": null,
    "created_by": 1,
    "created_at": "2026-06-07T10:20:00.000000Z",
    "updated_at": "2026-06-07T10:20:00.000000Z",
    "sender": { ... },
    "receiver": { ... },
    "creator": { ... }
  }
}
```

**Error Response (422):**
```json
{
  "success": false,
  "message": "Validasi gagal",
  "errors": {
    "receive_id": ["The receive id must be different from user id."],
    "message": ["The message field is required."]
  }
}
```

---

### 6. Mark Message as Seen
**PATCH** `/{messageId}/seen`

Menandai pesan tertentu sebagai sudah dilihat.

**Path Parameters:**
- `messageId` (required): UUID dari pesan

**Response:**
```json
{
  "success": true,
  "message": "Pesan berhasil ditandai sebagai sudah dilihat",
  "data": {
    "uuid": "550e8400-e29b-41d4-a716-446655440000",
    "sent_id": 2,
    "receive_id": 1,
    "message": "Terima kasih",
    "status_seen": true,
    "seen_at": "2026-06-07T10:25:00.000000Z",
    "created_by": 2,
    "created_at": "2026-06-07T10:20:00.000000Z",
    "updated_at": "2026-06-07T10:20:00.000000Z",
    "sender": { ... },
    "receiver": { ... },
    "creator": { ... }
  }
}
```

**Error Response (403):**
```json
{
  "success": false,
  "message": "Anda tidak memiliki akses untuk menandai pesan ini"
}
```

---

### 7. Mark All Messages in Conversation as Seen
**PATCH** `/conversation/{userId}/seen-all`

Menandai semua pesan dalam percakapan dengan user tertentu sebagai sudah dilihat.

**Path Parameters:**
- `userId` (required): ID dari user dalam percakapan

**Response:**
```json
{
  "success": true,
  "message": "Semua pesan dalam percakapan berhasil ditandai sebagai sudah dilihat"
}
```

---

### 8. Delete Message
**DELETE** `/{messageId}`

Menghapus pesan yang telah dikirim.

**Path Parameters:**
- `messageId` (required): UUID dari pesan

**Response:**
```json
{
  "success": true,
  "message": "Pesan berhasil dihapus"
}
```

**Error Response (403):**
```json
{
  "success": false,
  "message": "Anda hanya bisa menghapus pesan yang Anda kirim"
}
```

---

## Message Object Structure

```json
{
  "uuid": "550e8400-e29b-41d4-a716-446655440000",
  "sent_id": 1,
  "receive_id": 2,
  "message": "Pesan teks",
  "status_seen": false,
  "seen_at": null,
  "created_by": 1,
  "created_at": "2026-06-07T10:20:00.000000Z",
  "updated_at": "2026-06-07T10:20:00.000000Z",
  "sender": {
    "id": 1,
    "name": "Mahasiswa Budi",
    "nomor_induk": "M001",
    "role": "mahasiswa",
    "fakultas": "Teknik",
    "program_studi": "Informatika"
  },
  "receiver": {
    "id": 2,
    "name": "Dosen Ahmad",
    "nomor_induk": "N001",
    "role": "dosen",
    "fakultas": "Teknik",
    "program_studi": "Informatika"
  },
  "creator": {
    "id": 1,
    "name": "Mahasiswa Budi",
    "nomor_induk": "M001",
    "role": "mahasiswa",
    "fakultas": "Teknik",
    "program_studi": "Informatika"
  }
}
```

---

## Follow-Up System (10 Minute Check)

### Konsep
- Setiap pesan memiliki field `status_seen` (boolean) yang menunjukkan apakah pesan sudah dibaca
- Field `seen_at` menyimpan waktu kapan pesan dilihat
- Endpoint `/follow-up` mengembalikan semua pesan yang:
  - Belum dibaca (`status_seen` = false)
  - Sudah lebih dari 10 menit sejak dikirim (`created_at` <= now - 10 menit)

### Cara Kerja
1. Admin/Dosen dapat memanggil endpoint `/follow-up` untuk melihat pesan mana yang belum dibalas
2. Sistem akan menampilkan pesan-pesan yang memerlukan follow-up
3. Admin dapat mengambil tindakan (misal mengirim reminder) kepada user yang belum membalas

### Implementasi di Frontend
```javascript
// Check follow-up messages setiap 10 menit
setInterval(async () => {
  const response = await fetch('/api/messages/follow-up', {
    headers: { 'Authorization': 'Bearer ' + token }
  });
  const data = await response.json();
  
  if (data.data.length > 0) {
    // Ada pesan yang memerlukan follow-up
    console.log('Messages needing follow-up:', data.data);
  }
}, 10 * 60 * 1000);
```

---

## Status Codes

| Code | Meaning |
|------|---------|
| 200 | Success |
| 201 | Created |
| 403 | Forbidden |
| 404 | Not Found |
| 422 | Validation Error |
| 500 | Server Error |

---

## Error Handling

Semua error responses mengikuti format:
```json
{
  "success": false,
  "message": "Deskripsi error",
  "errors": {} // Hanya untuk validation errors
}
```
