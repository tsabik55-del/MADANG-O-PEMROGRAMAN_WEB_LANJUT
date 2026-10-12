/**
 * Perender perangkat lunak untuk buildNasiScene().
 *
 * Tujuannya bukan menghasilkan gambar sempurna, melainkan memberi pratinjau
 * bentuk piring nasi sambel tanpa harus membuka browser yang mendukung WebGL.
 * Catatan: tiga.js WebGL tidak bisa jalan di Node, jadi scene ini
 * diproyeksikan manual dengan algoritma painter + pencahayaan datar.
 *
 * Jalankan: node tests/js/preview-nasi.mjs [lebar] [tinggi]
 */
import fs from 'node:fs';
import zlib from 'node:zlib';
import * as THREE from 'three';

import { buildNasiScene, KAMERA } from '../../resources/js/hero-nasi.js';

const LEBAR = Number(process.argv[2] || 900);
const TINGGI = Number(process.argv[3] || 700);
// Argumen keempat: render hanya satu objek (untuk menelusuri artefak).
const HANYA_OBJEK = process.argv[4] !== undefined ? Number(process.argv[4]) : null;

// ---------- menulis PNG tanpa dependensi ----------
function crc32(buf) {
    let c;
    const tabel = crc32.tabel || (crc32.tabel = (() => {
        const t = new Int32Array(256);
        for (let n = 0; n < 256; n++) {
            c = n;
            for (let k = 0; k < 8; k++) c = c & 1 ? 0xedb88320 ^ (c >>> 1) : c >>> 1;
            t[n] = c;
        }
        return t;
    })());

    let crc = -1;
    for (let i = 0; i < buf.length; i++) crc = (crc >>> 8) ^ tabel[(crc ^ buf[i]) & 0xff];
    return (crc ^ -1) >>> 0;
}

function chunk(tipe, data) {
    const len = Buffer.alloc(4);
    len.writeUInt32BE(data.length);
    const body = Buffer.concat([Buffer.from(tipe, 'ascii'), data]);
    const crc = Buffer.alloc(4);
    crc.writeUInt32BE(crc32(body));
    return Buffer.concat([len, body, crc]);
}

function tulisPNG(path, lebar, tinggi, rgb) {
    const baris = Buffer.alloc((lebar * 3 + 1) * tinggi);
    for (let y = 0; y < tinggi; y++) {
        baris[y * (lebar * 3 + 1)] = 0;
        rgb.copy(baris, y * (lebar * 3 + 1) + 1, y * lebar * 3, (y + 1) * lebar * 3);
    }

    const ihdr = Buffer.alloc(13);
    ihdr.writeUInt32BE(lebar, 0);
    ihdr.writeUInt32BE(tinggi, 4);
    ihdr[8] = 8;
    ihdr[9] = 2;

    fs.writeFileSync(path, Buffer.concat([
        Buffer.from([0x89, 0x50, 0x4e, 0x47, 0x0d, 0x0a, 0x1a, 0x0a]),
        chunk('IHDR', ihdr),
        chunk('IDAT', zlib.deflateSync(baris)),
        chunk('IEND', Buffer.alloc(0)),
    ]));
}

// ---------- kumpulkan segitiga dari scene ----------
const { scene, piringGroup } = buildNasiScene(THREE);

const kamera = new THREE.PerspectiveCamera(34, LEBAR / TINGGI, 0.1, 40);
kamera.position.set(KAMERA.posisi[0], KAMERA.posisi[1], KAMERA.posisi[2]);
kamera.lookAt(KAMERA.fokus[0], KAMERA.fokus[1], KAMERA.fokus[2]);
kamera.updateMatrixWorld();
kamera.updateProjectionMatrix();

piringGroup.rotation.y = 0.6;
piringGroup.updateMatrixWorld(true);

const segitiga = [];
let nomorObjek = -1;
piringGroup.traverse((obj) => {
    if (!obj.isMesh) return;

    const geo = obj.geometry;
    const pos = geo.attributes.position;
    const idx = geo.index;

    nomorObjek++;
    if (HANYA_OBJEK !== null && nomorObjek !== HANYA_OBJEK) return;

    const warna = new THREE.Color().copy(obj.material.color);
    const mat = obj.matrixWorld;

    const jumlah = idx ? idx.count : pos.count;
    const ambil = (i) => {
        const v = new THREE.Vector3(pos.getX(i), pos.getY(i), pos.getZ(i)).applyMatrix4(mat);
        return v;
    };

    for (let i = 0; i < jumlah; i += 3) {
        const wa = ambil(idx ? idx.getX(i) : i);
        const wb = ambil(idx ? idx.getX(i + 1) : i + 1);
        const wc = ambil(idx ? idx.getX(i + 2) : i + 2);

        // Tidak ada backface culling: z-buffer di bawah sudah menangani
        // halangan. Culling membuat segitiga di tepi siluet hilang secara acak.
        // Normal bidang segitiga, dipakai untuk pencahayaan datar.
        const normal = new THREE.Vector3()
            .crossVectors(new THREE.Vector3().subVectors(wb, wa), new THREE.Vector3().subVectors(wc, wa))
            .normalize();

        segitiga.push({
            a: wa.clone(),
            b: wb.clone(),
            c: wc.clone(),
            normal,
            warna,
        });
    }
});

// ---------- transformasi ke layar ----------
// Matriks viewport memetakan NDC ke koordinat piksel, dengan sumbu Y
// dibalik supayayatatan atas di layar adalah nilai Y terkecil.
const viewport = new THREE.Matrix4().set(
    LEBAR / 2, 0, 0, LEBAR / 2,
    0, -TINGGI / 2, 0, TINGGI / 2,
    0, 0, 0.5, 0.5,
    0, 0, 0, 1
);

