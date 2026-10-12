import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

/**
 * Piring nasi sambel 3D di halaman depan.
 *
 * Modul ini diimpor secara dinamis supaya three.js (sekitar 190 KB gzip)
 * tidak ikut masuk ke bundle yang dimuat setiap halaman. Browser baru
 * mengunduhnya kalau halaman depan benar-benar memanggil fungsi di bawah.
 */
window.madangNasiSambel = function (el) {
    import('./hero-nasi.js')
        .then((mod) => mod.initNasiSambel(el))
        .catch(() => {
            // three.js gagal dimuat atau WebGL tidak tersedia.
            // Fallback di dalam halaman tetap menampilkannya.
        });
};

/**
 * Efek hover tabel: baris terangkat dan miring mengikuti posisi kursor.
 *
 * Didaftarkan satu kali untuk seluruh halaman, bukan di setiap view. Sudut
 * miring dikirim lewat variabel CSS --tilt pada elemen <tr>, lalu dibaca
 * oleh .madang-table tbody tr:hover > td di madang-theme.css.
 */
(function () {
    // Maksimal 3 derajat. Lebih besar dari itu teks tabel jadi sulit dibaca.
    const MAKSIMUM = 3;
    const KENDALI = 'select, input, textarea, button, a, label';

    let barisTerakhir = null;

    document.addEventListener(
        'pointermove',
        (e) => {
            const baris = e.target.closest?.('.madang-table tbody tr');

            if (!baris) {
                return;
            }

            // Kalau kursor berada di atas kontrol (mis. dropdown ubah status),
            // baris dibiarkan diam supaya kontrol tidak ikut bergerak.
            if (e.target.closest?.(KENDALI)) {
                baris.style.removeProperty('--tilt');
                return;
            }

            const kotak = baris.getBoundingClientRect();
            if (!kotak.width) return;

            const posisi = (e.clientX - kotak.left) / kotak.width;
            const sudut = (posisi - 0.5) * 2 * MAKSIMUM;

            baris.style.setProperty('--tilt', sudut.toFixed(2) + 'deg');
            barisTerakhir = baris;
        },
        { passive: true }
    );

    // Saat kursor keluar tabel, kembalikan semua baris ke sudut 0.
    document.addEventListener('pointerleave', () => {
        if (barisTerakhir) {
            barisTerakhir.style.removeProperty('--tilt');
            barisTerakhir = null;
        }
    });
})();