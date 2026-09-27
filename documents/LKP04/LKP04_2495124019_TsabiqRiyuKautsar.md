# LKP04 — Basis Data, Migrasi, Eloquent & Seeder

**Mata Kuliah:** Pemrograman Web Lanjut (3 SKS — Semester V)
**Dosen Pengampu:** Edwin Hari Agus Prastyo, S.Kom., M.Kom.
**Universitas Hasyim Asy'ari Tebuireng Jombang — Program Studi Sistem Informasi**

---

## I. IDENTITAS MAHASISWA & PROYEK

| Komponen | Isian |
|----------|-------|
| Nama Lengkap | Tsabiq Riyu Kautsar |
| NIM | 2495124019 |
| Kelas / Semester | SI-A / V (Lima) |
| Nama Kelompok | Kelompok 6 |
| Judul Proyek Web | MADANG-O — Aplikasi Pengelolaan & Pencatatan Pesanan "Sego Sambel Mbak Tatik" |
| Kasus Proyek Kelompok | Studi Kasus: Sego Sambel Mbak Tatik (usaha kuliner rumahan di Jombang) |
| URL Repositori Git | https://github.com/tsabik55-del/MADANG-O-PEMROGRAMAN_WEB_LANJUT |
| Branch Pengerjaan | `feature/praktikum04` |

**Anggota Kelompok 6:** Tsabiq Riyu Kautsar (2495124019, Backend/QA) · Dwika Cahaya Kelana (2495124006, Project Manager) · Regita Novika Ramadhani (2495124010, Frontend/UIUX)

**Kredensial login (akun demo):**

| Peran | Email | Password |
|-------|-------|----------|
| Owner | `tatik@segosambel.com` | `password` |
| Karyawan | `sari@segosambel.com` | `password` |
| Pelanggan | daftar sendiri lewat halaman Register | — |

---

## II. CAPAIAN & TUJUAN PRAKTIKUM

1. Mengonfigurasi koneksi basis data MySQL/MariaDB pada berkas `.env`.
2. Merancang Entity Relationship Diagram (ERD) yang selaras dengan rancangan Class Diagram dari Modul 03.
3. Membuat dan menyusun urutan file migrasi tabel beserta konstrain integritas data.
4. Mendefinisikan Model Eloquent dan mengimplementasikan relasi antar entitas.
5. Membangun data dummy realistis menggunakan kombinasi Factory (Faker) dan Database Seeder.
6. Menjalankan perintah migrasi dan memverifikasi konsistensi struktur data pada DBMS.

---

## III. RINGKASAN TEORI PENDUKUNG

1. **Konvensi Standar Laravel Eloquent**
   - Nama Tabel: jamak snake_case (`users`, `menus`, `order_items`).
   - Nama Model: tunggal PascalCase (`User`, `OrderItem`).
   - Foreign Key: nama model tunggal + `_id` (`user_id`, `category_id`).
   - Mass Assignment: kolom yang boleh diisi didaftarkan pada `$fillable`.

2. **Jenis Relasi Eloquent**

   | Jenis Relasi | Method Model Induk | Method Model Anak | Penempatan FK |
   |---|---|---|---|
   | One-to-Many | `hasMany(Child::class)` | `belongsTo(Parent::class)` | FK di tabel anak |
   | One-to-One | `hasOne(Child::class)` | `belongsTo(Parent::class)` | FK di tabel anak |
   | Many-to-Many | `belongsToMany(Other::class)` | `belongsToMany(Other::class)` | Tabel pivot terpisah |

3. **Urutan Eksekusi Migrasi**
   Tabel induk (parent) wajib dibuat lebih dahulu sebelum tabel anak (detail). Jika urutan terbalik, `foreignId()->constrained()` gagal dengan error *foreign key constraint fails*.

---

## IV. ALAT, BAHAN & LINGKUNGAN KERJA

