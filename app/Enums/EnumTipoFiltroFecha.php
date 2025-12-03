<?php

namespace App\Enums;

enum EnumTipoFiltroFecha: int
{
    case Hoy = 1;
    case DiaAnterior = 2;
    case MesActual = 3;
    case MesAnterior = 4;
    case UltimoAno = 5;
    case MesSeleccionado = 6;
    case Rango = 7;
}
