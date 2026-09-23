<?php

use App\Spaces\Space;
use App\Spaces\SpaceStore;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Tests\TestCase;

uses(TestCase::class)->in('Feature', 'Unit');

function spacesDisk(): FilesystemAdapter
{
    $disk = Storage::fake((string) config('bindrr.disk'));
    app()->forgetInstance(SpaceStore::class);

    return $disk;
}

function createSpace(string $name = 'Garden Notes'): Space
{
    $disk = Storage::disk((string) config('bindrr.disk'));

    if (! $disk->getAdapter() instanceof LocalFilesystemAdapter) {
        spacesDisk();
    }

    return app(SpaceStore::class)->create($name);
}