| Kebutuhan | Software / Komponen | Status |
|---|---|---|
| Web Server & Database | MariaDB 11.8.6 | [X] Aktif |
| Runtime & Package Manager | PHP 8.4.24 & Composer 2.x | [X] Terpasang |
| IDE / Code Editor | Visual Studio Code | [X] Siap |
| Database Client | phpMyAdmin 5.2 | [X] Siap |
| Diagramming Tool | draw.io / dbdiagram.io / PlantUML | [X] Siap |
| Proyek Laravel | Laravel Framework 13.31.0 (hasil LKP Modul 03) | [X] Siap berjalan |

**Lingkungan eksekusi:** Debian 13 (GNU/Linux) · MariaDB 11.8.6 · PHP 8.4.24 · Composer 2 · Node.js 20 & npm 9 (Vite) · Git.

---

## V. LEMBAR KERJA MAHASISWA MANDIRI

### TAHAP 1 — PERANCANGAN ENTITAS & RELASI (ERD)

#### 1.1 Identifikasi Entitas / Model Proyek

Entitas diambil dari **Class Diagram Modul 03**, lalu dipetakan ke tabel yang benar-benar diimplementasikan.

| No | Entitas (Class Diagram) | Tabel (Database) | Status & Fungsi |
|----|--------------------------|-------------------|-----------------|
| 1 | Pengguna | `users` | ✅ Diimplementasikan — autentikasi & identitas pengguna |
| 2 | Pelanggan | `users` (`role = pelanggan`) | ✅ Diserap ke tabel `users` — buat pesanan, isi keranjang, lihat riwayat. 20 record |
| 3 | Karyawan | `users` (`role = karyawan`) | ✅ Diserap ke tabel `users` — proses & verifikasi pembayaran. 1 record |
| 4 | Owner | `users` (`role = owner`) | ✅ Diserap ke tabel `users` — kelola menu & pengguna, rekap. 1 record |
| 5 | Pesanan | `orders` | ✅ Diimplementasikan — kepala pesanan (kode, status bayar/ambil, total). 20 record |
| 6 | DetailPesanan | `order_items` | ✅ Diimplementasikan — rincian isi tiap pesanan. 37 record |
| 7 | Menu | `menus` | ✅ Diimplementasikan — katalog makanan & harga. 7 record |
| 8 | BahanBaku | *(belum ada)* | ⏳ **Menyusul setelah LKP04** — pengelolaan stok bahan |
| 9 | Laporan | *(belum ada)* | ⏳ **Menyusul setelah LKP04** — sementara rekap digenerate via query `orders` |
| 10 | *Category* | `categories` | ✅ **Tabel tambahan** hasil normalisasi atribut `kategori` pada Menu. 2 record |

**Catatan Normalisasi:** Class Diagram menyimpan `kategori` sebagai atribut teks pada `Menu`. Pada implementasi, atribut tersebut dipisah menjadi tabel `categories` dengan relasi 1:N — ini merupakan **perbaikan** (normalisasi), bukan penyimpangan.

**Catatan Single-Table Inheritance:** Class Diagram memisahkan `Pelanggan`, `Karyawan`, dan `Owner` sebagai entitas berbeda. Pada implementasi, ketiganya diserap ke satu tabel `users` dengan pembeda kolom `role` (enum). Fungsionalitas tetap sama; keamanan dijaga lewat *middleware* `role` dan *policy*.

#### 1.2 Matriks Pemetaan Relasi Antar Entitas

| No | Entitas Asal | Bentuk Relasi | Entitas Target | Kolom Kunci Tamu (Foreign Key) |
|----|--------------|---------------|----------------|-------------------------------|
| 1 | `categories` | 1 : N | `menus` | `menus.category_id` — `cascadeOnDelete()` |
| 2 | `users` | 1 : N | `orders` | `orders.user_id` (nullable) — `nullOnDelete()` |
| 3 | `orders` | 1 : N | `order_items` | `order_items.order_id` — `cascadeOnDelete()` |
| 4 | `menus` | 1 : N | `order_items` | `order_items.menu_id` — `cascadeOnDelete()` |
| 5 | `users` | 1 : 1 | pelanggan / karyawan / owner | Diserap lewat kolom `role` (single-table inheritance) |
| 6 | `menus` | 1 : N | `bahan_bakus` ⏳ | Belum ada constraint — menyusul |

#### 1.3 Diagram Hubungan Entitas (ERD)

