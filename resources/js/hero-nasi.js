/**
 * Hero 3D: sepiring nasi sambel yang dibuat langsung dari kode.
 *
 * Modelnya tidak diunduh dari mana pun — piring, gunung nasi, sambel, dan
 * bumbu dibangun dari geometri bawaan three.js. Teksturnya juga digambar
 * saat runtime di atas <canvas>. Jadi tidak ada file model 3D maupun gambar
 * di repository, tidak ada masalah lisensi, dan ukurannya tetap kecil.
 *
 * three.js hanya dipakai sebagai library render (WebGL). Kalau perangkat
 * tidak mendukung WebGL, initNasiSambel() berhenti dan elemen fallback yang
 * sudah ada di halaman tetap tampil.
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
    sambal: 0xc4170f,
    bumbu: 0xe4572e,
    kerupuk: 0xd9a441,
    cabai: 0x8f1206,
    sesame: 0xf0e2c0,
};

// Bentuk kubah nasi dan sambel. Dipakai bersama oleh pembuatan geometri dan
// penempatan taburan, supaya keduanya tidak mungkin memakai angka berbeda.
const BENTUK_NASI = { radius: 0.62, lebar: 1.05, tinggi: 0.5 };
const BENTUK_SAMBAL = { radius: 0.3, lebar: 1.05, tinggi: 0.5 };

// Lamanya animasi masuk, dalam detik.
const DURASI_MASUK = 0.7;
// Putaran otomatis per frame (sama sekali sekitar 23 detik satu putaran).
const KECEPATAN_PUTAR = 0.0045;
// Maksimal sudut miring akibat kursor, dalam radian.
const MAKSIMUM_MIRING = 0.28;

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

/**
 * Pasang piring nasi sambel pada elemen kanvas.
 *
 * Keadaan disimpan sebagai atribut data-nasi-sambel pada elemen pembungkus,
 * lalu dipakai CSS untuk menyembunyikan atau menampilkan teks
 * "memuat piring 3D…".
 */
export function initNasiSambel(el) {
    if (!el || el.dataset.nasiSambel === 'siap') return;

    const wadah = el.closest('.nasi-sambel-3d-wrap');
    const tandai = (keadaan) => {
        el.dataset.nasiSambel = keadaan;
        if (wadah) wadah.dataset.nasiSambel = keadaan;
    };

    if (!deteksiWebGL()) {
        tandai('tanpa-webgl');
        return;
    }

    // three.js dimuat dinamis supaya bundle halaman lain tidak ikut
    // membawa library ini. Halaman ini pun baru memuatnya setelah konten
    // utama selesai digambar.
    Promise.all([import('three'), import('three/addons/environments/RoomEnvironment.js')])
        .then(([THREE, { RoomEnvironment }]) => bangunAdeganNasi(THREE, RoomEnvironment, el))
        .catch(() => tandai('gagal'));
}

/**
 * Kasar permukaan bola supaya terlihat butiran, bukan licin.
 *
 * Gangguan harus mulai halus di bagian bawah. Pemotongan tajam hanya
 * menggeser titik dengan y di atas ambang akan membuat segitiga di dekat
 * batas terbalik dan muncul lubang pada model.
 */
function buatGunungDenganButiran(geometri, { frekuensi, amplitudo }) {
    const posisi = geometri.attributes.position;

    // Hash posisi supaya hasilnya sama persis tiap kali scene dibangun.
    const acak = (a, b, c) => {
        const n = Math.sin(a * 12.9898 + b * 78.233 + c * 37.719) * 43758.5453;
        return n - Math.floor(n);
    };

    for (let i = 0; i < posisi.count; i++) {
        const x = posisi.getX(i);
        const y = posisi.getY(i);
        const z = posisi.getZ(i);

        // Bobot 0 di alas kubah sampai 1 di puncak, jadi tepi bawah tetap utuh.
        const bobot = Math.max(0, y);
        if (bobot <= 0) continue;

        // Noise halus, bukan acak murni, supaya permukaan tidak bergerigi.
        const n1 = acak(x * frekuensi, y * frekuensi, z * frekuensi);
        const n2 = acak(y * frekuensi * 1.7 + 4, z * frekuensi * 1.3 + 9, x * frekuensi * 1.1 + 2);
        const ggpangan = ((n1 + n2) / 2 - 0.5) * 2 * amplitudo * bobot;

        posisi.setXYZ(i, x, y + ggpangan, z);
    }

    posisi.needsUpdate = true;
}

