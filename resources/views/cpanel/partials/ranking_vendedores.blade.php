@php
    use App\Helpers\FileHelper;
@endphp

<div class="p-3">

    @if($rankingVendedores->isNotEmpty())
        @php

            $primerLugar = $rankingVendedores->first();
            $otrosLugares = $rankingVendedores->skip(1);

            // Nombre del archivo (puede ser '' o null)
            $fotoPerfil = $primerLugar->Vendedor['FotoPerfil'] ?? '';

            // Usamos el helper genérico
            $imgSrc = FileHelper::getOrDownloadFile(
                'images/usuarios/',                          // Carpeta
                $fotoPerfil,                                 // Archivo
                'assets/img/adminlte/img/default.png'        // Default
            );
        @endphp

        <!-- Primer Lugar Destacado -->
        <div class="text-center mb-3 p-3 border rounded shadow-sm bg-success bg-opacity-10">
            <div class="mb-2">
                <img src="{{ $imgSrc }}" 
                    alt="{{ $primerLugar->Vendedor['NombreCompleto'] ?? 'N/A' }}" 
                    class="rounded-circle border border-success" 
                    style="width: 80px; height: 80px; object-fit: cover;">
            </div>
            <div class="fw-bold text-success">Mejor Vendedor</div>
            <div class="fs-6 fw-semibold">{{ $primerLugar->Vendedor['NombreCompleto'] ?? 'N/A' }}</div>
            <div class="mt-1 small">
                <span class="me-3"><i class="fas fa-box me-1"></i>{{ $primerLugar->total_unidades ?? 0 }} unidades</span>
                <span class="me-3"><i class="fas fa-dollar-sign me-1"></i>${{ number_format($primerLugar->total_ventas ?? 0,2,'.',',') }}</span>
                <span><i class="fas fa-store me-1"></i>{{ $primerLugar->SucursalNombre ?? 'N/A' }}</span>
            </div>
        </div>

        <!-- Tabla para 2° y 3° lugar -->
        @if($otrosLugares->isNotEmpty())
            <table class="table table-hover table-borderless mb-0">
                <thead class="bg-success bg-opacity-10">
                    <tr>
                        <th class="text-center py-2" style="width: 50px;"><i class="fas fa-hashtag text-success"></i></th>
                        <th class="py-2"><i class="fas fa-user-tie me-1 text-success"></i>Vendedor</th>
                        <th class="text-center py-2" style="width: 120px;"><i class="fas fa-store me-1 text-success"></i>Sucursal</th>
                        <th class="text-center py-2" style="width: 100px;"><i class="fas fa-box me-1 text-success"></i>Unidades</th>
                        <th class="text-center py-2" style="width: 120px;"><i class="fas fa-dollar-sign me-1 text-success"></i>Total Ventas</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($otrosLugares as $index => $vendedor)
                        <tr class="border-bottom">
                            <td class="text-center py-2">{{ $index + 1 }}</td>
                            <td class="py-2">{{ $vendedor->Vendedor['NombreCompleto'] ?? 'N/A' }}</td>
                            <td class="text-center py-2">{{ $vendedor->SucursalNombre ?? 'N/A' }}</td>
                            <td class="text-center py-2">{{ $vendedor->total_unidades ?? 0 }}</td>
                            <td class="text-center py-2">${{ number_format($vendedor->total_ventas ?? 0, 2, '.', ',') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

    @else
        <div class="text-center text-muted py-4">No hay vendedores en el ranking para este rango de fechas.</div>
    @endif

    <!-- Enlace "Ver todos" -->
    <div class="mt-3 text-center">
        <a href="#" class="btn btn-outline-success btn-sm">
            <i class="fas fa-list me-1"></i>Ver todos los vendedores
        </a>
    </div>

</div>
