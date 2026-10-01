<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Katalog Menu') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-8">
                <div class="bg-white shadow rounded-lg p-6">
                    <p class="text-gray-600 mb-4">Selamat datang, <strong>{{ Auth::user()->name }}</strong>! Silakan pilih menu yang ingin dipesan.</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($categories as $category)
                            <button class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-green-100 hover:text-green-800 transition filter-btn" data-category="{{ $category->id }}">
                                @if($category->icon)<span class="mr-1">{{ $category->icon }}</span>@endif{{ $category->name }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($categories as $category)
                    @if($menus->has($category->id))
                        <div class="menu-category" data-category="{{ $category->id }}">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">@if($category->icon){{ $category->icon }} @endif{{ $category->name }}</h3>
                            <div class="grid grid-cols-1 gap-4">
                                @foreach($menus[$category->id] as $menu)
                                    @if($menu->status_ketersediaan)
                                        <div class="bg-white shadow rounded-lg overflow-hidden hover:shadow-md transition">
                                            <div class="bg-gray-100 h-32 flex items-center justify-center overflow-hidden">
                                                @if($menu->image)
                                                    <img src="{{ asset($menu->image) }}" alt="{{ $menu->name }}" class="w-full h-full object-cover">
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
                                                    <span class="text-lg font-bold text-green-600">Rp{{ number_format($menu->price, 0, ',', '.') }}</span>
                                                    <button class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700 transition add-to-cart" data-menu-id="{{ $menu->id }}" data-menu-name="{{ $menu->name }}" data-menu-price="{{ $menu->price }}">
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

            {{-- Keranjang Sidebar --}}
            <div id="cart-sidebar" class="fixed inset-0 z-50 hidden lg:block">
                <div class="fixed inset-0 bg-black/50" id="cart-overlay"></div>
                <div class="fixed right-0 top-0 h-full w-96 bg-white shadow-xl" id="cart-panel">
                    <div class="p-4 border-b flex justify-between items-center">
                        <h3 class="font-semibold text-lg">Keranjang Anda</h3>
                        <button id="close-cart" class="text-gray-500 hover:text-gray-700">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="p-4 overflow-y-auto flex-1" id="cart-items">
                        <p class="text-gray-500 text-center py-8" id="empty-cart">Keranjang kosong</p>
                    </div>
                    <div class="p-4 border-t bg-gray-50" id="cart-summary" style="display: none;">
                        <div class="flex justify-between mb-2">
                            <span>Subtotal</span>
                            <span class="font-medium" id="cart-subtotal">Rp0</span>
                        </div>
                        <button class="w-full bg-green-600 text-white py-3 rounded-lg font-medium hover:bg-green-700 transition" id="checkout-btn">
                            Checkout
                        </button>
                    </div>
                </div>
            </div>

            {{-- Mobile Cart Button --}}
            <div class="lg:hidden fixed bottom-6 right-6 z-40">
                <button id="open-cart-mobile" class="bg-green-600 text-white p-4 rounded-full shadow-lg flex items-center gap-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    <span id="cart-count-mobile" class="bg-white text-green-600 text-xs font-bold w-5 h-5 rounded-full flex items-center justify-center">0</span>
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
                            <input type="text" name="customer_name" value="{{ Auth::user()->name }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">No. WhatsApp</label>
                            <input type="tel" name="phone" value="{{ Auth::user()->wa_number }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Waktu Ambil</label>
                            <input type="datetime-local" name="pickup_datetime" required min="{{ now()->addHour()->format('Y-m-d\TH:i') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Metode Pembayaran</label>
                            <div class="space-y-2">
                                <label class="flex items-center gap-2">
                                    <input type="radio" name="payment_method" value="tunai" checked class="text-green-600 focus:ring-green-500">
                                    <span>Tunai (bayar di tempat)</span>
                                </label>
                                <label class="flex items-center gap-2">
                                    <input type="radio" name="payment_method" value="transfer" class="text-green-600 focus:ring-green-500">
                                    <span>Transfer Bank</span>
                                </label>
                                <label class="flex items-center gap-2">
                                    <input type="radio" name="payment_method" value="qris" class="text-green-600 focus:ring-green-500">
                                    <span>QRIS</span>
                                </label>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Catatan (opsional)</label>
                            <textarea name="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"></textarea>
                        </div>
                        <div class="flex gap-3">
                            <button type="button" id="cancel-checkout" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">Batal</button>
                            <button type="submit" class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">Konfirmasi Pesanan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Cart functionality
        let cart = JSON.parse(localStorage.getItem('madango_cart')) || [];
        const cartItemsEl = document.getElementById('cart-items');
        const cartSubtotalEl = document.getElementById('cart-subtotal');
        const cartCountMobile = document.getElementById('cart-count-mobile');
        const cartSummaryEl = document.getElementById('cart-summary');
        const emptyCartEl = document.getElementById('empty-cart');
        const checkoutBtn = document.getElementById('checkout-btn');
        const checkoutModal = document.getElementById('checkout-modal');
        const modalOverlay = document.getElementById('modal-overlay');
        const cancelCheckout = document.getElementById('cancel-checkout');
        const cartDataInput = document.getElementById('cart_data');
        const totalPriceInput = document.getElementById('total_price');
        const cartSidebar = document.getElementById('cart-sidebar');
        const cartOverlay = document.getElementById('cart-overlay');
        const closeCart = document.getElementById('close-cart');
        const openCartMobile = document.getElementById('open-cart-mobile');

        function saveCart() {
            localStorage.setItem('madango_cart', JSON.stringify(cart));
            renderCart();
        }

        function renderCart() {
            if (cart.length === 0) {
                cartItemsEl.innerHTML = '<p class="text-gray-500 text-center py-8" id="empty-cart">Keranjang kosong</p>';
                cartSummaryEl.style.display = 'none';
                cartCountMobile.textContent = '0';
                return;
            }
            cartSummaryEl.style.display = 'block';
            cartItemsEl.innerHTML = cart.map((item, index) => `
                <div class="flex items-center gap-3 py-3 border-b">
                    <div class="flex-1">
                        <p class="font-medium">${item.name}</p>
                        <p class="text-sm text-gray-500">Rp${item.price.toLocaleString('id-ID')} x ${item.quantity}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button onclick="updateQty(${index}, ${item.quantity - 1})" class="px-2 py-1 border rounded text-sm" ${item.quantity <= 1 ? 'disabled' : ''}>-</button>
                        <span class="w-8 text-center">${item.quantity}</span>
                        <button onclick="updateQty(${index}, ${item.quantity + 1})" class="px-2 py-1 border rounded text-sm">+</button>
                        <button onclick="removeItem(${index})" class="text-red-500 hover:text-red-700 ml-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        </button>
                    </div>
                </div>
            `).join('');

            const subtotal = cart.reduce((sum, item) => sum + item.price * item.quantity, 0);
            cartSubtotalEl.textContent = 'Rp' + subtotal.toLocaleString('id-ID');
            cartCountMobile.textContent = cart.length;
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
            document.body.style.overflow = 'hidden';
        }

        function closeCartFn() {
            cartSidebar.classList.add('hidden');
            document.body.style.overflow = '';
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
                saveCart();
                openCart();
            });
        });

        checkoutBtn.addEventListener('click', () => {
            if (cart.length === 0) return;
            cartDataInput.value = JSON.stringify(cart);
            const subtotal = cart.reduce((sum, item) => sum + item.price * item.quantity, 0);
            totalPriceInput.value = subtotal;
            checkoutModal.classList.remove('hidden');
        });

        function closeModal() {
            checkoutModal.classList.add('hidden');
        }

        cancelCheckout.addEventListener('click', closeModal);
        modalOverlay.addEventListener('click', closeModal);

        cartOverlay.addEventListener('click', closeCartFn);
        closeCart.addEventListener('click', closeCartFn);
        openCartMobile.addEventListener('click', openCart);

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
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('bg-green-100', 'text-green-800'));
                btn.classList.add('bg-green-100', 'text-green-800');
            });
        });

        // Initialize
        renderCart();
    </script>
    @endpush
</x-app-layout>