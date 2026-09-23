<?php

use App\Spaces\SpaceStore;
use Illuminate\Http\UploadedFile;

it('uploads a file onto the space disk and lists it', function () {
    $disk = spacesDisk();
    createSpace();

    $this->post(route('spaces.files.store', 'garden-notes'), [
        'file' => UploadedFile::fake()->createWithContent('notes.txt', 'hello from me'),
    ])->assertRedirect(route('spaces.show', 'garden-notes'))
        ->assertSessionHas('status', 'Saved notes.txt.');

    $disk->assertExists('spaces/garden-notes/notes.txt');
    expect($disk->get('spaces/garden-notes/notes.txt'))->toBe('hello from me');

    $this->get(route('spaces.show', 'garden-notes'))
        ->assertOk()
        ->assertSee('notes.txt')
        ->assertSee('13 B')
        ->assertSee('Saved notes.txt.')
        ->assertDontSee('Nothing in this space yet');
});

it('downloads a file from the space', function () {
    spacesDisk();
    $space = createSpace();
    app(SpaceStore::class)->put($space, 'report.txt', 'Report contents');

    $this->get(route('spaces.files.show', ['garden-notes', 'report.txt']))
        ->assertOk()
        ->assertDownload('report.txt')
        ->assertStreamedContent('Report contents');
});

it('returns 404 when the file is missing', function () {
    spacesDisk();
    createSpace();

    $this->get(route('spaces.files.show', ['garden-notes', 'missing.txt']))
        ->assertNotFound();
});

it('returns 404 for the space marker', function () {
    $disk = spacesDisk();
    createSpace();
    $marker = $disk->get('spaces/garden-notes/.space.json');

    $this->get(route('spaces.files.show', ['garden-notes', '.space.json']))
        ->assertNotFound();

    expect($disk->get('spaces/garden-notes/.space.json'))->toBe($marker);
});

it('returns 404 when uploading into a missing space', function () {
    $disk = spacesDisk();

    $this->post(route('spaces.files.store', 'missing-space'), [
        'file' => UploadedFile::fake()->createWithContent('notes.txt', 'hello'),
    ])->assertNotFound();

    $disk->assertMissing('spaces/missing-space/notes.txt');
});

it('rejects a missing upload', function () {
    $disk = spacesDisk();
    createSpace();

    $this->followingRedirects()
        ->from(route('spaces.show', 'garden-notes'))
        ->post(route('spaces.files.store', 'garden-notes'), [])
        ->assertSee('Choose a file to upload.');

    expect($disk->files('spaces/garden-notes'))->toHaveCount(1);
});

it('rejects a file over the size limit', function () {
    $disk = spacesDisk();
    createSpace();

    $this->from(route('spaces.show', 'garden-notes'))
        ->post(route('spaces.files.store', 'garden-notes'), [
            'file' => UploadedFile::fake()->create('big.bin', 10241),
        ])
        ->assertRedirect(route('spaces.show', 'garden-notes'))
        ->assertSessionHasErrors(['file' => 'That file is larger than 10 MB.']);

    $disk->assertMissing('spaces/garden-notes/big.bin');
});

it('rejects a reserved marker filename', function () {
    $disk = spacesDisk();
    createSpace();
    $marker = $disk->get('spaces/garden-notes/.space.json');

    $this->from(route('spaces.show', 'garden-notes'))
        ->post(route('spaces.files.store', 'garden-notes'), [
            'file' => UploadedFile::fake()->createWithContent('.space.json', '{"hijack":true}'),
        ])
        ->assertRedirect(route('spaces.show', 'garden-notes'))
        ->assertSessionHasErrors(['file' => 'That file name is reserved.']);

    expect($disk->get('spaces/garden-notes/.space.json'))->toBe($marker);
});

it('rejects a filename that contains a folder', function () {
    $disk = spacesDisk();
    createSpace();

    $this->from(route('spaces.show', 'garden-notes'))
        ->post(route('spaces.files.store', 'garden-notes'), [
            'file' => UploadedFile::fake()->createWithContent('../secret.txt', 'nope'),
        ])
        ->assertRedirect(route('spaces.show', 'garden-notes'))
        ->assertSessionHasErrors(['file' => 'Use a plain file name without folders.']);

    $disk->assertMissing('secret.txt');
    $disk->assertMissing('spaces/secret.txt');
    $disk->assertMissing('spaces/garden-notes/secret.txt');
});

it('escapes a file name that contains html', function () {
    spacesDisk();
    createSpace();

    $this->post(route('spaces.files.store', 'garden-notes'), [
        'file' => UploadedFile::fake()->createWithContent('a<script>.txt', 'x'),
    ])->assertRedirect();

    $this->get(route('spaces.show', 'garden-notes'))
        ->assertSee('&lt;script&gt;', false)
        ->assertDontSee('<script>', false);
});
