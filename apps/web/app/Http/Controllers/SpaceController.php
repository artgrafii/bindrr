<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SpaceController extends Controller
{
    public const DOWNLOAD_METADATA = '.downloads.meta.json';

    private function spacesRoot(): string
    {
        return 'spaces';
    }

    public function index()
    {
        $disk = Storage::disk('local');
        $disk->makeDirectory($this->spacesRoot());
        $dirs = collect($disk->directories($this->spacesRoot()))
            ->map(fn (string $path) => basename($path))
            ->sort()
            ->values();

        return view('spaces.index', ['spaces' => $dirs]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:64'],
        ]);

        $slug = Str::slug($validated['name']);
        if ($slug === '') {
            return back()->withErrors(['name' => 'Name must produce a slug.']);
        }

        Storage::disk('local')->makeDirectory($this->spacesRoot().'/'.$slug);

        return redirect()->route('spaces.show', $slug);
    }

    public function show(string $space)
    {
        $space = Str::slug($space);
        $root = $this->spacesRoot().'/'.$space;
        $disk = Storage::disk('local');

        if (! $disk->exists($root)) {
            abort(404);
        }

        $metadataPath = $root.'/'.self::DOWNLOAD_METADATA;
        $metadata = $disk->exists($metadataPath)
            ? json_decode(File::get($disk->path($metadataPath), true), true, flags: JSON_THROW_ON_ERROR)
            : [];

        $files = collect($disk->files($root))
            ->reject(fn (string $path): bool => basename($path) === self::DOWNLOAD_METADATA)
            ->map(fn (string $path) => [
                'name' => basename($path),
                'size' => $disk->size($path),
                'download_count' => (int) ($metadata[basename($path)]['download_count'] ?? 0),
            ])
            ->values();

        return view('spaces.show', [
            'space' => $space,
            'files' => $files,
        ]);
    }

    public function upload(Request $request, string $space)
    {
        $space = Str::slug($space);
        $root = $this->spacesRoot().'/'.$space;
        $disk = Storage::disk('local');

        if (! $disk->exists($root)) {
            abort(404);
        }

        $validated = $request->validate([
            'file' => ['required', 'file', 'max:10240'],
        ]);

        $file = $validated['file'];
        $name = basename($file->getClientOriginalName());
        abort_if($name === self::DOWNLOAD_METADATA, 422, 'This filename is reserved.');
        $disk->putFileAs($root, $file, $name);

        return redirect()->route('spaces.show', $space);
    }

    public function download(string $space, string $file): StreamedResponse
    {
        $space = Str::slug($space);
        $file = basename($file);
        $path = $this->spacesRoot().'/'.$space.'/'.$file;
        $disk = Storage::disk('local');

        if ($file === self::DOWNLOAD_METADATA || ! $disk->fileExists($path)) {
            abort(404);
        }

        $response = $disk->download($path, $file);
        $this->incrementDownloadCount(dirname($path), $file);

        return $response;
    }

    private function incrementDownloadCount(string $root, string $file): void
    {
        $path = Storage::disk('local')->path($root.'/'.self::DOWNLOAD_METADATA);
        $handle = fopen($path, 'c+');
        abort_if($handle === false, 500, 'Unable to open download metadata.');

        try {
            abort_unless(flock($handle, LOCK_EX), 500, 'Unable to lock download metadata.');

            $contents = stream_get_contents($handle);
            $metadata = $contents === '' ? [] : json_decode($contents, true, flags: JSON_THROW_ON_ERROR);
            $metadata[$file]['download_count'] = (int) ($metadata[$file]['download_count'] ?? 0) + 1;
            $json = json_encode($metadata, JSON_THROW_ON_ERROR);

            rewind($handle);
            abort_unless(fwrite($handle, $json) === strlen($json), 500, 'Unable to save download metadata.');
            abort_unless(ftruncate($handle, strlen($json)) && fflush($handle), 500, 'Unable to save download metadata.');
        } finally {
            fclose($handle);
        }
    }
}