![ERD Proyek MADANG-O](screenshots/erd_proyek.png)

**Path berkas gambar ERD:** `screenshots/erd_proyek.png`
**Tools yang digunakan:** draw.io / dbdiagram.io (diagram digenerate dari skema hasil `migrate`)

**Keterangan diagram:**
- **Garis solid** = entitas yang sudah diimplementasikan pada LKP04: `users`, `categories`, `menus`, `orders`, `order_items`
- **Garis putus-putus** = entitas dari Class Diagram Modul 03 yang direncanakan menyusul setelah LKP04: `BahanBaku`, `Laporan`

---

### TAHAP 2 — KONFIGURASI DATABASE & ENVIRONMENT

#### 2.1 Pembuatan Database pada DBMS

```
Nama Database  : madango_db
Collation      : utf8mb4_unicode_ci
DBMS           : MariaDB 11.8.6 (Debian 13) — diakses lewat phpMyAdmin 5.2
```

Perintah pembuatan:

```bash
mysql -u root -e "CREATE DATABASE madango_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

#### 2.2 Konfigurasi Berkas `.env`

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=madango_db
DB_USERNAME=laravel
DB_PASSWORD=
```

Berkas `.env` bersifat lokal dan tidak di-commit. `.env.example` di repositori telah diperbarui sesuai konfigurasi di atas.

> **Catatan:** `DB_USERNAME` menyesuaikan user MySQL lokal. Pada lingkungan Linux digunakan user `laravel` tanpa password; pada Windows/Laragon umumnya user `root` tanpa password.

#### 2.3 Uji Koneksi Database

```bash
php artisan migrate:status
```

```
 0001_01_01_000000_create_users_table .............. [1] Ran
 0001_01_01_000001_create_cache_table .............. [1] Ran
 0001_01_01_000002_create_jobs_table ............... [1] Ran
 2026_09_22_082623_create_categories_table ........ [1] Ran
 2026_09_22_082624_create_menus_table ............. [1] Ran
 2026_09_22_082625_create_orders_table ............ [1] Ran
 2026_09_22_082626_create_order_items_table ....... [1] Ran
```

**Status koneksi: [X] Terhubung Berhasil**

---

### TAHAP 3 — IMPLEMENTASI SKEMA MIGRASI

#### 3.1 Berkas Migrasi di Repositori

| No | Berkas Migrasi (`database/migrations/`) | Tabel yang Dibuat | Commit |
|----|------------------------------------------|-------------------|--------|
| 1 | `0001_01_01_000000_create_users_table.php` | `users` (dimodifikasi: + `username`, `wa_number`, `role`) | [X] |
| 2 | `0001_01_01_000001_create_cache_table.php` | `cache` | [X] |
| 3 | `0001_01_01_000002_create_jobs_table.php` | `jobs` | [X] |
| 4 | `2026_09_22_082623_create_categories_table.php` | `categories` | [X] |
| 5 | `2026_09_22_082624_create_menus_table.php` | `menus` | [X] |
| 6 | `2026_09_22_082625_create_orders_table.php` | `orders` | [X] |
| 7 | `2026_09_22_082626_create_order_items_table.php` | `order_items` | [X] |

**Urutannya:** master (`categories`, `users`) → anak (`menus`, `orders`) → detail (`order_items`).

#### 3.2 Skema Tabel Master Inti — Tabel 1: `categories`

- **Nama Tabel:** `categories`
- **Berkas Migrasi:** `database/migrations/2026_09_22_082623_create_categories_table.php`
- **Status Commit:** [X] Ter-commit

| Nama Kolom | Tipe Data | Nullable | Keterangan / Aturan |
|---|---|---|---|
| `id` | BIGINT UNSIGNED | NO | Primary key, auto increment |
| `name` | VARCHAR(255) | NO | Nama kategori (Menu Harian, Paket Katering) |
| `created_at` | TIMESTAMP | YES | Timestamp otomatis Laravel |
| `updated_at` | TIMESTAMP | YES | Timestamp otomatis Laravel |

#### 3.3 Skema Tabel dengan Foreign Key — Tabel 2: `menus`

