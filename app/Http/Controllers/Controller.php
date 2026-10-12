<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

abstract class Controller
{
    // Dipakai oleh $this->authorize() untuk mengecek Policy, misalnya
    // OrderPolicy pada halaman detail dan pembatalan pesanan pelanggan.
    use AuthorizesRequests;
}