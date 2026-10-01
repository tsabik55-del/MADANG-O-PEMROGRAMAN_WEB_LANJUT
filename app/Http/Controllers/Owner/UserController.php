<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Daftar semua pengguna dengan filter peran.
     */
    public function index(Request $request): View
    {
        $query = User::query()->latest();

        if ($request->filled('role') && in_array($request->role, ['pelanggan', 'karyawan', 'owner'])) {
            $query->where('role', $request->role);
        }

        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', $search)
                    ->orWhere('username', 'like', $search)
                    ->orWhere('email', 'like', $search);
            });
        }

        $users = $query->paginate(15)->withQueryString();

        $counts = [
            'pelanggan' => User::where('role', 'pelanggan')->count(),
            'karyawan' => User::where('role', 'karyawan')->count(),
            'owner' => User::where('role', 'owner')->count(),
        ];

        return view('owner.users.index', compact('users', 'counts'));
    }

    /**
     * Formulir tambah pengguna baru.
     */
    public function create(): View
    {
        return view('owner.users.create');
    }

    /**
     * Simpan pengguna baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'nullable|email|max:255|unique:users,email',
            'wa_number' => 'required|string|max:20|unique:users,wa_number',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:pelanggan,karyawan,owner',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()
            ->route('owner.users.index')
            ->with('success', 'Pengguna berhasil ditambahkan.');
    }

    /**
     * Formulir ubah pengguna.
     */
    public function edit(User $user): View
    {
        return view('owner.users.edit', compact('user'));
    }

    /**
     * Perbarui pengguna.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'wa_number' => ['required', 'string', 'max:20', Rule::unique('users', 'wa_number')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|in:pelanggan,karyawan,owner',
        ]);

        // Cegah owner menurunkan perannya sendiri (mencegah terkunci).
        if ($user->id === $request->user()->id && $validated['role'] !== 'owner') {
            return back()->withErrors([
                'role' => 'Anda tidak dapat menurunkan peran akun sendiri.',
            ])->withInput();
        }

        if (empty($validated['password'])) {
            unset($validated['password']);
        } else {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        return redirect()
            ->route('owner.users.index')
            ->with('success', 'Pengguna berhasil diperbarui.');
    }

    /**
     * Hapus pengguna.
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        // Cegah owner menghapus akunnya sendiri.
        if ($user->id === $request->user()->id) {
            return back()->withErrors([
                'user' => 'Anda tidak dapat menghapus akun sendiri.',
            ]);
        }

        $user->delete();

        return redirect()
            ->route('owner.users.index')
            ->with('success', 'Pengguna berhasil dihapus. Pesanan miliknya tetap tersimpan di database.');
    }
}