- **Nama Tabel:** `menus`
- **Berkas Migrasi:** `database/migrations/2026_09_22_082624_create_menus_table.php`
- **Merujuk ke Tabel:** `categories`
- **Status Commit:** [X] Ter-commit

| Nama Kolom | Tipe Data | Foreign Key / Constraint | Keterangan |
|---|---|---|---|
| `id` | BIGINT UNSIGNED | — | Primary key, auto increment |
| `category_id` | BIGINT UNSIGNED | `constrained()` → `categories.id` | `cascadeOnDelete()` — hapus kategori ikut hapus menu |
| `name` | VARCHAR(255) | — | Nama menu |
| `description` | TEXT | — | Deskripsi singkat, boleh kosong |
| `price` | DECIMAL(10,2) | — | Harga satuan (DECIMAL, bukan float) |
| `status_ketersediaan` | BOOLEAN (TINYINT 1) | — | `default(true)` — status tersedia |

#### 3.4 Skema Tabel Tambahan / Transaksi (Tabel 3 dan Seterusnya)

**Tabel 3: `orders`**

- **Nama Tabel:** `orders`
- **Berkas Migrasi:** `database/migrations/2026_09_22_082625_create_orders_table.php`
- **Status Commit:** [X] Ter-commit

| Nama Kolom | Tipe Data | Constraint / Relasi | Keterangan |
|---|---|---|---|
| `id` | BIGINT UNSIGNED | — | Primary key, auto increment |
| `order_number` | VARCHAR(255) | `unique()` | Kode unik, format `MAD-YYYYMMDD-XXXX` |
| `user_id` | BIGINT UNSIGNED | `constrained()` → `users.id`, **nullable**, `nullOnDelete()` | NULL bila pesanan dicatat manual |
| `customer_name` | VARCHAR(255) | — | Nama pemesan (online & manual) |
| `phone` | VARCHAR(255) | — | Nomor WhatsApp, boleh kosong |
| `pickup_datetime` | DATETIME | — | Jadwal waktu pengambilan |
| `payment_method` | ENUM | `('tunai','transfer')` | `default('tunai')` |
| `payment_status` | ENUM | `('belum_lunas','lunas')` | `default('belum_lunas')` |
| `pickup_status` | ENUM | `('belum_diambil','sudah_diambil')` | `default('belum_diambil')` |
| `source` | ENUM | `('online','manual')` | `default('online')` |
| `total_price` | DECIMAL(12,2) | — | Total bayar, dihitung dari penjumlahan subtotal |
| `notes` | TEXT | — | Catatan tambahan, boleh kosong |

**Tabel 4: `order_items`**

- **Nama Tabel:** `order_items`
- **Berkas Migrasi:** `database/migrations/2026_09_22_082626_create_order_items_table.php`
- **Status Commit:** [X] Ter-commit

| Nama Kolom | Tipe Data | Constraint / Relasi | Keterangan |
|---|---|---|---|
| `id` | BIGINT UNSIGNED | — | Primary key, auto increment |
| `order_id` | BIGINT UNSIGNED | `constrained()` → `orders.id` | `cascadeOnDelete()` |
| `menu_id` | BIGINT UNSIGNED | `constrained()` → `menus.id` | `cascadeOnDelete()` |
| `quantity` | INT | — | Jumlah porsi pesan |
| `price` | DECIMAL(10,2) | — | Harga satuan saat transaksi (*snapshot*) |
| `subtotal` | DECIMAL(12,2) | — | `quantity` × `price` |

---

### TAHAP 4 — IMPLEMENTASI MODEL ELOQUENT & RELASI

#### 4.1 Berkas Model di Repositori

| No | Model (`app/Models/`) | Tabel | Relasi Utama | Commit |
|----|----------------------|-------|--------------|--------|
| 1 | `User.php` | `users` | `hasMany(Order::class)` → `orders()` | [X] |
| 2 | `Category.php` | `categories` | `hasMany(Menu::class)` → `menus()` | [X] |
| 3 | `Menu.php` | `menus` | `belongsTo(Category::class)`, `hasMany(OrderItem::class)` | [X] |
| 4 | `Order.php` | `orders` | `belongsTo(User::class)`, `hasMany(OrderItem::class)` | [X] |
| 5 | `OrderItem.php` | `order_items` | `belongsTo(Order::class)`, `belongsTo(Menu::class)` | [X] |

