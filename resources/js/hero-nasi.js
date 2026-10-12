/**
 * Hero 3D: sepiring nasi sambel yang dibuat langsung dari kode.
 *
 * Modelnya tidak diunduh dari mana pun — piring, gunung nasi, sambel, dan
 * bumbu dibangun dari geometri bawaan three.js. Jadi tidak ada file model
 * 3D di repository, tidak ada masalah lisensi, dan ukurannya tetap kecil.
 *
 * three.js hanya dipakai sebagai library render (WebGL). Kalau perangkat
 * tidak mendukung WebGL, fungsi initNasiSambel() berhenti dan
 * elemen fallback yang sudah ada di halaman tetap tampil.
 */

// Sudut pandang kamera. Diekspor supaya perender pratinjau di
// tests/js/preview-nasi.mjs memakai angka yang sama persis.
export const KAMERA = {
    posisi: [0, 2.35, 3.45],
    fokus: [0, 0.0, 0.02],
};

const PALET = {
    piring: 0xf3ece1,
    piringTepi: 0xd9cbb8,
    nasi: 0xfbf8f2,
    nasiGelap: 0xe8dfcd,
    sambal: 0xc4170f,
    sambalGelap: 0x6d0806,
    bumbu: 0xe4572e,
    kerupuk: 0xd9a441,
};

function deteksiWebGL() {
    try {
        const canvas = document.createElement('canvas');
        return Boolean(
            window.WebGLRenderingContext &&
            (canvas.getContext('webgl') || canvas.getContext('experimental-webgl'))
        );
    } catch (e) {
        return false;
    }
}

export function initNasiSambel(el) {
    if (!el || el.dataset.nasiSambel === 'siap') return;
    if (!deteksiWebGL()) return;

    el.dataset.nasiSambel = 'siap';

    // three.js dimuat dinamis supaya bundle halaman lain tidak ikut
    // membawa library ini. Halaman ini pun baru memuatnya setelah konten
    // utama selesai digambar.
    import('three')
        .then((THREE) => {
            if (el.dataset.nasiSambel === 'batal') return;
            bangunAdeganNasi(THREE, el);
        })
        .catch(() => {
            el.dataset.nasiSambel = 'gagal';
        });
}

/**
 * Kasar permukaan bola supaya terlihat butiran, bukan licin.
 *
 * Gangguan harus:DIMULAI HALUS di bagian bawah. Pemotongan tajam (misalnya
 * hanya menggeser titik dengan y di atas ambang) membuat segitiga di dekat
 * batas terbalik, lalu dihapus oleh penCull backface sehingga muncul lubang
 * pada model.
 */
function buatGgunenganButiran(geometri, { frekuensi, amplitudo, batasBawah }) {
    const posisi = geometri.attributes.position;

    // Hash position supaya hasilnya sama persis tiap kali scene dibangun.
    const acak = (a, b, c) => {
        const n = Math.sin(a * 12.9898 + b * 78.233 + c * 37.719) * 43758.5453;
        return n - Math.floor(n);
    };

    for (let i = 0; i < posisi.count; i++) {
        const x = posisi.getX(i);
        const y = posisi.getY(i);
        const z = posisi.getZ(i);

        // Bobot mulai dari 0 di batas bawah sampai 1 di puncak,
        // jadi permukaan bagian bawah tetap utuh.
        const bobot = Math.min(1, Math.max(0, (y - batasBawah) / (1 + batasBawah)));
        if (bobot <= 0) continue;

        // noise halus, bukan acak murni, supaya permukaan tidak bergerigi tajam
        const n1 = acak(x * frekuensi, y * frekuensi, z * frekuensi);
        const n2 = acak(y * frekuensi * 1.7 + 4, z * frekuensi * 1.3 + 9, x * frekuensi * 1.1 + 2);
        const ggpangan = ((n1 + n2) / 2 - 0.5) * 2 * amplitudo * bobot;

        posisi.setXYZ(i, x, y + ggpangan, z);
    }

    posisi.needsUpdate = true;
}

