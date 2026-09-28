<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CierreBovedaMail extends Mailable
{
    use Queueable, SerializesModels;

    public $boveda;
    public $denominacionesDivisa;
    public $denominacionesBs;
    public $conciliacionPDV;
    public $conciliacionOtros;
    public $conciliacionEfectivo;
    public $totales;

    public function __construct(
        $boveda,
        $denominacionesDivisa,
        $denominacionesBs,
        $conciliacionPDV,
        $conciliacionOtros,
        $conciliacionEfectivo,
        $totales
    ) {
        $this->boveda = $boveda;
        $this->denominacionesDivisa = $denominacionesDivisa;
        $this->denominacionesBs = $denominacionesBs;
        $this->conciliacionPDV = $conciliacionPDV;
        $this->conciliacionOtros = $conciliacionOtros;
        $this->conciliacionEfectivo = $conciliacionEfectivo;
        $this->totales = $totales;
    }

    public function build()
    {
        $fecha = \Carbon\Carbon::parse($this->boveda->Fecha)->format('d/m/Y');

        return $this->subject('Cierre de Bóveda #' . $this->boveda->BovedaId . ' - ' . $fecha)
                    ->view('emails.cierre_boveda');
    }
}