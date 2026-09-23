<?php

namespace App\Spaces;

final readonly class SpaceFile
{
    public function __construct(
        public string $name,
        public int $size,
        public int $updatedAt,
    ) {}
}
