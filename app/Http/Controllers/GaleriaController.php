<?php

namespace App\Http\Controllers;

use App\Models\Galeria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GaleriaController extends Controller
{
    public function index()
    {
        $galerias = Galeria::orderBy('Id', 'desc')->get();
        session(['menu_active' => 'Diseno', 'submenu_active' => 'Galeria']);
        return view('cpanel.galeria.index', compact('galerias'));
    }

        public function store(Request $request)
    {
        $request->validate(['imagen' => 'required|image|mimes:jpeg,png,jpg|max:4096']);

        try {
            $fileName = null;

            if ($request->hasFile('imagen')) {
                $imagen = $request->file('imagen');
                $extension = $imagen->getClientOriginalExtension();
                $fileName = 'galeria_' . time() . '_' . uniqid() . '.' . $extension;

                if (app()->environment() === 'production') {
                    $imagen->move(public_path('images/items/thumbs'), $fileName);
                } else {
                    $folder = 'public/images/items/thumbs/';
                    if (!Storage::exists($folder)) Storage::makeDirectory($folder, 0775, true);
                    Storage::putFileAs($folder, $imagen, $fileName);
                }
            }

            Galeria::create([
                'UrlFoto' => $fileName,
                'EsActivo' => $request->has('es_activo') ? 1 : 0,
            ]);

            return redirect()->back()->with('success', 'Imagen agregada correctamente.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al agregar la imagen: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $galeria = Galeria::findOrFail($id);
        $request->validate(['imagen' => 'nullable|image|mimes:jpeg,png,jpg|max:4096']);

        try {
            $data = [];

            if ($request->hasFile('imagen')) {
                $imagen = $request->file('imagen');
                $extension = $imagen->getClientOriginalExtension();
                $fileName = 'galeria_' . time() . '_' . uniqid() . '.' . $extension;

                if (app()->environment() === 'production') {
                    if ($galeria->UrlFoto) {
                        $oldFile = public_path('images/items/thumbs/' . $galeria->UrlFoto);
                        if (file_exists($oldFile)) unlink($oldFile);
                    }
                    $imagen->move(public_path('images/items/thumbs'), $fileName);
                } else {
                    if ($galeria->UrlFoto) {
                        $oldFile = 'public/images/items/thumbs/' . $galeria->UrlFoto;
                        if (Storage::exists($oldFile)) Storage::delete($oldFile);
                    }
                    $folder = 'public/images/items/thumbs/';
                    Storage::putFileAs($folder, $imagen, $fileName);
                }

                $data['UrlFoto'] = $fileName;
            }

            $data['EsActivo'] = $request->has('es_activo') ? 1 : 0;
            $galeria->update($data);
            return redirect()->back()->with('success', 'Imagen actualizada correctamente.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al actualizar: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $galeria = Galeria::findOrFail($id);
            
            if ($galeria->UrlFoto) {
                if (app()->environment() === 'production') {
                    $file = public_path('images/items/thumbs/' . $galeria->UrlFoto);
                    if (file_exists($file)) unlink($file);
                } else {
                    $file = 'public/images/items/thumbs/' . $galeria->UrlFoto;
                    if (Storage::exists($file)) Storage::delete($file);
                }
            }

            $galeria->delete();
            return redirect()->back()->with('success', 'Imagen eliminada correctamente.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al eliminar: ' . $e->getMessage());
        }
    }

    public function toggle($id)
    {
        try {
            $galeria = Galeria::findOrFail($id);
            $nuevoEstado = $galeria->EsActivo ? 0 : 1;
            $galeria->update(['EsActivo' => $nuevoEstado]);

            return response()->json([
                'success' => true, 
                'message' => $nuevoEstado ? 'Imagen activada' : 'Imagen desactivada',
                'estado' => $nuevoEstado
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al cambiar el estado'], 500);
        }
    }
}