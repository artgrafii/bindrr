<?php

use App\Spaces\SpaceStore;
use Illuminate\Filesystem\FilesystemAdapter;
use League\Flysystem\UnableToListContents;

it('lists personal spaces', function () {
    spacesDisk();
    $this->travelTo('2026-09-23 10:08:00');
    createSpace('Beta');
    createSpace('Alpha');

    $this->getJson('/api/spaces')
        ->assertOk()
        ->assertExactJson([
            'ok' => true,
            'spaces' => [
                [
                    'name' => 'Alpha',
                    'slug' => 'alpha',
                    'created_at' => '2026-09-23T10:08:00+00:00',
                ],
                [
                    'name' => 'Beta',
                    'slug' => 'beta',
                    'created_at' => '2026-09-23T10:08:00+00:00',
                ],
            ],
        ]);
});

it('returns 503 when spaces cannot be listed', function () {
    $adapter = Mockery::mock(FilesystemAdapter::class);
    $adapter->shouldReceive('directories')
        ->once()
        ->andThrow(UnableToListContents::atLocation('spaces', false, new RuntimeException('offline')));

    $this->app->instance(SpaceStore::class, new SpaceStore($adapter));

    $this->getJson('/api/spaces')
        ->assertServiceUnavailable()
        ->assertExactJson([
            'ok' => false,
            'error' => 'storage_unavailable',
            'message' => 'Spaces are unavailable right now.',
        ]);
});
