# Laporan QA — MADANG-O (Fase 1–4)

Folder ini berisi laporan pengujian dan bukti aktual hasil eksekusi QA.

## Isi

| File / Folder | Keterangan |
|---|---|
| `Website_QA_Testing_Report.docx` | Laporan QA lengkap: ringkasan eksekutif, metodologi, cakupan, traceability FR-01…FR-15, hasil per modul/peran, bug report, lampiran screenshot, rekomendasi, dan kesimpulan. |
| `bukti/screenshots/` | 30 screenshot aktual dari eksekusi Playwright (satu per skenario). |
| `bukti/bug-evidence/` | Bukti BUG-01 sebelum perbaikan (`BUG-01_before_fix.png`) dan versi crop pesan error. |
| `bukti/results/` | `test-results.json` (detail 30 skenario) dan `summary.json` (ringkasan eksekusi). |

## Ringkasan hasil

- **30/30** skenario end-to-end lulus (`pass_rate` 100%).
- **75** test otomatis Laravel lulus, **248 assertion**.
- **1** bug prioritas tinggi ditemukan dan ditutup: duplikat nomor pesanan pada pencatatan manual (`OrderService::generateNumber()`), sudah diperbaiki dan dilengkapi regression test.

## Catatan

- Screenshot diambil dari aplikasi yang berjalan di `http://127.0.0.1:8124` dengan Chromium headless Playwright pada 24 November 2026.
- File hasil pengujian tidak memuat kredensial akun demo.
- Untuk menjalankan ulang pengujian Laravel: `php artisan test`.
- Sebelum demo, kembalikan data awal dengan `php artisan migrate:fresh --seed`.
