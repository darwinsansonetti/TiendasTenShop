<?php

namespace App\Http\Controllers;

use App\Models\Publicidad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PublicidadController extends Controller
{
    public function index()
    {
        $publicidades = Publicidad::orderBy('Id', 'desc')->get();
        session(['menu_active' => 'Diseno', 'submenu_active' => 'Publicidad']);
        return view('cpanel.publicidad.index', compact('publicidades'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'imagen' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'fecha_ini' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_ini',
        ]);

        try {
            $fileName = null;

            if ($request->hasFile('imagen')) {
                $imagen = $request->file('imagen');
                $extension = $imagen->getClientOriginalExtension();
                $fileName = 'publicidad_' . time() . '_' . uniqid() . '.' . $extension;

                // ✅ DETECTAR ENTORNO: Si es producción, mover a public. Si es local, usar storage.
                if (app()->environment() === 'production') {
                    // En SmarterASP se guarda físicamente en public/images/items/thumbs/
                    $imagen->move(public_path('images/items/thumbs'), $fileName);
                } else {
                    // En local se guarda en storage/app/public/images/items/thumbs/
                    $folder = 'public/images/items/thumbs/';
                    if (!Storage::exists($folder)) {
                        Storage::makeDirectory($folder, 0775, true);
                    }
                    Storage::putFileAs($folder, $imagen, $fileName);
                }
            }

            Publicidad::create([
                'UrlFoto' => $fileName,
                'EsActivo' => $request->has('es_activo') ? 1 : 0,
                'FechaIni' => $request->fecha_ini,
                'FechaFin' => $request->fecha_fin,
            ]);

            return redirect()->back()->with('success', 'Publicidad creada correctamente.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al crear la publicidad: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $publicidad = Publicidad::findOrFail($id);
        $request->validate([
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'fecha_ini' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_ini',
        ]);

        try {
            $data = [
                'FechaIni' => $request->fecha_ini,
                'FechaFin' => $request->fecha_fin,
            ];

            if ($request->hasFile('imagen')) {
                $imagen = $request->file('imagen');
                $extension = $imagen->getClientOriginalExtension();
                $fileName = 'publicidad_' . time() . '_' . uniqid() . '.' . $extension;

                if (app()->environment() === 'production') {
                    // Eliminar imagen anterior
                    if ($publicidad->UrlFoto) {
                        $oldFile = public_path('images/items/thumbs/' . $publicidad->UrlFoto);
                        if (file_exists($oldFile)) unlink($oldFile);
                    }
                    // Guardar nueva
                    $imagen->move(public_path('images/items/thumbs'), $fileName);
                } else {
                    // Eliminar imagen anterior
                    if ($publicidad->UrlFoto) {
                        $oldFile = 'public/images/items/thumbs/' . $publicidad->UrlFoto;
                        if (Storage::exists($oldFile)) Storage::delete($oldFile);
                    }
                    // Guardar nueva
                    $folder = 'public/images/items/thumbs/';
                    Storage::putFileAs($folder, $imagen, $fileName);
                }

                $data['UrlFoto'] = $fileName;
            }

            if ($request->has('es_activo') && !$publicidad->EsActivo) {
                Publicidad::where('EsActivo', 1)->update(['EsActivo' => 0]);
                $data['EsActivo'] = 1;
            } elseif (!$request->has('es_activo') && $publicidad->EsActivo) {
                $data['EsActivo'] = 0;
            }

            $publicidad->update($data);
            return redirect()->back()->with('success', 'Publicidad actualizada correctamente.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al actualizar: ' . $e->getMessage());
        }
    }

    public function toggle($id)
    {
        try {
            $publicidad = Publicidad::findOrFail($id);
            Publicidad::where('EsActivo', 1)->update(['EsActivo' => 0]);
            $nuevoEstado = $publicidad->EsActivo ? 0 : 1;
            $publicidad->update(['EsActivo' => $nuevoEstado]);

            return response()->json([
                'success' => true, 
                'message' => $nuevoEstado ? 'Publicidad activada' : 'Publicidad desactivada',
                'estado' => $nuevoEstado
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al cambiar el estado'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $publicidad = Publicidad::findOrFail($id);
            
            if ($publicidad->UrlFoto) {
                $file = 'public/images/items/thumbs/' . $publicidad->UrlFoto;
                if (Storage::exists($file)) {
                    Storage::delete($file);
                }
            }

            $publicidad->delete();
            return redirect()->back()->with('success', 'Publicidad eliminada correctamente.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al eliminar: ' . $e->getMessage());
        }
    }
}