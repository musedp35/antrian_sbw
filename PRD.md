# Sistem Antrian Kasir Voice — PRD

## 1. Overview
Koperasi Setia Bhakti Wanita mengalami kemacetan antrian akibat proses panggilan manual yang lambat dan rentan kesalahan. Solusi ini adalah aplikasi antrian berbasis web dengan fitur panggilan suara otomatis (Text-to-Speech Bahasa Indonesia) untuk tiga tipe transaksi: SPP, Tunai, dan Tabungan/Tambah Angsuran. Tujuan utamanya adalah meningkatkan efisiensi waktu layanan, mengurangi kesalahan panggil, dan memberikan pengalaman pelanggan yang lebih modern melalui sistem manajemen tiket real-time.

## 2. Requirements
*   **Validasi Input:** Nomor tiket harus unik dan otomatis terincrement per sesi kasir.
*   **Role-Based Access Control (RBAC):** Implementasi middleware Laravel untuk membatasi akses halaman sesuai role (Super Admin, Admin PJ Kartu, Admin SPP, Admin Kasir).
*   **Real-time Update:** Penggunaan Laravel Echo & Pusher (atau Redis) untuk sinkronisasi data antara halaman Admin/Kasir dan Display tanpa refresh manual.
*   **Voice Engine:** Integrasi library TTS (misal: `spatie/laravel-tts` atau wrapper Google Cloud TTS/Amazon Polly) dengan suara Bahasa Indonesia natural.
*   **Out of Scope:** Aplikasi mobile native, integrasi hardware printer fisik (hanya logika cetak struk jika diperlukan nanti), sistem pembayaran gateway online.

## 3. Core Features

### Fase 1: MVP Panggilan & Dashboard
*   **Manajemen Tiket:** Admin Kasir/SPP/PJ Kartu dapat membuat tiket baru dengan memilih tipe (SPP, Tunai, Tabungan).
*   **Panggilan Voice Otomatis:** Sistem otomatis membacakan nomor tiket dan tipe transaksi saat tiket dibuat/dipanggil.
*   **Display Real-time:** Halaman Display menampilkan nomor tiket aktif, tipe transaksi, dan status (Menunggu/Dipanggil/Selesai).
*   **Dashboard Admin:** Ringkasan jumlah tiket hari ini per tipe dan per kasir.

### Fase 2: Manajemen User & Role
*   **Halaman Admin PJ Kartu:** Fokus pada manajemen tiket tipe "Tabungan/Tambah Angsuran" dan laporan khusus kartu.
*   **Halaman Admin SPP:** Fokus pada manajemen tiket tipe "SPP" dan rekap pembayaran bulanan.
*   **Halaman Admin Kasir:** Fokus pada transaksi "Tunai" dan pengelolaan antrean kasir fisik.
*   **Super Admin Panel:** CRUD user, assign role, dan konfigurasi global sistem.

### Fase 3: Optimasi & Laporan
*   **Riwayat Transaksi:** Log lengkap panggilan yang sudah selesai untuk audit.
*   **Konfigurasi Suara:** Pengaturan volume dan kecepatan suara panggilan.
*   **Notifikasi Web:** Toast notification di browser admin saat ada panggilan baru masuk.

## 4. User Flow

**Admin Kasir / SPP / PJ Kartu:**
1.  Login ke Dashboard menggunakan kredensial role masing-masing.
2.  Masuk ke halaman "Antrian" atau "Admin [Role]" sesuai hak akses.
3.  Klik tombol "Buat Tiket Baru".
4.  Pilih Tipe Transaksi (SPP/Tunai/Tabungan).
5.  Sistem generate nomor tiket, simpan ke DB, dan memicu event `TicketCreated`.
6.  Event memicu panggilan voice otomatis dan update halaman Display secara real-time.

**Pengunjung (Melihat Display):**
1.  Membuka halaman Display (kiosk TV/monitor).
2.  Melihat nomor tiket yang sedang dipanggil dan antrian berikutnya.
3.  Mendengar suara panggilan melalui speaker yang terhubung ke device display.

