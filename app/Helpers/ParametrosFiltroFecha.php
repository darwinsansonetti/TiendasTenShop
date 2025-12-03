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

        // 1️⃣ Filtro por rango manual
        if ($fechaInicioManual && $fechaFinManual) {
            $this->fechaInicio = $fechaInicioManual->copy()->startOfDay();
            $this->fechaFin    = $fechaFinManual->copy()->endOfDay();
            $this->tipoFiltroFecha = EnumTipoFiltroFecha::Rango;
            $this->mes  = EnumMes::from($now->month);
            $this->anno = $now->year;
            return;
        }

        // 2️⃣ Filtro por mes seleccionado
        if ($mesSeleccionado && !$tipoFiltroFecha) {
            $mesNum = $mesSeleccionado->value;

            if ($annoAnterior && $mesNum > $now->month) {
                $anno--;
            }

            $anno = $anno ?? $now->year;

            $this->fechaInicio = Carbon::create($anno, $mesNum, 1, 0, 0, 0, 'America/Caracas');

            if ($mesNum == 12) {
                $this->fechaFin = Carbon::create($anno, 12, 31, 23, 59, 59, 'America/Caracas');
            } else {
                $this->fechaFin = Carbon::create($anno, $mesNum + 1, 1, 0, 0, 0, 'America/Caracas')->subDay()->endOfDay();
            }

            $this->mes = $mesSeleccionado;
            $this->anno = $anno;
            $this->tipoFiltroFecha = EnumTipoFiltroFecha::MesSeleccionado;
            return;
        }

        // 3️⃣ Filtro por tipo (Hoy, Día Anterior, Mes Actual, Mes Anterior, Último Año)
        switch ($tipoFiltroFecha) {
            case EnumTipoFiltroFecha::Hoy:
                $this->fechaInicio = $now->copy()->startOfDay();
                $this->fechaFin    = $now->copy()->endOfDay();
                break;

            case EnumTipoFiltroFecha::DiaAnterior:
                $ayer = $now->copy()->subDay();
                $this->fechaInicio = $ayer->copy()->startOfDay();
                $this->fechaFin    = $ayer->copy()->endOfDay();
                break;

            case EnumTipoFiltroFecha::MesActual:
                $this->fechaInicio = $now->copy()->startOfMonth();
                $this->fechaFin    = $now->copy()->endOfDay();
                break;

            case EnumTipoFiltroFecha::MesAnterior:
                $mesAnterior = $now->copy()->subMonth();
                $this->fechaInicio = $mesAnterior->copy()->startOfMonth();
                $this->fechaFin    = $mesAnterior->copy()->endOfMonth();
                break;

            case EnumTipoFiltroFecha::UltimoAno:
                $this->fechaInicio = $now->copy()->subYear()->startOfMonth();
                $this->fechaFin    = $now->copy()->endOfDay();
                break;

            default:
                // Valor por defecto = Mes Actual
                $this->fechaInicio = $now->copy()->startOfMonth();
                $this->fechaFin    = $now->copy()->endOfDay();
                $tipoFiltroFecha   = EnumTipoFiltroFecha::MesActual;
        }

        $this->tipoFiltroFecha = $tipoFiltroFecha ?? EnumTipoFiltroFecha::MesActual;
        $this->mes = EnumMes::from($now->month);
        $this->anno = $now->year;
    }
}
