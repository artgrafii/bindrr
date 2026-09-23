<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Spaces\Space;
use App\Spaces\SpaceStore;
use Illuminate\Http\JsonResponse;

class SpaceController extends Controller
{
    use RespondsWithSpaceErrors;

    public function index(SpaceStore $spaces): JsonResponse
    {
        return response()->json([
            'ok' => true,
            'spaces' => $spaces->list()->map(fn (Space $space): array => [
                'name' => $space->name,
                'slug' => $space->slug,
                'created_at' => $space->createdAt,
            ])->all(),
        ]);
    }
}