#### 4.2 Model Induk (HasMany) — `Category`

- **Berkas:** `app/Models/Category.php`
- **Status Commit:** [X] Ter-commit

| Method / Properti | Jenis | Target / Isi | Keterangan |
|---|---|---|---|
| `$fillable` | array | `['name']` | Mass assignment — hanya kolom ini boleh diisi |
| `$casts` | — | tidak ada | Kolom `name` bertipe string, tidak perlu casting |
| `menus()` | `HasMany` | `Menu::class` | Satu kategori memiliki banyak menu |

#### 4.3 Model Anak (BelongsTo) — `Menu`

- **Berkas:** `app/Models/Menu.php`
- **Status Commit:** [X] Ter-commit

| Method Relasi | Target Model | Foreign Key | Keterangan |
|---|---|---|---|
| `category()` | `Category` | `category_id` | Menu berada dalam satu kategori |
| `orderItems()` | `OrderItem` | `menu_id` | Menu dapat dipesan pada banyak item pesanan |

| Properti | Isi |
|---|---|
| `$fillable` | `['category_id', 'name', 'description', 'price', 'status_ketersediaan']` |
| `$casts` | `['price' => 'decimal:2', 'status_ketersediaan' => 'boolean']` |

#### 4.4 Ringkasan Relasi Seluruh Model

| Model | Method Relasi | Target Model | Jenis Relasi Eloquent |
|---|---|---|---|
| `Category` | `menus()` | `Menu` | hasMany |
| `Menu` | `category()` | `Category` | belongsTo |
| `Menu` | `orderItems()` | `OrderItem` | hasMany |
| `Order` | `user()` | `User` | belongsTo |
| `Order` | `orderItems()` | `OrderItem` | hasMany |
| `OrderItem` | `order()` | `Order` | belongsTo |
| `OrderItem` | `menu()` | `Menu` | belongsTo |

**Kesimpulan:** terdapat 4 relasi `hasMany` dan 3 relasi `belongsTo` — seluruh relasi bersifat dua arah antara model induk dan anak.

---

### TAHAP 5 — FACTORY & DATABASE SEEDER

#### 5.1 Berkas Factory & Seeder di Repositori

| No | Berkas | Jenis | Model / Tabel Sasaran | Commit |
|----|--------|-------|----------------------|--------|
| 1 | `database/factories/UserFactory.php` | Factory | `User` (states: `owner`, `karyawan`, `pelanggan`, `unverified`) | [X] |
| 2 | `database/factories/CategoryFactory.php` | Factory | `Category` | [X] |
| 3 | `database/factories/MenuFactory.php` | Factory | `Menu` | [X] |
| 4 | `database/factories/OrderFactory.php` | Factory | `Order` | [X] |
| 5 | `database/factories/OrderItemFactory.php` | Factory | `OrderItem` | [X] |
| 6 | `database/seeders/CategorySeeder.php` | Seeder | `Category` (2 data statis) | [X] |
| 7 | `database/seeders/MenuSeeder.php` | Seeder | `Menu` (7 data statis) | [X] |
| 8 | `database/seeders/UserSeeder.php` | Seeder | `User` (2 statis + 20 factory) | [X] |
| 9 | `database/seeders/OrderSeeder.php` | Seeder | `Order` (20) & `OrderItem` (44) | [X] |
| 10 | `database/seeders/DatabaseSeeder.php` | Seeder | Orchestrator — memanggil 4 seeder | [X] |

#### 5.2 Rancangan Factory

