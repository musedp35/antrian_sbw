# Web Notification API for Real-Time Ticket Alerts

## Implementasi Phase 3: Sistem Notifikasi Web yang Informatif dan Lengkap

### Fitur Utama:
1. **Notifikasi Browser** - Menggunakan Web Notification API untuk menampilkan notifikasi sistem
2. **Real-time Polling** - Memeriksa tiket baru setiap 5 detik (client-side polling)
3. **Toast UI Notifications** - Notifikasi visual di bagian kanan atas halaman admin
4. **Badge Counter** - Menampilkan jumlah notifikasi belum dibaca pada ikon bel
5. **Informatif** - Menampilkan nomor tiket dan tipe (SPP/Tunai/Tabungan) dengan warna berbeda

### Backend Implementation:

#### 1. NewTicketNotification.php (`app/Notifications/NewTicketNotification.php`)
- Class notification Laravel yang mengirim notifikasi database
- Queueable agar tidak memblokir proses
- Menyimpan data ticket_number, type, created_at

#### 2. NotificationController Enhancement (`app/Http/Controllers/NotificationController.php`)
- Method `getNewTickets()` - API endpoint untuk polling tiket baru
- Mendapatkan tiket yang sudah dipanggil setelah last_seen timestamp
- Mengembalikan maksimal 5 tiket terbaru

#### 3. TicketController Enhancement (`app/Http/Controllers/TicketController.php`)
- Method `broadcastTicketCreated()` - Broadcast ke semua admin users saat ada tiket baru
- Triggered saat create ticket & saat call ticket
- Menggunakan Laravel Notifications

#### 4. Routes (`routes/web.php`)
```php
Route::get('/api/tickets/new', [NotificationController::class, 'getNewTickets']);
```

### Frontend Implementation:

#### 1. notifications.js (`resources/js/notifications.js`)
Class `TicketNotificationManager`:
- `init()` - Setup manager
- `requestPermission()` - Request browser notification permission
- `createToastContainer()` - Create container for toast notifications
- `showToast()` - Display browser + toast notification
- `renderToast()` - Render UI toast notification
- `startPolling()` - Auto poll every 5 seconds
- `fetchNewTickets()` - Fetch new tickets via API
- `showNewTicketNotification()` - Show notification with color coding

#### 2. Integration with app.js
- Import notification module in resources/js/app.js
- Only activates on /tickets and /dashboard pages

### User Experience:
- Saat ada tiket baru, user mendapat notifikasi toast + browser notification
- Notifikasi menampilkan nomor tiket, tipe, dan waktu
- Klik notifikasi langsung redirect ke halaman tickets
- Warna berbeda untuk tiap tipe: SPP (biru), Tunai (ungu), Tabungan (hijau)
- Auto-dismiss setelah 5 detik

### Testing:
```bash
# Test notification system
curl -H "Accept: application/json" http://antrian_sbw.test/api/tickets/new
# Should return JSON: {"tickets": [...]}
```

### Security:
- API protected by auth middleware
- CSRF token protection via headers
- Rate limiting via browser polling interval
