<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait FileUploadTrait
{
    /**
     * Upload a file to the specified directory
     *
     * @param UploadedFile $file
     * @param string $directory
     * @return string The path to the uploaded file
     */
    protected function uploadFile(UploadedFile $file, string $directory = 'uploads'): string
    {
        $filename = time() . '_' . $file->getClientOriginalName();
        $uploadPath = public_path('storage/' . $directory);

        // Create directory if it doesn't exist
        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        // Move the file
        $file->move($uploadPath, $filename);
        
        return $directory . '/' . $filename;
    }

    /**
     * Delete a file from storage
     *
     * @param string $path
     * @return bool
     */
    protected function deleteFile(string $path): bool
    {
        return Storage::disk('public')->delete($path);
    }

    /**
     * Upload multiple files to the specified directory
     *
     * @param array $files
     * @param string $directory
     * @return array The paths to the uploaded files
     */
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
