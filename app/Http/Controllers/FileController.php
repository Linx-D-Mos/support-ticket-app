<?php

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class FileController extends Controller
{

    /**
     * Remove the specified resource from storage.
     */
    use AuthorizesRequests;
    public function destroy(File $file)
    {
        $file->load('fileable');
        $this->authorize('delete', $file);
        $file->delete();
        return response()
            ->noContent();
    }
}
