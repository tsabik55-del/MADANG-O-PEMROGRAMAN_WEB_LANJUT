<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class PelangganLayout extends Component
{
    /**
     * Cangkang halaman pelanggan: sidebar, profil, dan tombol keluar.
     *
     * Dipisahkan dari layouts.app supaya navigasi atas bawaan Breeze tidak
     * ikut tampil. Halaman pelanggan memakai sidebar sendiri seperti desain
     * acuan tim.
     */
    public function render(): View
    {
        return view('layouts.pelanggan');
    }
}
