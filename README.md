# MADANG-O — Sistem Informasi Sego Sambel Mbak Tatik

Aplikasi pemesanan dan pengelolaan usaha kuliner berbasis web. Pelanggan dapat
melihat katalog, memesan, membayar (tunai/transfer/QRIS), dan memantau pesanan.
Owner dan karyawan mengelola menu, pesanan, pengguna, serta laporan penjualan.

Dibangun dengan **Laravel 13**, **PHP 8.3+**, **MySQL/MariaDB**, **Blade + Tailwind CSS + Alpine.js**, dan **Laravel Breeze** untuk autentikasi.

## Fitur

### Pelanggan
- Registrasi & login (email + kata sandi, nomor WhatsApp unik).
- Katalog menu dengan foto, harga, filter kategori, dan pencarian.
- Varian nasi (putih/daun jeruk/kuning) dengan tambahan harga dihitung di server.
- Keranjang, checkout, dan nomor pesanan unik otomatis.
- Pembayaran tunai, transfer, dan QRIS (QR dibuat di server).
- Konfirmasi pembayaran → menunggu verifikasi kasir.
- Riwayat pesanan dan pembatalan selama masih `menunggu_pembayaran` (soft cancel).
- Kelola biodata (nama, WhatsApp, alamat).

### Karyawan (Kasir)
- Dashboard ringkasan pesanan.
- Daftar & detail pesanan dengan pencarian/filter (nomor, nama, telepon, status, tanggal).
- Catat pesanan manual (telepon/WhatsApp).
- Ubah status pembayaran, status ambil, dan alur pesanan dengan aturan transisi.
- Verifikasi pembayaran QRIS/transfer.

### Owner (Admin)
- Dashboard statistik (pendapatan dihitung dari pembayaran lunas, pesanan batal dikecualikan).
- CRUD menu termasuk unggah foto (JPG/PNG/WEBP, maks. 2 MB).
- Kelola semua pesanan + catat pesanan manual.
- Management pengguna: cari/filter peran, tambah, ubah, reset kata sandi, hapus.
- Proteksi akun sendiri (tidak bisa hapus/downgrade peran sendiri).
- Rekap & laporan harian/bulanan, menu terlaris, dan cetak laporan.

## Peran & Hak Akses

| Peran | Area | Catatan |
|---|---|---|
| `owner` | `/owner/*` | Akses penuh, boleh mengoreksi status pesanan |
| `karyawan` | `/karyawan/*` | Pesanan hanya boleh maju satu tahap |
| `pelanggan` | `/pelanggan/*` | Hanya data miliknya sendiri |

Middleware `role` (`App\Http\Middleware\EnsureRole`) menolak akses lintas peran
dengan HTTP 403.

## Kebutuhan Sistem
- PHP 8.3+ dengan ekstensi `pdo_mysql`, `gd`, `fileinfo`
- Composer 2
- Node.js 20+ dan npm
- MySQL 8 / MariaDB 10.4+

## Instalasi

```bash
# 1. Dependensi
composer install
npm install

# 2. Konfigurasi
cp .env.example .env
php artisan key:generate

# 3. Sesuaikan koneksi database di .env
#    DB_DATABASE=madango_db
#    DB_USERNAME=...
#    DB_PASSWORD=...

# 4. Migrasi + data contoh
php artisan migrate:fresh --seed

# 5. Tautkan folder foto menu
php artisan storage:link

# 6. Aset frontend
npm run build      # produksi
# atau: npm run dev

# 7. Jalankan
php artisan serve
```

Buka `http://localhost:8000`.

## Akun Demo (hasil seeder)

| Peran | Email | Kata sandi |
|---|---|---|
| Owner | `tatik@segosambel.com` | `password` |
| Karyawan | `sari@segosambel.com` | `password` |
| Pelanggan | 20 akun acak dari factory | `password` |

## Pengujian

```bash
php artisan test          # 75 test, 248 assertion
./vendor/bin/pint --test  # cek gaya kode
```

Test menggunakan SQLite in-memory (lihat `phpunit.xml`) sehingga tidak
menyentuh database pengembangan.

## Struktur Singkat

```
app/
├── Http/Controllers/
│   ├── Auth/            # Breeze
│   ├── Karyawan/        # dashboard & pesanan kasir
│   ├── Owner/           # dashboard, menu, pesanan, pengguna, laporan
│   └── Pelanggan/       # katalog, pesanan, profil
├── Models/              # Order, OrderItem, Menu, Payment, Category, User
├── Policies/            # OrderPolicy (kepemilikan & pembatalan)
└── Services/            # OrderService, OrderItemBuilder
database/
├── migrations/
├── seeders/
└── factories/
resources/views/         # owner/, karyawan/, pelanggan/, orders/, auth/
routes/web.php
```

## Aturan Bisnis Utama
1. Pelanggan hanya boleh membatalkan pesanan selama status masih `menunggu_pembayaran`.
2. Satu pesanan dapat memuat beberapa menu (1–3 item atau lebih).
3. Metode pembayaran: tunai, transfer, QRIS.
4. Nominal pembayaran selalu dihitung ulang dari harga di database.
5. Status pesanan pada `orders` dan status pembayaran pada `payments` selalu disinkronkan.
6. Menu yang sudah dipakai di pesanan tidak dapat dihapus (riwayat tetap utuh).
