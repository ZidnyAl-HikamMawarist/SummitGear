<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageOptimizer
{
    /**
     * Mengompres dan mengubah ukuran file gambar secara proporsional.
     *
     * @param string $sourcePath Path fisik file sumber
     * @param string $destinationPath Path fisik file tujuan
     * @param int $maxDimension Dimensi maksimum (lebar atau tinggi dalam pixel)
     * @param int $quality Kualitas kompresi JPEG (1-100)
     * @return bool
     */
    public static function optimizeFile(string $sourcePath, string $destinationPath, int $maxDimension = 600, int $quality = 78): bool
    {
        if (!file_exists($sourcePath)) {
            return false;
        }

        $imageInfo = @getimagesize($sourcePath);
        if (!$imageInfo) {
            return false;
        }

        [$width, $height, $type] = $imageInfo;

        // Load image resource berdasarkan tipe MIME
        $sourceImage = null;
        switch ($type) {
            case IMAGETYPE_JPEG:
                $sourceImage = @imagecreatefromjpeg($sourcePath);
                break;
            case IMAGETYPE_PNG:
                $sourceImage = @imagecreatefrompng($sourcePath);
                break;
            case IMAGETYPE_WEBP:
                $sourceImage = function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($sourcePath) : null;
                break;
            case IMAGETYPE_GIF:
                $sourceImage = @imagecreatefromgif($sourcePath);
                break;
            default:
                return false;
        }

        if (!$sourceImage) {
            return false;
        }

        // Perbaiki orientasi EXIF jika JPEG memiliki data orientasi kamera
        if ($type === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
            $exif = @exif_read_data($sourcePath);
            if (!empty($exif['Orientation'])) {
                switch ($exif['Orientation']) {
                    case 3:
                        $sourceImage = imagerotate($sourceImage, 180, 0);
                        break;
                    case 6:
                        $sourceImage = imagerotate($sourceImage, -90, 0);
                        [$width, $height] = [$height, $width];
                        break;
                    case 8:
                        $sourceImage = imagerotate($sourceImage, 90, 0);
                        [$width, $height] = [$height, $width];
                        break;
                }
            }
        }

        // Hitung dimensi baru secara proporsional
        $maxSide = max($width, $height);
        if ($maxSide > $maxDimension) {
            $ratio = $maxDimension / $maxSide;
            $newWidth = (int) round($width * $ratio);
            $newHeight = (int) round($height * $ratio);
        } else {
            $newWidth = $width;
            $newHeight = $height;
        }

        // Buat kanvas gambar baru
        $canvas = imagecreatetruecolor($newWidth, $newHeight);

        // Jika gambar sumber PNG transparan, buat background putih bersih untuk output JPEG
        $white = imagecolorallocate($canvas, 255, 255, 255);
        imagefilledrectangle($canvas, 0, 0, $newWidth, $newHeight, $white);

        // Resample dengan kualitas tinggi
        imagecopyresampled($canvas, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        // Buat direktori tujuan jika belum ada
        $destDir = dirname($destinationPath);
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        // Simpan sebagai progressive JPEG terkompresi
        imageinterlace($canvas, true);
        $success = imagejpeg($canvas, $destinationPath, $quality);

        // Bebaskan memori
        imagedestroy($sourceImage);
        imagedestroy($canvas);

        return $success;
    }

    /**
     * Mengompres file yang diunggah (Livewire / HTTP Request) dan menyimpannya ke storage disk.
     *
     * @param UploadedFile|\Livewire\Features\SupportFileUploads\TemporaryUploadedFile $uploadedFile
     * @param string $folder Subfolder di storage (misal: 'items')
     * @param string $disk Nama disk storage (default: 'public')
     * @param int $maxDimension Dimensi maksimum
     * @param int $quality Kualitas kompresi JPEG
     * @return string Relative path ke file yang tersimpan
     */
    public static function optimizeAndStore($uploadedFile, string $folder = 'items', string $disk = 'public', int $maxDimension = 800, int $quality = 80): string
    {
        $filename = $folder . '/' . Str::random(40) . '.jpg';
        $fullPath = Storage::disk($disk)->path($filename);

        $sourcePath = $uploadedFile->getRealPath();

        $destDir = dirname($fullPath);
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $optimized = self::optimizeFile($sourcePath, $fullPath, $maxDimension, $quality);

        if (!$optimized) {
            // Fallback jika GD gagal, simpan file biasa
            return $uploadedFile->store($folder, $disk);
        }

        return $filename;
    }
}
