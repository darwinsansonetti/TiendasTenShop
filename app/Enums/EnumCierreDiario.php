<?php

namespace App\Enums;

enum EnumCierreDiario: int
{
    case Edicion = 0;
    case Nuevo = 1;
    case Auditoria = 2;        // "En auditoría"
    case Contabilizado = 3;
    case Cerrada = 4;
    case Todos = -100;

    // Opcional: método para mostrar nombre amigable
    public function label(): string
    {
        return match($this) {
            self::Edicion => 'Edición',
            self::Nuevo => 'Nuevo',
            self::Auditoria => 'En auditoría',
            self::Contabilizado => 'Contabilizado',
            self::Cerrada => 'Cerrada',
            self::Todos => 'Todos',
        };
    }
}