/**
 * Ubah setengah bola menjadi kubah nasi.
 *
 * Setengah bola dari SphereGeometry punya tinggi dari 0 (alas) sampai radius
 * (puncak). Yang dipakai hanya bagian di atas y = 0, jadi cukup menormalkan
 * tinggi kubah menjadi 0..tinggi. Titik yang sedikit di bawah garis alas
 * otomatis ikut menyatu ke alas, tanpa perlu memotong mesh.
 *
 * Mesh sengaja tidak dipotong sesudahnya: pemotongan menyisakan segitiga
 * dengan tinggi mendekati nol di tepi, dan segitiga seperti itu mudah
 * terbalik lalu hilang ketika digambar.
 */
function buatKubah(geometri, { radius, lebar, tinggi }) {
    const posisi = geometri.attributes.position;

    let puncak = 0;
    for (let i = 0; i < posisi.count; i++) {
        if (posisi.getY(i) > puncak) puncak = posisi.getY(i);
    }
    if (puncak <= 0) puncak = radius;

    for (let i = 0; i < posisi.count; i++) {
        posisi.setY(i, (posisi.getY(i) / puncak) * tinggi);
    }

    posisi.needsUpdate = true;
    // Sumbu Y sudah berada di skala yang benar, jadi hanya X dan Z dilebarkan.
    geometri.scale(lebar, 1, lebar);
    geometri.computeVertexNormals();
    return geometri;
}

/**
 * Bangun adegan piring nasi sambel.
 *
 * Dipisah dari pembuatan renderer supaya bagian ini bisa diuji tanpa WebGL
 * dan tanpa DOM. Parameter `tekstur` opsional: kalau diisi, hasilnya dipakai
 * sebagai peta warna dan relief; kalau tidak, mesh tetap tampil polos.
 */