// P * V, lalu digabung dengan viewport. applied langsung ke titik dunia:
// V sudah termasuk di dalamnya, jadi tidak boleh dibalik sumbu Z manual.
const vp = new THREE.Matrix4()
    .multiplyMatrices(kamera.projectionMatrix, kamera.matrixWorldInverse)
    .premultiply(viewport);

const dipakaiTotal = { dipakai: 0, diLuar: 0 };

const e = vp.elements;

// Proyeksi manual sambil menyimpan w, karena Vector3.applyMatrix4()
// membuang nilai w padahal Interpolasi kedalaman butuh w untuk
// koreksi perspektif. Tanpa koreksi ini, segitiga bagian belakang bola
// bisa lolos z-buffer dan muncul sebagai garis-garis radial.
for (const s of segitiga) {
    for (const k of ['a', 'b', 'c']) {
        const v = s[k];
        const x = v.x;
        const y = v.y;
        const z = v.z;
        const w = e[3] * x + e[7] * y + e[11] * z + e[15];

        v.x = (e[0] * x + e[4] * y + e[8] * z + e[12]) / w;
        v.y = (e[1] * x + e[5] * y + e[9] * z + e[13]) / w;
        v.z = (e[2] * x + e[6] * y + e[10] * z + e[14]) / w;
        v.w = 1 / w;
    }

    dipakaiTotal.dipakai++;
    const semuaDiLayar =
        [s.a, s.b, s.c].every(
            (v) => v.x >= -2 && v.x <= LEBAR + 2 && v.y >= -2 && v.y <= TINGGI + 2
        );
    if (!semuaDiLayar) dipakaiTotal.diLuar++;

}

// segitiga tidak diurutkan: kedalaman diinterpolasi per piksel lewat z-buffer,
// supaya pratinjau tidak menipu karena urutan gambar.

// ---------- isi piksel ----------
const rgb = Buffer.alloc(LEBAR * TINGGI * 3);
// latar krem sepertiallel di halaman
for (let i = 0; i < LEBAR * TINGGI; i++) {
    rgb[i * 3] = 250;
    rgb[i * 3 + 1] = 245;
    rgb[i * 3 + 2] = 236;
}

// z-buffer: 1 = paling jauh (NDC +1), -1 = paling dekat (NDC -1)
const zBuffer = new Float32Array(LEBAR * TINGGI).fill(Infinity);

const arahCahaya = new THREE.Vector3(0.5, 0.9, 0.6).normalize();
const arahIsi = new THREE.Vector3(0.5, 0.2, -0.8).normalize();

for (const s of segitiga) {
    // Pencahayaan datar: cahaya utama + pantulan lembut dari bawah,
    // meniru HemisphereLight + DirectionalLight di three.js.
    const utama = Math.max(0, s.normal.dot(arahCahaya));
    const isi = Math.max(0, s.normal.dot(arahIsi));
    const cahaya = 0.52 + utama * 0.52 + isi * 0.14;

    const r = Math.min(255, s.warna.r * 255 * cahaya) | 0;
    const g = Math.min(255, s.warna.g * 255 * cahaya) | 0;
    const b = Math.min(255, s.warna.b * 255 * cahaya) | 0;

    const luas = (s.b.x - s.a.x) * (s.c.y - s.a.y) - (s.c.x - s.a.x) * (s.b.y - s.a.y);
    if (Math.abs(luas) < 1e-9) continue;

    const x0 = Math.floor(Math.min(s.a.x, s.b.x, s.c.x));
    const x1 = Math.ceil(Math.max(s.a.x, s.b.x, s.c.x));
    const y0 = Math.floor(Math.min(s.a.y, s.b.y, s.c.y));
    const y1 = Math.ceil(Math.max(s.a.y, s.b.y, s.c.y));

    for (let y = Math.max(0, y0); y <= Math.min(TINGGI - 1, y1); y++) {
        for (let x = Math.max(0, x0); x <= Math.min(LEBAR - 1, x1); x++) {
            const px = x + 0.5;
            const py = y + 0.5;

            // bobot barycentric: w0 untuk simpul a, w1 untuk b, w2 untuk c
            const w0 = ((s.b.x - s.a.x) * (py - s.a.y) - (px - s.a.x) * (s.b.y - s.a.y)) / luas;
            const w1 = ((px - s.a.x) * (s.c.y - s.a.y) - (s.c.x - s.a.x) * (py - s.a.y)) / luas;
            const w2 = 1 - w0 - w1;

            if (w0 < 0 || w1 < 0 || w2 < 0) continue;

            // Koreksi perspektif: bagi dengan bobot 1/w interpolasi.
            const iw = w0 * s.a.w + w1 * s.b.w + w2 * s.c.w;
            if (Math.abs(iw) < 1e-12) continue;

            const z =
                (w0 * s.a.z * s.a.w + w1 * s.b.z * s.b.w + w2 * s.c.z * s.c.w) / iw;
            const idxPiksel = y * LEBAR + x;

            // NDC z yang lebih kecil berarti lebih dekat
            if (z >= zBuffer[idxPiksel]) continue;

            zBuffer[idxPiksel] = z;
            const o = idxPiksel * 3;
            rgb[o] = r;
            rgb[o + 1] = g;
            rgb[o + 2] = b;
        }
    }
}

const keluar = HANYA_OBJEK !== null ? `tests/js/preview-objek-${HANYA_OBJEK}.png` : 'tests/js/preview-nasi.png';
tulisPNG(keluar, LEBAR, TINGGI, rgb);
console.log(`Pratinjau ditulis ke ${keluar} (${LEBAR}x${TINGGI}, ${segitiga.length} segitiga, objek ke-${HANYA_OBJEK ?? 'semua'})`);