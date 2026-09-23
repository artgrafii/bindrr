<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSpaceRequest;
use App\Spaces\SpaceAlreadyExists;
use App\Spaces\SpaceStore;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class SpaceController extends Controller
{
    public function index(SpaceStore $spaces): View
    {
        return view('spaces.index', [
            'spaces' => $spaces->list(),
        ]);
    }

    public function store(StoreSpaceRequest $request, SpaceStore $spaces): RedirectResponse
    {
        try {
            $space = $spaces->create($request->validated('name'));
        } catch (SpaceAlreadyExists) {
            throw ValidationException::withMessages([
                'name' => 'You already have a space with that name.',
            ]);
        }

        return redirect()->route('spaces.show', $space->slug);
    }

    public function show(string $space, SpaceStore $spaces): View
    {
        $record = $spaces->find($space) ?? abort(404);

        return view('spaces.show', [
            'space' => $record,
            'files' => $spaces->files($record),
        ]);
    }
}
