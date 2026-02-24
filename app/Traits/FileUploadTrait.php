<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

trait FileUploadTrait
{
    protected function uploadFile(UploadedFile $file, string $directory = 'uploads'): string
    {
        try {
            $filename = time().'_'.uniqid().'_'.preg_replace('/\s+/', '_', $file->getClientOriginalName());
            $uploadPath = public_path('storage/'.$directory);

            if (! file_exists($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            $file->move($uploadPath, $filename);

            return $directory.'/'.$filename;
        } catch (\Exception $e) {
            Log::error('File upload error: '.$e->getMessage());

            return 'uploads/error.png';
        }
    }

    protected function deleteFile(?string $path): bool
    {
        if ($path && Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->delete($path);
        }

        return false;
    }

    protected function uploadMultipleFiles(array $files, string $directory = 'uploads'): array
    {
        $paths = [];

        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $paths[] = $this->uploadFile($file, $directory);
            }
        }

        return $paths;
    }
}
