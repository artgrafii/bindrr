<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSpaceNoteRequest;
use App\Spaces\InvalidSpaceFilename;
use App\Spaces\SpaceFilename;
use App\Spaces\SpaceStore;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class SpaceNoteController extends Controller
{
    public function store(StoreSpaceNoteRequest $request, string $space, SpaceStore $spaces): RedirectResponse
    {
        $record = $spaces->find($space) ?? abort(404);

        try {
            $name = SpaceFilename::noteName($request->validated('name'));
        } catch (InvalidSpaceFilename $exception) {
            throw ValidationException::withMessages([
                'name' => $exception->getMessage(),
            ]);
        }

        $spaces->put(
            $record,
            $name,
            $request->string('body')->toString(),
            SpaceFilename::mime($name),
        );

        return redirect()
            ->route('spaces.show', $record->slug)
            ->with('status', "Saved {$name}.");
    }
}
