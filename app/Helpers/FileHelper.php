<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

class FileHelper
{
    /**
     * Obtiene o descarga un archivo desde el servidor .NET, o devuelve un default genérico.
     *
     * @param string $folder Carpeta interna: "images/usuarios/"
     * @param string|null $filename Nombre del archivo: "VDD01_2.png"
     * @param string|null $defaultFile Archivo por defecto (si no existe). Si es null → retornar false.
     * @return string|false URL pública o false si no existe
     */
    public static function getOrDownloadFile($folder, $filename, $defaultFile = null)
    {
        // Si no viene archivo y tampoco hay default → archivo no existe
        if (empty($filename)) {
            return empty($defaultFile)
                ? false
                : asset($defaultFile);
        }

        // Normalizar carpeta
        $folder = trim($folder, '/') . '/';

        // Ruta interna en storage (respetando estructura .NET)
        $storagePath = 'public/' . $folder;

        // Crear carpeta si no existe
        if (!Storage::exists($storagePath)) {
            Storage::makeDirectory($storagePath, 0775, true);
        }

        $internalFile = $storagePath . $filename;

        // Si existe localmente → devolver URL
        if (Storage::exists($internalFile)) {
            return asset('storage/' . $folder . $filename);
        }

        // URL remota del servidor original
        $remoteUrl = "https://tiendastenshop.com/{$folder}{$filename}";

        // Intentar descargar desde .NET
        try {
            $response = Http::timeout(5)->get($remoteUrl);

            if ($response->successful()) {
                Storage::put($internalFile, $response->body());
                return asset('storage/' . $folder . $filename);
            }
        } catch (\Exception $e) {
            // Ignorar y pasar a default/false
        }

        // No existe ni en storage ni en .NET → revisar default
        return empty($defaultFile)
            ? false
            : asset($defaultFile);
    }
}
