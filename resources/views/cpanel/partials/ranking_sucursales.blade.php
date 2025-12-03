<div class="table-responsive" style="max-height: 350px;">
    <table class="table table-hover table-borderless mb-0">
        <thead class="bg-primary bg-opacity-10">
            <tr>
                <th class="text-center py-2" style="width: 50px;"><i class="fas fa-hashtag text-primary"></i></th>
                <th class="py-2"><i class="fas fa-store me-1 text-primary"></i>Sucursal</th>
                <th class="text-center py-2" style="width: 100px;"><i class="fas fa-box me-1 text-primary"></i>Unidades</th>
                <th class="text-center py-2" style="width: 120px;"><i class="fas fa-chart-pie me-1 text-primary"></i>% Volumen</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rankingSucursales as $index => $s)
            <tr class="border-bottom">
                <td class="text-center py-2">
                    @if($index < 3)
                        <span class="badge bg-{{ ['warning', 'secondary', 'danger'][$index] }} 
                              rounded-circle d-inline-flex align-items-center justify-content-center" 
                              style="width: 28px; height: 28px;">{{ $index + 1 }}</span>
                    @else
                        <span class="text-muted fw-bold">{{ $index + 1 }}</span>
                    @endif
                </td>
                <td class="py-2">{{ $s->Sucursal }}</td>
                <td class="text-center py-2">{{ $s->Unidades }}</td>
                <td class="text-center py-2">{{ $s->PorcentajeVolumen }}%</td>
            </tr>
            @endforeach

            <!-- Fila de Totales -->
            @if($rankingSucursales->isNotEmpty())
            <tr class="bg-primary bg-opacity-5 fw-bold">
                <td class="py-2 text-center">
                    <i class="fas fa-total text-primary"></i>
                </td>
                <td class="py-2">Total General</td>
                <td class="py-2 text-center">{{ $rankingSucursales->sum('Unidades') }}</td>
                <td class="py-2 text-center">100%</td>
            </tr>
            @endif
        </tbody>
    </table>

    @if($rankingSucursales->isEmpty())
    <div class="text-center py-5 text-muted">
        <i class="fas fa-chart-bar fa-3x mb-3"></i>
        <p>No hay datos disponibles</p>
        <small>Seleccione otro rango de fechas</small>
    </div>
    @endif
</div>