**Super Admin:**
1.  Login sebagai Super Admin.
2.  Mengelola akun staff (Create/Edit/Delete) dan menetapkan role.
3.  Memantau seluruh aktivitas antrian dari semua divisi.

## 5. Architecture
*   **Frontend:** Blade Templates dengan Tailwind CSS untuk UI responsif. Alpine.js/Vue.js ringan untuk interaktivitas real-time (polling atau WebSocket).
*   **Backend:** Laravel 10 REST API & MVC Controller.
*   **Auth:** Laravel Breeze/Jetstream (sesuai kebutuhan) dengan Middleware RBAC.
*   **Real-time:** Laravel Echo + Pusher (atau Laravel Websockets) untuk broadcast event antrian.
*   **Storage:** Local storage untuk aset statis, MySQL untuk data relasional.

## 6. Database Schema

**Table: `users`**
*   `id` (BIGINT, PK)
*   `name` (VARCHAR)
*   `email` (VARCHAR, Unique)
*   `password` (VARCHAR)
*   `role` (ENUM: 'super_admin', 'admin_pj_kartu', 'admin_spp', 'admin_kasir')
*   `remember_token`, `created_at`, `updated_at`

**Table: `tickets`**
*   `id` (BIGINT, PK)
*   `ticket_number` (VARCHAR, Unique, e.g., "A-001")
*   `type` (ENUM: 'spp', 'tunai', 'tabungan')
*   `status` (ENUM: 'waiting', 'called', 'served', 'cancelled')
*   `assigned_cashier_id` (BIGINT, FK -> users.id)
*   `created_at`, `updated_at`

**Table: `call_logs`** (Opsional untuk audit)
*   `id` (BIGINT, PK)
*   `ticket_id` (BIGINT, FK)
*   `voice_file_path` (VARCHAR, path file audio jika disimpan)
*   `played_at` (TIMESTAMP)

## 7. Tech Stack
*   **PHP 8.2+:** Runtime server-side.
*   **Laravel 10:** Framework backend untuk struktur MVC, Eloquent ORM, dan Queue worker.
*   **MySQL 8.0:** Database relasional utama.
*   **Tailwind CSS 3.x:** Utility-first CSS framework untuk styling cepat dan konsisten.
*   **Pusher / Laravel Websockets:** Untuk komunikasi real-time antar halaman.
*   **Google Cloud TTS / Amazon Polly (API):** Layanan eksternal untuk menghasilkan suara Bahasa Indonesia yang natural (alternatif lokal: `espeak-ng`).

---

## Rencana Task

### [Fase 1] Setup Proyek & Infrastruktur Dasar
Menyiapkan lingkungan pengembangan Laravel dengan database dan konfigurasi dasar.
Selesai bila: Proyek Laravel berhasil dijalankan di localhost tanpa error; Koneksi ke database MySQL berhasil dan migrasi berjalan sukses; Autentikasi dasar (Login/Register) berfungsi dengan baik

**Inisialisasi Proyek Laravel** — Membuat struktur folder dan instalasi dependensi inti.
- [ ] Buat proyek Laravel baru menggunakan Composer
- [ ] Instalasi package untuk autentikasi (Laravel Breeze/Jetstream)
- [ ] Konfigurasi file .env untuk koneksi database lokal
- [ ] Jalankan migrasi default user dan session

**Konfigurasi Database Schema** — Membuat tabel users, tickets, dan call_logs sesuai PRD.
- [ ] Buat migration untuk tabel 'users' dengan kolom role ENUM
- [ ] Buat migration untuk tabel 'tickets' dengan kolom status ENUM
- [ ] Buat migration untuk tabel 'call_logs'
- [ ] Jalankan migrasi dan verifikasi struktur tabel di database

### [Fase 1] Manajemen Tiket Dasar (MVP)
Fitur utama untuk membuat tiket antrian dengan nomor unik dan tipe transaksi.
Selesai bila: Admin dapat membuat tiket baru melalui form; Nomor tiket otomatis bertambah (increment) setiap pembuatan; Tiket yang dibuat tersimpan di database dengan status 'waiting'; Validasi input mencegah duplikasi nomor tiket

