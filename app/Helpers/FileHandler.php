<?php

namespace App\Helpers;

use Illuminate\Support\Facades\File;
use Exception;
use URL;
use Log;

class FileHandler
{
    public function handle($file, $folder, $action)
    {
        if($action === 'save'){
            return $this->saveFormFile($folder, $file);
        }else if($action === 'delete'){
            return $this->deleteFile($file);
        }
    }

    public function saveFormFile($folder, $file)
    {
        $originalName = $file->getClientOriginalName();
        $mimeType = $file->getMimeType();
        $type = $file->extension();
        $fileSize = $file->getSize();

        $file = file_get_contents($file->path());
        $filename = bin2hex(random_bytes(16));
        $relativePath = self::saveToPath($folder, $filename, $type, $file);

        return [
            'originalName' => $originalName,
            'mimeType' => $mimeType,
            'fileSize' => $fileSize,
            'relativePath' => $relativePath,
            'fileUrl' => URL::to($relativePath)
        ];
    }

    protected static function saveToPath($folder, $filename, $ext, $file)
    {
        $dir = $folder.'/';
        $fileHash = $filename . '.' . $ext;
        $absolutePath = public_path($dir);
        $relativePath = $dir . $fileHash;
        if (!File::exists($absolutePath)) {
            File::makeDirectory($absolutePath, 0755, true);
        }
        file_put_contents($relativePath, $file);
        return $relativePath;
    }

    public function deleteFile($relativePath): void
    {
        File::delete(public_path($relativePath));
    }
}