#!/usr/bin/env python3
"""
Buat gambar placeholder untuk 7 menu MADANG-O.

Placeholder ini hanya sementara supaya halaman tidak menampilkan kotak rusak
selama file foto aslinya belum ada. Setelah foto asli siap, cukup ganti isi
folder storage/app/public/images/menus/ tanpa mengubah kode, karena nama file
sudah dicocokkan dengan isi kolom menus.image.

Jalankan:  python3 scripts/buat-placeholder-menu.py
"""

import math
import random
from pathlib import Path

from PIL import Image, ImageDraw, ImageFilter, ImageFont

# Gambar digambar pada ukuran besar lalu diperkecil supaya tepinya halus
# (anti-aliasing), sebab Pillow tidak punya anti-aliasing bawaan.
SKALA = 3
LEBAR, TINGGI = 800, 600

AKAR = Path(__file__).resolve().parent.parent
KELUARAN = AKAR / "storage/app/public/images/menus"

FONT_JUDUL = "/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf"
FONT_BADAN = "/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf"


def font(path, ukuran):
    return ImageFont.truetype(path, ukuran * SKALA)


def elip(d, cx, cy, rx, ry, warna):
    d.ellipse([cx - rx, cy - ry, cx + rx, cy + ry], fill=warna)


def latar():
    """Latar krem dengan cahaya lembut dari kiri atas."""
    w, h = LEBAR * SKALA, TINGGI * SKALA
    img = Image.new("RGB", (w, h), (250, 245, 236))
    d = ImageDraw.Draw(img)

    for y in range(h):
        t = y / h
        d.line(
            [(0, y), (w, y)],
            fill=(int(252 - 10 * t), int(248 - 14 * t), int(240 - 20 * t)),
        )

    cahaya = Image.new("L", (w, h), 0)
    dc = ImageDraw.Draw(cahaya)
    dc.ellipse([-200 * SKALA, -320 * SKALA, 620 * SKALA, 420 * SKALA], fill=110)
    cahaya = cahaya.filter(ImageFilter.GaussianBlur(50 * SKALA))
    return Image.composite(Image.new("RGB", (w, h), (255, 253, 248)), img, cahaya)


def piring(d, cx, cy, rx, ry):
    """Piring oval dengan tepi lebih gelap dan bayangan di bawahnya."""
    elip(d, cx, cy + int(ry * 0.10), rx * 1.02, ry * 0.96, (226, 213, 194))
    elip(d, cx, cy, rx, ry, (238, 231, 219))
    elip(d, cx, cy - int(ry * 0.04), rx * 0.93, ry * 0.90, (233, 227, 216))
    d.ellipse(
        [cx - rx, cy - ry, cx + rx, cy + ry],
        outline=(214, 200, 180),
        width=3 * SKALA,
    )


def nasi(d, cx, cy, rx, ry):
    """Gunung nasi.

    Bentuknya elips pipih, bukan setengah lingkaran, supaya tidak muncul tepi
    rata di bagian bawah. Bagian atas diberi sorotan lebih terang supaya
    terbaca sebagai gunungan, bukan hanya piring warna putih.
    """
    elip(d, cx, cy + int(ry * 0.12), rx * 1.02, ry * 0.94, (212, 203, 188))

    d.ellipse(
        [cx - rx, cy - ry, cx + rx, cy + ry],
        fill=(252, 246, 233),
        outline=(214, 203, 183),
        width=2 * SKALA,
    )

    # sorotan bagian atas
    d.pieslice(
        [cx - int(rx * 0.9), cy - int(ry * 1.02), cx + int(rx * 0.9), cy + int(ry * 0.45)],
        180, 360, fill=(255, 251, 242),
    )

    # butiran nasi: titik kecil, hanya di dalam elip
    acak = random.Random(int(cx + cy))
    for _ in range(int(rx * 1.8)):
        px = cx + acak.uniform(-rx * 0.93, rx * 0.93)
        py = cy + acak.uniform(-ry * 0.9, ry * 0.9)
        if ((px - cx) / (rx * 0.93)) ** 2 + ((py - cy) / (ry * 0.9)) ** 2 > 1:
            continue
        s = acak.uniform(1.2, 2.8) * SKALA
        d.ellipse(
            [px, py, px + s, py + s],
            fill=(232, 224, 208) if acak.random() < 0.5 else (255, 253, 247),
        )


def sambel(d, cx, cy, rx, ry):
    """Gumpalan sambel merah dengan bagian atas lebih terang."""
    elip(d, cx, cy, rx, ry, (163, 22, 18))
    elip(d, cx - int(rx * 0.26), cy - int(ry * 0.4), rx * 0.40, ry * 0.34, (196, 44, 34))


# ---------- lauk per menu ----------
def lauk_ayam(d, cx, cy):
    s = SKALA
    elip(d, cx, cy, 38 * s, 28 * s, (176, 118, 62))
    elip(d, cx - 8 * s, cy - 6 * s, 22 * s, 16 * s, (206, 154, 92))


def lauk_lele(d, cx, cy):
    s = SKALA
    elip(d, cx, cy, 48 * s, 21 * s, (122, 104, 88))
    d.polygon(
        [(cx + 42 * s, cy), (cx + 74 * s, cy - 20 * s), (cx + 74 * s, cy + 20 * s)],
        fill=(104, 88, 74),
    )


def lauk_tahu(d, cx, cy):
    s = SKALA
    for dx in (-40, 0, 40):
        d.rounded_rectangle(
            [cx + (dx - 25) * s, cy - 23 * s, cx + (dx + 25) * s, cy + 23 * s],
            radius=7 * s, fill=(226, 184, 96),
        )