**Form Input Tiket** — Halaman admin untuk memilih tipe transaksi dan memicu pembuatan tiket.
- [ ] Buat controller TicketController dengan method store
- [ ] Buat view Blade form dengan dropdown tipe (SPP, Tunai, Tabungan)
- [ ] Implementasi logika auto-increment nomor tiket di model
- [ ] Tambahkan validasi server-side untuk tipe transaksi

**Logika Status Tiket** — Mengelola perubahan status tiket dari menunggu menjadi dipanggil.
- [ ] Buat method di controller untuk mengubah status tiket menjadi 'called'
- [ ] Pastikan timestamp updated_at berubah saat status diperbarui
- [ ] Verifikasi bahwa hanya kasir yang ditunjuk bisa memanggil tiket
- [ ] Simpan log awal ke tabel call_logs saat status berubah

### [Fase 1] Real-time Display Antrian
Menampilkan daftar tiket aktif secara real-time di halaman kiosk TV tanpa refresh manual.
Selesai bila: Halaman display menampilkan tiket terbaru secara instan; Data tiket terurut berdasarkan waktu panggilan terbaru; Tidak ada delay lebih dari 2 detik saat data berubah; Status tiket (Menunggu/Dipanggil) terlihat jelas di UI

**Konfigurasi Broadcasting** — Menyiapkan Pusher/Laravel Websockets untuk event real-time.
- [ ] Instalasi package Pusher PHP SDK atau Laravel Websockets
- [ ] Konfigurasi credentials Pusher di file .env
- [ ] Buat Event class 'TicketCreated' yang implements ShouldBroadcast
- [ ] Test pengiriman event dummy melalui console Laravel

**Frontend Real-time Viewer** — Halaman display yang mendengarkan event dan memperbarui UI.
- [ ] Buat view Blade khusus untuk halaman Display (kiosk mode)
- [ ] Integrasikan Laravel Echo JavaScript di halaman display
- [ ] Listen pada event 'TicketCreated' dan update DOM via Alpine.js/Vue
- [ ] Styling Tailwind CSS agar teks nomor tiket besar dan terbaca jauh

### [Fase 1] Integrasi Text-to-Speech (TTS)
Memainkan suara otomatis berbahasa Indonesia saat tiket dipanggil.
Selesai bila: Sistem membacakan nomor tiket dan tipe transaksi saat dibuat; Suara terdengar jelas melalui speaker browser/device display; Proses TTS tidak memblokir proses pembuatan tiket lainnya; Format audio yang dihasilkan kompatibel dengan browser modern

**Service TTS Backend** — Membuat wrapper service untuk memanggil API TTS (Google/Amazon).
- [ ] Buat Service Class 'VoiceService' untuk menangani request TTS
- [ ] Integrasikan library atau HTTP client ke Google Cloud TTS/Polly
- [ ] Implementasi queue worker untuk memproses generate audio secara async
- [ ] Simpan path file audio hasil generate ke tabel call_logs

**Playback Audio Frontend** — Memainkan file audio atau stream TTS langsung di browser.
- [ ] Buat komponen JavaScript untuk memutar file audio saat event diterima
- [ ] Pastikan audio diputar hanya sekali per panggilan baru
- [ ] Tambahkan fallback jika browser memblokir autoplay (interaksi user)
- [ ] Verifikasi kualitas suara Bahasa Indonesia yang dihasilkan

### [Fase 1] Dashboard Admin Ringkas
Halaman ringkasan statistik antrian untuk monitoring kasir.
Selesai bila: Dashboard menampilkan jumlah tiket hari ini per tipe transaksi; Statistik diperbarui secara dinamis tanpa reload halaman; UI dashboard responsif dan rapi menggunakan Tailwind CSS; Data statistik akurat dibandingkan dengan isi database

**Endpoint Statistik API** — API backend untuk mengambil data agregasi tiket.
- [ ] Buat controller DashboardController dengan method index
- [ ] Query Eloquent untuk menghitung count tiket grouped by type dan status
- [ ] Return response JSON yang siap dikonsumsi frontend
- [ ] Tambahkan caching jika diperlukan untuk performa query berat

