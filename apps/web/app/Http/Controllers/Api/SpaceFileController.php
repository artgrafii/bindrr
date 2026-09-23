<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Spaces\InvalidSpaceFilename;
use App\Spaces\SpaceFile;
use App\Spaces\SpaceFilename;
use App\Spaces\SpaceStore;
use App\Spaces\UploadLimit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;

class SpaceFileController extends Controller
{
    use RespondsWithSpaceErrors;

    public function index(string $space, SpaceStore $spaces): JsonResponse
    {
        $record = $spaces->find($space);

        if ($record === null) {
            return $this->spaceError('not_found', 'That space does not exist.', 404);
        }

        return response()->json([
            'ok' => true,
            'space' => $record->slug,
            'files' => $spaces->files($record)->map(fn (SpaceFile $file): array => [
                'name' => $file->name,
                'size' => $file->size,
                'updated_at' => $file->updatedAt,
            ])->all(),
        ]);
    }

    public function show(string $space, string $file, SpaceStore $spaces): Response
    {
        $record = $spaces->find($space);

        if ($record === null) {
            return $this->spaceError('not_found', 'That space does not exist.', 404);
        }

        if (! $spaces->hasFile($record, $file)) {
            return $this->spaceError('not_found', 'That file does not exist.', 404);
        }

        return response($spaces->contents($record, $file), 200, [
            'Content-Type' => SpaceFilename::mime($file),
            'Content-Disposition' => HeaderUtils::makeDisposition(HeaderUtils::DISPOSITION_ATTACHMENT, $file),
            'Cache-Control' => 'private, no-store',
        ]);
    }

    public function update(Request $request, string $space, string $file, SpaceStore $spaces): JsonResponse
    {
        $record = $spaces->find($space);

        if ($record === null) {
            return $this->spaceError('not_found', 'That space does not exist.', 404);
        }

        if (! SpaceFilename::isSafe($file)) {
            return $this->spaceError('invalid_name', SpaceFilename::rejectionMessage($file), 422);
        }

        $contents = $request->getContent();

        if (strlen($contents) > UploadLimit::bytes()) {
            return $this->spaceError('too_large', UploadLimit::message(), 413);
        }

        $replaced = $spaces->hasFile($record, $file);

        try {
            $stored = $spaces->put($record, $file, $contents, SpaceFilename::mime($file));
        } catch (InvalidSpaceFilename $exception) {
            return $this->spaceError('invalid_name', $exception->getMessage(), 422);
        }

        return response()->json([
            'ok' => true,
            'space' => $record->slug,
            'file' => $stored->name,
            'size' => $stored->size,
            'replaced' => $replaced,
        ], $replaced ? 200 : 201);
    }
}