export function buildNasiScene(THREE, tekstur = {}) {
    const scene = new THREE.Scene();

    // Dua grup bersarang. Grup luarisersegera miring kursor, grup dalam
    // berputar sendiri. Kalau keduanya memakai satu grup, putaran dan
    // kemiringan saling menimpa dan sabotase.
    const grupMiring = new THREE.Group();
    const piringGroup = new THREE.Group();
    grupMiring.add(piringGroup);

    // Piring: keramik berglasur, jadi sangat mengkilap di lapisan atas.
    const badanPiring = new THREE.Mesh(
        new THREE.CylinderGeometry(1.18, 1.02, 0.09, 96),
        new THREE.MeshPhysicalMaterial({
            color: PALET.piring,
            roughness: 0.22,
            metalness: 0.02,
            clearcoat: 1,
            clearcoatRoughness: 0.08,
            envMapIntensity: 0.9,
            map: tekstur.piring || null,
        })
    );
    badanPiring.position.y = -0.05;
    badanPiring.castShadow = true;
    badanPiring.receiveShadow = true;
    piringGroup.add(badanPiring);

    const lipatanPiring = new THREE.Mesh(
        new THREE.TorusGeometry(1.16, 0.055, 20, 96),
        new THREE.MeshPhysicalMaterial({
            color: PALET.piringTepi,
            roughness: 0.28,
            clearcoat: 1,
            clearcoatRoughness: 0.1,
            envMapIntensity: 0.8,
        })
    );
    lipatanPiring.rotation.x = Math.PI / 2;
    lipatanPiring.position.y = -0.005;
    lipatanPiring.castShadow = true;
    piringGroup.add(lipatanPiring);

    // Gunung nasi. Berpermukaan diffus: cahaya melawan dan spreading di
    // permukaan, bukan terpantul tajam seperti plastik.
    const geometriNasi = buatKubah(
        new THREE.SphereGeometry(0.62, 96, 48, 0, Math.PI * 2, 0, Math.PI / 2),
        BENTUK_NASI
    );
    buatGunungDenganButiran(geometriNasi, { frekuensi: 9, amplitudo: 0.016 });
    geometriNasi.computeVertexNormals();

    const nasi = new THREE.Mesh(
        geometriNasi,
        new THREE.MeshPhysicalMaterial({
            color: PALET.nasi,
            roughness: 0.78,
            metalness: 0,
            sheen: 0.6,
            sheenColor: 0xfff4e2,
            sheenRoughness: 0.9,
            envMapIntensity: 0.5,
            map: tekstur.nasi || null,
            bumpMap: tekstur.nasi || null,
            bumpScale: 0.02,
        })
    );
    nasi.position.set(-0.12, 0.28, -0.02);
    nasi.rotation.y = 0.35;
    nasi.castShadow = true;
    nasi.receiveShadow = true;
    piringGroup.add(nasi);

    // Sambel: saus basah, jadi paling mengkilap dari semua bahan.
    const geometriSambal = buatKubah(
        new THREE.SphereGeometry(0.3, 64, 32, 0, Math.PI * 2, 0, Math.PI / 2),
        BENTUK_SAMBAL
    );
    buatGunungDenganButiran(geometriSambal, { frekuensi: 17, amplitudo: 0.009 });
    geometriSambal.computeVertexNormals();

    const sambal = new THREE.Mesh(
        geometriSambal,
        new THREE.MeshPhysicalMaterial({
            color: PALET.sambal,
            roughness: 0.45,
            metalness: 0,
            clearcoat: 1,
            clearcoatRoughness: 0.12,
            envMapIntensity: 0.85,
            map: tekstur.sambal || null,
            roughnessMap: tekstur.sambal || null,
        })
    );
    sambal.position.set(0.58, 0.1, 0.42);
    sambal.rotation.y = -0.6;
    sambal.castShadow = true;
    sambal.receiveShadow = true;
    piringGroup.add(sambal);

    // Bumbu kerupuk di tepi sambel.
    const kerupuk = new THREE.Group();
    const posisiBumbu = [
        [0.82, 0.03, 0.3, 0.3],
        [0.66, 0.02, 0.6, 0.24],
        [0.92, 0.02, -0.08, 0.22],
    ];

    posisiBumbu.forEach(([x, y, z, skala]) => {
        const kerupukMesh = new THREE.Mesh(
            new THREE.SphereGeometry(skala, 24, 16),
            new THREE.MeshPhysicalMaterial({
                color: PALET.bumbu,
                roughness: 0.92,
                metalness: 0,
                envMapIntensity: 0.4,
            })
        );
        kerupukMesh.position.set(x, y, z);
        kerupukMesh.scale.set(1, 0.55, 1);
        kerupukMesh.castShadow = true;
        kerupuk.add(kerupukMesh);
    });
    piringGroup.add(kerupuk);

    const kerupukKuning = new THREE.Mesh(
        new THREE.SphereGeometry(0.19, 24, 16),
        new THREE.MeshPhysicalMaterial({
            color: PALET.kerupuk,
            roughness: 0.9,
            envMapIntensity: 0.4,
        })
    );
    kerupukKuning.position.set(0.08, 0.03, 0.62);
    kerupukKuning.scale.set(1, 0.45, 1);
    kerupukKuning.castShadow = true;
    piringGroup.add(kerupukKuning);

    // Taburan cabai dan sesame di permukaan nasi. Posisi dihitung dari rumus
    // kubah supaya menempel rapi dan tidak menembus permukaan.
    taburCabai(THREE, piringGroup, nasi.position, BENTUK_NASI);
    taburSesame(THREE, piringGroup, nasi.position, BENTUK_NASI);

    scene.add(grupMiring);

    // Pencahayaan dasar. Environment untuk pantulan diambil di
    // bangunAdeganNasi karena butuh renderer.
    scene.add(new THREE.HemisphereLight(0xfff6e8, 0x5a3a30, 1.1));

    const lampuUtama = new THREE.DirectionalLight(0xfff3e0, 1.7);
    lampuUtama.position.set(2.4, 3.4, 2.2);
    // Bayangan dibatasiarea piring saja supaya resolusinya terpakai penuh.
    lampuUtama.castShadow = true;
    lampuUtama.shadow.mapSize.set(1024, 1024);
    lampuUtama.shadow.camera.left = -2;
    lampuUtama.shadow.camera.right = 2;
    lampuUtama.shadow.camera.top = 2;
    lampuUtama.shadow.camera.bottom = -2;
    lampuUtama.shadow.camera.near = 0.5;
    lampuUtama.shadow.camera.far = 12;
    // Nilai negatif kecil mencegah "acne" (bercak gelap) pada bidang
    // melengkung seperti kubah nasi.
    lampuUtama.shadow.bias = -0.0005;
    scene.add(lampuUtama);

    const lampuPendamping = new THREE.DirectionalLight(0xffd9c0, 0.5);
    lampuPendamping.position.set(-2.6, 1.2, -1.6);
    scene.add(lampuPendamping);

    return { scene, grupMiring, piringGroup };
}

