<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Helpers\GeneralHelper;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PDO;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // SOLUCIÓN PARA SQL SERVER - Forzar configuración segura de PDO
        $this->fixSqlServerConnection();

        // Tu código existente
        View::composer('layout.layout_dashboard', function ($view) {
            $tasa = GeneralHelper::obtenerTasaCambioDiaria(now());
            $listaSucursales = Cache::remember('lista_sucursales', 3600, function() {
                return GeneralHelper::buscarSucursales(0);
            });
            $user = Auth::user();

            $view->with(compact('tasa', 'listaSucursales', 'user'));
        });
    }

    /**
     * Solución específica para el error de atributo PDO inválido en SQL Server
     * Esta función fuerza una configuración de PDO que sabemos que funciona
     */
    private function fixSqlServerConnection(): void
    {
        // Solo aplicar si estamos usando SQL Server
        if (config('database.default') === 'sqlsrv') {
            // Registrar un callback para crear la conexión PDO de manera segura
            DB::connection('sqlsrv')->setPdo(function() {
                try {
                    // Configuración simple y segura que sabemos que funciona
                    // basado en los tests que ejecutaste
                    $dsn = sprintf(
                        "sqlsrv:Server=%s,%s;Database=%s",
                        config('database.connections.sqlsrv.host', 'SQL5110.site4now.net'),
                        config('database.connections.sqlsrv.port', 1433),
                        config('database.connections.sqlsrv.database', 'db_a509ee_calzatodo2022')
                    );
                    
                    // Solo atributos VERIFICADOS como funcionales
                    $options = [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::SQLSRV_ATTR_ENCODING => PDO::SQLSRV_ENCODING_UTF8,
                    ];
                    
                    $pdo = new PDO(
                        $dsn,
                        config('database.connections.sqlsrv.username', 'db_a509ee_calzatodo2022_admin'),
                        config('database.connections.sqlsrv.password', 'sa1QwePoi'),
                        $options
                    );
                    
                    // Log opcional para debugging (solo en desarrollo)
                    if (config('app.debug')) {
                        \Log::info('SQL Server PDO connection created successfully with safe configuration');
                    }
                    
                    return $pdo;
                    
                } catch (\PDOException $e) {
                    // Log del error detallado
                    \Log::error('Error creating SQL Server PDO connection: ' . $e->getMessage());
                    \Log::error('DSN attempted: ' . ($dsn ?? 'No DSN'));
                    
                    // Re-lanzar la excepción para que Laravel la maneje
                    throw new \Exception(
                        "Error de conexión SQL Server: " . $e->getMessage() . 
                        " [Verifica las credenciales en .env]"
                    );
                }
            });
            
            // Opcional: Configurar un reconnect handler para manejar conexiones caídas
            DB::connection('sqlsrv')->setReconnector(function ($connection) {
                $connection->setPdo(null);
                $connection->setReadPdo(null);
                $connection->reconnect();
            });
        }
    }
}