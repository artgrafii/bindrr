<?php

namespace App\Spaces;

use Illuminate\Support\Number;

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

    public static function label(): string
    {
        return Number::fileSize(self::bytes());
    }

    public static function message(): string
    {
        return 'That file is larger than '.self::label().'.';
    }
}
