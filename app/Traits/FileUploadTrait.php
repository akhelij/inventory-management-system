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
        try {
            // Generate a unique filename
            $filename = time() . '_' . uniqid() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
            $uploadPath = public_path('storage/' . $directory);

            // Create directory if it doesn't exist
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            // Move the file
            $file->move($uploadPath, $filename);
            
            return $directory . '/' . $filename;
        } catch (\Exception $e) {
            // Log the error
            \Illuminate\Support\Facades\Log::error('File upload error: ' . $e->getMessage());
            
            // Return a default path or throw an exception
            return 'uploads/error.png';
        }
    }

    /**
     * Delete a file from storage
     */
    protected function deleteFile($path)
    {
        if ($path && Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->delete($path);
        }
        
        return false;
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
