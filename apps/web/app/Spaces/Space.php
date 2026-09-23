<?php

namespace App\Spaces;

final readonly class Space
{
    public function __construct(
        public string $slug,
        public string $name,
        public string $createdAt,
    ) {}
}
