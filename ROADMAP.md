# ROADMAP MADANG-O — Modul & Fitur ke Depan

> Dokumen ini adalah peta jalan pengembangan aplikasi
> "Sistem Informasi Sego Sambel Mbak Tatik" (MADANG-O).
> Sumber acuan: **SRS** — 15 Kebutuhan Fungsional (FR-01…FR-15),
> 7 Use Case (UC-01…UC-07), MoSCoW, dan `catatan.txt` (aturan mitra).

**Tim Kelompok 6** — Dwika Cahaya Kelana (2495124006, Project Manager & System Analyst) ·
Regita Novika Ramadhani (2495124010, Frontend / UI-UX) ·
Tsabiq Riyu Kautsar (2495124019, Backend / QA)

**Repo:** https://github.com/tsabik55-del/MADANG-O-PEMROGRAMAN_WEB_LANJUT

---

## Status Kode per Branch

| Branch | Isi | Status |
|---|---|---|
| `main` | Laravel awal | Arsip |
| `feature/praktikum04` | Database, model, seeder + lembar kerja LKP04 + ERD + screenshot | ✅ Selesai |
| `feature/praktikum05` | Login & hak akses peran (Breeze) | ✅ Selesai |
| `feature/sesi05-management-pengguna` | Integrasi perubahan DB teman + Management Pengguna + pembayaran QRIS | 🔄 Sesi 5 (berjalan) |

**Kredensial demo:** owner `tatik@segosambel.com` / karyawan `sari@segosambel.com` / password keduanya `password`.

---

## Daftar Modul / Fitur (dipetakan dari SRS)

Legenda status: ✅ Selesai · 🔄 Sesi 5 (sedang dikerjakan) · ⬜ Belum · Prioritas dari MoSCoW SRS.

### A. Autentikasi & Akun (FR-01, FR-02)

| No | Fitur | FR / UC | Status | PIC |
|----|-------|---------|--------|-----|
| A1 | Login (email + password) + redirect sesuai peran | FR-01 | ✅ | Tsabiq |
| A2 | Register pelanggan (username, email, WA unik) | FR-02 | ✅ | Tsabiq |
| A3 | Logout + proteksi halaman per peran (`EnsureRole` middleware) | FR-01 | ✅ | Tsabiq |
| A4 | **Management Pengguna** — daftar, cari/filter peran, tambah (pilih peran), ubah data + peran, hapus, reset password | Class Diagram `Owner.kelolaPengguna()` | 🔄 **Sesi 5** | Tsabiq (backend) + Regita (tampilan tabel & form) |
| A5 | Proteksi hapus akun sendiri & turunkan peran sendiri | Keamanan | 🔄 **Sesi 5** | Tsabiq |

### B. Katalog Menu (FR-03, FR-04 · UC-01)

| No | Fitur | FR / UC | Status | PIC |
|----|-------|---------|--------|-----|
| B1 | Katalog menu + harga (7 menu, 2 kategori) | FR-03 · UC-01 | ✅ | Tsabiq + Regita |
| B2 | Ikon kategori (`categories.icon`) + foto menu (`menus.image`) | Struktur DB (teman) | 🔄 **Sesi 5** | Dwika (struktur) · Tsabiq (seeder) · Regita (tampil) |
| B3 | **Pencarian / filter menu** per kategori harian vs katering | FR-04 (Should) | ⬜ | Regita (UI filter) + Tsabiq (query) |

### C. Pemesanan (FR-05…~FR-10 · UC-03, UC-04, UC-07)

