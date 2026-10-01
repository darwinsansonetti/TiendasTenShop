@extends('layout.layout_dashboard')

@section('title', 'Verificación Pago Móvil')

@php
    $hdrBg = 'linear-gradient(135deg,#8b5cf6 0%,#7c3aed 100%)';
    $hdrIcon = 'credit-card';
    $hdrTitle = 'Verificación Pago Móvil';
    $hdrSubtitle = 'Pagos verificados en el Banco de Venezuela';
@endphp

@section('content')

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <div class="d-flex align-items-center gap-2">
                    <div class="d-flex align-items-center justify-content-center rounded-2 me-1"
                         style="width:36px;height:36px;background:{{ $hdrBg }};">
                        <i class="bi bi-{{ $hdrIcon }} text-white" style="font-size:1.1rem;"></i>
                    </div>
                    <div>
                        <h4 class="mb-0 fw-bold text-dark" style="font-size:1.1rem;">{{ $hdrTitle }}</h4>
                        <p class="mb-0 text-muted" style="font-size:0.78rem;">{{ $hdrSubtitle }}</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item active">Verificación Pago Móvil</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle me-1"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-triangle me-1"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Tarjetas resumen --}}
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100" style="background:linear-gradient(135deg,#10b981 0%,#059669 100%);">
                    <div class="card-body text-white">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="mb-1 text-white-50" style="font-size:0.75rem;">TOTAL PAGOS VERIFICADOS</p>
                                <h3 class="fw-bold mb-0">{{ number_format($totalPagos, 0) }}</h3>
                            </div>
                            <i class="bi bi-receipt" style="font-size:2.5rem;opacity:0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100" style="background:linear-gradient(135deg,#3b82f6 0%,#1d4ed8 100%);">
                    <div class="card-body text-white">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="mb-1 text-white-50" style="font-size:0.75rem;">MONTO TOTAL</p>
                                <h3 class="fw-bold mb-0">VES {{ number_format($totalMonto, 2) }}</h3>
                            </div>
                            <i class="bi bi-cash-stack" style="font-size:2.5rem;opacity:0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabla --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header border-0 py-3" style="background:{{ $hdrBg }};">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <h6 class="mb-0 fw-bold text-white">
                        <i class="bi bi-list-ul me-2"></i>Pagos Verificados
                    </h6>
                    <div class="d-flex gap-2">
                        <a href="{{ route('cpanel.pago.movil.form') }}" class="btn btn-light btn-sm fw-semibold">
                            <i class="bi bi-plus-lg me-1"></i>Verificar Pago
                        </a>
                        <button type="button" class="btn btn-success btn-sm fw-semibold" onclick="exportarExcel()">
                            <i class="bi bi-file-earmark-excel me-1"></i>Excel
                        </button>
                        <button type="button" class="btn btn-danger btn-sm fw-semibold" onclick="exportarPDF()">
                            <i class="bi bi-file-earmark-pdf me-1"></i>PDF
                        </button>
                    </div>
                </div>
            </div>

            {{-- Filtros --}}
            <div class="card-body py-3 border-bottom">
                <form method="GET" action="{{ route('cpanel.pago.movil.index') }}" class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold" style="font-size:0.8rem;">Fecha Inicio</label>
                        <input type="date" name="fecha_inicio" class="form-control form-control-sm"
                               value="{{ $fechaInicio }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold" style="font-size:0.8rem;">Fecha Fin</label>
                        <input type="date" name="fecha_fin" class="form-control form-control-sm"
                               value="{{ $fechaFin }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold" style="font-size:0.8rem;">Sucursal</label>
                        <select name="sucursal_id" class="form-select form-select-sm">
                            <option value="">Todas</option>
                            @foreach($sucursales as $suc)
                                <option value="{{ $suc->ID }}" {{ $sucursalId == $suc->ID ? 'selected' : '' }}>
                                    {{ $suc->Nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-sm text-white" style="background:{{ $hdrBg }};border:none;">
                            <i class="bi bi-search me-1"></i>Filtrar
                        </button>
                        <a href="{{ route('cpanel.pago.movil.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    </div>
                </form>
            </div>

            {{-- Tabla --}}
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="tablaPagos">
                        <thead style="background:#f8fafc;">
                            <tr>
                                <th class="ps-4 py-3 text-muted fw-semibold" style="font-size:0.75rem;">REFERENCIA</th>
                                <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;">FECHA PAGO</th>
                                <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;">PAGADOR</th>
                                <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;">BANCO ORIGEN</th>
                                <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;">MONTO</th>
                                <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;">SUCURSAL</th>
                                <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;">FECHA VERIF.</th>
                                <th class="pe-4 py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;">ACCIÓN</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pagos as $p)
                            <tr>
                                <td class="ps-4">
                                    <code class="px-2 py-1 rounded-2" style="background:#f1f5f9;color:#7c3aed;font-size:0.75rem;font-weight:bold;">
                                        {{ $p->Referencia }}
                                    </code>
                                </td>
                                <td>{{ $p->FechaPagoFormateada }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $p->CedulaPagador }}</div>
                                    <small class="text-muted">{{ $p->TelefonoPagador }}</small>
                                </td>
                                <td>
                                    <div>{{ $p->BancoOrigen }}</div>
                                    <small class="text-muted">{{ $p->BancoOrigenNombre }}</small>
                                </td>
                                <td class="text-end fw-bold text-success">
                                    VES {{ number_format($p->Importe, 2) }}
                                </td>
                                <td>
                                    <div>{{ $p->sucursal_nombre }}</div>
                                    @if($p->alias_pago_movil)
                                        <small class="text-muted">{{ $p->alias_pago_movil }}</small>
                                    @endif
                                </td>
                                <td>
                                    <small>{{ $p->FechaVerificacionFormateada }}</small>
                                </td>
                                <td class="pe-4 text-center">
                                    <a href="{{ route('cpanel.pago.movil.detalle', $p->PagoMovilId) }}"
                                       class="btn btn-sm"
                                       style="width:30px;height:30px;background:rgba(20,184,166,0.1);color:#0d9488;border:1px solid rgba(20,184,166,0.25);"
                                       title="Ver detalle">
                                        <i class="bi bi-eye" style="font-size:0.75rem;"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    No hay pagos verificados en el rango seleccionado
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

{{-- Datos para exportación --}}
@php
    $exportData = [
        'meta' => [
            'fechaInicio' => \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y'),
            'fechaFin'    => \Carbon\Carbon::parse($fechaFin)->format('d/m/Y'),
            'sucursal'    => $sucursalId != ''
                            ? ($sucursales->where('ID', $sucursalId)->first()->Nombre ?? 'Todas')
                            : 'Todas',
            'generado'    => now()->format('d/m/Y H:i'),
        ],
        'pagos' => $pagos->map(fn($p) => [
            'Referencia'         => $p->Referencia,
            'Fecha Pago'         => $p->FechaPagoFormateada,
            'Cédula Pagador'     => $p->CedulaPagador,
            'Teléfono Pagador'   => $p->TelefonoPagador,
            'Banco Origen'       => $p->BancoOrigen . ' - ' . $p->BancoOrigenNombre,
            'Monto'              => (float) $p->Importe,
            'Sucursal'           => $p->sucursal_nombre,
            'Alias Pago Móvil'   => $p->alias_pago_movil,
            'Fecha Verificación' => $p->FechaVerificacionFormateada,
        ])->values(),
    ];
@endphp

<script>
    window.PAGOS_MOVILES_DATA = @json($exportData);
</script>

@endsection

@section('js')
<script src="https://cdn.sheetjs.com/xlsx-0.20.2/package/dist/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function exportarExcel() {
    try {
        const D = window.PAGOS_MOVILES_DATA;
        if (!D || !D.pagos.length) {
            Swal.fire({ icon: 'warning', title: 'Sin datos', text: 'No hay pagos para exportar' });
            return;
        }
        const wb = XLSX.utils.book_new();

        const resumen = [
            ['PAGOS MÓVILES VERIFICADOS'],
            ['Desde', D.meta.fechaInicio, '', 'Hasta', D.meta.fechaFin],
            ['Sucursal', D.meta.sucursal],
            ['Generado', D.meta.generado],
        ];
        const wsResumen = XLSX.utils.aoa_to_sheet(resumen);
        wsResumen['!cols'] = [{ wch: 25 }, { wch: 18 }, { wch: 5 }, { wch: 18 }, { wch: 18 }];
        XLSX.utils.book_append_sheet(wb, wsResumen, 'RESUMEN');

        const ws = XLSX.utils.json_to_sheet(D.pagos);
        ws['!cols'] = [
            { wch: 14 }, { wch: 12 }, { wch: 16 }, { wch: 16 },
            { wch: 28 }, { wch: 12 }, { wch: 22 }, { wch: 18 }, { wch: 18 }
        ];
        XLSX.utils.book_append_sheet(wb, ws, 'PAGOS');

        const fecha = new Date().toISOString().slice(0, 10);
        XLSX.writeFile(wb, `pagos_moviles_${fecha}.xlsx`);

        Swal.fire({ icon: 'success', title: 'Excel generado', timer: 2000, showConfirmButton: false });
    } catch (e) {
        Swal.fire({ icon: 'error', title: 'Error', text: e.message });
    }
}

