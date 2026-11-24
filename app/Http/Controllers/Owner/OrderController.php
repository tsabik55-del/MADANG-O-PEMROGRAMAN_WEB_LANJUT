<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Concerns\CreatesManualOrders;
use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OrderController extends Controller
{
    use CreatesManualOrders;

    public function __construct(private readonly OrderService $orders) {}

    public function index(Request $request): View
    {
        $orders = Order::with(['user', 'items.menu'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $q = '%'.$request->string('q').'%';
                $query->where(function ($sub) use ($q) {
                    $sub->where('order_number', 'like', $q)
                        ->orWhere('customer_name', 'like', $q)
                        ->orWhere('phone', 'like', $q);
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($request->filled('payment_status'), fn ($query) => $query->where('payment_status', $request->payment_status))
            ->when($request->filled('source'), fn ($query) => $query->where('source', $request->source))
            ->when($request->filled('date'), fn ($query) => $query->whereDate('pickup_datetime', $request->date))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('owner.orders.index', compact('orders'));
    }

    /**
     * Form pesanan manual (telepon/WhatsApp).
     */
    public function create(): View
    {
        $menus = Menu::with('category')
            ->where('status_ketersediaan', true)
            ->orderBy('category_id')
            ->orderBy('name')
            ->get();

        return view('orders.manual-create', [
            'menus' => $menus,
            'action' => route('owner.orders.store'),
            'backRoute' => route('owner.orders.index'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateManualOrder($request);

        $rows = $this->manualOrderRows($request);

        try {
            $built = $this->orders->buildItems($rows);
        } catch (ValidationException $e) {
            return back()->withInput()->withErrors(['items' => $e->validator->errors()->first()]);
        }

        $order = $this->orders->create(
            $validated,
            $built['items'],
            $built['total_price'],
            null,
            'manual',
            $validated['payment_status'],
        );

        return redirect()
            ->route('owner.orders.show', $order)
            ->with('success', 'Pesanan manual berhasil dicatat.');
    }

    public function show(Order $order): View
    {
        $order->load(['user', 'items.menu']);

        return view('owner.orders.show', compact('order'));
    }

    public function edit(Order $order): View
    {
        return view('owner.orders.edit', compact('order'));
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'payment_status' => 'required|in:belum_lunas,menunggu_verifikasi,lunas',
            'pickup_status' => 'required|in:belum_diambil,sudah_diambil',
            'status' => 'required|in:'.implode(',', Order::STATUSES),
            'notes' => 'nullable|string',
        ]);

        $this->orders->updateStatus($order, $validated, $request->user());

        return redirect()->route('owner.orders.index')->with('success', 'Pesanan berhasil diperbarui.');
    }

    /**
     * Pembatalan oleh owner tetap menyimpan riwayat (soft cancel).
     */
    public function destroy(Request $request, Order $order): RedirectResponse
    {
        $this->orders->cancel($order);

        return redirect()->route('owner.orders.index')->with('success', 'Pesanan ditandai dibatalkan.');
    }
}
