<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSpaceFileRequest;
use App\Spaces\SpaceFilename;
use App\Spaces\SpaceStore;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SpaceFileController extends Controller
{
    public function store(StoreSpaceFileRequest $request, string $space, SpaceStore $spaces): RedirectResponse
    {
        $record = $spaces->find($space) ?? abort(404);
        $upload = $request->file('file');
        $name = $upload->getClientOriginalName();

        $spaces->put($record, $name, $upload->getContent(), $upload->getMimeType() ?: SpaceFilename::mime($name));

        return redirect()
            ->route('spaces.show', $record->slug)
            ->with('status', "Saved {$name}.");
    }

    public function show(string $space, string $file, SpaceStore $spaces): StreamedResponse
    {
        $record = $spaces->find($space) ?? abort(404);

        if (! $spaces->hasFile($record, $file)) {
            abort(404);
        }

        return $spaces->download($record, $file);
    }
}