| Kolom | Helper Faker | Alasan Pemilihan |
|---|---|---|
| `users.name` | `fake()->name()` | Nama orang realistis sesuai data pelanggan |
| `users.username` | `fake()->unique()->userName()` | Wajib unik karena kolom `UNIQUE` |
| `users.email` | `fake()->unique()->safeEmail()` | Wajib unik; `safeEmail()` meniru domain `example.com` |
| `users.wa_number` | `'08' . fake()->numerify('##########')` | Format nomor WhatsApp Indonesia (08 + 10 digit) |
| `users.password` | `Hash::make('password')` | Disimpan 1× pada variabel statis agar hash konsisten & seeding cepat |
| `users.role` | Nilai tetap + *state* | *State* `owner()`, `karyawan()`, `pelanggan()` untuk seeding berbasis peran |
| `menus.category_id` | `Category::factory()` | Relasi antar factory otomatis |
| `menus.name` / `price` / `description` | `fake()->randomElement([...])` | Dictionary harga tetap sesuai katalog usaha (15.000 … 250.000) |
| `menus.status_ketersediaan` | `fake()->boolean(90)` | 90% tersedia — lebih realistis daripada selalu `true` |
| `orders.order_number` | `'MAD-'.Ymd.'-'.str_pad($counter++,4)` | Nomor unik berurutan |
| `orders.user_id` | `fake()->optional(0.7)->passthrough(User::factory()->pelanggan())` | 70% pesanan tertaut pelanggan, 30% NULL (manual) |
| `orders.customer_name` | `fake()->name()` | Nama pemesan untuk pesanan manual |
| `orders.phone` | `'08' . fake()->numerify('##########')` | Nomor WhatsApp realistis |
| `orders.pickup_datetime` | `fake()->dateTimeBetween('+1 day', '+7 days')` + `setTime()` | Jadwal ambil 1–7 hari ke depan pada jam 08–17 |
| `orders.payment_method` | `fake()->randomElement(['tunai','transfer'])` | Variasi metode bayar |
| `orders.payment_status` | `fake()->randomElement(['belum_lunas','lunas'])` | Variasi status bayar agar rekap/vendor lebih menarik |
| `orders.pickup_status` | `fake()->randomElement(['belum_diambil','sudah_diambil'])` | Variasi status pengambilan |
| `orders.source` | `fake()->randomElement(['online','manual'])` | Mencerminkan 2 kanal pemesanan |
| `orders.total_price` | `fake()->numberBetween(10000, 500000)` | Rentang total sesuai katalog |
| `orders.notes` | `fake()->optional(0.3)->sentence()` | 70% kosong — tidak semua pesanan bercatatan |
| `order_items.order_id` / `menu_id` | `Order::factory()` / `Menu::factory()` | Relasi antar factory otomatis |
| `order_items.quantity` | `fake()->numberBetween(1, 50)` | Jumlah porsi bervariasi |
| `order_items.price` | `fake()->numberBetween(10000, 250000)` | Harga snapshot saat transaksi |
| `order_items.subtotal` | `quantity * price` | **Dihitung**, bukan angka acak — menjaga konsistensi total |

#### 5.3 Rancangan Seeder

| Model | Sumber Data | Jumlah Baris | Keterangan |
|---|---|---|---|
| `Category` | `Model::create([...])` — data statis | 2 | `['Menu Harian', 'Paket Katering']` — data master tetap |
| `Menu` | `Model::create([...])` — data statis | 7 | Harga & deskripsi mengikuti katalog usaha (Ayam 15.000, Lele 12.000, Tahu 10.000, Tempe 10.000, Telor 11.000, Nasi Kotak 18.000, Tumpeng 250.000) |
| `User` | `Model::create([...])` + `Model::factory(20)` | 22 | 1 owner (`Mbak Tatik`) + 1 karyawan (`Sari`) statis sebagai akun demo, + 20 pelanggan acak |
| `Order` | `Model::create([...])` dengan helper `fake()` langsung di dalam seeder | 20 | 10 `online` (`user_id` terisi) + 10 `manual` (`user_id` NULL) |
| `OrderItem` | `Model::create([...])` di dalam loop `OrderSeeder` | 37 | 1–3 item per pesanan; `subtotal` dihitung dari `menu->price × quantity`, lalu `total_price` di-*update* |

> **Catatan:** `OrderSeeder` tidak memakai `OrderFactory`, melainkan memanggil `Order::create()` dan `OrderItem::create()` secara langsung dengan helper `fake()`. Hal ini diperlukan karena `order_items` harus merujuk menu yang benar-benar dipilih pada pesanan tersebut, beserta harga yang konsisten.

