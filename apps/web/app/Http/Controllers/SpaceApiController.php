<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Minimal agent-facing HTTP API for bindrr spaces.
 * Same storage as the human UI (local disk spaces/).
 */
class SpaceApiController extends Controller
{
    private function root(): string
    {
        return 'spaces';
    }

    public function index()
    {
        $disk = Storage::disk('local');
        $disk->makeDirectory($this->root());

        $spaces = collect($disk->directories($this->root()))
            ->map(fn (string $path) => basename($path))
            ->sort()
            ->values();

        return response()->json(['ok' => true, 'spaces' => $spaces]);
    }

    public function files(string $space)
    {
        $space = Str::slug($space);
        $dir = $this->root().'/'.$space;
        $disk = Storage::disk('local');

        if (! $disk->exists($dir)) {
            return response()->json(['ok' => false, 'error' => 'not_found'], 404);
        }

        $files = collect($disk->files($dir))
            ->reject(fn (string $path): bool => basename($path) === SpaceController::DOWNLOAD_METADATA)
            ->map(fn (string $path) => [
                'name' => basename($path),
                'size' => $disk->size($path),
            ])
            ->values();

        return response()->json(['ok' => true, 'space' => $space, 'files' => $files]);
    }

    public function put(Request $request, string $space, string $file)
    {
        $space = Str::slug($space);
        $file = basename($file);
        abort_if($file === SpaceController::DOWNLOAD_METADATA, 422, 'This filename is reserved.');
        $disk = Storage::disk('local');
        $dir = $this->root().'/'.$space;

        $disk->makeDirectory($dir);

        $content = $request->getContent();
        if (strlen($content) > 1024 * 1024) {
            return response()->json(['ok' => false, 'error' => 'too_large'], 413);
        }

        $path = $dir.'/'.$file;
        $disk->put($path, $content);

        return response()->json([
            'ok' => true,
            'space' => $space,
            'file' => $file,
            'size' => $disk->size($path),
        ]);
    }

    public function get(string $space, string $file)
    {
        $space = Str::slug($space);
        $file = basename($file);
        $path = $this->root().'/'.$space.'/'.$file;
        $disk = Storage::disk('local');

        if ($file === SpaceController::DOWNLOAD_METADATA || ! $disk->exists($path)) {
            return response()->json(['ok' => false, 'error' => 'not_found'], 404);
        }

        return response($disk->get($path), 200, [
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="'.$file.'"',
        ]);
    }
}