function exportarPDF() {
    try {
        const D = window.PAGOS_MOVILES_DATA;
        if (!D || !D.pagos.length) {
            Swal.fire({ icon: 'warning', title: 'Sin datos', text: 'No hay pagos para exportar' });
            return;
        }
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('landscape', 'mm', 'a4');

        doc.setFontSize(14);
        doc.setTextColor(124, 58, 237);
        doc.text('PAGOS MÓVILES VERIFICADOS', 14, 15);
        doc.setFontSize(9);
        doc.setTextColor(80, 80, 80);
        doc.text(`Período: ${D.meta.fechaInicio} al ${D.meta.fechaFin}`, 14, 22);
        doc.text(`Sucursal: ${D.meta.sucursal}`, 14, 27);
        doc.text(`Generado: ${D.meta.generado}`, 14, 32);

        const headers = ['Ref', 'Fecha Pago', 'Cédula', 'Teléfono', 'Banco', 'Monto', 'Sucursal', 'Fecha Verif.'];
        const rows = D.pagos.map(p => [
            p.Referencia, p['Fecha Pago'], p['Cédula Pagador'], p['Teléfono Pagador'],
            p['Banco Origen'], p.Monto, p.Sucursal, p['Fecha Verificación']
        ]);

        doc.autoTable({
            head: [headers],
            body: rows,
            startY: 38,
            theme: 'grid',
            headStyles: { fillColor: [124, 58, 237], textColor: 255, fontSize: 8, fontStyle: 'bold' },
            bodyStyles: { fontSize: 7.5, cellPadding: 1.5 },
            alternateRowStyles: { fillColor: [245, 245, 250] },
            margin: { left: 14, right: 14 },
        });

        const totalPag = doc.internal.getNumberOfPages();
        for (let i = 1; i <= totalPag; i++) {
            doc.setPage(i);
            doc.setFontSize(7);
            doc.setTextColor(120, 120, 120);
            doc.text(`Página ${i} de ${totalPag}`, doc.internal.pageSize.width - 25, doc.internal.pageSize.height - 8);
        }

        const fecha = new Date().toISOString().slice(0, 10);
        doc.save(`pagos_moviles_${fecha}.pdf`);

        Swal.fire({ icon: 'success', title: 'PDF generado', timer: 2000, showConfirmButton: false });
    } catch (e) {
        Swal.fire({ icon: 'error', title: 'Error', text: e.message });
    }
}
</script>
@endsection

@push('styles')
<style>
    .card-header { border-radius: 8px 8px 0 0; }
</style>
@endpush