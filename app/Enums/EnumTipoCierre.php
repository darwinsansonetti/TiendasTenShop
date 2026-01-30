<?php

namespace App\Enums;

enum EnumTipoCierre: int
{
    case CierreDiario = 0;       // "Cierre de Diario"
    case Auditoria = 1;          // "Auditoría de Cierre"
    case Todos = -100;

    // Opcional: método para mostrar nombre amigable
    public function label(): string
    {
        return match($this) {
            self::CierreDiario => 'Cierre de Diario',
            self::Auditoria => 'Auditoría de Cierre',
            self::Todos => 'Todos',
        };
    }
}