/**
 * Sebarkan titik pada seluruh permukaan kubah.
 *
 * Azimut memakai kelipatan sudut emas (2,399963) supaya titik tersebar merata
 * tanpa menumpuk, dan sudut kutub dibuat menurun agar tidak ada titik yang
 * jatuh tepat di puncak dan menutupi titik lain.
 */
function sebarDiKubah(jumlah, radius, lebar, tinggi, seedAwal) {
    const titik = [];
    for (let i = 0; i < jumlah; i++) {
        // Sudut kutub menurun dari puncak, azimut berputar konstan.
        const kutub = Math.acos(1 - ((i + 0.5) / jumlah) * 0.92);
        const azimut = i * 2.399963 + seedAwal;
        const sinK = Math.sin(kutub);

        // Kubah dibuat dengan geometri setengah bola yang tingginya sudah
        // dinormalkan ke 0..tinggi, lalu X dan Z dilebarkan oleh `lebar`.
        // Jadi tinggi memakai `tinggi` langsung (bukan radius * tinggi), dan
        // Z juga harus dikali `lebar` supaya simetris dengan X.
        titik.push({
            x: Math.cos(azimut) * sinK * radius * lebar,
            y: Math.cos(kutub) * tinggi,
            z: Math.sin(azimut) * sinK * radius * lebar,
            // Normal permukaan kubah, dipakai untuk menghadapkan keripik cabainya.
            nx: Math.cos(azimut) * sinK,
            ny: Math.cos(kutub),
            nz: Math.sin(azimut) * sinK,
        });
    }
    return titik;
}

function taburCabai(THREE, induk, posisiNasi, bentuk) {
    const target = new THREE.Group();
    const titik = sebarDiKubah(7, bentuk.radius, bentuk.lebar, bentuk.tinggi, 0.7);

    const material = new THREE.MeshPhysicalMaterial({
        color: PALET.cabai,
        roughness: 0.5,
        clearcoat: 0.7,
        envMapIntensity: 0.6,
    });

    // CircleGeometry menghadap ke arah +Z, jadi quaternion dipakai untuk
    // memiringkannya sesuai normal permukaan kubah.
    const tegak = new THREE.Vector3(0, 0, 1);

    titik.forEach((p) => {
        const iris = new THREE.Mesh(new THREE.CircleGeometry(0.035, 12), material);

        // Agak ditarik ke luar dari permukaan supaya tidak tenggelam di
        // gangguan butiran nasi.
        const x = p.x + p.nx * 0.006;
        const y = p.y + p.ny * 0.006;
        const z = p.z + p.nz * 0.006;
        iris.position.set(x, y, z);

        iris.quaternion.setFromUnitVectors(
            tegak,
            new THREE.Vector3(p.nx, p.ny, p.nz).normalize()
        );
        iris.castShadow = true;
        target.add(iris);
    });

    target.position.copy(posisiNasi);
    induk.add(target);
}

