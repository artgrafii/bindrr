<?php

it('saves a text note as a file on the space disk', function () {
    $disk = spacesDisk();
    $this->travelTo('2026-09-23 10:08:00');
    createSpace();

    $this->post(route('spaces.notes.store', 'garden-notes'), [
        'name' => 'Meeting notes',
        'body' => 'Bring the sketches.',
    ])->assertRedirect(route('spaces.show', 'garden-notes'))
        ->assertSessionHas('status', 'Saved meeting-notes.txt.');

    expect($disk->get('spaces/garden-notes/meeting-notes.txt'))->toBe('Bring the sketches.');

    $this->get(route('spaces.show', 'garden-notes'))
        ->assertSee('meeting-notes.txt')
        ->assertSee('Saved meeting-notes.txt.');
});

it('names a blank note from the current time', function () {
    $disk = spacesDisk();
    $this->travelTo('2026-09-23 10:08:00');
    createSpace();

    $this->post(route('spaces.notes.store', 'garden-notes'), [
        'body' => 'Untitled thought.',
    ])->assertRedirect(route('spaces.show', 'garden-notes'));

    expect($disk->get('spaces/garden-notes/note-20260923-100800.txt'))->toBe('Untitled thought.');
});

it('rejects an empty note', function () {
    $disk = spacesDisk();
    createSpace();

    $this->from(route('spaces.show', 'garden-notes'))
        ->post(route('spaces.notes.store', 'garden-notes'), [
            'name' => 'Meeting notes',
            'body' => '',
        ])
        ->assertRedirect(route('spaces.show', 'garden-notes'))
        ->assertSessionHasErrors(['body' => 'Write something to save.']);

    $disk->assertMissing('spaces/garden-notes/meeting-notes.txt');
});

it('rejects a note name that cannot be a file', function () {
    $disk = spacesDisk();
    createSpace();

    $this->from(route('spaces.show', 'garden-notes'))
        ->post(route('spaces.notes.store', 'garden-notes'), [
            'name' => '!!!',
            'body' => 'Hello',
        ])
        ->assertRedirect(route('spaces.show', 'garden-notes'))
        ->assertSessionHasErrors(['name' => 'Use letters or numbers in the note name.']);

    expect($disk->files('spaces/garden-notes'))->toHaveCount(1);
});
