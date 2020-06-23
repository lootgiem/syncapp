<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Support\Renderable;

class ProfilController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return Renderable
     */
    public function __invoke()
    {
        return view('profil');
    }
}
