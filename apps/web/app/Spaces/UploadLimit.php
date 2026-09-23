<?php

namespace App\Spaces;

final class UploadLimit
{
    public static function kilobytes(): int
    {
        return (int) config('bindrr.max_upload_kilobytes');
    }

    public static function bytes(): int
    {
        return self::kilobytes() * 1024;
    }

    /**
     * Human size for a stored file. Uses sprintf so preview PHP without intl can render it.
     */
    public static function formatBytes(int|float $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $i = 0;

        if (is_finite($bytes)) {
            while ((abs($bytes) / 1024) > 0.9 && $i < count($units) - 1) {
                $bytes /= 1024;
                $i++;
            }
        }

        return sprintf('%d %s', (int) round($bytes), $units[$i]);
    }

    public static function label(): string
    {
        $kilobytes = self::kilobytes();

        if ($kilobytes >= 1024) {
            return sprintf('%d MB', (int) round($kilobytes / 1024));
        }

        return sprintf('%d KB', $kilobytes);
    }

    public static function message(): string
    {
        return 'That file is larger than '.self::label().'.';
    }
}
