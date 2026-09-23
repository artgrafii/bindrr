<?php

namespace App\Spaces;

use Illuminate\Support\Str;

final class SpaceFilename
{
    public const MARKER = '.space.json';

    public const MAX_LENGTH = 180;

    public static function isSafe(string $name): bool
    {
        if ($name === '' || $name !== trim($name)) {
            return false;
        }

        if ($name === '.' || $name === '..' || $name === self::MARKER) {
            return false;
        }

        if (str_contains($name, '/') || str_contains($name, '\\') || str_contains($name, "\0")) {
            return false;
        }

        if (preg_match('/\p{C}/u', $name) === 1) {
            return false;
        }

        if (Str::length($name) > self::MAX_LENGTH) {
            return false;
        }

        return $name === basename($name);
    }

    public static function rejectionMessage(string $name): string
    {
        $base = basename(str_replace('\\', '/', $name));

        if ($name === self::MARKER || $base === self::MARKER) {
            return 'That file name is reserved.';
        }

        if (Str::length($name) > self::MAX_LENGTH || Str::length($base) > self::MAX_LENGTH) {
            return 'Keep the file name under 180 characters.';
        }

        return 'Use a plain file name without folders.';
    }

    public static function noteName(?string $name): string
    {
        $name = trim((string) $name);

        if ($name === '') {
            return 'note-'.now()->format('Ymd-His').'.txt';
        }

        if (! str_contains($name, '.')) {
            $slug = Str::slug($name);

            if ($slug === '') {
                throw new InvalidSpaceFilename('Use letters or numbers in the note name.');
            }

            $name = $slug.'.txt';
        }

        if (! self::isSafe($name)) {
            throw new InvalidSpaceFilename(self::rejectionMessage($name));
        }

        return $name;
    }

    public static function mime(string $filename): string
    {
        return match (strtolower(pathinfo($filename, PATHINFO_EXTENSION))) {
            'txt', 'md', 'csv', 'log' => 'text/plain; charset=UTF-8',
            'json' => 'application/json',
            'html', 'htm' => 'text/html; charset=UTF-8',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'pdf' => 'application/pdf',
            default => 'application/octet-stream',
        };
    }
}
