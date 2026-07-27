# Folder Images - Antrian SBW

Folder ini digunakan untuk menyimpan semua file image yang digunakan oleh aplikasi Antrian SBW (Koperasi Setia Bhakti Wanita).

## Struktur Subfolder

| Folder        | Fungsi                                                       |
|---------------|--------------------------------------------------------------|
| `logos/`      | Logo aplikasi, logo koperasi, favicon                        |
| `services/`   | Image untuk layanan SPP, Tunai, Tabungan                     |
| `backgrounds/`| Background image untuk halaman display & login               |
| `icons/`      | Icon custom, icon layanan, icon UI                           |

## Cara Penggunaan

Akses image dari blade template dengan helper `asset()`:

```blade
<img src="{{ asset('images/logos/logo-sbw.png') }}" alt="Logo SBW">
```

Atau langsung di CSS:

```css
background-image: url('/images/backgrounds/bg-display.jpg');
```

## Format Image yang Disarankan

- **Logo**: PNG (transparan) atau SVG
- **Photo/Background**: JPG (dikompresi) atau WebP
- **Icon**: SVG atau PNG
- **Ukuran maksimal**: 500KB per file (untuk performa optimal)

## Penamaan File

Gunakan format `kebab-case` dan deskriptif:
- logo-sbw.png
- bg-display-antrian.jpg
- icon-spp.svg

## Catatan

- Jangan hapus file `.gitkeep` (memastikan folder tetap ter-version control)
- Compress image sebelum upload (gunakan tools seperti TinyPNG, Squoosh)
- Image yang sensitif/berubah sering, simpan juga di storage (Laravel Storage)