/**
 * Bangun adegan piring nasi sambel.
 *
 * Dipisah dari pembuatan renderer supaya bagian ini bisa diuji tanpa WebGL.
 * Tidak ada satu pun API browser yang dipakai di sini, hanya geometri dan
 * material three.js.
 */
export function buildNasiScene(THREE) {
    const scene = new THREE.Scene();

    // Piring dibagi beberapa bagian supaya tidak terlihat seperti silinder polos.
    const piringGroup = new THREE.Group();

    const badanPiring = new THREE.Mesh(
        new THREE.CylinderGeometry(1.18, 1.02, 0.09, 64),
        new THREE.MeshStandardMaterial({ color: PALET.piring, roughness: 0.62, metalness: 0.04 })
    );
    badanPiring.position.y = -0.05;
    piringGroup.add(badanPiring);

    const lipatanPiring = new THREE.Mesh(
        new THREE.TorusGeometry(1.16, 0.055, 16, 64),
        new THREE.MeshStandardMaterial({ color: PALET.piringTepi, roughness: 0.5 })
    );
    lipatanPiring.rotation.x = Math.PI / 2;
    lipatanPiring.position.y = -0.005;
    piringGroup.add(lipatanPiring);

    // Gunung nasi: bola dipipihkan, lalu permukaannya diberi sedikit gangguan
    // supaya terlihat butiran, bukan seperti bola licin.
    // Setengah bola, bukan bola penuh. Alasannya: bola penuh menembus
    // permukaan piring sehingga dua bidang bersinggungan dan muncul
    // z-fighting (garis-garis bergerigi) saat digambar.
    const geometriesNasi = new THREE.SphereGeometry(0.62, 48, 28, 0, Math.PI * 2, 0, Math.PI / 2);
    buatGgunenganButiran(geometriesNasi, { frekuensi: 9, amplitudo: 0.014, batasBawah: 0 });
    geometriesNasi.scale(1.14, 0.5, 1.0);
    geometriesNasi.computeVertexNormals();

    const nasi = new THREE.Mesh(
        geometriesNasi,
        new THREE.MeshStandardMaterial({ color: PALET.nasi, roughness: 0.9 })
    );
    nasi.position.set(-0.12, 0.28, -0.02);
    nasi.rotation.y = 0.35;
    piringGroup.add(nasi);

    // Sambel merah, dibuat sebagai gumpalan kecil di samping nasi.
    const geometriesSambal = new THREE.SphereGeometry(0.3, 32, 18, 0, Math.PI * 2, 0, Math.PI / 2);
    buatGgunenganButiran(geometriesSambal, { frekuensi: 17, amplitudo: 0.008, batasBawah: 0 });
    geometriesSambal.scale(1.05, 0.5, 0.92);
    geometriesSambal.computeVertexNormals();

    const sambal = new THREE.Mesh(
        geometriesSambal,
        new THREE.MeshStandardMaterial({ color: PALET.sambal, roughness: 0.55 })
    );
    sambal.position.set(0.5, 0.1, 0.28);
    sambal.rotation.y = -0.6;
    piringGroup.add(sambal);

    // Bumbu: beberapa keping kecil berwarna oranye di tepi sambel.
    const kerupuk = new THREE.Group();
    const posisiBumbu = [
        [0.82, 0.03, 0.3, 0.3],
        [0.66, 0.02, 0.6, 0.24],
        [0.92, 0.02, -0.08, 0.22],
    ];

    posisiBumbu.forEach(([x, y, z, skala]) => {
        const kerupukMesh = new THREE.Mesh(
            new THREE.SphereGeometry(skala, 20, 14),
            new THREE.MeshStandardMaterial({ color: PALET.bumbu, roughness: 0.72 })
        );
        kerupukMesh.position.set(x, y, z);
        kerupukMesh.scale.set(1, 0.55, 1);
        kerupuk.add(kerupukMesh);
    });
    piringGroup.add(kerupuk);

    // Kerupuk kuning sebagai kontras kecil.
    const kerupukKuning = new THREE.Mesh(
        new THREE.SphereGeometry(0.19, 20, 14),
        new THREE.MeshStandardMaterial({ color: PALET.kerupuk, roughness: 0.75 })
    );
    kerupukKuning.position.set(0.08, 0.03, 0.62);
    kerupukKuning.scale.set(1, 0.45, 1);
    piringGroup.add(kerupukKuning);

    scene.add(piringGroup);

    // Pencahayaan sederhana, tanpa HDR supaya ringan.
    scene.add(new THREE.HemisphereLight(0xfff6e8, 0x5a3a30, 1.5));

    const lampuUtama = new THREE.DirectionalLight(0xfff3e0, 1.5);
    lampuUtama.position.set(2.4, 3.4, 2.2);
    scene.add(lampuUtama);

    const lampuPendamping = new THREE.DirectionalLight(0xffd9c0, 0.6);
    lampuPendamping.position.set(-2.6, 1.2, -1.6);
    scene.add(lampuPendamping);

    return { scene, piringGroup };
}

