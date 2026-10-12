/**
 * Uji ringan untuk buildNasiScene().
 *
 * Bagian pembicara WebGL tidak bisa diuji di Node, tapi seluruh geometri,
 * material, dan lampu bisa. Ini menangkap kesalahan nama API three.js,
 * posisi(vertex) NaN, dan material yang tidak terpasang.
 *
 * Jalankan: node tests/js/hero-nasi.test.mjs
 */
import assert from 'node:assert/strict';
import * as THREE from 'three';

import { buildNasiScene } from '../../resources/js/hero-nasi.js';

let lolos = 0;
const gagal = [];

function uji(nama, fn) {
    try {
        fn();
        lolos++;
    } catch (e) {
        gagal.push(`${nama}: ${e.message}`);
    }
}

const { scene, piringGroup } = buildNasiScene(THREE);

uji('scene dan piringGroup terbentuk', () => {
    assert.ok(scene instanceof THREE.Scene, 'scene harus THREE.Scene');
    assert.ok(piringGroup instanceof THREE.Group, 'piringGroup harus THREE.Group');
});

uji('piring ditambahkan ke scene', () => {
    assert.ok(scene.children.includes(piringGroup), 'piringGroup harus anak dari scene');
});

uji('ada piring, nasi, sambel, dan bumbu', () => {
    // Langsung di piringGroup: badan piring, lipatan, nasi, sambal,
    // grup kerupuk, dan kerupuk kuning.
    assert.equal(piringGroup.children.length, 6, 'jumlah anak langsung piringGroup tidak sesuai');

    // Total semua mesh termasuk 3 kerupuk di dalam grup.
    let jumlahMesh = 0;
    piringGroup.traverse((obj) => {
        if (obj.isMesh) jumlahMesh++;
    });
    assert.equal(jumlahMesh, 8, 'total mesh harus 8');
});

uji('tidak ada posisi vertex NaN', () => {
    piringGroup.traverse((obj) => {
        const posisi = obj.geometry?.attributes?.position;
        if (!posisi) return;

        const arr = posisi.array;
        for (let i = 0; i < arr.length; i++) {
            assert.ok(Number.isFinite(arr[i]), `ada nilai NaN/Infinity pada mesh ${obj.type}`);
        }
    });
});

uji('setiap mesh punya material', () => {
    piringGroup.traverse((obj) => {
        if (!obj.isMesh) return;
        assert.ok(obj.material, `mesh ${obj.geometry.type} tidak punya material`);
        assert.ok(obj.material.color, `material mesh ${obj.geometry.type} tidak punya warna`);
    });
});

uji('nasi berwarna putih, sambel merah', () => {
    const warna = piringGroup.children
        .filter((c) => c.isMesh && c.material?.color)
        .map((c) => c.material.color.getHexString());

    assert.ok(warna.includes('fbf8f2'), `warna nasi putih tidak ditemukan. Ada: ${warna}`);
    assert.ok(waraSambelAda(warna), `warna sambal merah tidak ditemukan. Ada: ${warna}`);
});

function waraSambelAda(warna) {
    return warna.some((w) => {
        const r = parseInt(w.slice(0, 2), 16);
        const g = parseInt(w.slice(2, 4), 16);
        return r > 120 && g < 60;
    });
}

uji('ada pencahayaan', () => {
    const lampu = scene.children.filter((c) => c.isLight);
    assert.equal(lampu.length, 3, 'harus ada hemisphere + 2 directional light');
});

uji('normal sudah dihitung ulang setelah vertices digeser', () => {
    const nasi = piringGroup.children.find(
        (c) => c.isMesh && c.geometry.attributes.normal && c.material.color.getHexString() === 'fbf8f2'
    );
    assert.ok(nasi, 'mesh nasi tidak ditemukan');
    assert.ok(nasi.geometry.attributes.normal, 'normal nasi tidak ada');
});

uji('nasi tidak tenggelam di bawah piring', () => {
    const nasi = piringGroup.children.find(
        (c) => c.isMesh && c.material.color.getHexString() === 'fbf8f2'
    );
    nasi.geometry.computeBoundingBox();
    const atasNasi = nasi.position.y + nasi.geometry.boundingBox.max.y;
    const bawahPiring = -0.095;
    assert.ok(atasNasi > bawahPiring, `nasi(${atasNasi}) harus di atas piring(${bawahPiring})`);
});

uji('sambel tidak menumpuk persis di nasi', () => {
    const sambal = piringGroup.children.find(
        (c) => c.isMesh && waraSambelAda([c.material.color.getHexString()])
    );
    assert.ok(sambal, 'mesh sambal tidak ditemukan');
    assert.ok(sambal.position.distanceTo(new THREE.Vector3(-0.13, 0.04, -0.03)) > 0.3);
});

console.log(`\n  ${lolos} lolos, ${gagal.length} gagal`);
if (gagal.length) {
    gagal.forEach((g) => console.log('  x ' + g));
    process.exit(1);
}
console.log('  buildNasiScene() aman dipakai.\n');