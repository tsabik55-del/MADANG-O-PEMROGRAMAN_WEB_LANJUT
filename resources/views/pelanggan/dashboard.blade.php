<x-pelanggan-layout title="Menu & Pesan">
    <div x-data="dashboardPelanggan(@js($menuData), @js($kategoriData), @js($userData))"
         x-init="init()"
         class="mx-auto w-full max-w-7xl">

        {{-- ================= TOPBAR ================= --}}
        <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
            <div class="flex min-w-0 flex-1 items-center gap-2">
                <div class="relative w-full max-w-xs">
                    <input type="text" x-model="pencarian" placeholder="Cari nasi, lele, tumpeng…"
                           class="w-full rounded-xl border border-gray-200 bg-white py-2 pl-9 pr-3 text-xs shadow-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-300">
                    <span class="absolute left-3 top-2 text-xs text-gray-400">🔍</span>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <div class="flex items-center gap-1 rounded-xl border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-700 shadow-sm">
                    <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-emerald-500"></span>
                    <span>Pemesanan Aktif</span>
                </div>

                <div class="flex items-center gap-1.5 rounded-xl border border-gray-200 bg-white px-3 py-1 font-mono text-xs text-gray-700 shadow-sm">
                    <span>📅</span>
                    <span x-text="jamRealtime" class="font-bold text-brand-800"></span>
                </div>

                <a :href="waLink" target="_blank" rel="noopener"
                   class="flex items-center gap-1 rounded-xl border border-gray-200 bg-white px-3 py-1.5 text-xs font-bold text-gray-700 shadow-sm transition hover:bg-gray-50">
                    <span>💬</span>
                    <span class="hidden sm:inline">Chat Kasir</span>
                </a>

                <button type="button" @click="bukaKeranjang()"
                        class="flex items-center gap-1.5 rounded-xl border border-red-200 bg-red-100 px-3 py-1.5 text-xs font-bold text-brand-800 shadow-sm transition hover:bg-red-200">
                    <span>🛒</span>
                    <span>Pesanan</span>
                    <span class="rounded-full bg-brand-800 px-1.5 py-0.5 text-[10px] font-black text-white" x-text="totalItem">0</span>
                </button>
            </div>
        </div>

        {{-- ================= BANNER BIODATA ================= --}}
        <template x-if="!biodataLengkap">
            <div class="mb-5 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-amber-300 bg-amber-50 p-3 shadow-sm">
                <div class="flex items-start gap-2.5">
                    <span class="text-base">⚠️</span>
                    <div>
                        <h4 class="text-xs font-bold text-amber-950">Biodata Anda belum lengkap!</h4>
                        <p class="text-[11px] text-amber-800">Lengkapi nama, nomor WhatsApp, dan alamat agar pesanan bisa diproses.</p>
                    </div>
                </div>
                <button type="button" @click="bukaBiodata()"
                        class="whitespace-nowrap rounded-lg bg-amber-500 px-3 py-1.5 text-xs font-bold text-amber-950 shadow transition hover:bg-amber-600">
                    Lengkapi Biodata
                </button>
            </div>
        </template>

        {{-- ================= KATEGORI ================= --}}
        <div class="mb-5">
            <span class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-gray-400">Pilih kategori menu</span>
            <div class="flex flex-wrap gap-1.5">
                <button type="button" @click="kategoriAktif = 'semua'"
                        :class="kategoriAktif === 'semua' ? 'bg-brand-800 text-white font-bold shadow-sm' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-100'"
                        class="flex items-center gap-1 rounded-xl px-3 py-1.5 text-[11px] transition">
                    <span>🍱</span><span>Semua Menu</span>
                </button>

                <template x-for="kat in kategori" :key="kat.id">
                    <button type="button" @click="kategoriAktif = kat.id"
                            :class="kategoriAktif === kat.id ? 'bg-brand-800 text-white font-bold shadow-sm' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-100'"
                            class="flex items-center gap-1 rounded-xl px-3 py-1.5 text-[11px] transition">
                        <span x-text="kat.ikon"></span>
                        <span x-text="kat.nama"></span>
                    </button>
                </template>
            </div>
        </div>

        {{-- ================= GRID MENU ================= --}}
        <div>
            <div class="mb-3 flex items-center justify-between">
                <h3 class="text-sm font-black text-gray-800" x-text="judulKategori"></h3>
                <span class="text-[11px] text-gray-400" x-text="menuTersaring.length + ' item tersedia'"></span>
            </div>

            <div class="grid grid-cols-1 gap-3.5 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4">
                <template x-for="item in menuTersaring" :key="item.id">
                    <div class="flex flex-col justify-between overflow-hidden rounded-2xl border border-gray-200/80 bg-white shadow-sm transition hover:shadow-md">
                        <div class="group relative cursor-pointer" @click="bukaDetail(item)">
                            <template x-if="item.foto">
                                <img :src="item.foto" :alt="item.nama" class="h-28 w-full object-cover">
                            </template>
                            <template x-if="!item.foto">
                                <div class="grid h-28 w-full place-items-center bg-gray-100 text-4xl">🍚</div>
                            </template>
                            <div class="absolute inset-0 flex items-center justify-center bg-black/20 text-[10px] font-semibold text-white opacity-0 transition group-hover:opacity-100">
                                Lihat Deskripsi
                            </div>
                        </div>

                        <div class="flex flex-1 flex-col justify-between p-3">
                            <div>
                                <h4 class="text-xs font-bold leading-snug text-gray-800" x-text="item.nama"></h4>
                                <p class="mt-0.5 line-clamp-2 text-[10px] text-gray-500" x-text="item.deskripsi"></p>
                            </div>

                            <div class="mt-2.5 flex items-center justify-between border-t border-gray-100 pt-2">
                                <div>
                                    <span class="block text-[9px] text-gray-400">Harga</span>
                                    <span class="text-xs font-black text-brand-800" x-text="formatRupiah(item.harga)"></span>
                                </div>
                                <button type="button" @click.stop="pilihOpsiDanTambah(item)"
                                        class="grid h-7 w-7 place-items-center rounded-full bg-brand-800 text-sm font-bold text-white shadow transition hover:bg-brand-700"
                                        :aria-label="'Tambah ' + item.nama">
                                    +
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <template x-if="menuTersaring.length === 0">
                <div class="rounded-2xl border border-dashed border-gray-300 bg-white/60 py-12 text-center">
                    <p class="text-sm font-bold text-gray-500">Menu tidak ditemukan.</p>
                    <p class="mt-1 text-[11px] text-gray-400">Coba kata kunci atau kategori lain.</p>
                </div>
            </template>
        </div>

        {{-- ================= MODAL DETAIL MENU ================= --}}
        <template x-if="modalDetail">
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4" @keydown.escape.window="modalDetail = false">
                <div class="w-full max-w-sm overflow-hidden rounded-2xl bg-white shadow-2xl">
                    <template x-if="detailItem.foto">
                        <img :src="detailItem.foto" :alt="detailItem.nama" class="h-36 w-full object-cover">
                    </template>
                    <template x-if="!detailItem.foto">
                        <div class="grid h-36 w-full place-items-center bg-gray-100 text-6xl">🍚</div>
                    </template>
                    <div class="p-4">
                        <h3 class="text-sm font-black text-gray-800" x-text="detailItem.nama"></h3>
                        <p class="mt-0.5 text-xs font-bold text-brand-800" x-text="formatRupiah(detailItem.harga)"></p>
                        <div class="mt-2.5 rounded-xl border border-amber-200 bg-amber-50 p-3 text-xs leading-relaxed text-gray-700">
                            <strong class="mb-0.5 block text-gray-800">Deskripsi:</strong>
                            <p x-text="detailItem.deskripsi || 'Tidak ada deskripsi.'"></p>
                        </div>
                        <div class="mt-3 flex justify-end gap-2">
                            <button type="button" @click="modalDetail = false"
                                    class="rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-bold text-gray-700">Tutup</button>
                            <button type="button" @click="modalDetail = false; pilihOpsiDanTambah(detailItem)"
                                    class="rounded-lg bg-brand-800 px-3 py-1.5 text-xs font-bold text-white shadow">Pesan Sekarang</button>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        {{-- ================= MODAL KUSTOMISASI ================= --}}
        <template x-if="modalOpsi">
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4">
                <div class="w-full max-w-sm space-y-3 rounded-2xl bg-white p-5 shadow-2xl">
                    <h3 class="text-xs font-black text-gray-800" x-text="'Kustomisasi: ' + itemDipilih.nama"></h3>

                    <div>
                        <label class="mb-1 block text-[11px] font-bold text-gray-700" x-text="itemDipilih.opsi?.label || 'Pilih varian'"></label>
                        <select x-model="opsiKustom[itemDipilih.id]"
                                class="w-full rounded-xl border-gray-300 bg-gray-50 p-2 text-xs focus:border-amber-400 focus:ring-amber-300">
                            <template x-for="ch in (itemDipilih.opsi?.choices || [])" :key="ch.key">
                                <option :value="ch.key" x-text="ch.label + (ch.delta > 0 ? ' (+' + formatRupiah(ch.delta) + ')' : '')"></option>
                            </template>
                        </select>
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="modalOpsi = false"
                                class="rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-bold text-gray-700">Batal</button>
                        <button type="button" @click="konfirmasiTambahKeranjang()"
                                class="rounded-lg bg-brand-800 px-3 py-1.5 text-xs font-bold text-white shadow">Masukkan Keranjang</button>
                    </div>
                </div>
            </div>
        </template>

        {{-- ================= MODAL KERANJANG & CHECKOUT ================= --}}
        <template x-if="modalKeranjang">
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4">
                <div class="max-h-[85vh] w-full max-w-md overflow-y-auto rounded-2xl bg-white p-5 shadow-2xl">
                    <div class="flex items-center justify-between border-b pb-2">
                        <h3 class="text-xs font-black text-gray-800">Keranjang Pesanan Anda</h3>
                        <button type="button" @click="modalKeranjang = false" class="font-bold text-gray-400 hover:text-gray-600">✕</button>
                    </div>

                    <template x-if="keranjang.length === 0">
                        <div class="py-8 text-center">
                            <p class="text-xs font-bold text-gray-500">Keranjang masih kosong.</p>
                            <p class="mt-1 text-[11px] text-gray-400">Pilih menu dulu ya.</p>
                        </div>
                    </template>

                    <template x-if="keranjang.length > 0">
                        <div class="my-3 space-y-3">
                            <template x-for="(k, idx) in keranjang" :key="k.uid">
                                <div class="flex items-center justify-between rounded-xl border border-gray-200 bg-gray-50 p-2.5">
                                    <div class="min-w-0">
                                        <h5 class="truncate text-xs font-bold text-gray-800" x-text="k.nama"></h5>
                                        <p class="text-[10px] text-gray-500" x-text="k.opsiLabel || 'Standar'"></p>
                                        <span class="text-xs font-bold text-brand-800" x-text="formatRupiah(k.harga * k.jumlah)"></span>
                                    </div>
                                    <div class="flex shrink-0 items-center gap-1.5">
                                        <button type="button" @click="ubahJumlah(idx, -1)"
                                                class="grid h-6 w-6 place-items-center rounded-full bg-gray-200 text-xs font-bold text-gray-700 hover:bg-gray-300">−</button>
                                        <span class="w-5 text-center text-xs font-bold" x-text="k.jumlah"></span>
                                        <button type="button" @click="ubahJumlah(idx, 1)"
                                                class="grid h-6 w-6 place-items-center rounded-full bg-brand-800 text-xs font-bold text-white hover:bg-brand-700">+</button>
                                    </div>
                                </div>
                            </template>

                            <div class="space-y-2 border-t pt-3">
                                <template x-if="!biodataLengkap">
                                    <button type="button" @click="modalKeranjang = false; bukaBiodata()"
                                            class="w-full rounded-xl border border-amber-300 bg-amber-50 px-3 py-2 text-left text-[11px] font-bold text-amber-900">
                                        ⚠️ Lengkapi biodata dulu (nama, WA, alamat) →
                                    </button>
                                </template>

                                <div>
                                    <label class="mb-0.5 block text-[11px] font-bold text-gray-700">📅 Tanggal ambil / kirim</label>
                                    <input type="date" x-model="checkout.tanggal" :min="tanggalMin"
                                           class="w-full rounded-xl border border-gray-300 bg-gray-50 p-2 text-xs">
                                </div>

                                <div>
                                    <label class="mb-0.5 block text-[11px] font-bold text-gray-700">⏰ Jam ambil / kirim</label>
                                    <input type="time" x-model="checkout.jam"
                                           class="w-full rounded-xl border border-gray-300 bg-gray-50 p-2 text-xs">
                                </div>

                                <div>
                                    <label class="mb-0.5 block text-[11px] font-bold text-gray-700">💳 Metode pembayaran</label>
                                    <select x-model="checkout.pembayaran"
                                            class="w-full rounded-xl border border-gray-300 bg-gray-50 p-2 text-xs">
                                        <option value="tunai">Tunai / Bayar di Kasir (COD)</option>
                                        <option value="qris">QRIS (semua bank / e-wallet)</option>
                                        <option value="transfer">Transfer Bank</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="mb-0.5 block text-[11px] font-bold text-gray-700">📝 Catatan (opsional)</label>
                                    <textarea x-model="checkout.catatan" rows="2" placeholder="Mis. sambal dipisah"
                                              class="w-full rounded-xl border border-gray-300 bg-gray-50 p-2 text-xs"></textarea>
                                </div>

                                <div class="flex items-center justify-between rounded-xl bg-brand-800 p-2.5 text-xs font-bold text-white">
                                    <span>Total Pembayaran</span>
                                    <span x-text="formatRupiah(subtotal)"></span>
                                </div>

                                <p x-show="pesanGalat" x-cloak class="rounded-lg bg-red-50 px-3 py-2 text-[11px] font-semibold text-red-700" x-text="pesanGalat"></p>

                                <button type="button" @click="prosesCheckout()" :disabled="memproses"
                                        class="w-full rounded-xl bg-amber-500 py-2.5 text-xs font-black text-amber-950 shadow transition hover:bg-amber-600 disabled:opacity-60">
                                    <span x-show="!memproses">KONFIRMASI PESANAN SEKARANG</span>
                                    <span x-show="memproses" x-cloak>Memproses…</span>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </template>

        {{-- ================= MODAL QRIS ================= --}}
        <template x-if="modalQRIS">
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4">
                <div class="w-full max-w-xs space-y-3 rounded-2xl bg-white p-5 text-center shadow-2xl">
                    <h3 class="text-sm font-black text-gray-800">Pembayaran QRIS</h3>

                    <div class="rounded-xl border border-amber-200 bg-amber-50 p-3">
                        <div class="mx-auto h-40 w-40 overflow-hidden rounded-lg border bg-white [&>svg]:h-full [&>svg]:w-full"
                             x-html="qrSvg || '<div class=&quot;grid h-full place-items-center text-xs text-gray-400&quot;>Memuat QR…</div>'"></div>
                        <p class="mt-1 text-[9px] text-gray-400">Scan dengan m-banking / e-wallet</p>
                    </div>

                    <div>
                        <span class="block text-[10px] text-gray-400">Total Pembayaran</span>
                        <span class="text-base font-black text-brand-800" x-text="formatRupiah(pesananTerakhir.total || 0)"></span>
                    </div>

                    <p class="text-[10px] text-gray-500">Nomor pesanan <span class="font-bold" x-text="pesananTerakhir.nomor"></span></p>

                    <button type="button" @click="konfirmasiBayar()" :disabled="memproses"
                            class="w-full rounded-xl bg-brand-800 py-2.5 text-xs font-bold text-white shadow transition hover:bg-brand-700 disabled:opacity-60">
                        Konfirmasi Sudah Bayar
                    </button>
                </div>
            </div>
        </template>

        {{-- ================= MODAL STATUS PESANAN ================= --}}
        <template x-if="modalStatus">
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4">
                <div class="w-full max-w-sm space-y-3 rounded-2xl bg-white p-5 text-center shadow-2xl">
                    <div class="mx-auto grid h-12 w-12 place-items-center rounded-full bg-amber-100 text-2xl text-amber-800 shadow-inner">🍳</div>
                    <h3 class="text-base font-black text-gray-800" x-text="pesanStatusJudul"></h3>
                    <p class="text-xs text-gray-500" x-text="pesanStatusIsi"></p>
                    <p class="text-[11px] font-bold text-brand-800" x-text="'No. pesanan: ' + (pesananTerakhir.nomor || '-')"></p>

                    <a :href="pesananTerakhir.detail_url || '#'"
                       class="block w-full rounded-xl bg-gray-900 py-2.5 text-xs font-bold text-white shadow">
                        Lihat Detail Pesanan
                    </a>
                    <button type="button" @click="modalStatus = false"
                            class="w-full rounded-xl bg-gray-100 py-2 text-xs font-bold text-gray-700">Kembali ke Menu</button>
                </div>
            </div>
        </template>

        {{-- ================= MODAL RIWAYAT ================= --}}
        <template x-if="modalRiwayat">
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4">
                <div class="max-h-[85vh] w-full max-w-md overflow-y-auto rounded-2xl bg-white p-5 shadow-2xl">
                    <div class="flex items-center justify-between border-b pb-2">
                        <h3 class="text-xs font-black text-gray-800">Riwayat Pesanan Anda</h3>
                        <button type="button" @click="modalRiwayat = false" class="font-bold text-gray-400 hover:text-gray-600">✕</button>
                    </div>

                    <div x-show="memuatRiwayat" x-cloak class="py-8 text-center text-xs text-gray-400">Memuat riwayat…</div>

                    <template x-if="!memuatRiwayat && riwayat.length === 0">
                        <div class="py-8 text-center">
                            <p class="text-xs font-bold text-gray-500">Belum ada pesanan.</p>
                        </div>
                    </template>

                    <div class="my-3 space-y-3">
                        <template x-for="r in riwayat" :key="r.id">
                            <div class="space-y-1.5 rounded-xl border border-amber-200 bg-amber-50/60 p-3">
                                <div class="flex items-center justify-between border-b border-amber-200 pb-1">
                                    <span class="text-xs font-bold text-gray-700" x-text="'#' + r.nomor"></span>
                                    <span class="rounded-full bg-amber-200 px-2 py-0.5 text-[9px] font-bold text-amber-900" x-text="labelStatus(r.status)"></span>
                                </div>
                                <div class="text-[10px] text-gray-600">
                                    <p x-text="'Ambil: ' + (r.tanggal || '-') + ' ' + (r.jam || '')"></p>
                                    <p x-text="'Metode: ' + labelBayar(r.metode)"></p>
                                    <p class="mt-0.5 font-bold text-brand-800" x-text="'Total: ' + formatRupiah(r.total)"></p>
                                </div>
                                <template x-if="r.items && r.items.length">
                                    <ul class="mt-1 space-y-0.5 border-t border-amber-200 pt-1 text-[10px] text-gray-600">
                                        <template x-for="(it, i) in r.items" :key="i">
                                            <li class="flex justify-between gap-2">
                                                <span class="truncate">
                                                    <span x-text="it.jumlah + '× ' + it.nama"></span>
                                                    <span class="text-gray-400" x-text="it.opsi ? ' — ' + it.opsi : ''"></span>
                                                </span>
                                                <span class="shrink-0" x-text="formatRupiah(it.subtotal)"></span>
                                            </li>
                                        </template>
                                    </ul>
                                </template>
                                <a :href="r.detail_url" class="inline-block text-[10px] font-bold text-brand-800 underline">Lihat detail →</a>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </template>

        {{-- ================= MODAL BIODATA ================= --}}
        <template x-if="modalBiodata">
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4">
                <div class="w-full max-w-sm space-y-2.5 rounded-2xl bg-white p-5 shadow-2xl">
                    <h3 class="text-xs font-black text-gray-800">Lengkapi Biodata Anda</h3>

                    <div>
                        <label class="mb-0.5 block text-[10px] font-bold text-gray-700">Nama lengkap</label>
                        <input type="text" x-model="biodata.name" class="w-full rounded-xl border-gray-300 p-1.5 text-xs">
                    </div>
                    <div>
                        <label class="mb-0.5 block text-[10px] font-bold text-gray-700">No. WhatsApp</label>
                        <input type="tel" x-model="biodata.wa" placeholder="08xxxxxxxxxx" class="w-full rounded-xl border-gray-300 p-1.5 text-xs">
                    </div>
                    <div>
                        <label class="mb-0.5 block text-[10px] font-bold text-gray-700">Alamat pengiriman</label>
                        <textarea x-model="biodata.alamat" placeholder="Jalan, RT/RW, kelurahan…" class="h-16 w-full rounded-xl border-gray-300 p-1.5 text-xs"></textarea>
                    </div>

                    <p x-show="galatBiodata" x-cloak class="rounded-lg bg-red-50 px-3 py-2 text-[11px] font-semibold text-red-700" x-text="galatBiodata"></p>

                    <div class="flex justify-end gap-1.5 pt-1">
                        <button type="button" @click="modalBiodata = false"
                                class="rounded-lg bg-gray-100 px-3 py-1 text-xs font-bold text-gray-700">Batal</button>
                        <button type="button" @click="simpanBiodata()" :disabled="memproses"
                                class="rounded-lg bg-brand-800 px-3 py-1 text-xs font-bold text-white shadow disabled:opacity-60">Simpan</button>
                    </div>
                </div>
            </div>
        </template>
    </div>

    @push('scripts')
    <script>
        window.MADANGO_CART_KEY = 'madango_cart_{{ Auth::id() }}';

        window.MADANGO_URLS = {
            store: '{{ route('pelanggan.orders.store') }}',
            data: '{{ route('pelanggan.orders.data') }}',
            profile: '{{ route('pelanggan.profile.update') }}',
            qris: '{{ route('pelanggan.orders.qris', ['order' => '__ID__']) }}',
            bayar: '{{ route('pelanggan.orders.bayar', ['order' => '__ID__']) }}',
            whatsapp: '{{ config('madang.whatsapp') }}',
        };

        function dashboardPelanggan(menuAwal, kategoriAwal, userAwal) {
            return {
                menu: menuAwal || [],
                kategori: kategoriAwal || [],
                user: userAwal || { name: '', email: '', wa: '', alamat: '' },

                kategoriAktif: 'semua',
                pencarian: '',
                jamRealtime: '',

                modalDetail: false,
                modalOpsi: false,
                modalKeranjang: false,
                modalRiwayat: false,
                modalBiodata: false,
                modalQRIS: false,
                modalStatus: false,

                detailItem: {},
                itemDipilih: {},
                opsiKustom: {},

                keranjang: [],
                riwayat: [],
                memuatRiwayat: false,

                checkout: { tanggal: '', jam: '', pembayaran: 'tunai', catatan: '' },
                biodata: { name: '', wa: '', alamat: '' },

                pesananTerakhir: {},
                qrSvg: '',
                pesanGalat: '',
                galatBiodata: '',
                memproses: false,
                pesanStatusJudul: '',
                pesanStatusIsi: '',

                init() {
                    this.muatJam();
                    setInterval(() => this.muatJam(), 1000);

                    this.biodata = { name: this.user.name, wa: this.user.wa, alamat: this.user.alamat };

                    // Tanggal default hari ini, jam default satu jam ke depan.
                    const sekarang = new Date();
                    const lebih = new Date(sekarang.getTime() + 60 * 60 * 1000);
                    this.checkout.tanggal = lebih.toISOString().slice(0, 10);
                    this.checkout.jam = String(lebih.getHours()).padStart(2, '0') + ':' + String(lebih.getMinutes()).padStart(2, '0');

                    this.muatKeranjang();
                    this.$watch('keranjang', () => this.simpanKeranjang());
                },

                // ---------- util ----------
                formatRupiah(angka) {
                    return new Intl.NumberFormat('id-ID', {
                        style: 'currency', currency: 'IDR', maximumFractionDigits: 0,
                    }).format(Number(angka) || 0);
                },

                muatJam() {
                    this.jamRealtime = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                },

                labelStatus(status) {
                    const peta = {
                        menunggu_pembayaran: 'Menunggu Pembayaran',
                        diproses: 'Diproses',
                        siap: 'Siap Diambil',
                        dikirim: 'Dikirim',
                        selesai: 'Selesai',
                        dibatalkan: 'Dibatalkan',
                    };
                    return peta[status] || status || '-';
                },

                labelBayar(metode) {
                    const peta = { tunai: 'Tunai', transfer: 'Transfer', qris: 'QRIS' };
                    return peta[metode] || metode || '-';
                },

                get waLink() {
                    const nomor = String(window.MADANGO_URLS.whatsapp || '').replace(/\D/g, '');
                    const teks = encodeURIComponent('Halo Kasir Sego Sambel Mbak Tatik, saya mau bertanya soal pesanan saya.');
                    return nomor ? ('https://wa.me/' + nomor + '?text=' + teks) : '#';
                },

                // ---------- filter ----------
                get menuTersaring() {
                    const q = this.pencarian.trim().toLowerCase();
                    return this.menu.filter((item) => {
                        const cocokKategori = this.kategoriAktif === 'semua' || item.kategori === this.kategoriAktif;
                        const cocokCari = q === ''
                            || String(item.nama).toLowerCase().includes(q)
                            || String(item.deskripsi).toLowerCase().includes(q);
                        return cocokKategori && cocokCari;
                    });
                },

                get judulKategori() {
                    if (this.kategoriAktif === 'semua') return 'Semua Menu';
                    const kat = this.kategori.find((k) => k.id === this.kategoriAktif);
                    return kat ? 'Kategori: ' + kat.nama : 'Daftar Menu';
                },

                get tanggalMin() {
                    return new Date().toISOString().slice(0, 10);
                },

                // ---------- keranjang ----------
                get totalItem() {
                    return this.keranjang.reduce((akum, k) => akum + k.jumlah, 0);
                },

                get subtotal() {
                    return this.keranjang.reduce((akum, k) => akum + k.harga * k.jumlah, 0);
                },

                get biodataLengkap() {
                    return Boolean(this.user.wa && this.user.alamat && this.user.name);
                },

                muatKeranjang() {
                    try {
                        const mentah = JSON.parse(localStorage.getItem(window.MADANGO_CART_KEY) || '[]');
                        if (!Array.isArray(mentah)) return;
                        this.keranjang = mentah.filter((k) => k && Number(k.id) && Number(k.jumlah) > 0).map((k) => ({
                            uid: String(k.id) + '|' + (k.opsiKey || ''),
                            id: Number(k.id),
                            nama: String(k.nama || 'Menu'),
                            harga: Number(k.harga) || 0,
                            jumlah: Number(k.jumlah),
                            opsiKey: k.opsiKey || null,
                            opsiLabel: k.opsiLabel || null,
                        }));
                    } catch (e) {
                        localStorage.removeItem(window.MADANGO_CART_KEY);
                        this.keranjang = [];
                    }
                },

                simpanKeranjang() {
                    try {
                        localStorage.setItem(window.MADANGO_CART_KEY, JSON.stringify(this.keranjang));
                    } catch (e) {
                        // localStorage penuh / mode privat — abaikan.
                    }
                },

                bukaDetail(item) {
                    this.detailItem = item;
                    this.modalDetail = true;
                },

                pilihOpsiDanTambah(item) {
                    this.pesanGalat = '';
                    if (item.opsi && Array.isArray(item.opsi.choices) && item.opsi.choices.length) {
                        this.itemDipilih = item;
                        if (!this.opsiKustom[item.id]) {
                            this.opsiKustom[item.id] = item.opsi.choices[0].key;
                        }
                        this.modalOpsi = true;
                    } else {
                        this.tambahKeKeranjang(item, null);
                    }
                },

                konfirmasiTambahKeranjang() {
                    const item = this.itemDipilih;
                    const key = this.opsiKustom[item.id];
                    const pilihan = (item.opsi?.choices || []).find((c) => c.key === key) || null;
                    this.modalOpsi = false;
                    this.tambahKeKeranjang(item, pilihan);
                },

                tambahKeKeranjang(item, pilihan) {
                    const uid = String(item.id) + '|' + (pilihan ? pilihan.key : '');
                    const harga = Number(item.harga) + (pilihan ? Number(pilihan.delta || 0) : 0);
                    const ada = this.keranjang.find((k) => k.uid === uid);

                    if (ada) {
                        ada.jumlah += 1;
                    } else {
                        this.keranjang.push({
                            uid: uid,
                            id: item.id,
                            nama: item.nama,
                            harga: harga,
                            jumlah: 1,
                            opsiKey: pilihan ? pilihan.key : null,
                            opsiLabel: pilihan ? pilihan.label : null,
                        });
                    }

                    this.simpanKeranjang();
                    this.bukaKeranjang();
                },

                ubahJumlah(idx, delta) {
                    const baris = this.keranjang[idx];
                    if (!baris) return;
                    baris.jumlah += delta;
                    if (baris.jumlah <= 0) {
                        this.keranjang.splice(idx, 1);
                    }
                },

                bukaKeranjang() {
                    this.pesanGalat = '';
                    this.modalKeranjang = true;
                },

                bukaBiodata() {
                    this.galatBiodata = '';
                    this.biodata = { name: this.user.name, wa: this.user.wa, alamat: this.user.alamat };
                    this.modalBiodata = true;
                },

                // ---------- checkout ----------
                async prosesCheckout() {
                    this.pesanGalat = '';

                    if (this.keranjang.length === 0) {
                        this.pesanGalat = 'Keranjang masih kosong.';
                        return;
                    }
                    if (!this.biodataLengkap) {
                        this.modalKeranjang = false;
                        this.bukaBiodata();
                        return;
                    }
                    if (!this.checkout.tanggal || !this.checkout.jam) {
                        this.pesanGalat = 'Silakan pilih tanggal dan jam terlebih dahulu.';
                        return;
                    }

                    const waktu = new Date(this.checkout.tanggal + 'T' + this.checkout.jam + ':00');
                    if (isNaN(waktu.getTime()) || waktu <= new Date()) {
                        this.pesanGalat = 'Waktu ambil harus di masa depan.';
                        return;
                    }

                    this.memproses = true;

                    try {
                        const isi = new FormData();
                        isi.append('customer_name', this.user.name);
                        isi.append('phone', this.user.wa);
                        isi.append('pickup_datetime', this.checkout.tanggal + 'T' + this.checkout.jam);
                        isi.append('payment_method', this.checkout.pembayaran);
                        isi.append('notes', [this.user.alamat, this.checkout.catatan].filter(Boolean).join(' | '));
                        isi.append('cart_data', JSON.stringify(this.keranjang.map((k) => ({
                            id: k.id, quantity: k.jumlah, option: k.opsiKey,
                        }))));

                        const res = await fetch(window.MADANGO_URLS.store, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: isi,
                        });

                        const data = await res.json().catch(() => ({}));

                        if (!res.ok) {
                            const galatValidasi = data.errors ? Object.values(data.errors)[0][0] : null;
                            this.pesanGalat = data.message || galatValidasi || 'Pesanan gagal dibuat.';
                            return;
                        }

                        this.pesananTerakhir = data.order || {};
                        this.keranjang = [];
                        this.simpanKeranjang();
                        this.modalKeranjang = false;

                        if (this.checkout.pembayaran === 'tunai') {
                            this.pesanStatusJudul = 'Pesanan Diterima!';
                            this.pesanStatusIsi = 'Silakan bayar di kasir saat mengambil pesanan.';
                            this.modalStatus = true;
                        } else {
                            await this.bukaQris();
                        }
                    } catch (e) {
                        this.pesanGalat = 'Tidak dapat menghubungi server. Periksa koneksi Anda.';
                    } finally {
                        this.memproses = false;
                    }
                },

                async bukaQris() {
                    this.qrSvg = '';
                    this.modalQRIS = true;

                    try {
                        const res = await fetch(window.MADANGO_URLS.qris.replace('__ID__', this.pesananTerakhir.id), {
                            headers: { 'Accept': 'application/json' },
                        });
                        const data = await res.json().catch(() => ({}));
                        if (res.ok) {
                            this.qrSvg = data.svg || '';
                        }
                    } catch (e) {
                        this.qrSvg = '';
                    }
                },

                async konfirmasiBayar() {
                    this.memproses = true;

                    try {
                        const res = await fetch(window.MADANGO_URLS.bayar.replace('__ID__', this.pesananTerakhir.id), {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                        });
                        const data = await res.json().catch(() => ({}));

                        this.modalQRIS = false;
                        this.pesanStatusJudul = 'Pembayaran Diterima';
                        this.pesanStatusIsi = data.message || 'Pembayaran akan diverifikasi kasir.';
                        this.modalStatus = true;
                    } catch (e) {
                        this.pesanGalat = 'Konfirmasi gagal. Coba lagi.';
                    } finally {
                        this.memproses = false;
                    }
                },

                // ---------- riwayat ----------
                async bukaRiwayat() {
                    this.modalRiwayat = true;
                    this.memuatRiwayat = true;

                    try {
                        const res = await fetch(window.MADANGO_URLS.data, { headers: { 'Accept': 'application/json' } });
                        const data = await res.json().catch(() => ({ orders: [] }));
                        this.riwayat = Array.isArray(data.orders) ? data.orders : [];
                    } catch (e) {
                        this.riwayat = [];
                    } finally {
                        this.memuatRiwayat = false;
                    }
                },

                // ---------- biodata ----------
                async simpanBiodata() {
                    this.galatBiodata = '';

                    if (!this.biodata.name || !this.biodata.wa || !this.biodata.alamat) {
                        this.galatBiodata = 'Mohon lengkapi semua kolom.';
                        return;
                    }

                    this.memproses = true;

                    try {
                        const isi = new FormData();
                        isi.append('name', this.biodata.name);
                        isi.append('wa_number', this.biodata.wa);
                        isi.append('address', this.biodata.alamat);
                        isi.append('_method', 'PATCH');

                        const res = await fetch(window.MADANGO_URLS.profile, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: isi,
                        });

                        const data = await res.json().catch(() => ({}));

                        if (!res.ok) {
                            const galatValidasi = data.errors ? Object.values(data.errors)[0][0] : null;
                            this.galatBiodata = data.message || galatValidasi || 'Biodata gagal disimpan.';
                            return;
                        }

                        if (data.user) {
                            this.user = data.user;
                        }
                        this.modalBiodata = false;
                    } catch (e) {
                        this.galatBiodata = 'Tidak dapat menghubungi server.';
                    } finally {
                        this.memproses = false;
                    }
                },
            };
        }
    </script>
    @endpush
</x-pelanggan-layout>