#### 5.4 Urutan Pemanggilan pada `DatabaseSeeder.php`

- **Berkas:** `database/seeders/DatabaseSeeder.php`
- **Status Commit:** [X] Ter-commit

| Urutan | Seeder Class | Tujuan Data |
|---|---|---|
| 1 | `CategorySeeder::class` | Data master kategori — harus pertama karena dipakai `MenuSeeder` |
| 2 | `MenuSeeder::class` | Menu, membutuhkan kategori yang sudah ada |
| 3 | `UserSeeder::class` | Akun owner, karyawan, dan 20 pelanggan |
| 4 | `OrderSeeder::class` | Pesanan + item, membutuhkan `User` & `Menu` yang sudah tersedia |

```php
$this->call([
    CategorySeeder::class,
    MenuSeeder::class,
    UserSeeder::class,
    OrderSeeder::class,
]);
```

---

### TAHAP 6 — EKSEKUSI, VERIFIKASI & PENGUJIAN BASIS DATA

#### 6.1 Jalankan Fresh Migration + Seeding

```bash
php artisan migrate:fresh --seed --force
```

```
INFO  Preparing database.
  Creating migration table .. DONE

INFO  Running migrations.
  0001_01_01_000000_create_users_table .............. DONE
  0001_01_01_000001_create_cache_table .............. DONE
  0001_01_01_000002_create_jobs_table ............... DONE
  2026_09_22_082623_create_categories_table ........ DONE
  2026_09_22_082624_create_menus_table ............. DONE
  2026_09_22_082625_create_orders_table ............ DONE
  2026_09_22_082626_create_order_items_table ....... DONE

INFO  Seeding database.
  Database\Seeders\CategorySeeder ................... DONE
  Database\Seeders\MenuSeeder ....................... DONE
  Database\Seeders\UserSeeder ....................... DONE
  Database\Seeders\OrderSeeder ...................... DONE

INFO  Database seeding completed successfully.
```

#### 6.2 Tabel Checklist Verifikasi di DBMS

| No | Nama Tabel | Status Migrasi | Jumlah Baris Data | Tipe Data Sesuai ERD |
|----|------------|----------------|-------------------|---------------------|
| 1 | `users` | [X] Berhasil | 22 | [X] Ya |
| 2 | `categories` | [X] Berhasil | 2 | [X] Ya |
| 3 | `menus` | [X] Berhasil | 7 | [X] Ya |
| 4 | `orders` | [X] Berhasil | 20 | [X] Ya |
| 5 | `order_items` | [X] Berhasil | 37 | [X] Ya |

**Catatan jumlah data:** angka di atas merupakan hasil langsung dari `php artisan migrate:fresh --seed --force`, sehingga **dapat direproduksi** dosen. Rinciannya: 1 owner + 1 karyawan + 20 pelanggan = 22 user; 10 pesanan `online` + 10 pesanan `manual` = 20 pesanan; tiap pesanan berisi 1–3 item sehingga total 37 baris `order_items`.

> Jumlah `order_items` bersifat **acak** (dipengaruhi `fake()`), sehingga dapat sedikit berbeda antar eksekusi. Nilai 37 berasal dari eksekusi terakhir.

**Verifikasi konstrain:** 4 foreign key aktif (`menus.category_id`, `orders.user_id`, `order_items.order_id`, `order_items.menu_id`) dan 1 unique constraint (`orders.order_number`).

#### 6.3 Bukti Screenshot Verifikasi

| No | Konten Screenshot | Nama File |
|----|-------------------|-----------|
| 1 | Daftar tabel di DBMS (phpMyAdmin) | `screenshots/db_tables_list.png` |
| 2 | Contoh data dummy tabel master (`menus`) | `screenshots/db_data_master.png` |
| 3 | Contoh data dummy tabel transaksi (`orders` + `order_items`) | `screenshots/db_data_transaksi.png` |
| 4 | Diagram ERD (dilampirkan di Bagian 1.3) | `screenshots/erd_proyek.png` |

