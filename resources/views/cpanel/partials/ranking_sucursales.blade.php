<div class="table-responsive" style="max-height:380px; overflow-y:auto;">

  @if($rankingSucursales->isNotEmpty())
  <table class="table table-borderless mb-0 align-middle">
    <thead>
      <tr style="background:#f8fafc; border-bottom:2px solid #e2e8f0; position:sticky; top:0; z-index:1;">
        <th class="ps-3 py-2 text-muted fw-semibold text-center" style="width:48px; font-size:11px; letter-spacing:.06em;">POS</th>
        <th class="py-2 text-muted fw-semibold" style="font-size:11px; letter-spacing:.06em;">SUCURSAL</th>
        <th class="py-2 text-center text-muted fw-semibold" style="width:80px; font-size:11px; letter-spacing:.06em;">UNID.</th>
        <th class="pe-3 py-2 text-muted fw-semibold" style="width:150px; font-size:11px; letter-spacing:.06em;">% VOLUMEN</th>
      </tr>
    </thead>
    <tbody>
      @foreach($rankingSucursales as $index => $s)
      @php
        $podiumColors = ['#f59e0b', '#94a3b8', '#b45309'];
        $barColors    = ['#f59e0b', '#64748b', '#b45309'];
        $isTop        = $index < 3;
      @endphp
      <tr class="border-bottom {{ $index === 0 ? 'bg-warning bg-opacity-5' : '' }}"
          style="transition: background .15s;">
        <td class="ps-3 py-3 text-center">
          @if($isTop)
            <span class="d-inline-flex align-items-center justify-content-center rounded-circle fw-bold text-white"
                  style="width:28px; height:28px; font-size:11px; background:{{ $podiumColors[$index] }};">
              {{ $index + 1 }}
            </span>
          @else
            <span class="text-muted fw-semibold" style="font-size:13px;">{{ $index + 1 }}</span>
          @endif
        </td>
        <td class="py-3">
          <span class="fw-semibold text-dark" style="font-size:13px;">{{ $s->Sucursal }}</span>
        </td>
        <td class="py-3 text-center">
          <span class="fw-semibold text-dark" style="font-size:13px;">
            {{ number_format($s->Unidades) }}
          </span>
        </td>
        <td class="pe-3 py-3">
          <div class="d-flex align-items-center gap-2">
            <div class="flex-grow-1" style="height:6px; background:#e2e8f0; border-radius:99px; overflow:hidden;">
              <div style="width:{{ $s->PorcentajeVolumen }}%; height:100%; border-radius:99px;
                          background:{{ $isTop ? $barColors[$index] : '#3b82f6' }};
                          transition: width .6s ease;"></div>
            </div>
            <span class="text-muted fw-semibold flex-shrink-0" style="font-size:11px; min-width:34px; text-align:right;">
              {{ $s->PorcentajeVolumen }}%
            </span>
          </div>
        </td>
      </tr>
      @endforeach
    </tbody>
    <tfoot>
      <tr style="background:#f8fafc; border-top:2px solid #e2e8f0;">
        <td class="ps-3 py-2 text-center">
          <i class="bi bi-sigma text-primary" style="font-size:14px;"></i>
        </td>
        <td class="py-2 fw-bold text-dark" style="font-size:13px;">Total General</td>
        <td class="py-2 text-center fw-bold text-dark" style="font-size:13px;">
          {{ number_format($rankingSucursales->sum('Unidades')) }}
        </td>
        <td class="pe-3 py-2 text-end">
          <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold px-2 py-1" style="font-size:11px;">
            100%
          </span>
        </td>
      </tr>
    </tfoot>
  </table>

  @else
  <div class="d-flex flex-column align-items-center justify-content-center text-muted py-5">
    <i class="bi bi-bar-chart-line" style="font-size:2.5rem; opacity:.25;"></i>
    <p class="mt-3 mb-1 fw-semibold">Sin datos disponibles</p>
    <small>Selecciona otro rango de fechas</small>
  </div>
  @endif

</div>
