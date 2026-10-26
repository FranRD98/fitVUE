<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class SdrImage
{
    /**
     * Las fotos del iPhone incluyen un mapa de ganancia HDR. Safari lo pinta
     * más brillante que la interfaz y una captura, al aplanar a SDR, lo oculta.
     * GD reescribe solo la imagen base y descarta ese mapa.
     */
    public static function flattenStored(?string $stored): void
    {
        $relative = self::relativePath($stored);

        if (! $relative || ! function_exists('imagecreatefromstring')) {
            return;
        }

        $absolute = Storage::disk('public')->path($relative);

        if (! is_file($absolute)) {
            return;
        }

        $size = filesize($absolute);

        if ($size === false || $size > 12_000_000) {
            return;
        }

        $bytes = file_get_contents($absolute);

        if ($bytes === false || ! self::hasGainMap($bytes)) {
            return;
        }

        $image = @imagecreatefromstring($bytes);

        if (! $image) {
            return;
        }

        $extension = strtolower(pathinfo($absolute, PATHINFO_EXTENSION));

        if (in_array($extension, ['jpg', 'jpeg'], true)) {
            imagejpeg($image, $absolute, 90);
        } elseif ($extension === 'png') {
            imagepng($image, $absolute);
        } elseif ($extension === 'webp' && function_exists('imagewebp')) {
            imagewebp($image, $absolute, 90);
        } else {
            imagejpeg($image, $absolute, 90);
        }

        imagedestroy($image);
    }

    private static function hasGainMap(string $bytes): bool
    {
        return str_contains($bytes, 'hdrgm:')
            || str_contains($bytes, 'GainMap')
            || str_contains($bytes, 'urn:iso:std:iso:ts:21496');
    }

    private static function relativePath(?string $stored): ?string
    {
        if (! $stored) {
            return null;
        }

        if (preg_match('#/storage/(.+)$#', $stored, $matches)) {
            return $matches[1];
        }

        if (! str_contains($stored, '://')) {
            return ltrim($stored, '/');
        }

        return null;
    }
}
