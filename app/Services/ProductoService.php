<?php

namespace App\Services;

use App\Models\ProductoModel;
use App\Models\Producto;
use App\DTO\ProductoDTO;
use Carbon\Carbon;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProductoService
{
    public static function buscarProducto(int $productoId, int $sucursalId)
    {
        if ($sucursalId !== 0) {
            $producto = Producto::where('Id', $productoId)
                ->where('SucursalId', $sucursalId)
                ->first();
        } else {
            $producto = ProductoModel::where('ID', $productoId)->first();
        }

        return $producto ? ProductoDTO::fromModel($producto) : null;
    }

}
