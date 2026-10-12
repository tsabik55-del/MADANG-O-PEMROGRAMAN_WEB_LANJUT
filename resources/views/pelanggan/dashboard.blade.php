<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Katalog Menu') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="madang-hero madang-stripes madang-stripes-animated rounded-2xl p-6 sm:p-8 mb-8 text-cream shadow-lg hero-animate">
                <p class="font-term text-xs tracking-[0.25em] text-cream/70">MADANG-O // KATALOG</p>
                <h2 class="text-2xl sm:text-3xl font-extrabold mt-1">Mau makan apa hari ini, {{ Auth::user()->name }}?</h2>
                <p class="text-cream/80 text-sm mt-1">Pilih menu, masukkan keranjang, checkout — ambil sesuai jadwal.</p>
            </div>
            <div class="mb-8">
                <div class="bg-white shadow rounded-lg p-6">
                    <div class="flex items-center justify-between gap-3 mb-4">
                        <p class="text-gray-600">Silakan pilih menu yang ingin dipesan.</p>
                        <button id="open-cart-desktop" class="hidden sm:inline-flex items-center gap-2 bg-coal text-cream px-4 py-2 rounded-lg hover:bg-brand-800 transition shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                            Keranjang
                            <span id="cart-count-desktop" class="bg-brand-700 text-white text-xs font-bold min-w-5 h-5 px-1 rounded-full inline-flex items-center justify-center">0</span>
                        </button>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @foreach($categories as $category)
                            <button class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-brand-100 hover:text-brand-800 transition filter-btn font-term text-sm" data-category="{{ $category->id }}">
                                @if($category->icon)<span class="mr-1">{{ $category->icon }}</span>@endif{{ $category->name }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 reveal-stagger">
                @foreach($categories as $category)
                    @if($menus->has($category->id))
                        <div class="menu-category" data-category="{{ $category->id }}">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">@if($category->icon){{ $category->icon }} @endif{{ $category->name }}</h3>
                            <div class="grid grid-cols-1 gap-4">
                                @foreach($menus[$category->id] as $menu)
                                    @if($menu->status_ketersediaan)
                                        <div class="bg-white shadow rounded-lg overflow-hidden hover:shadow-md transition">
                                            <div class="bg-gray-100 h-32 flex items-center justify-center overflow-hidden">
                                                @if($menu->image_url)
                                                    <img src="{{ $menu->image_url }}" alt="{{ $menu->name }}" class="w-full h-full object-cover">
                                                @else
                                                    <svg class="w-16 h-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                                                    </svg>
                                                @endif
                                            </div>
                                            <div class="p-4">
                                                <h4 class="font-semibold text-gray-800">{{ $menu->name }}</h4>
                                                <p class="text-sm text-gray-500 mt-1">{{ $menu->description }}</p>
                                                <div class="flex items-center justify-between mt-3">
                                                    <span class="text-lg font-bold text-brand-700 font-term">Rp{{ number_format($menu->price, 0, ',', '.') }}</span>
                                                    <button class="bg-brand-700 text-white px-3 py-1 rounded text-sm hover:bg-brand-800 transition add-to-cart" data-menu-id="{{ $menu->id }}" data-menu-name="{{ $menu->name }}" data-menu-price="{{ $menu->price }}">
                                                        Tambah
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>

            {{-- Keranjang: panel ramping di kanan, tidak menutupi layar --}}
            <div id="cart-sidebar" class="fixed inset-0 z-50 hidden pointer-events-none" aria-hidden="true">
                <div class="cart-overlay fixed inset-0 bg-black/40 backdrop-blur-[2px] pointer-events-auto" id="cart-overlay"></div>
                <div class="cart-panel fixed right-0 top-0 h-full w-[88vw] sm:w-[22rem] bg-white shadow-2xl flex flex-col pointer-events-auto"
                     id="cart-panel" role="dialog" aria-modal="true" aria-labelledby="cart-title">
                    <div class="px-4 py-3 border-b-4 border-brand-700 flex justify-between items-center bg-paper">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-brand-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                            <div>
                                <h3 class="font-bold leading-tight" id="cart-title">Keranjang</h3>
                                <p class="font-term text-[10px] text-gray-500 tracking-widest">MADANG-O // CART</p>
                            </div>
                        </div>
                        <button id="close-cart" class="p-1.5 rounded-md text-gray-500 hover:text-brand-700 hover:bg-brand-50 transition" aria-label="Tutup keranjang">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="flex-1 overflow-y-auto overscroll-contain p-3" id="cart-items">
                        <p class="text-gray-500 text-center py-10 text-sm" id="empty-cart">
                            Keranjang masih kosong.<br>
                            <span class="font-term text-xs text-brand-700">→ pilih menu dulu ya</span>
                        </p>
                    </div>

                    <div class="p-4 border-t bg-gray-50" id="cart-summary" style="display: none;">
                        <div class="flex justify-between items-baseline mb-3">
                            <span class="text-sm text-gray-600">Subtotal</span>
                            <span class="text-xl font-bold font-term text-brand-700" id="cart-subtotal">Rp0</span>
                        </div>
                        <button class="w-full bg-brand-700 text-white py-3 rounded-lg font-semibold hover:bg-brand-800 active:scale-[0.99] transition" id="checkout-btn">
                            Lanjut Checkout
                        </button>
                        <p class="text-center text-[11px] text-gray-500 mt-2">Bayar tunai, transfer, atau QRIS</p>
                    </div>
                </div>
            </div>

            {{-- Tombol Keranjang mengambang (semua ukuran layar) --}}
            <div class="fixed bottom-5 right-5 z-40">
                <button id="cart-float" class="cart-float relative bg-brand-700 text-white pl-4 pr-5 py-3 rounded-full shadow-lg flex items-center gap-2.5 hover:bg-brand-800 active:scale-95 transition"
                        aria-label="Buka keranjang">
                    <span class="relative">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        <span id="cart-count-float" class="cart-badge absolute -top-2 -right-2 bg-coal text-white text-[10px] font-bold min-w-[18px] h-[18px] px-1 rounded-full inline-flex items-center justify-center ring-2 ring-brand-700">0</span>
                    </span>
                    <span class="font-semibold text-sm">Keranjang</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Checkout Modal --}}
    <div id="checkout-modal" class="fixed inset-0 z-50 hidden">
        <div class="fixed inset-0 bg-black/50" id="modal-overlay"></div>
        <div class="fixed inset-0 flex items-center justify-center p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full max-h-[90vh] overflow-y-auto">
                <div class="p-6">
                    <h3 class="text-xl font-semibold mb-6">Checkout</h3>
                    <form id="checkout-form" action="{{ route('pelanggan.orders.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="cart_data" id="cart_data">
                        <input type="hidden" name="total_price" id="total_price">

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                            <input type="text" name="customer_name" value="{{ Auth::user()->name }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-600">
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">No. WhatsApp</label>
                            <input type="tel" name="phone" value="{{ Auth::user()->wa_number }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-600">
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Waktu Ambil</label>
                            <input type="datetime-local" name="pickup_datetime" required min="{{ now()->addHour()->format('Y-m-d\TH:i') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-600">
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Metode Pembayaran</label>
                            <div class="space-y-2">
                                <label class="flex items-center gap-2">
                                    <input type="radio" name="payment_method" value="tunai" checked class="text-brand-700 focus:ring-brand-600">
                                    <span>Tunai (bayar di tempat)</span>
                                </label>
                                <label class="flex items-center gap-2">
                                    <input type="radio" name="payment_method" value="transfer" class="text-brand-700 focus:ring-brand-600">
                                    <span>Transfer Bank</span>
                                </label>
                                <label class="flex items-center gap-2">
                                    <input type="radio" name="payment_method" value="qris" class="text-brand-700 focus:ring-brand-600">
                                    <span>QRIS</span>
                                </label>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Catatan (opsional)</label>
                            <textarea name="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-600"></textarea>
                        </div>
                        <div class="flex gap-3">
                            <button type="button" id="cancel-checkout" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">Batal</button>
                            <button type="submit" class="flex-1 px-4 py-2 bg-brand-700 text-white rounded-lg hover:bg-brand-800 transition">Konfirmasi Pesanan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Cart functionality
        // Kunci per pengguna: keranjang tidak lagi tertukar antar akun yang
        // memakai browser sama, dan keranjang ikut terhapus bersama akun.
        const CART_KEY = 'madango_cart_{{ Auth::id() }}';

        // localStorage bisa berisi JSON rusak (mis. versi lama aplikasi atau
        //diedit manual lewat DevTools). Tanpa penanganan error, JSON.parse
        // akan melempar exception sehingga seluruh script mati dan tombol
        // tambah tidak pernah terpasang sama sekali.
        function loadCart() {
            try {
                const raw = JSON.parse(localStorage.getItem(CART_KEY) || '[]');
                if (!Array.isArray(raw)) return [];

                return raw
                    .filter(item => item && Number.isInteger(Number(item.id)) && Number(item.quantity) > 0)
                    .map(item => ({
                        id: Number(item.id),
                        name: String(item.name ?? 'Menu'),
                        price: Number(item.price) || 0,
                        quantity: Number(item.quantity),
                    }));
            } catch (e) {
                localStorage.removeItem(CART_KEY);
                return [];
            }
        }

        let cart = loadCart();

        // Nama menu masuk ke innerHTML, jadi harus di-escape agar karakter
        // seperti < atau & tidak merusak tampilan (XSS).
        const esc = (value) => String(value).replace(/[&<>"']/g, (c) => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
        }[c]));
        const cartItemsEl = document.getElementById('cart-items');
        const cartSubtotalEl = document.getElementById('cart-subtotal');
        const cartSummaryEl = document.getElementById('cart-summary');
        const checkoutBtn = document.getElementById('checkout-btn');
        const checkoutModal = document.getElementById('checkout-modal');
        const modalOverlay = document.getElementById('modal-overlay');
        const cancelCheckout = document.getElementById('cancel-checkout');
        const cartDataInput = document.getElementById('cart_data');
        const totalPriceInput = document.getElementById('total_price');
        const cartSidebar = document.getElementById('cart-sidebar');
        const cartOverlay = document.getElementById('cart-overlay');
        const cartPanel = document.getElementById('cart-panel');
        const closeCart = document.getElementById('close-cart');
        const cartFloat = document.getElementById('cart-float');
        const cartCountFloat = document.getElementById('cart-count-float');
        const openCartDesktop = document.getElementById('open-cart-desktop');
        const cartCountDesktop = document.getElementById('cart-count-desktop');

        // Elemen yang di-highlight saat item baru ditambahkan
        let lastAddedId = null;

        function saveCart() {
            localStorage.setItem(CART_KEY, JSON.stringify(cart));
            renderCart();
        }

        function updateCounts(bump = false) {
            const totalQty = cart.reduce((sum, item) => sum + item.quantity, 0);
            if (cartCountFloat) {
                cartCountFloat.textContent = String(totalQty);
                if (bump) {
                    cartCountFloat.classList.remove('is-bump');
                    void cartCountFloat.offsetWidth; // restart animasi
                    cartCountFloat.classList.add('is-bump');
                }
            }
            if (cartCountDesktop) cartCountDesktop.textContent = String(totalQty);
            if (cartFloat) cartFloat.classList.toggle('has-items', cart.length > 0);
        }

        function renderCart() {
            if (cart.length === 0) {
                cartItemsEl.innerHTML = '<p class="text-gray-500 text-center py-10 text-sm" id="empty-cart">Keranjang masih kosong.<br><span class="font-term text-xs text-brand-700">→ pilih menu dulu ya</span></p>';
                cartSummaryEl.style.display = 'none';
                lastAddedId = null;
                updateCounts();
                return;
            }
            cartSummaryEl.style.display = 'block';
            cartItemsEl.innerHTML = cart.map((item, index) => `
                <div class="cart-item flex items-center gap-3 p-2.5 rounded-lg hover:bg-gray-50 transition ${item.id === lastAddedId ? 'is-new' : ''}">
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-sm truncate">${esc(item.name)}</p>
                        <p class="text-xs text-gray-500 font-term">Rp${item.price.toLocaleString('id-ID')} × ${item.quantity} = <span class="text-brand-700 font-bold">Rp${(item.price * item.quantity).toLocaleString('id-ID')}</span></p>
                    </div>
                    <div class="flex items-center gap-1 shrink-0">
                        <button onclick="updateQty(${index}, ${item.quantity - 1})" class="w-7 h-7 border border-gray-300 rounded-md text-sm hover:bg-brand-50 hover:border-brand-400 active:scale-90 transition" aria-label="Kurangi">−</button>
                        <span class="w-7 text-center text-sm font-semibold font-term">${item.quantity}</span>
                        <button onclick="updateQty(${index}, ${item.quantity + 1})" class="w-7 h-7 border border-gray-300 rounded-md text-sm hover:bg-brand-50 hover:border-brand-400 active:scale-90 transition" aria-label="Tambah">+</button>
                        <button onclick="removeItem(${index})" class="w-7 h-7 rounded-md text-gray-400 hover:text-white hover:bg-red-600 active:scale-90 transition ml-1" aria-label="Hapus">
                            <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        </button>
                    </div>
                </div>
            `).join('');

            const subtotal = cart.reduce((sum, item) => sum + item.price * item.quantity, 0);
            cartSubtotalEl.textContent = 'Rp' + subtotal.toLocaleString('id-ID');
            lastAddedId = null;
            updateCounts();
        }

        window.updateQty = function(index, qty) {
            if (qty <= 0) {
                removeItem(index);
                return;
            }
            cart[index].quantity = qty;
            saveCart();
        };

        window.removeItem = function(index) {
            cart.splice(index, 1);
            saveCart();
        };

        function openCart() {
            cartSidebar.classList.remove('hidden');
            cartSidebar.setAttribute('aria-hidden', 'false');
            cartSidebar.classList.add('pointer-events-auto');
            cartSidebar.classList.remove('pointer-events-none');
            // reflow supaya transisi masuk berjalan
            void cartPanel.offsetWidth;
            cartPanel.classList.add('is-open');
            cartOverlay.classList.add('is-open');
            document.body.style.overflow = 'hidden';
        }

        function closeCartFn() {
            cartPanel.classList.remove('is-open');
            cartOverlay.classList.remove('is-open');
            cartSidebar.setAttribute('aria-hidden', 'true');
            cartSidebar.classList.add('pointer-events-none');
            cartSidebar.classList.remove('pointer-events-auto');
            document.body.style.overflow = '';
            setTimeout(() => {
                if (!cartPanel.classList.contains('is-open')) {
                    cartSidebar.classList.add('hidden');
                }
            }, 320);
        }

        document.querySelectorAll('.add-to-cart').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = parseInt(btn.dataset.menuId);
                const name = btn.dataset.menuName;
                const price = parseInt(btn.dataset.menuPrice);
                const existing = cart.find(item => item.id === id);
                if (existing) {
                    existing.quantity++;
                } else {
                    cart.push({ id, name, price, quantity: 1 });
                }
                lastAddedId = id;
                saveCart();
                // Dipanggil lagi dengan bump=true supaya badge keranjang
                // ikut beranimasi "pop". saveCart() sendiri hanya
                // menyinkronkan angka tanpa animasi.
                updateCounts(true);
                if (cartSidebar.classList.contains('hidden')) {
                    openCart();
                }
            });
        });

        // Semua listener memakai ?. supaya elemen yang hilang tidak membuat
        // seluruh skrip berhenti di tengah jalan.
        checkoutBtn?.addEventListener('click', () => {
            if (cart.length === 0) return;
            cartDataInput.value = JSON.stringify(cart);
            const subtotal = cart.reduce((sum, item) => sum + item.price * item.quantity, 0);
            totalPriceInput.value = subtotal;
            checkoutModal.classList.remove('hidden');
        });

        function closeModal() {
            checkoutModal.classList.add('hidden');
            // Kunci scroll body baru dilepas kalau sidebar keranjang juga sudah tertutup.
            if (cartSidebar.classList.contains('hidden')) {
                document.body.style.overflow = '';
            }
        }

        cancelCheckout?.addEventListener('click', closeModal);
        modalOverlay?.addEventListener('click', closeModal);

        cartOverlay?.addEventListener('click', closeCartFn);
        closeCart?.addEventListener('click', closeCartFn);
        cartFloat?.addEventListener('click', openCart);
        openCartDesktop?.addEventListener('click', openCart);

        // Tutup dengan tombol Escape.
        // Modal checkout berada di atas sidebar keranjang, jadi modal harus
        // ditutup lebih dulu; kalau tidak, sidebar tertutup tapi modal
        // tetap menggantung di layar.
        document.addEventListener('keydown', (e) => {
            if (e.key !== 'Escape') return;

            if (!checkoutModal.classList.contains('hidden')) {
                closeModal();
                return;
            }

            if (!cartSidebar.classList.contains('hidden')) {
                closeCartFn();
            }
        });

        // Animasi muncul saat scroll ditangani oleh layouts/app.blade.php
        // untuk seluruh halaman, jadi di sini tidak diulang lagi. Observer
        // sebelumnya ditulis dua kali (satu di layout, satu di halaman ini)
        // sehingga elemen yang sama diproses dua kali.

        // Filter kategori
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const cat = btn.dataset.category;
                document.querySelectorAll('.menu-category').forEach(el => {
                    if (cat === 'all' || el.dataset.category === cat) {
                        el.style.display = 'block';
                    } else {
                        el.style.display = 'none';
                    }
                });
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('bg-brand-100', 'text-brand-800'));
                btn.classList.add('bg-brand-100', 'text-brand-800');
            });
        });

        // Initialize
        renderCart();
    </script>
    @endpush
</x-app-layout>