---

### TAHAP 7 — REFLEKSI DAN EVALUASI MANDIRI

#### 7.1 Mengapa urutan timestamp file migrasi krusial saat mendefinisikan foreign key?

Karena kolom foreign key hanya dapat dibuat jika tabel referensinya (parent) sudah ada di database. Laravel menjalankan migrasi berurutan berdasarkan timestamp pada nama file, sehingga tabel **master** harus memiliki timestamp lebih awal daripada tabel **anak**:

```
0001_01_01_000000_create_users_table        (master)  ← paling awal
2026_09_22_082623_create_categories_table  (master)
2026_09_22_082624_create_menus_table        (anak dari categories)
2026_09_22_082625_create_orders_table       (anak dari users)
2026_09_22_082626_create_order_items_table  (anak dari orders & menus) ← paling akhir
```

Jika urutannya dibalik, perintah `foreignId()->constrained()` akan gagal dengan error *foreign key constraint fails* karena tabel parent belum ada.

#### 7.2 Apa perbedaan `cascadeOnDelete()` dengan `nullOnDelete()`?

| | `cascadeOnDelete()` | `nullOnDelete()` |
|---|---|---|
| **Perilaku** | Data anak **ikut dihapus** | Foreign key **di-set NULL**, data anak tetap ada |
| **Dipakai pada** | `menus → categories`, `order_items → orders`, `order_items → menus` | `orders → users` |
| **Alasan** | Detail menu/item tidak ada makna bila induknya hilang | Riwayat pesanan tetap tersimpan walaupun akun pelanggan dihapus |

#### 7.3 Mengapa `role` disimpan sebagai ENUM di tabel `users`, bukan tabel terpisah untuk Owner, Karyawan, dan Pelanggan?

Ketiga peran tersebut memiliki fungsi yang serupa — sama-sama melakukan *login* dan menyimpan profil — dan hanya berbeda pada **hak akses**. Karena itu *single-table inheritance* lebih ringkas: satu tabel `users` dengan kolom `role`, lalu keamanan dijaga lewat *middleware* `role` dan *policy* (bukan dengan menyembunyikan menu di tampilan saja).

Pendekatan ini juga menghindari query tambahan (*join*) pada setiap pengecekan peran. Tabel terpisah baru diperlukan apabila suatu saat tiap role memiliki atribut yang berbeda secara signifikan — misalnya `BahanBaku` yang hanya relevan bagi `Karyawan` pada tahap pengembangan berikutnya.

---

## VII. PEMETAAN KE SUB-CPMK

| Tahap Kegiatan | Sub-CPMK yang Dicapai |
|---|---|
| Tahap 1 — Identifikasi entitas & ERD | S2 |
| Tahap 2 — Konfigurasi database | S4 |
| Tahap 3 — Migrasi & skema tabel | S4 |
| Tahap 4 — Model Eloquent & relasi | S2, S4 |
| Tahap 5 — Factory & Seeder | S4 |
| Tahap 6 — Verifikasi basis data | S4 |
| Tahap 7 — Refleksi mandiri | S2, S4 |

---

## VIII. TATA CARA PENGUMPULAN

**Yang dikumpulkan:**

1. **Repositori GitHub** — seluruh *source code* (migrasi, model, factory, seeder, `.env.example`) pada branch `feature/praktikum04`; repositori dapat diakses dosen/asisten pengampu.
   - Branch LKP04: https://github.com/tsabik55-del/MADANG-O-PEMROGRAMAN_WEB_LANJUT/tree/feature/praktikum04
   - Branch LKP05: https://github.com/tsabik55-del/MADANG-O-PEMROGRAMAN_WEB_LANJUT/tree/feature/praktikum05
2. **File lembar kerja ini** (format `.md` atau PDF) — terisi lengkap; *source code* tidak dilampirkan pada lembar kerja.
3. **Folder `screenshots/`** di repositori — berisi ERD dan screenshot verifikasi DBMS.

**Format nama file lembar kerja:** `LKP04_[NIM]_[NamaLengkap].md`
**File ini:** `LKP04_2495124019_TsabiqRiyuKautsar.md`
