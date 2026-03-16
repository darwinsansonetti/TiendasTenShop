<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class EjecutarProcesoDiario extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:ejecutar-proceso-diario';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando proceso desde comando...');
        
        try {
            // Crear instancia del controlador (NO usar ::)
            $controlador = new \App\Http\Controllers\ContabilidadController();
            
            // Llamar al método de instancia (con ->)
            $resultado = $controlador->cerrar_dia_automaticamente();
            
            $this->info('Proceso completado: ' . $resultado);
            
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            \Log::error("Error en comando: " . $e->getMessage());
        }
        
        return 0;
    }
}
