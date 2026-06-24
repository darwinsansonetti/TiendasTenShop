@extends('layout.layout_dashboard')

@section('title', 'Recibir de sucursal')

@section('content')

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">
                    <i class="bi bi-arrow-left-right me-2"></i>Recibir de sucursal
                </h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item active">Recibir de sucursal</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="bi bi-truck me-2"></i>Transferencias pendientes
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="tablaTransferencias">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Núm. Operación</th>
                                        <th>Suc. Origen</th>
                                        <th>Suc. Destino</th>
                                        <th>Items</th>
                                        <th class="text-end">Und. Enviadas</th>
                                        <th class="text-end">Und. Disp.</th>
                                        <th>Avance</th>
                                        <th>Estatus</th>
                                        <th style="width: 100px">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($transferencias as $item)
                                    @php
                                        $porcentaje = $item->PorcentajeRecibido ?? 0;
                                        if ($porcentaje < 25) $color = 'bg-danger';
                                        elseif ($porcentaje < 50) $color = 'bg-warning';
                                        elseif ($porcentaje < 75) $color = 'bg-info';
                                        else $color = 'bg-success';
                                        
                                        $estatusTexto = '';
                                        $estatusClase = '';
                                        switch($item->Estatus) {
                                            case 3:
                                                $estatusTexto = 'Registrada';
                                                $estatusClase = 'badge bg-secondary';
                                                break;
                                            case 4:
                                                $estatusTexto = 'Recibiendo';
                                                $estatusClase = 'badge bg-info';
                                                break;
                                            case 5:
                                                $estatusTexto = 'Disponible';
                                                $estatusClase = 'badge bg-success';
                                                break;
                                            default:
                                                $estatusTexto = 'Desconocido';
                                                $estatusClase = 'badge bg-dark';
                                        }
                                    @endphp
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($item->Fecha)->format('d/m/Y') }}</td>
                                        <td><strong>{{ $item->Numero }}</strong></td>
                                        <td>{{ $item->Origen ?? 'N/A' }}</td>
                                        <td>{{ $item->Destino ?? 'N/A' }}</td>
                                        <td class="text-center">{{ $item->CantidadItems ?? 0 }}</td>
                                        <td class="text-end">{{ number_format($item->CantidadEmitida ?? 0, 0) }}</td>
                                        <td class="text-end">{{ number_format($item->CantidadDisponible ?? 0, 0) }}</td>
                                        <td style="min-width: 150px;">
                                            <div class="progress mb-1" style="height: 8px;">
                                                <div class="progress-bar {{ $color }}" role="progressbar"
                                                     style="width: {{ $porcentaje }}%;"
                                                     aria-valuenow="{{ $porcentaje }}" aria-valuemin="0" aria-valuemax="100">
                                                </div>
                                            </div>
                                            <small>{{ number_format($item->CantidadRecibida ?? 0) }} Und. | {{ number_format($porcentaje, 1) }}%</small>
                                        </td>
                                        <td><span class="{{ $estatusClase }}">{{ $estatusTexto }}</span></td>
                                        <td>
                                            <a href="{{ route('cpanel.transferencias.recibir', $item->TransferenciaId) }}" 
                                               class="btn btn-sm btn-warning" title="Recibir">
                                                <i class="bi bi-arrow-down-circle"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="10" class="text-center py-4">
                                            <i class="bi bi-inbox fs-1 text-muted"></i><br>
                                            No hay transferencias pendientes
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')
<script>
    $(document).ready(function() {
        $('#tablaTransferencias').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
            },
            order: [[0, 'desc']],
            pageLength: 10
        });
    });
</script>
@endsection