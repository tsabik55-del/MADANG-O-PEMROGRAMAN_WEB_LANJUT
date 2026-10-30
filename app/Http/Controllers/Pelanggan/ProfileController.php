<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * Simpan biodata pelanggan dari modal "Lengkapi Biodata".
     *
     * Nama, nomor WhatsApp, dan alamat dipakai sebagai data awal saat
     * checkout sehingga pelanggan tidak perlu mengetik ulang.
     */
    public function update(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'wa_number' => ['required', 'string', 'max:30', 'regex:/^[0-9+\-\s()]+$/'],
            'address' => ['required', 'string', 'max:500'],
        ], [
            'wa_number.regex' => 'Nomor WhatsApp hanya boleh berisi angka dan tanda +, -, atau spasi.',
        ]);

        $user = $request->user();
        $user->update($data);

        if ($request->expectsJson()) {
            return response()->json([
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'wa' => $user->wa_number,
                    'alamat' => $user->address,
                ],
                'message' => 'Biodata berhasil disimpan.',
            ]);
        }

        return back()->with('success', 'Biodata berhasil disimpan.');
    }
}
