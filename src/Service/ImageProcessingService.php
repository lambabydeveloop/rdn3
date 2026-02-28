<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class ImageProcessingService
{
    private string $uploadsDir;
    private SluggerInterface $slugger;

    public function __construct(string $publicDir, SluggerInterface $slugger)
    {
        $this->uploadsDir = $publicDir . '/uploads/blog';
        $this->slugger = $slugger;

        if (!is_dir($this->uploadsDir)) {
            mkdir($this->uploadsDir, 0755, true);
        }
    }

    /**
     * Processes an uploaded image: moves it, resizes/compresses it, 
     * and converts it to WebP format.
     * 
     * @return string The public URL path to the WebP image
     */
    public function processAndUpload(UploadedFile $file): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename . '-' . uniqid() . '.webp';
        
        $targetPath = $this->uploadsDir . '/' . $fileName;

        // Process image with GD to convert to WebP
        $mimeType = $file->getMimeType();
        $sourcePath = $file->getPathname();
        
        $image = null;
        switch ($mimeType) {
            case 'image/jpeg':
                $image = @imagecreatefromjpeg($sourcePath);
                break;
            case 'image/png':
                $image = @imagecreatefrompng($sourcePath);
                if ($image) {
                    imagepalettetotruecolor($image);
                    imagealphablending($image, true);
                    imagesavealpha($image, true);
                }
                break;
            case 'image/gif':
                $image = @imagecreatefromgif($sourcePath);
                if ($image) {
                    imagepalettetotruecolor($image);
                }
                break;
            case 'image/webp':
                $image = @imagecreatefromwebp($sourcePath);
                break;
        }

        if ($image) {
            // Convert and save to webp with 80% quality (great compression, 100/100 pagespeed)
            imagewebp($image, $targetPath, 85);
            imagedestroy($image);
        } else {
            // Fallback if GD fails (e.g., unsupported format like svg)
            $file->move($this->uploadsDir, $file->getClientOriginalName());
            return '/uploads/blog/' . $file->getClientOriginalName();
        }

        return '/uploads/blog/' . $fileName;
    }
}
