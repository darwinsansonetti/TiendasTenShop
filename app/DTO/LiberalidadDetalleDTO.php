<?php
// app/DTO/LiberalidadDetalleDTO.php

namespace App\DTO;

use App\Models\LiberalidadDetalle;
use App\Models\Usuario;
use App\Models\AspNetUser;

class LiberalidadDetalleDTO
{
    public int $LiberalidadDetalleId;
    public int $LiberalidadId;
    public ?int $UsuarioId;
    public ?string $EmpleadoId;
    public int $Estatus;
    public bool $EsVendedor;
    public int $Unidades;
    public float $SaldoFavor;
    public float $Venta;
    public float $MontoLiberalidad;
    public float $AbonoPrestamo;
    public float $OtraLiberalidad;
    public float $DeudaPrestamo;
    public float $PagoPrestamo;
    public float $TotalPagado;
    public float $Pago;
    public ?string $Motivo;
    
    // Datos del usuario/vendedor (opcionales)
    public ?object $Usuario = null;
    public ?object $Empleado = null;
    
    /**
     * Constructor desde el modelo
     */
    public function __construct(LiberalidadDetalle $detalle)
    {
        $this->LiberalidadDetalleId = $detalle->LiberalidadDetalleId;
        $this->LiberalidadId = $detalle->LiberalidadId;
        $this->UsuarioId = $detalle->UsuarioId;
        $this->EmpleadoId = $detalle->EmpleadoId;
        $this->Estatus = $detalle->Estatus;
        $this->EsVendedor = $detalle->EsVendedor;
        $this->Unidades = $detalle->Unidades ?? 0;
        $this->SaldoFavor = (float) ($detalle->SaldoFavor ?? 0);
        $this->Venta = (float) ($detalle->Venta ?? 0);
        $this->MontoLiberalidad = (float) ($detalle->MontoLiberalidad ?? 0);
        $this->AbonoPrestamo = (float) ($detalle->AbonoPrestamo ?? 0);
        $this->OtraLiberalidad = (float) ($detalle->OtraLiberalidad ?? 0);
        $this->DeudaPrestamo = (float) ($detalle->DeudaPrestamo ?? 0);
        $this->PagoPrestamo = (float) ($detalle->PagoPrestamo ?? 0);
        $this->TotalPagado = (float) ($detalle->TotalPagado ?? 0);
        $this->Pago = (float) ($detalle->Pago ?? 0);
        $this->Motivo = $detalle->Motivo;
        
        // Cargar datos del usuario si existe
        if ($detalle->UsuarioId) {
            $usuario = Usuario::find($detalle->UsuarioId);
            if ($usuario) {
                $this->Usuario = (object) [
                    'UsuarioId' => $usuario->UsuarioId,
                    'VendedorId' => $usuario->VendedorId,
                    'NombreCompleto' => $usuario->NombreCompleto,
                    'Email' => $usuario->Email,
                    'SucursalId' => $usuario->SucursalId,
                    'FotoPerfil' => $usuario->FotoPerfil
                ];
            }
        }
        
        // Cargar datos del empleado si existe
        if ($detalle->EmpleadoId) {
            $empleado = AspNetUser::where('Id', $detalle->EmpleadoId)->first();
            if ($empleado) {
                $this->Empleado = (object) [
                    'Id' => $empleado->Id,
                    'VendedorId' => $empleado->VendedorId,
                    'NombreCompleto' => $empleado->NombreCompleto,
                    'Email' => $empleado->Email,
                    'SucursalId' => $empleado->SucursalId,
                    'FotoPerfil' => $empleado->FotoPerfil
                ];
            }
        }
    }
    
    /**
     * Propiedad calculada: Disponible
     */
    public function getDisponible(): float
    {
        return round(
            $this->MontoLiberalidad + $this->SaldoFavor + $this->OtraLiberalidad 
            - $this->TotalPagado - $this->AbonoPrestamo,
            2
        );
    }
    
    /**
     * Obtener el nombre del empleado/vendedor
     */
    public function getNombreCompleto(): string
    {
        if ($this->Usuario) {
            return $this->Usuario->NombreCompleto;
        }
        if ($this->Empleado) {
            return $this->Empleado->NombreCompleto;
        }
        return $this->EmpleadoId ?? 'N/A';
    }
    
    /**
     * Obtener el código de vendedor
     */
    public function getVendedorId(): string
    {
        if ($this->Usuario) {
            return $this->Usuario->VendedorId ?? '';
        }
        if ($this->Empleado) {
            return $this->Empleado->VendedorId ?? '';
        }
        return '';
    }
    
    /**
     * Convertir a array
     */
    public function toArray(): array
    {
        return [
            'LiberalidadDetalleId' => $this->LiberalidadDetalleId,
            'LiberalidadId' => $this->LiberalidadId,
            'UsuarioId' => $this->UsuarioId,
            'EmpleadoId' => $this->EmpleadoId,
            'Estatus' => $this->Estatus,
            'EsVendedor' => $this->EsVendedor,
            'Unidades' => $this->Unidades,
            'SaldoFavor' => $this->SaldoFavor,
            'Venta' => $this->Venta,
            'MontoLiberalidad' => $this->MontoLiberalidad,
            'AbonoPrestamo' => $this->AbonoPrestamo,
            'OtraLiberalidad' => $this->OtraLiberalidad,
            'DeudaPrestamo' => $this->DeudaPrestamo,
            'PagoPrestamo' => $this->PagoPrestamo,
            'TotalPagado' => $this->TotalPagado,
            'Pago' => $this->Pago,
            'Motivo' => $this->Motivo,
            'Disponible' => $this->getDisponible(),
            'NombreCompleto' => $this->getNombreCompleto(),
            'VendedorId' => $this->getVendedorId()
        ];
    }
}