<?php

namespace App\Helpers;

use Carbon\Carbon;
use App\Models\Sucursal;
use App\Models\Divisa;
use App\Models\DivisaValor;
use App\Models\Mensaje;

class GeneralHelper
{
    // Obtener Tasa del Dia
    public static function obtenerTasaCambioDiaria($fecha)
    {
        $fecha = Carbon::parse($fecha)->format('Y-m-d');

        $IDDolar = 1; // Ajusta si usas otra ID

        $divisa = Divisa::find($IDDolar);

        if (!$divisa) {
            return null;
        }

        $divisaDTO = [
            'EsActiva' => true,
            'EsPrincipal' => true,
            'Nombre' => 'Dolar',
            'Simbolo' => '$',
            'DivisaValor' => null
        ];

        $valor = DivisaValor::whereDate('Fecha', $fecha)->first();

        if ($valor) {
            $divisaDTO['DivisaValor'] = [
                'Id' => $valor->Id,
                'Fecha' => $valor->Fecha,
                'Valor' => $valor->Valor
            ];
        }

        return $divisaDTO;
    }

    // Obtener último Mensaje
    public static function ultimoMensaje()
    {
        $mensaje = Mensaje::orderByDesc('Fecha')
                        ->orderByDesc('MensajeId') // Por si hay varios en la misma fecha
                        ->first();

        if ($mensaje) {
            return $mensaje->Mensaje;
        }

        // Mensaje por defecto
        return '¡Bienvenido a Tiendas TenShop! | Descubre nuestras ofertas y promociones especiales hoy mismo.';
    }

    // Obtener todas las sucursales
    public static  function buscarSucursales($tipoSucursal)
    {
        $query = Sucursal::orderBy('Nombre');

        // 0 = Todas (igual que EnumTipoSucursal.Todas)
        if ($tipoSucursal !== 0) {
            $query->where('Tipo', $tipoSucursal);
        }

        return $query->get();
    }
}