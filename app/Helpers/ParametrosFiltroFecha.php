<?php

namespace App\Helpers;

use Carbon\Carbon;
use App\Enums\EnumMes;
use App\Enums\EnumTipoFiltroFecha;

class ParametrosFiltroFecha
{
    public Carbon $fechaInicio;
    public Carbon $fechaFin;
    public EnumTipoFiltroFecha $tipoFiltroFecha;
    public EnumMes $mes;
    public int $anno;

    public function __construct(
        ?EnumTipoFiltroFecha $tipoFiltroFecha = null,
        ?EnumMes $mesSeleccionado = null,
        ?int $anno = null,
        bool $annoAnterior = false,
        ?Carbon $fechaInicioManual = null,
        ?Carbon $fechaFinManual = null
    ) {
        // Timezone Venezuela
        $now = Carbon::now('America/Caracas');

        /*
        |--------------------------------------------------------------------------
        | 1️⃣ Filtro por rango manual (IGUAL A .NET)
        |--------------------------------------------------------------------------
        | FechaInicio = inicio del día
        | FechaFin    = inicio del día (NO endOfDay)
        */
        if ($fechaInicioManual && $fechaFinManual) {
            $this->fechaInicio = $fechaInicioManual->copy()->startOfDay();
            $this->fechaFin    = $fechaFinManual->copy()->startOfDay();

            $this->tipoFiltroFecha = EnumTipoFiltroFecha::Rango;
            $this->mes  = EnumMes::from($now->month);
            $this->anno = $now->year;
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | 2️⃣ Filtro por mes seleccionado (IGUAL A .NET)
        |--------------------------------------------------------------------------
        | FechaInicio = 01/MM/AAAA 00:00:00
        | FechaFin    = ÚLTIMO_DÍA/MM/AAAA 00:00:00
        */
        if ($mesSeleccionado && !$tipoFiltroFecha) {
            $mesNum = $mesSeleccionado->value;

            if ($annoAnterior && $mesNum > $now->month) {
                $anno--;
            }

            $anno = $anno ?? $now->year;

            // Inicio del mes
            $this->fechaInicio = Carbon::create(
                $anno,
                $mesNum,
                1,
                0,
                0,
                0,
                'America/Caracas'
            );

            // Inicio del último día del mes (NO endOfDay)
            $this->fechaFin = Carbon::create(
                $anno,
                $mesNum,
                1,
                0,
                0,
                0,
                'America/Caracas'
            )->endOfMonth()->startOfDay();

            $this->mes = $mesSeleccionado;
            $this->anno = $anno;
            $this->tipoFiltroFecha = EnumTipoFiltroFecha::MesSeleccionado;
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | 3️⃣ Filtro por tipo (Hoy, Día Anterior, Mes Actual, etc.)
        |--------------------------------------------------------------------------
        | TODOS respetan el patrón .NET
        */
        switch ($tipoFiltroFecha) {
            case EnumTipoFiltroFecha::Hoy:
                $this->fechaInicio = $now->copy()->startOfDay();
                $this->fechaFin    = $now->copy()->startOfDay();
                break;

            case EnumTipoFiltroFecha::DiaAnterior:
                $ayer = $now->copy()->subDay();
                $this->fechaInicio = $ayer->copy()->startOfDay();
                $this->fechaFin    = $ayer->copy()->startOfDay();
                break;

            case EnumTipoFiltroFecha::MesActual:
                $this->fechaInicio = $now->copy()->startOfMonth();
                $this->fechaFin    = $now->copy()->endOfMonth()->startOfDay();
                break;

            case EnumTipoFiltroFecha::MesAnterior:
                $mesAnterior = $now->copy()->subMonth();
                $this->fechaInicio = $mesAnterior->copy()->startOfMonth();
                $this->fechaFin    = $mesAnterior->copy()->endOfMonth()->startOfDay();
                break;

            case EnumTipoFiltroFecha::UltimoAno:
                $this->fechaInicio = $now->copy()->subYear()->startOfMonth();
                $this->fechaFin    = $now->copy()->endOfMonth()->startOfDay();
                break;

            default:
                // Valor por defecto = Mes Actual
                $this->fechaInicio = $now->copy()->startOfMonth();
                $this->fechaFin    = $now->copy()->endOfMonth()->startOfDay();
                $tipoFiltroFecha   = EnumTipoFiltroFecha::MesActual;
        }

        $this->tipoFiltroFecha = $tipoFiltroFecha ?? EnumTipoFiltroFecha::MesActual;
        $this->mes  = EnumMes::from($now->month);
        $this->anno = $now->year;
    }
}
