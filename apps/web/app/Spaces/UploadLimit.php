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
     * Human size without the intl extension (Number::fileSize needs it).
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

        return number_format($bytes, 0).' '.$units[$i];
    }

    public static function label(): string
    {
        return self::formatBytes(self::bytes());
    }

    public static function message(): string
    {
        return 'That file is larger than '.self::label().'.';
    }
}