**Visualisasi Data Dashboard** — Menampilkan angka dan grafik sederhana di halaman admin.
- [ ] Buat view Blade untuk halaman Dashboard Admin
- [ ] Gunakan Alpine.js untuk fetch dan render data statistik dari API
- [ ] Desain layout grid menggunakan Tailwind CSS untuk kartu statistik
- [ ] Pastikan angka ditampilkan dengan format yang mudah dibaca (misal: A-001)

### [Fase 2] Role-Based Access Control (RBAC) Dasar
Memastikan setiap user hanya mengakses fitur sesuai role mereka.
Selesai bila: Super Admin dapat melihat semua menu dan data; Admin Kasir hanya bisa membuat tiket tipe 'Tunai'; Akses langsung ke URL admin lain ditolak (403 Forbidden); Middleware berhasil memfilter request berdasarkan role user

**Implementasi Middleware** — Membuat middleware Laravel untuk cek role user.
- [ ] Buat middleware 'CheckRole' untuk memvalidasi role user
- [ ] Daftarkan middleware di kernel.php atau route groups
- [ ] Terapkan middleware pada route grup Admin, Kasir, dan Super Admin
- [ ] Test akses unauthorized untuk memastikan keamanan route

**Pembatasan Fitur per Role** — Menyembunyikan tombol atau menu yang tidak relevan bagi role tertentu.
- [ ] Buat blade directive @role('super_admin') untuk show/hide elemen UI
- [ ] Pisahkan form input tiket berdasarkan role yang login
- [ ] Pastikan data laporan hanya terlihat oleh role yang berhak
- [ ] Verifikasi bahwa user non-admin tidak bisa mengakses endpoint admin

### [Fase 2] Manajemen User & Konfigurasi
Panel untuk Super Admin mengelola akun staff dan pengaturan sistem.
Selesai bila: Super Admin dapat menambah, edit, dan hapus user lain; Role user dapat diubah melalui form edit; Pengaturan volume/kecepatan suara tersimpan dan diterapkan; Perubahan data user langsung terlihat di sistem login

**CRUD User Management** — Halaman admin untuk mengelola daftar pengguna sistem.
- [ ] Buat controller UserController untuk manajemen user
- [ ] Buat form create/edit user dengan field role selector
- [ ] Implementasi validasi password dan email unik
- [ ] Tambahkan fitur reset password untuk user yang lupa kredensial

**Pengaturan Sistem Global** — Fitur konfigurasi preferensi suara dan tampilan.
- [ ] Buat tabel konfigurasi atau gunakan cache untuk menyimpan setting
- [ ] Buat form admin untuk mengatur volume dan kecepatan TTS
- [ ] Simpan preferensi ke database saat form disubmit
- [ ] Terapkan setting global saat generate suara di VoiceService

### [Fase 3] Optimasi & Notifikasi
Penambahan fitur notifikasi browser dan riwayat lengkap untuk audit.
Selesai bila: Browser admin menampilkan toast notification saat tiket baru masuk; Halaman riwayat menampilkan log panggilan yang sudah selesai; Notifikasi muncul bahkan jika tab admin tidak sedang aktif (jika didukung); Data riwayat dapat difilter berdasarkan tanggal atau kasir

**Web Notification API** — Mengirim notifikasi pop-up ke browser admin.
- [ ] Permintaan izin notifikasi browser saat user pertama kali login
- [ ] Trigger notifikasi 'Toast' saat event TicketCreated diterima
- [ ] Desain UI notifikasi yang informatif (nomor tiket & tipe)
- [ ] Test kompatibilitas notifikasi di Chrome/Firefox/Edge

**Riwayat Transaksi Audit** — Halaman khusus untuk melihat log panggilan masa lalu.
- [ ] Buat controller HistoryController untuk query log call_logs
- [ ] Buat view Blade tabel riwayat dengan pagination
- [ ] Tambahkan filter pencarian berdasarkan tanggal dan status
- [ ] Pastikan data log mencakup waktu panggil dan nama kasir