function bangunAdeganNasi(THREE, el) {
    const { scene, piringGroup } = buildNasiScene(THREE);

    const camera = new THREE.PerspectiveCamera(34, 1, 0.1, 40);
    camera.position.set(KAMERA.posisi[0], KAMERA.posisi[1], KAMERA.posisi[2]);
    camera.lookAt(KAMERA.fokus[0], KAMERA.fokus[1], KAMERA.fokus[2]);

    const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    renderer.outputColorSpace = THREE.SRGBColorSpace;
    el.appendChild(renderer.domElement);

    // Ukuran kanvas mengikuti wadahnya, lalu dikoreksi saat jendela diubah.
    const sesuaikanUkuran = () => {
        const lebar = el.clientWidth;
        const tinggi = el.clientHeight;

        if (!lebar || !tinggi) return;

        renderer.setSize(lebar, tinggi, false);
        camera.aspect = lebar / tinggi;
        camera.updateProjectionMatrix();
    };
    sesuaikanUkuran();

    if ('ResizeObserver' in window) {
        new ResizeObserver(sesuaikanUkuran).observe(el);
    } else {
        window.addEventListener('resize', sesuaikanUkuran);
    }

    // Sudut miring mengikuti posisi kursor di atas elemen.
    const target = { x: 0, y: 0 };
    const sekarang = { x: 0, y: 0 };

    const gerakKursor = (e) => {
        const kotak = el.getBoundingClientRect();
        const x = (e.clientX - kotak.left) / kotak.width - 0.5;
        const y = (e.clientY - kotak.top) / kotak.height - 0.5;
        target.y = x * 0.5;
        target.x = y * 0.32;
    };

    el.addEventListener('pointermove', gerakKursor);
    el.addEventListener('pointerleave', () => {
        target.x = 0;
        target.y = 0;
    });

    const kurangiGerak = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    let berhenti = false;

    const animate = () => {
        if (berhenti) return;

        // Gradual menuju sudut target supaya gerakannya halus, bukan kaku.
        sekarang.x += (target.x - sekarang.x) * 0.08;
        sekarang.y += (target.y - sekarang.y) * 0.08;

        if (!kurangiGerak) {
            piringGroup.rotation.y += 0.0045;
        }

        piringGroup.rotation.x = sekarang.x;
        piringGroup.rotation.y += sekarang.y * 0.35;

        renderer.render(scene, camera);
        requestAnimationFrame(animate);
    };
    requestAnimationFrame(animate);

    // Berhenti animasi saat elemen ini tidak terlihat, supaya tidak membebani
    // memori dan processor ketika pengguna sedang scrolls ke bagian lain.
    if ('IntersectionObserver' in window) {
        new IntersectionObserver(
            ([entry]) => {
                berhenti = !entry.isIntersecting;
                if (!berhenti) requestAnimationFrame(animate);
            },
            { threshold: 0.05 }
        ).observe(el);
    }

    // Hentikan total saat halaman pindah, supaya tidak tetap berjalan di
    // cache browser.
    window.addEventListener('pagehide', () => {
        berhenti = true;
        renderer.dispose();
    });
}