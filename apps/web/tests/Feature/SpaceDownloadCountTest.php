<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

uses(TestCase::class);

test('successful space downloads persist a per-file count and display it', function (): void {
    $disk = Storage::fake('local');
    $disk->put('spaces/demo/report.txt', 'Report contents');
    $disk->put('spaces/demo/other.txt', 'Other contents');
    $metadataPath = 'spaces/demo/.downloads.meta.json';

    $this->get(route('spaces.show', 'demo'))->assertSee('다운로드 0회');
    $disk->assertMissing($metadataPath);

    $this->get(route('spaces.download', ['demo', 'report.txt']))
        ->assertOk()
        ->assertDownload('report.txt')
        ->assertStreamedContent('Report contents');

    expect($disk->json($metadataPath))->toBe(['report.txt' => ['download_count' => 1]]);
    $this->get(route('spaces.show', 'demo'))
        ->assertSee('다운로드 1회')
        ->assertSee('다운로드 0회')
        ->assertDontSee('.downloads.meta.json');

    $this->get(route('spaces.download', ['demo', 'report.txt']))
        ->assertOk()
        ->assertStreamedContent('Report contents');
    expect($disk->json($metadataPath))->toBe(['report.txt' => ['download_count' => 2]]);

    $this->get(route('spaces.download', ['demo', 'missing.txt']))->assertNotFound();
    $this->get(route('spaces.download', ['demo', '.downloads.meta.json']))->assertNotFound();
    $this->get('/api/spaces/demo/files/.downloads.meta.json')->assertNotFound();
    $this->put('/api/spaces/demo/files/.downloads.meta.json', [], ['Content-Type' => 'application/json'])
        ->assertUnprocessable();
    $this->post(route('spaces.upload', 'demo'), [
        'file' => UploadedFile::fake()->createWithContent('.downloads.meta.json', '{}'),
    ])->assertUnprocessable();
    $this->get('/api/spaces/demo/files')->assertJsonCount(2, 'files');

    expect($disk->json($metadataPath))->toBe(['report.txt' => ['download_count' => 2]]);
    $this->get(route('spaces.show', 'demo'))->assertSee('다운로드 2회');
});
