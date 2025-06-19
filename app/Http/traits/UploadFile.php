<?php
namespace App\Http\Traits;

use Illuminate\Support\Facades\File;
use Illuminate\Http\UploadedFile;

trait UploadFile
{
    private function UploadImage(UploadedFile $image, string $path): ?string
    {
        if (!in_array($image->getClientMimeType(), ['image/jpeg', 'image/png', 'image/gif', 'image/webp'])) {
            return null;
        }

        $fullPath = public_path($path);
        if (!File::exists($fullPath)) {
            File::makeDirectory($fullPath, 0755, true);
        }

        $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();

        $image->move($fullPath, $filename);

        return $filename;
    }

    private function DeleteImage(?string $filename, string $path): bool
    {
        if (!$filename) {
            return false;
        }

        $fullPath = public_path(trim($path, '/') . '/' . $filename);

        if (File::exists($fullPath) && File::isFile($fullPath)) {
            try {
                File::delete($fullPath);
                return true;
            } catch (\Exception $e) {
                return false;
            }
        }

        return false;
    }
}