function taburSesame(THREE, induk, posisiNasi, bentuk) {
    const target = new THREE.Group();
    const titik = sebarDiKubah(14, bentuk.radius, bentuk.lebar, bentuk.tinggi, 1.9);

    const material = new THREE.MeshPhysicalMaterial({
        color: PALET.sesame,
        roughness: 0.72,
        envMapIntensity: 0.4,
    });

    titik.forEach((p, i) => {
        const benih = new THREE.Mesh(new THREE.SphereGeometry(0.017, 8, 6), material);
        benih.scale.set(1, 0.55, 1.7);

        // Tarik ke luar permukaan dengan jarak yang bervariasi supaya sebagian
        // tenggelam seperti sesame yang benar-benar menempel di nasi.
        const jarak = 0.004 + (i % 3) * 0.004;
        benih.position.set(
            p.x + p.nx * jarak,
            p.y + p.ny * jarak,
            p.z + p.nz * jarak
        );

        //.small kecil, tapi orientasi acak supaya tidak terlihat seragam.
        benih.rotation.y = i * 1.1;
        benih.rotation.x = 0.2;
        benih.castShadow = true;
        target.add(benih);
    });

    target.position.copy(posisiNasi);
    induk.add(target);
}


function bangunAdeganNasi(THREE, RoomEnvironment, el) {
    const wadah = el.closest('.nasi-sambel-3d-wrap');

    const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    renderer.outputColorSpace = THREE.SRGBColorSpace;
    // ACES membuat rentang warna lebih sinematik dan tidak mudah burn out
    // saat pantulan environment mengenai keramik mengkilap.
    renderer.toneMapping = THREE.ACESFilmicToneMapping;
    renderer.toneMappingExposure = 1.05;
    renderer.shadowMap.enabled = true;
    renderer.shadowMap.type = THREE.PCFSoftShadowMap;
    el.appendChild(renderer.domElement);

    // Image Based Lighting: pantulan studio dari adegan RoomEnvironment.
    // RoomEnvironment hanya beberapa ribu byte dan menghasilkan
    // pantulan yang jauh lebih meyakinkan daripada lampu polos.
    const pmrem = new THREE.PMREMGenerator(renderer);
    const environment = pmrem.fromScene(new RoomEnvironment(), 0.04);
    pmrem.dispose();

    const tekstur = buatTeksturMakanan(THREE, renderer);

    const { scene, grupMiring, piringGroup } = buildNasiScene(THREE, tekstur);
    scene.environment = environment.texture;
    scene.environmentIntensity = 0.75;

    const camera = new THREE.PerspectiveCamera(34, 1, 0.1, 40);
    camera.position.set(KAMERA.posisi[0], KAMERA.posisi[1], KAMERA.posisi[2]);
    camera.lookAt(KAMERA.fokus[0], KAMERA.fokus[1], KAMERA.fokus[2]);

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

    let pengamatUkuran;
    if ('ResizeObserver' in window) {
        pengamatUkuran = new ResizeObserver(sesuaikanUkuran);
        pengamatUkuran.observe(el);
    } else {
        window.addEventListener('resize', sesuaikanUkuran);
    }

    // Sudut miring mengikuti posisi kursor di atas elemen.
    const target = { x: 0, y: 0 };
    const sekarang = { x: 0, y: 0 };

    const gerakKursor = (e) => {
        const kotak = el.getBoundingClientRect();
        if (!kotak.width || !kotak.height) return;

        const x = (e.clientX - kotak.left) / kotak.width - 0.5;
        const y = (e.clientY - kotak.top) / kotak.height - 0.5;

        target.y = x * MAKSIMUM_MIRING * 2;
        target.x = -y * MAKSIMUM_MIRING;
    };

    el.addEventListener('pointermove', gerakKursor, { passive: true });
    el.addEventListener('pointerleave', () => {
        target.x = 0;
        target.y = 0;
    });

    const kurangiGerak = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // Animasi masuk dihitung dari delta waktu asli, jadi tetap benar di layar
    // 60 Hz maupun 144 Hz, dan tidak melompat setelah tab sempat tidak aktif.
    let waktuMasuk = 0;
    let ayunanMasuk = 0;
    let waktuSebelum = null;
    let berhenti = false;

    const animasiMasuk = (dt) => {
        if (waktuMasuk >= DURASI_MASUK) return;

        waktuMasuk = Math.min(DURASI_MASUK, waktuMasuk + dt);
        // easeOutCubic: mulai cepat lalu melambat halus.
        const t = waktuMasuk / DURASI_MASUK;
        const e = 1 - Math.pow(1 - t, 3);

        piringGroup.scale.setScalar(0.82 + 0.18 * e);
        grupMiring.position.y = (1 - e) * 0.12;
        // Disimpan terpisah, bukan ditumpuk di grupMiring.rotation.y, karena
        // nilai itu tiap frame ditimpa oleh miring kursor.
        ayunanMasuk = (1 - e) * 0.5;

        if (waktuMasuk >= DURASI_MASUK) {
            piringGroup.scale.setScalar(1);
            grupMiring.position.y = 0;
            ayunanMasuk = 0;
        }
    };

    const animate = (waktu) => {
        if (berhenti) return;

        const dt = waktuSebelum === null ? 0.016 : Math.min(0.05, (waktu - waktuSebelum) / 1000);
        waktuSebelum = waktu;

        // Gradual menuju sudut target supaya gerakannya halus, bukan kaku.
        sekarang.x += (target.x - sekarang.x) * 0.08;
        sekarang.y += (target.y - sekarang.y) * 0.08;

        animasiMasuk(dt);

        if (!kurangiGerak) {
            // Putaran otomatis berada pada grup dalam, terpisah dari kemiringan
            // pada grup luar, jadi keduanya tidak saling menimpa.
            piringGroup.rotation.y += KECEPATAN_PUTAR;
        }

        grupMiring.rotation.x = sekarang.x;
        grupMiring.rotation.y = sekarang.y + ayunanMasuk;

        renderer.render(scene, camera);
        requestAnimationFrame(animate);
    };

    // Kalau pengguna meminta pengurangan gerak, lewati animasi masuk dan
    // tampilkan piring langsung pada posisi akhir.
    if (kurangiGerak) {
        piringGroup.scale.setScalar(1);
        grupMiring.position.y = 0;
        animasiMasuk(DURASI_MASUK);
    }

    requestAnimationFrame(animate);

    el.dataset.nasiSambel = 'siap';
    if (wadah) wadah.dataset.nasiSambel = 'siap';

    // Berhenti animasi saat elemen ini tidak terlihat, supaya tidak membebani
    // memori dan processor ketika pengguna sedang scroll ke bagian lain.
    let pengamatTampil;
    if ('IntersectionObserver' in window) {
        pengamatTampil = new IntersectionObserver(
            ([entry]) => {
                berhenti = !entry.isIntersecting;
                // Reset pencatat waktu supaya tidak ada lompatan setelah
                // lama tidak tampil.
                waktuSebelum = null;
                if (!berhenti) requestAnimationFrame(animate);
            },
            { threshold: 0.05 }
        );
        pengamatTampil.observe(el);
    }

    // Hentikan total dan lepas semua sumber daya saat halaman ditinggalkan,
    // supaya tidak tetap berjalan di cache browser.
    window.addEventListener('pagehide', () => {
        berhenti = true;

        if (pengamatUkuran) pengamatUkuran.disconnect();
        if (pengamatTampil) pengamatTampil.disconnect();

        scene.traverse((obj) => {
            if (obj.isMesh) {
                obj.geometry.dispose();
                if (Array.isArray(obj.material)) obj.material.forEach((m) => m.dispose());
                else obj.material.dispose();
            }
        });

        Object.values(tekstur).forEach((t) => t && t.dispose && t.dispose());
        environment.texture.dispose();
        renderer.dispose();
    }, { once: true });
}

