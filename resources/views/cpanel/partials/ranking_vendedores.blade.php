@php
    use App\Helpers\FileHelper;
@endphp

@if($rankingVendedores->isNotEmpty())
  @php
    $primerLugar  = $rankingVendedores->first();
    $otrosLugares = $rankingVendedores->skip(1);

    $fotoPerfil = $primerLugar->Vendedor['FotoPerfil'] ?? '';
    $imgSrc     = FileHelper::getOrDownloadFile(
        'images/usuarios/',
        $fotoPerfil,
        'assets/img/adminlte/img/default.png'
    );

    $maxVentas = $rankingVendedores->max('total_ventas') ?: 1;
  @endphp

  {{-- #1 Destacado --}}
  <div class="position-relative p-4 text-center overflow-hidden"
       style="background: linear-gradient(135deg, #064e3b 0%, #065f46 55%, #047857 100%);">
    {{-- Línea brillo superior --}}
    <div class="position-absolute top-0 start-0 end-0"
         style="height:2px; background:linear-gradient(90deg,transparent,rgba(255,255,255,.35),transparent);"></div>

    {{-- Trofeo --}}
    <div class="mb-2">
      <i class="bi bi-trophy-fill" style="font-size:1.5rem; color:#fbbf24;"></i>
    </div>

    {{-- Avatar con badge de posición --}}
    <div class="position-relative d-inline-block mb-2">
      <img src="{{ $imgSrc }}"
           alt="{{ $primerLugar->Vendedor['NombreCompleto'] ?? 'N/A' }}"
           class="rounded-circle"
           style="width:76px; height:76px; object-fit:cover;
                  border:3px solid #fbbf24; box-shadow:0 0 0 4px rgba(251,191,36,.25);">
      <span class="position-absolute bottom-0 end-0 d-flex align-items-center justify-content-center
                   rounded-circle fw-bold text-dark border border-2 border-white"
            style="width:22px; height:22px; font-size:10px; background:#fbbf24;">1</span>
    </div>

    <div class="text-warning fw-bold mb-1" style="font-size:10px; letter-spacing:.1em; text-transform:uppercase;">
      Mejor Vendedor
    </div>
    <div class="text-white fw-bold lh-sm" style="font-size:15px;">
      {{ $primerLugar->Vendedor['NombreCompleto'] ?? 'N/A' }}
    </div>
    <div class="mt-1" style="font-size:12px; color:rgba(255,255,255,.65);">
      <i class="bi bi-shop me-1"></i>{{ $primerLugar->SucursalNombre ?? 'N/A' }}
    </div>

    {{-- Stats --}}
    <div class="d-flex justify-content-center gap-4 mt-3 pt-3"
         style="border-top:1px solid rgba(255,255,255,.15);">
      <div class="text-center">
        <div class="text-white fw-bold" style="font-size:17px;">
          {{ number_format($primerLugar->total_unidades ?? 0) }}
        </div>
        <div style="font-size:10px; color:rgba(255,255,255,.55); text-transform:uppercase; letter-spacing:.06em;">
          Unidades
        </div>
      </div>
      <div style="width:1px; background:rgba(255,255,255,.2);"></div>
      <div class="text-center">
        <div class="text-white fw-bold" style="font-size:17px;">
          ${{ number_format($primerLugar->total_ventas ?? 0, 0, '.', ',') }}
        </div>
        <div style="font-size:10px; color:rgba(255,255,255,.55); text-transform:uppercase; letter-spacing:.06em;">
          Total Ventas
        </div>
      </div>
    </div>
  </div>

  {{-- 2do y 3er lugar --}}
  @if($otrosLugares->isNotEmpty())
    <table class="table table-borderless mb-0 align-middle">
      <thead>
        <tr style="background:#f8fafc; border-bottom:1px solid #e2e8f0;">
          <th class="ps-3 py-2 text-center text-muted fw-semibold" style="width:36px; font-size:11px; letter-spacing:.06em;">#</th>
          <th class="py-2 text-muted fw-semibold" style="font-size:11px; letter-spacing:.06em;">VENDEDOR</th>
          <th class="py-2 text-center text-muted fw-semibold" style="width:55px; font-size:11px; letter-spacing:.06em;">UNID.</th>
          <th class="pe-3 py-2 text-muted fw-semibold" style="width:120px; font-size:11px; letter-spacing:.06em;">VENTAS</th>
        </tr>
      </thead>
      <tbody>
        @foreach($otrosLugares as $vendedor)
          @php
            $rankPos = $loop->index + 2;
            $fotoV   = $vendedor->Vendedor['FotoPerfil'] ?? '';
            $imgV    = FileHelper::getOrDownloadFile('images/usuarios/', $fotoV, 'assets/img/adminlte/img/default.png');
            $pct     = $maxVentas > 0 ? round(($vendedor->total_ventas / $maxVentas) * 100) : 0;
          @endphp
          <tr class="border-bottom">
            <td class="ps-3 py-2 text-center">
              <span class="fw-bold text-muted" style="font-size:13px;">{{ $rankPos }}</span>
            </td>
            <td class="py-2">
              <div class="d-flex align-items-center gap-2">
                <img src="{{ $imgV }}"
                     alt="{{ $vendedor->Vendedor['NombreCompleto'] ?? '' }}"
                     class="rounded-circle flex-shrink-0"
                     style="width:32px; height:32px; object-fit:cover; border:2px solid #e2e8f0;">
                <div style="min-width:0;">
                  <div class="fw-semibold text-dark text-truncate" style="font-size:12px; line-height:1.3;">
                    {{ $vendedor->Vendedor['NombreCompleto'] ?? 'N/A' }}
                  </div>
                  <div class="text-muted text-truncate" style="font-size:11px;">
                    {{ $vendedor->SucursalNombre ?? 'N/A' }}
                  </div>
                </div>
              </div>
            </td>
            <td class="py-2 text-center">
              <span class="fw-semibold text-dark" style="font-size:12px;">
                {{ number_format($vendedor->total_unidades ?? 0) }}
              </span>
            </td>
            <td class="pe-3 py-2">
              <div class="fw-semibold text-dark mb-1" style="font-size:12px;">
                ${{ number_format($vendedor->total_ventas ?? 0, 0, '.', ',') }}
              </div>
              <div style="height:4px; background:#e2e8f0; border-radius:99px; overflow:hidden;">
                <div style="width:{{ $pct }}%; height:100%; border-radius:99px; background:#198754; transition:width .6s ease;"></div>
              </div>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  @endif

  <div class="p-3 text-center" style="border-top:1px solid #f1f5f9;">
    <a href="{{ route('cpanel.empleados.ranking') }}" class="btn btn-outline-success btn-sm px-4">
      <i class="bi bi-list-ol me-1"></i>Ver ranking completo
    </a>
  </div>

@else
  <div class="d-flex flex-column align-items-center justify-content-center text-muted py-5">
    <i class="bi bi-people" style="font-size:2.5rem; opacity:.25;"></i>
    <p class="mt-3 mb-1 fw-semibold">Sin datos disponibles</p>
    <small>Selecciona otro rango de fechas</small>
  </div>
@endif