| No | Fitur | FR / UC | Status | PIC |
|----|-------|---------|--------|-----|
| C1 | Keranjang (localStorage) + checkout online | FR-05 · UC-03 | ✅ | Tsabiq + Regita |
| C2 | Total otomatis (`quantity × price`) + nomor order `MAD-…` | FR-05 | ✅ | Tsabiq |
| C3 | **Status alur pesanan** 6 tahap (`orders.status`) + ubah status (owner & karyawan) | Struktur DB (teman) + `catatan.txt` #1 | 🔄 **Sesi 5** | Dwika (struktur) · Tsabiq (logika + tampilan) |
| C4 | Catat pesanan **manual** (kasir) | UC-04 · seeder `source=manual` | ⬜ | Tsabiq (form) + Regita (UI) |
| C5 | Cari & pantau pesanan (UC-05) — filter status/tanggal/nama | UC-05 | ⬜ | Tsabiq + Regita |
| C6 | Kelola pesanan (ubah/hapus pesanan oleh owner, UC-07) | UC-07 | ✅ sebagian | Tsabiq |

### D. Pembayaran (FR-… · `catatan.txt` #3)

| No | Fitur | FR / UC | Status | PIC |
|----|-------|---------|--------|-----|
| D1 | Metode tunai & transfer di checkout | `catatan.txt` #3 | ✅ | Tsabiq |
| D2 | Tabel `payments` + record otomatis saat checkout | Struktur DB (teman) | 🔄 **Sesi 5** | Dwika (struktur) · Tsabiq (logika) |
| D3 | Opsi QRIS di checkout + `method` diperluas (tunai/transfer/qris) | Aturan mitra | 🔄 **Sesi 5** | Tsabiq |
| D4 | Tampilkan riwayat & status pembayaran per pesanan (owner) | UC-04 | ⬜ | Regita (UI) + Tsabiq (query) |

### E. Stok Bahan & Laporan (Class Diagram · FR-… · UC-06)

| No | Fitur | FR / UC | Status | PIC |
|----|-------|---------|--------|-----|
| E1 | Tabel `bahan_bakus` + CRUD stok (kurang/tambah) | Class Diagram `Karyawan.perbaruiStok()` | ⬜ | Tsabiq (backend) |
| E2 | **Rekap & laporan** penjualan harian/bulanan (UC-06, FR-15) | UC-06 · FR-13/14/15 | ⬜ | Dwika (rumus rekap) + Tsabiq (query) + Regita (tampilan + cetak) |
| E3 | Dashboard owner: total pendapatan (sudah ada, perluas dengan rekap) | — | ⬜ | Regita + Tsabiq |

---

## Aturan Bisnis dari Mitra (`catatan.txt`)

1. Pelanggan boleh membatalkan pesanan **selama status masih `menunggu_pembayaran`**; setelah masuk tahap proses, pelanggan **tidak bisa** membatalkan sendiri. → diterapkan di `OrderPolicy::delete()` + tombol Batal.
2. Pelanggan bisa memesan **beberapa menu** dalam satu pesanan. → `order_items` (1–3 item per pesanan).
3. Metode pembayaran bisa **transfer dan tunai** (ditambah QRIS). → checkout + `payments.method`.

---

## Pembagian Tugas ke Depan

| Anggota | Peran (SRS) | Fokus Sesi 5 | Fokus Berikutnya |
|---|---|---|---|
| **Dwika** | Project Manager & System Analyst | Struktur DB (icon/image/status/payments) — *sudah dikerjakan* · rumus rekap laporan | Integrasi fitur (Sprint 3 SRS): rekap laporan, evaluasi kebutuhan |
| **Regita** | Frontend / UI-UX | Tampilan tabel pengguna, badge status, ikon & foto menu; rapikan responsivitas | Filter/pencarian (menu & pesanan), halaman rekap & cetak |
| **Tsabiq** | Backend / QA | Pull + rapikan struktur teman, seeder lengkap, Management Pengguna, logika pembayaran, validasi & pengujian | Bahan baku, rekap/laporan backend, test menyeluruh |

---

## Definisi Selesai (DoD) per Fitur

1. Kode di branch fitur, lolos `php artisan test` (kecuali 7 kegagalan bawaan Breeze).
2. `migrate:fresh --seed` jalan bersih di MariaDB maupun SQLite (test).
3. Halaman bisa dibuka sesuai peran; lintas peran mendapat 403.
4. README/ROADMAP diperbarui bila ada fitur baru.