def lauk_tempe(d, cx, cy):
    s = SKALA
    for dx in (-32, 22):
        d.rounded_rectangle(
            [cx + (dx - 34) * s, cy - 20 * s, cx + (dx + 34) * s, cy + 20 * s],
            radius=8 * s, fill=(178, 128, 66),
        )


def lauk_telor(d, cx, cy):
    s = SKALA
    for geser in (-46, 14):
        elip(d, cx + geser * s, cy, 32 * s, 28 * s, (252, 250, 244))
        elip(d, cx + (geser + 8) * s, cy, 21 * s, 19 * s, (246, 196, 62))


def tanpa_lauk(d, cx, cy):
    s = SKALA
    elip(d, cx, cy, 42 * s, 20 * s, (206, 152, 90))


def gambar_piring(nama_menu, lauk, pita):
    img = latar()
    d = ImageDraw.Draw(img)
    s = SKALA
    cx, cy = 400 * s, 320 * s

    piring(d, cx, cy, 250 * s, 132 * s)
    nasi(d, cx - 42 * s, cy + 34 * s, 168 * s, 74 * s)
    sambel(d, cx + 130 * s, cy + 26 * s, 92 * s, 50 * s)
    lauk(d, cx + 20 * s, cy - 58 * s)

    return finishing(img, d, nama_menu, pita)


def gambar_kotak(nama_menu, pita):
    img = latar()
    d = ImageDraw.Draw(img)
    s = SKALA
    cx, cy = 400 * s, 320 * s

    d.rounded_rectangle(
        [cx - 215 * s, cy - 40 * s, cx + 215 * s, cy + 95 * s],
        radius=18 * s, fill=(226, 216, 202),
    )
    d.rounded_rectangle(
        [cx - 200 * s, cy - 60 * s, cx + 200 * s, cy + 72 * s],
        radius=16 * s, fill=(250, 248, 243),
    )
    nasi(d, cx - 40 * s, cy + 6 * s, 118 * s, 40 * s)
    sambel(d, cx + 110 * s, cy + 22 * s, 64 * s, 33 * s)
    tanpa_lauk(d, cx - 20 * s, cy - 22 * s)

    return finishing(img, d, nama_menu, pita)


def gambar_tumpeng(nama_menu, pita):
    img = latar()
    d = ImageDraw.Draw(img)
    s = SKALA
    cx, cy = 400 * s, 320 * s

    d.polygon(
        [(cx, cy - 185 * s), (cx + 152 * s, cy + 70 * s), (cx - 152 * s, cy + 70 * s)],
        fill=(245, 222, 132),
    )
    d.polygon(
        [(cx, cy - 185 * s), (cx + 152 * s, cy + 70 * s), (cx, cy + 70 * s)],
        fill=(252, 238, 172),
    )
    elip(d, cx, cy + 72 * s, 152 * s, 34 * s, (243, 219, 126))
    d.arc(
        [cx - 150 * s, cy + 38 * s, cx + 150 * s, cy + 106 * s],
        0, 180, fill=(212, 184, 90), width=3 * s,
    )

    return finishing(img, d, nama_menu, pita)


def finishing(img, d, nama_menu, pita):
    """Pita warna di kiri, nama menu, dan keterangan sebagai gambar sementara."""
    s = SKALA

    d.rectangle([0, 0, 14 * s, TINGGI * s], fill=pita)

    f = font(FONT_JUDUL, 25)
    judul = nama_menu.upper()
    kotak = d.textbbox((0, 0), judul, font=f)
    d.text(
        ((LEBAR * s - (kotak[2] - kotak[0])) / 2, TINGGI * s - 62 * s),
        judul, font=f, fill=(60, 28, 24),
    )

    f2 = font(FONT_BADAN, 14)
    ket = "gambar sementara · ganti dengan foto asli"
    kotak2 = d.textbbox((0, 0), ket, font=f2)
    d.text(
        ((LEBAR * s - (kotak2[2] - kotak2[0])) / 2, TINGGI * s - 33 * s),
        ket, font=f2, fill=(152, 122, 114),
    )

    return img.resize((LEBAR, TINGGI), Image.LANCZOS)


def main():
    KELUARAN.mkdir(parents=True, exist_ok=True)

    daftar = [
        ("nasi-sambel-ayam", "Nasi Sambel Ayam", gambar_piring, lauk_ayam, (165, 13, 39)),
        ("nasi-sambel-lele", "Nasi Sambel Lele", gambar_piring, lauk_lele, (165, 13, 39)),
        ("nasi-sambel-tahu", "Nasi Sambel Tahu", gambar_piring, lauk_tahu, (165, 13, 39)),
        ("nasi-sambel-tempe", "Nasi Sambel Tempe", gambar_piring, lauk_tempe, (165, 13, 39)),
        ("nasi-sambel-telor", "Nasi Sambel Telor", gambar_piring, lauk_telor, (165, 13, 39)),
        ("nasi-kotak", "Nasi Kotak", gambar_kotak, tanpa_lauk, (34, 13, 10)),
        ("tumpeng", "Tumpeng", gambar_tumpeng, tanpa_lauk, (216, 164, 46)),
    ]

    for nama_file, nama_menu, penggambar, lauk, pita in daftar:
        img = penggambar(nama_menu, lauk, pita) if penggambar is gambar_piring else penggambar(nama_menu, pita)
        path = KELUARAN / f"{nama_file}.jpg"
        img.save(path, "JPEG", quality=88, optimize=True)
        ukuran = path.stat().st_size // 1024
        print(f"  images/menus/{nama_file}.jpg  ({ukuran} KB)")


if __name__ == "__main__":
    main()