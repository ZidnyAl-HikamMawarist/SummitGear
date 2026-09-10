<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('catalog:optimize-images {--max-dim=600 : Dimensi maksimal pixel} {--quality=78 : Kualitas kompresi JPEG}', function () {
    $maxDim = (int) $this->option('max-dim');
    $quality = (int) $this->option('quality');
    $this->info("Mengompres seluruh gambar katalog (Max Dim: {$maxDim}px, Quality: {$quality}%)...");

    $optimizer = \App\Services\ImageOptimizer::class;
    $processed = 0;
    $origTotal = 0;
    $newTotal = 0;

    $directories = [
        'Katalog Storage' => ['path' => storage_path('app/public/images/catalog'), 'dim' => $maxDim],
        'Kategori'        => ['path' => storage_path('app/public/catalog'), 'dim' => $maxDim],
        'Katalog Public'  => ['path' => public_path('images/catalog'), 'dim' => $maxDim],
        'Hero Background' => ['path' => public_path('images'), 'dim' => 1440, 'pattern' => 'hero_*.{jpg,jpeg,png}'],
    ];

    foreach ($directories as $name => $config) {
        $dir = $config['path'];
        if (!is_dir($dir)) continue;

        $pattern = $config['pattern'] ?? '*.{jpg,jpeg,png}';
        $files = glob($dir . '/' . $pattern, GLOB_BRACE);
        $this->line("<comment>[{$name}]</comment> Menemukan " . count($files) . " file...");

        foreach ($files as $file) {
            $origSize = filesize($file);
            $origTotal += $origSize;

            $tempFile = sys_get_temp_dir() . '/sg_opt_' . uniqid() . '.jpg';
            $ok = $optimizer::optimizeFile($file, $tempFile, $config['dim'], $quality);

            if ($ok && file_exists($tempFile) && filesize($tempFile) > 0) {
                $newSize = filesize($tempFile);
                copy($tempFile, $file);
                unlink($tempFile);

                $newTotal += $newSize;
                $processed++;
                $savedPct = round((1 - ($newSize / max(1, $origSize))) * 100, 1);
                $this->line("  ✔ " . basename($file) . ": " . round($origSize / 1024, 1) . "KB -> " . round($newSize / 1024, 1) . "KB (-{$savedPct}%)");
            } else {
                $newTotal += $origSize;
                if (file_exists($tempFile)) unlink($tempFile);
            }
        }
    }

    $savedBytes = $origTotal - $newTotal;
    $savedMb = round($savedBytes / (1024 * 1024), 2);
    $savedPctTotal = round(($savedBytes / max(1, $origTotal)) * 100, 1);

    $this->newLine();
    $this->info("Berhasil mengompres {$processed} gambar!");
    $this->info("Ukuran Awal: " . round($origTotal / (1024 * 1024), 2) . " MB | Ukuran Baru: " . round($newTotal / (1024 * 1024), 2) . " MB");
    $this->info("Hemat Ruang: {$savedMb} MB (-{$savedPctTotal}%)");
})->purpose('Kompres gambar katalog dan kategori agar ringan');

use Illuminate\Support\Facades\Schedule;
use App\Jobs\CalculateLatePenaltyJob;
use App\Jobs\ReleaseHoldBookingJob;
use App\Jobs\ExpireOnlineBookingJob;

Schedule::job(new CalculateLatePenaltyJob)->hourly();
Schedule::job(new ReleaseHoldBookingJob)->everyThirtyMinutes();
Schedule::job(new ExpireOnlineBookingJob)->hourly();
