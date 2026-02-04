<?php

namespace App\Services;

use App\Models\File;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class FileService
{
    public function storeFile(Model $model, UploadedFile|array|null $files, string $folder = 'attachments', string $disk = 'public'): Model
    {
        if (!$files) {
            return $model;
        }
        $files = is_array($files) ? $files : [$files];
        foreach ($files as $file) {
            $path = $file->store($folder,$disk);
            $model->files()->create(['file_path' => $path]);
        }
        return $model->load('files');
    }
    public function downloadFile(File $file){
        return URL::temporarySignedRoute(
            'files.download',
            Carbon::now()->addMinutes(30),
            ['path' => $file->file_path],
        );
    }
    public function delete(File $file){
         if(Storage::disk('public')->exists($file->file_path)){
            Storage::disk('public')->delete($file->file_path);
         }
    }
}
