<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\GeneralHelper;

class LandingpageController extends Controller
{
    // Llamada al Index del LandinPage
    public function index()
    {
        $mensaje = GeneralHelper::ultimoMensaje();

        return view('landingpage.index', compact('mensaje'));
    }
}
