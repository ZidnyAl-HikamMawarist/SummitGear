<?php

namespace App\Services;

use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Str;

class TwoFactorService
{
    protected Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    /**
     * Menghasilkan Secret Key acak Base32 untuk Google Authenticator
     */
    public function generateSecretKey(): string
    {
        return $this->google2fa->generateSecretKey(32);
    }

    /**
     * Menghasilkan URL otpath untuk QR Code Google Authenticator
     */
    public function getQrCodeUrl(string $email, string $secret): string
    {
        return $this->google2fa->getQRCodeUrl(
            'SummitGear POS',
            $email,
            $secret
        );
    }

    /**
     * Menghasilkan SVG QR Code murni (offline & tajam)
     */
    public function getQrCodeSvg(string $email, string $secret, int $size = 200): string
    {
        $url = $this->getQrCodeUrl($email, $secret);

        $renderer = new ImageRenderer(
            new RendererStyle($size, 1),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);

        return $writer->writeString($url);
    }

    /**
     * Memverifikasi kode 6-digit TOTP dari Google Authenticator
     * Window = 1 (toleransi waktu +/- 30 detik untuk drift jam HP)
     */
    public function verifyKey(string $secret, string $code): bool
    {
        $cleanCode = preg_replace('/\s+/', '', $code);
        if (strlen($cleanCode) !== 6 || !ctype_digit($cleanCode)) {
            return false;
        }

        return (bool) $this->google2fa->verifyKey($secret, $cleanCode, 1);
    }

    /**
     * Menghasilkan Recovery Codes darurat jika pengguna kehilangan akses ke HP
     */
    public function generateRecoveryCodes(int $count = 8): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = strtoupper(Str::random(5) . '-' . Str::random(5));
        }
        return $codes;
    }
}
