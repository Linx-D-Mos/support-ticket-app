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
    public function destroy(File $file, FileService $service)
    {
        $file->load('fileable');
        $this->authorize('delete', $file);
        $service->delete($file);
        $file->delete();
        return response()
            ->noContent();
    }
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