/**
 * Buat tekstur makanan di atas canvas, lalu jadi peta warna.
 *
 * Semua digambar saat runtime, jadi tidak ada berkas gambar yang perlu
 * diunduh. Ukuran 512 dipakai karena butiran nasi masih terbaca pada layar
 * sedangkan ukuran yang lebih besar hanya menambah memori tanpa pengaruh
 * yang berarti.
 */
function buatTeksturMakanan(THREE, renderer) {
    const UKURAN = 512;
    const kanvas = document.createElement('canvas');
    kanvas.width = UKURAN;
    kanvas.height = UKURAN;
    const g = kanvas.getContext('2d');

    // Butiran nasi: hundreds butir lonjong dengan sedikit variasi warna.
    g.fillStyle = '#f6efe0';
    g.fillRect(0, 0, UKURAN, UKURAN);

    let benih = 20260911;
    const acak = () => {
        benih = (benih * 1664525 + 1013904223) % 4294967296;
        return benih / 4294967296;
    };

    for (let i = 0; i < 2200; i++) {
        const x = acak() * UKURAN;
        const y = acak() * UKURAN;
        const panjang = 9 + acak() * 5;
        const lebar = 4.4 + acak() * 1.6;
        const sudut = acak() * Math.PI;

        g.save();
        g.translate(x, y);
        g.rotate(sudut);
        g.beginPath();
        g.ellipse(0, 0, panjang / 2, lebar / 2, 0, 0, Math.PI * 2);

        const gelap = acak() < 0.5;
        g.fillStyle = gelap ? 'rgba(214,200,176,0.85)' : 'rgba(255,253,246,0.9)';
        g.fill();

        // Garis tipis di tengah butir supaya tidak terlihat seperti titik.
        g.strokeStyle = 'rgba(206,190,163,0.45)';
        g.lineWidth = 0.8;
        g.stroke();
        g.restore();
    }

    const nasi = new THREE.CanvasTexture(kanvas);
    nasi.colorSpace = THREE.SRGBColorSpace;
    nasi.anisotropy = renderer.capabilities.getMaxAnisotropy();

    // Sambal: dasar merah pekat dengan bintik lebih gelap.
    const kSambal = document.createElement('canvas');
    kSambal.width = UKURAN;
    kSambal.height = UKURAN;
    const gs = kSambal.getContext('2d');

    gs.fillStyle = '#7e0f08';
    gs.fillRect(0, 0, UKURAN, UKURAN);

    for (let i = 0; i < 900; i++) {
        const x = acak() * UKURAN;
        const y = acak() * UKURAN;
        const r = 3 + acak() * 11;
        gs.beginPath();
        gs.arc(x, y, r, 0, Math.PI * 2);
        gs.fillStyle = acak() < 0.45 ? 'rgba(58,4,3,0.55)' : 'rgba(174,34,20,0.5)';
        gs.fill();
    }

    const sambal = new THREE.CanvasTexture(kSambal);
    sambal.colorSpace = THREE.SRGBColorSpace;
    sambal.anisotropy = nasi.anisotropy;

    // Piring: gradien radial halus supaya bidang putihnya tidak terlihat
    // datar saat diputar.
    const kPiring = document.createElement('canvas');
    kPiring.width = UKURAN;
    kPiring.height = UKURAN;
    const gp = kPiring.getContext('2d');

    const gradien = gp.createRadialGradient(UKURAN / 2, UKURAN / 2, UKURAN * 0.05, UKURAN / 2, UKURAN / 2, UKURAN * 0.62);
    gradien.addColorStop(0, '#fdfbf6');
    gradien.addColorStop(0.7, '#f4eee3');
    gradien.addColorStop(1, '#e6dccb');
    gp.fillStyle = gradien;
    gp.fillRect(0, 0, UKURAN, UKURAN);

    const piring = new THREE.CanvasTexture(kPiring);
    piring.colorSpace = THREE.SRGBColorSpace;
    piring.anisotropy = nasi.anisotropy;

    return { nasi, sambal, piring };
}