<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SignedStorageController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $path = $request->route('path');
        if(!Storage::disk('public')->exists($path)){
            throw new Exception('No se encontró el archivo.');
        }
        return Storage::disk('public')->download($path);
    }
}
