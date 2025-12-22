<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CacheCleanerHelper
{
    /**
     * Limpieza dinámica de cache basada en claves.
     *
     * @param array $keys Array con las claves exactas de cache a borrar
     */
    public static function limpiarCacheDashboard(array $keys = []): void
    {
        if (empty($keys)) {
            Log::warning('[CacheCleaner] No se recibieron claves para limpiar.');
            return;
        }

        foreach ($keys as $key) {
            if (Cache::has($key)) {
                Cache::forget($key);
                Log::info("[CacheCleaner] Cache eliminado: {$key}");
            } else {
                Log::info("[CacheCleaner] Cache no existe: {$key}");
            }
        }
    }

    /**
     * Limpiar todo el cache del sistema
     */
    public static function flushCacheTotal(): void
    {
        Cache::flush();
        Log::warning('[CacheCleaner] CACHE COMPLETO limpiado manualmente.');
    }
}