# PLAN - Antrian SBW Development

## Current Status
- History page redesign: ✅ COMPLETED (card-based style, stats, filter, icons)
- Note recording: ✅ COMPLETED (see below)

## Notes
- **History view**: Bulan harus ditampilkan dengan nama (Januari, Februari, dst), bukan angka
- **History view**: Tambahkan label "Simpanan" di atas kolom Pokok/Wajib/Sukarela
- **Export**: Format bulan juga perlu diubah di export Excel dan PDF

## Pending Tasks
- [ ] History view: Ubah format tanggal (bulan angka → nama)
- [ ] History view: Tambah label "Simpanan" di atas kolom
- [ ] Export Excel: Ubah format bulan
- [ ] Export PDF: Ubah format bulan
- [ ] Simulasi tampilan untuk kolom Simpanan

## Recent Commits
- 83fa7e0 docs: Add PLAN.md
- 08210f1 feat(history): Redesign halaman riwayat ke card-based style

---

## 🎯 TODO: Perubahan Display Antrian — 3 Kategori Last Called

### Deskripsi
Mengubah tampilan halaman Display dari daftar antrian menunggu di footer menjadi **3 kartu kategori** (SPP, Tunai, Tabungan) yang masing-masing menampilkan **tiket terakhir yang dipanggil** untuk kategorinya.

### Analisa Dampak

| Aspek | Efek Positif | Efek Negatif / Risiko |
|-------|-------------|----------------------|
| **UX Pelanggan** | Lebih jelas melihat antrian yang sedang dilayani per kategori | Tidak ada lagi daftar antrian menunggu di display utama |
| **Layout** | Lebih terstruktur, 3 kolom setara | Perlu penyesuaian layout video vs kartu |
| **API** | Tambahan query ringan (3x GROUP BY / 3x subquery) | Sedikit menambah beban query, tapi masih minimal |
| **Konsistensi** | Sesuai expectation user untuk display antrian bank/koperasi | Footer daftar antrian menunggu hilang — perlu konfirmasi apakah tetap ditampilkan di tempat lain |

### Rencana Perubahan

#### 1. `app/Http/Controllers/Api/DisplayApiController.php`
Tambahkan query `lastCalledByType` yang mengambil tiket terakhir dipanggil per kategori:
```php
'lastCalledByType' => [
    'spp'     => Ticket::where('type', 'spp')->where('status', 'called')->whereDate('created_at', $today)->orderByDesc('updated_at')->first(),
    'tunai'   => Ticket::where('type', 'tunai')->where('status', 'called')->whereDate('created_at', $today)->orderByDesc('updated_at')->first(),
    'tabungan' => Ticket::where('type', 'tabungan')->where('status', 'called')->whereDate('created_at', $today)->orderByDesc('updated_at')->first(),
]
```
Response JSON tambahan → format yang sama seperti `called` saat ini.

#### 2. `resources/views/display.blade.php`
- **Footer**: Ganti `<div id="waiting-list">` (daftar antrian menunggu) dengan 3 kartu kategori
- **Layout**: 3 kartu setara lebar, masing-masing:
  - Header: nama kategori (SPP / Tunai / Tabungan) + warna sesuai
  - Body: nomor tiket besar (bold), loket, animasi pulse jika baru dipanggil
  - Footer kecil: jam panggilan terakhir
- **Warna**: biru (SPP), ungu (Tunai), teal (Tabungan) — sesuai CSS yang sudah ada
- **JavaScript**: Update `fetchTickets()` untuk render 3 kartu dari `data.lastCalledByType`
- **Hapus** elemen `count-number` dan `waiting-list` lama (diganti container baru `category-cards`)

### Perkiraan waktu: ~30-45 menit

---

## 🎯 TODO: Auto Reset Counter Tiket Setiap Pergantian Hari (00:00)

### Deskripsi
Mengubah logika generate nomor tiket agar **counter reset ke 001 secara otomatis** setiap pergantian hari (00:00). Setiap tipe tiket (SPP, Tunai, Tabungan) akan memulai counter dari 001 setiap hari.

### Analisa Dampak

| Aspek | Efek Positif | Efek Negatif / Risiko |
|-------|-------------|----------------------|
| **Counter Logic** | Mulai dari 001 setiap hari, lebih jelas secara operasional | - |
| **Database** | Filter tambahan `whereDate('created_at', today)` | Query sedikit lebih berat (masih ringan) |
| **Backward Compat** | Data historis tetap utuh, tidak ada perubahan ke data lama | - |
| **Tidak perlu cron job** | Reset otomatis berdasarkan tanggal, tidak perlu scheduling | - |
| **Testing** | Perlu test pergantian hari (simulasi dengan tanggal berbeda) | - |

### Logika Saat Ini (di `Ticket.php`)
```php
$lastTicket = self::where('type', $type)
    ->orderByDesc('id')
    ->first();
// → Mengambil tiket terakhir secara GLOBAL (tanpa filter tanggal)
```

### Logika Baru (Rencana)
```php
$lastTicket = self::where('type', $type)
    ->whereDate('created_at', now()->format('Y-m-d'))  // ← TAMBAH FILTER TANGGAL
    ->orderByDesc('id')
    ->first();
// Jika tidak ada tiket hari ini → newNum = 1 (reset ke 001)
```

### Simulasi Skenario

| Hari | Aksi | Hasil Counter |
|-------|------|---------------|
| **2026-08-12** | Tiket ke-1 sampai ke-50 | A-001 sampai A-050 |
| **00:00 → 2026-08-13** | Reset otomatis | Counter mulai dari 001 |
| **2026-08-13** | Tiket ke-1 sampai ke-30 | A-001 sampai A-030 |
| **2026-08-14** | Tiket ke-1 | A-001 |

### File yang Perlu Diubah

1. **`app/Models/Ticket.php`** — Fungsi `generateTicketNumber()`
   - Tambah filter `whereDate('created_at', today)` pada query last ticket
   - Jika tidak ada tiket hari ini → `newNum = 1` (otomatis reset)

2. **Verifikasi file lain yang terkait:**
   - `app/Http/Controllers/TicketController.php` (sudah pakai `generateTicketNumber()`)
   - `app/Http/Controllers/MemberController.php` (sudah pakai `generateTicketNumber()`)
   - `app/Services/VoiceService.php` (cek format parsing — tidak perlu ubah karena format tetap)
   - `app/Http/Controllers/Api/DisplayApiController.php` (filter sudah berdasarkan `whereDate`, tidak perlu ubah)

### Testing Plan

1. **Test Manual:**
   - Buat tiket SPP-001 (hari ini) — sukses
   - Buat tiket SPP-002 (hari ini) — sukses
   - Verifikasi: `whereDate('created_at', today)` mengembalikan tiket hari ini saja

2. **Test Reset (Simulasi):**
   - Backup database, ubah `created_at` tiket terakhir ke tanggal kemarin
   - Buat tiket baru → harus mulai dari **001**, bukan lanjut ke nomor berikutnya

3. **Test Backward Compatibility:**
   - Pastikan data tiket kemarin tetap utuh di database
   - Histori tidak berubah, hanya logika generate yang reset

### Perkiraan waktu: ~10-15 menit

