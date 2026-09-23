<?php

use App\Spaces\SpaceStore;
use Illuminate\Http\UploadedFile;

it('lists files without the space marker', function () {
    spacesDisk();
    $space = createSpace();
    app(SpaceStore::class)->put($space, 'b.txt', 'bb');
    app(SpaceStore::class)->put($space, 'a.txt', 'a');

    $response = $this->getJson('/api/spaces/garden-notes/files')
        ->assertOk()
        ->assertJsonPath('ok', true)
        ->assertJsonPath('space', 'garden-notes')
        ->assertJsonPath('files.0.name', 'a.txt')
        ->assertJsonPath('files.0.size', 1)
        ->assertJsonPath('files.1.name', 'b.txt')
        ->assertJsonPath('files.1.size', 2)
        ->assertJsonMissing(['name' => '.space.json']);

    expect($response->json('files.0.updated_at'))->toBeInt();
});

it('returns 404 when listing files in a missing space', function () {
    spacesDisk();

    $this->getJson('/api/spaces/missing-space/files')
        ->assertNotFound()
        ->assertExactJson([
            'ok' => false,
            'error' => 'not_found',
            'message' => 'That space does not exist.',
        ]);
});

it('puts a new file and returns 201', function () {
    $disk = spacesDisk();
    createSpace();

    $this->call(
        'PUT',
        '/api/spaces/garden-notes/files/notes.txt',
        [],
        [],
        [],
        ['CONTENT_TYPE' => 'text/plain'],
        'hello',
    )->assertCreated()
        ->assertExactJson([
            'ok' => true,
            'space' => 'garden-notes',
            'file' => 'notes.txt',
            'size' => 5,
            'replaced' => false,
        ]);

    expect($disk->get('spaces/garden-notes/notes.txt'))->toBe('hello');
});

it('replaces an existing file and returns 200', function () {
    $disk = spacesDisk();
    $space = createSpace();
    app(SpaceStore::class)->put($space, 'notes.txt', 'old');

    $this->call(
        'PUT',
        '/api/spaces/garden-notes/files/notes.txt',
        [],
        [],
        [],
        ['CONTENT_TYPE' => 'text/plain'],
        'new',
    )->assertOk()
        ->assertExactJson([
            'ok' => true,
            'space' => 'garden-notes',
            'file' => 'notes.txt',
            'size' => 3,
            'replaced' => true,
        ]);

    expect($disk->get('spaces/garden-notes/notes.txt'))->toBe('new');
});

it('returns the bytes uploaded from the personal space page', function () {
    spacesDisk();
    createSpace();

    $this->post(route('spaces.files.store', 'garden-notes'), [
        'file' => UploadedFile::fake()->createWithContent('notes.txt', 'hello from me'),
    ])->assertRedirect();

    $response = $this->get('/api/spaces/garden-notes/files/notes.txt');

    $response->assertOk();
    $response->assertHeader('content-type', 'text/plain; charset=UTF-8');
    expect($response->getContent())->toBe('hello from me');
});

it('returns 404 when the file is missing', function () {
    spacesDisk();
    createSpace();

    $this->getJson('/api/spaces/garden-notes/files/missing.txt')
        ->assertNotFound()
        ->assertExactJson([
            'ok' => false,
            'error' => 'not_found',
            'message' => 'That file does not exist.',
        ]);
});

it('returns 404 for the space marker', function () {
    $disk = spacesDisk();
    createSpace();
    $marker = $disk->get('spaces/garden-notes/.space.json');

    $this->getJson('/api/spaces/garden-notes/files/.space.json')
        ->assertNotFound()
        ->assertExactJson([
            'ok' => false,
            'error' => 'not_found',
            'message' => 'That file does not exist.',
        ]);

    expect($disk->get('spaces/garden-notes/.space.json'))->toBe($marker);
});

it('returns 422 when putting a reserved name', function () {
    $disk = spacesDisk();
    createSpace();
    $marker = $disk->get('spaces/garden-notes/.space.json');

    $this->call(
        'PUT',
        '/api/spaces/garden-notes/files/.space.json',
        [],
        [],
        [],
        ['CONTENT_TYPE' => 'application/json'],
        '{"hijack":true}',
    )->assertUnprocessable()
        ->assertExactJson([
            'ok' => false,
            'error' => 'invalid_name',
            'message' => 'That file name is reserved.',
        ]);

    expect($disk->get('spaces/garden-notes/.space.json'))->toBe($marker);
});

it('returns 413 when the body is too large', function () {
    $disk = spacesDisk();
    createSpace();

    $this->call(
        'PUT',
        '/api/spaces/garden-notes/files/big.txt',
        [],
        [],
        [],
        ['CONTENT_TYPE' => 'text/plain'],
        str_repeat('x', (10 * 1024 * 1024) + 1),
    )->assertStatus(413)
        ->assertExactJson([
            'ok' => false,
            'error' => 'too_large',
            'message' => 'That file is larger than 10 MB.',
        ]);

    $disk->assertMissing('spaces/garden-notes/big.txt');
});

it('returns 404 when putting into a missing space', function () {
    $disk = spacesDisk();

    $this->call(
        'PUT',
        '/api/spaces/missing-space/files/notes.txt',
        [],
        [],
        [],
        ['CONTENT_TYPE' => 'text/plain'],
        'hello',
    )->assertNotFound()
        ->assertExactJson([
            'ok' => false,
            'error' => 'not_found',
            'message' => 'That space does not exist.',
        ]);

    $disk->assertMissing('spaces/missing-space/.space.json');
    $disk->assertMissing('spaces/missing-space/notes.txt');
});
