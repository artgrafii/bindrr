<?php

use App\Spaces\InvalidSpaceFilename;
use App\Spaces\SpaceFilename;

it('rejects an unsafe file name', function (string $name, string $message) {
    expect(SpaceFilename::isSafe($name))->toBeFalse();
    expect(SpaceFilename::rejectionMessage($name))->toBe($message);
})->with([
    'reserved marker' => ['.space.json', 'That file name is reserved.'],
    'parent folder' => ['../secret.txt', 'Use a plain file name without folders.'],
    'backslash' => ['..\\secret.txt', 'Use a plain file name without folders.'],
    'current directory' => ['.', 'Use a plain file name without folders.'],
    'parent directory' => ['..', 'Use a plain file name without folders.'],
    'empty' => ['', 'Use a plain file name without folders.'],
    'surrounding spaces' => [' notes.txt ', 'Use a plain file name without folders.'],
    'newline' => ["notes\n.txt", 'Use a plain file name without folders.'],
    'too long' => [str_repeat('a', 181), 'Keep the file name under 180 characters.'],
]);

it('accepts a plain file name', function () {
    expect(SpaceFilename::isSafe('notes.txt'))->toBeTrue();
});

it('turns a title into a text file name', function () {
    expect(SpaceFilename::noteName('Meeting notes'))->toBe('meeting-notes.txt');
});

it('keeps a file name that already has an extension', function () {
    expect(SpaceFilename::noteName('plan.md'))->toBe('plan.md');
});

it('names a note from the current time when the title is blank', function (?string $name) {
    $this->travelTo('2026-09-23 10:08:00');

    expect(SpaceFilename::noteName($name))->toBe('note-20260923-100800.txt');
})->with([
    'null' => null,
    'empty' => '',
    'spaces' => '   ',
]);

it('rejects a note title that cannot be a file name', function (string $name, string $message) {
    expect(fn () => SpaceFilename::noteName($name))
        ->toThrow(InvalidSpaceFilename::class, $message);
})->with([
    'symbols' => ['!!!', 'Use letters or numbers in the note name.'],
    'reserved' => ['.space.json', 'That file name is reserved.'],
    'folder' => ['../secret.txt', 'Use a plain file name without folders.'],
]);
