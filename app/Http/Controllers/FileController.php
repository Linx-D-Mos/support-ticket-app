<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Services\FileService;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class FileController extends Controller
{

    /**
     * Remove the specified resource from storage.
     */
    use AuthorizesRequests;
    /**
     * Eliminar archivo.
     *
     * Elimina un archivo adjunto del sistema.
     *
     * @group Gestión de Archivos
     * @authenticated
     *
     * @urlParam file integer required El ID del archivo.
     *
     * @response 204 scenario="Eliminado con éxito" {}
     */
    public function destroy(File $file, FileService $service)
    {
        $file->load('fileable');
        $this->authorize('delete', $file);
        $service->delete($file);
        $file->delete();
        return response()
            ->noContent();
    }
    /**
     * Obtener URL de descarga.
     *
     * Genera una URL temporal firmada para descargar el archivo.
     *
     * @group Gestión de Archivos
     * @authenticated
     *
     * @urlParam file integer required El ID del archivo.
     *
     * @response 200 {
     *   "url": "http://api.example.com/signed/download/...",
     *   "message": "Url generada correctamente"
     * }
     */
    public function download(File $file, FileService $service)
    {   
        $this->authorize('download', $file);
        $url = $service->downloadFile($file);
        return response()->json([
            'url' => $url,
            'message' => 'Url generada correctamente'
        ]);
    }
}
