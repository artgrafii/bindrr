<?php

use App\Spaces\SpaceStore;
use Illuminate\Filesystem\FilesystemAdapter;
use League\Flysystem\UnableToListContents;
use Mockery\MockInterface;

describe('index', function () {
    it('renders an empty personal spaces page', function () {
        spacesDisk();

        $this->get(route('spaces.index'))
            ->assertOk()
            ->assertSee('Your spaces')
            ->assertSee('No spaces yet')
            ->assertSee('Create space')
            ->assertSee('Personal spaces');
    });

    it('lists spaces by name', function () {
        spacesDisk();
        $this->travelTo('2026-09-23 10:08:00');
        createSpace('Beta');
        createSpace('Alpha');

        $this->get(route('spaces.index'))
            ->assertOk()
            ->assertSeeInOrder(['Alpha', 'Beta'])
            ->assertDontSee('No spaces yet');
    });

    it('renders a storage error when spaces cannot be listed', function () {
        $adapter = Mockery::mock(FilesystemAdapter::class, function (MockInterface $mock): void {
            $mock->shouldReceive('directories')
                ->once()
                ->andThrow(UnableToListContents::atLocation('spaces', false, new RuntimeException('offline')));
        });

        $this->app->instance(SpaceStore::class, new SpaceStore($adapter));

        $this->get(route('spaces.index'))
            ->assertServiceUnavailable()
            ->assertSee("We couldn't reach your files", false)
            ->assertSee('Try again');
    });
});

describe('store', function () {
    it('creates a personal space on the s3 disk', function () {
        $disk = spacesDisk();
        $this->travelTo('2026-09-23 10:08:00');

        $this->post(route('spaces.store'), ['name' => 'Garden Notes'])
            ->assertRedirect(route('spaces.show', 'garden-notes'));

        $disk->assertExists('spaces/garden-notes/.space.json');
        expect($disk->json('spaces/garden-notes/.space.json'))->toBe([
            'name' => 'Garden Notes',
            'slug' => 'garden-notes',
            'created_at' => '2026-09-23T10:08:00+00:00',
        ]);

        $this->get(route('spaces.show', 'garden-notes'))
            ->assertOk()
            ->assertSee('Garden Notes');
    });

    it('rejects an empty space name', function () {
        $disk = spacesDisk();

        $this->from(route('spaces.index'))
            ->post(route('spaces.store'), ['name' => ''])
            ->assertRedirect(route('spaces.index'))
            ->assertSessionHasErrors(['name' => 'Give this space a name.']);

        expect($disk->allFiles('spaces'))->toBe([]);
    });

    it('rejects a name that does not produce a slug', function () {
        spacesDisk();

        $this->from(route('spaces.index'))
            ->post(route('spaces.store'), ['name' => '***'])
            ->assertRedirect(route('spaces.index'))
            ->assertSessionHasErrors(['name' => 'Use letters or numbers in the name.']);
    });

    it('rejects a name longer than 80 characters', function () {
        spacesDisk();

        $this->from(route('spaces.index'))
            ->post(route('spaces.store'), ['name' => str_repeat('a', 81)])
            ->assertRedirect(route('spaces.index'))
            ->assertSessionHasErrors(['name' => 'Keep the name under 80 characters.']);
    });

    it('rejects a duplicate space name', function () {
        $disk = spacesDisk();
        createSpace('Garden Notes');
        $marker = $disk->get('spaces/garden-notes/.space.json');

        $this->from(route('spaces.index'))
            ->post(route('spaces.store'), ['name' => 'Garden Notes'])
            ->assertRedirect(route('spaces.index'))
            ->assertSessionHasErrors(['name' => 'You already have a space with that name.']);

        expect($disk->get('spaces/garden-notes/.space.json'))->toBe($marker);
    });

    it('escapes a space name that contains html', function () {
        spacesDisk();

        $this->post(route('spaces.store'), [
            'name' => "Notes <script>alert('xss')</script>",
        ])->assertRedirect();

        $this->get(route('spaces.index'))
            ->assertSee('&lt;script&gt;', false)
            ->assertDontSee("<script>alert('xss')</script>", false);
    });
});

describe('show', function () {
    it('renders an empty space', function () {
        spacesDisk();
        createSpace();

        $this->get(route('spaces.show', 'garden-notes'))
            ->assertOk()
            ->assertSee('Garden Notes')
            ->assertSee('Nothing in this space yet')
            ->assertSee('Up to 10 MB.')
            ->assertSee('Your agents read and write the same files.');
    });

    it('returns 404 when the space does not exist', function () {
        spacesDisk();

        $this->get(route('spaces.show', 'missing-space'))
            ->assertNotFound()
            ->assertSee("That page isn't in your spaces", false)
            ->assertSee('Back to your spaces');
    });

    it('shows a file an agent put in the space', function () {
        spacesDisk();
        createSpace();

        $this->call(
            'PUT',
            '/api/spaces/garden-notes/files/from-agent.txt',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'text/plain'],
            'agent note',
        )->assertCreated();

        $this->get(route('spaces.show', 'garden-notes'))
            ->assertOk()
            ->assertSee('from-agent.txt')
            ->assertSee('10 B')
            ->assertDontSee('.space.json');
    });
});
