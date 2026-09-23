<?php

namespace App\Spaces;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use JsonException;
use League\Flysystem\FilesystemException;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Personal spaces stored as prefixes on the configured S3 disk.
 *
 * File names are kept as the person or agent wrote them. Agents address
 * objects by name, the objects are private, and downloads use an attachment
 * disposition instead of the public web root.
 */
final class SpaceStore
{
    public function __construct(private readonly FilesystemAdapter $disk) {}

    /**
     * @return Collection<int, Space>
     */
    public function list(): Collection
    {
        return $this->guard(function (): Collection {
            return collect($this->disk->directories($this->prefix()))
                ->map(fn (string $directory): ?Space => $this->readMarker(basename($directory)))
                ->filter()
                ->sortBy(fn (Space $space): string => Str::lower($space->name))
                ->values();
        });
    }

    public function find(string $slug): ?Space
    {
        if (! $this->isSlug($slug)) {
            return null;
        }

        return $this->guard(fn (): ?Space => $this->readMarker($slug));
    }

    public function create(string $name): Space
    {
        $slug = Str::slug($name);

        if ($slug === '') {
            throw new InvalidSpaceFilename('Use letters or numbers in the name.');
        }

        if ($this->find($slug) instanceof Space) {
            throw new SpaceAlreadyExists('You already have a space with that name.');
        }

        $space = new Space($slug, $name, now()->toIso8601String());

        $this->guard(function () use ($space): void {
            $written = $this->disk->put($this->markerPath($space->slug), json_encode([
                'name' => $space->name,
                'slug' => $space->slug,
                'created_at' => $space->createdAt,
            ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE));

            if ($written === false) {
                throw new SpaceStorageUnavailable;
            }
        });

        return $space;
    }

    /**
     * @return Collection<int, SpaceFile>
     */
    public function files(Space $space): Collection
    {
        return $this->guard(function () use ($space): Collection {
            return collect($this->disk->files($this->spacePath($space)))
                ->reject(fn (string $path): bool => basename($path) === SpaceFilename::MARKER)
                ->map(fn (string $path): SpaceFile => $this->describe($space, basename($path)))
                ->sortBy(fn (SpaceFile $file): string => Str::lower($file->name))
                ->values();
        });
    }

    public function hasFile(Space $space, string $filename): bool
    {
        if (! SpaceFilename::isSafe($filename)) {
            return false;
        }

        return $this->guard(fn (): bool => $this->disk->exists($this->filePath($space, $filename)));
    }

    public function put(Space $space, string $filename, string $contents, ?string $mimeType = null): SpaceFile
    {
        if (! SpaceFilename::isSafe($filename)) {
            throw new InvalidSpaceFilename(SpaceFilename::rejectionMessage($filename));
        }

        $path = $this->filePath($space, $filename);

        $this->guard(function () use ($path, $contents, $mimeType, $filename): void {
            $written = $this->disk->put($path, $contents, [
                'visibility' => 'private',
                'ContentType' => $mimeType ?: SpaceFilename::mime($filename),
            ]);

            if ($written === false) {
                throw new SpaceStorageUnavailable;
            }
        });

        return $this->describe($space, $filename);
    }

    public function contents(Space $space, string $filename): string
    {
        return $this->guard(fn (): string => $this->disk->get($this->filePath($space, $filename)));
    }

    public function download(Space $space, string $filename): StreamedResponse
    {
        return $this->guard(fn (): StreamedResponse => $this->disk->download(
            $this->filePath($space, $filename),
            $filename,
            ['Content-Type' => SpaceFilename::mime($filename)],
        ));
    }

    public function disk(): Filesystem
    {
        return $this->disk;
    }

    private function readMarker(string $slug): ?Space
    {
        if (! $this->isSlug($slug)) {
            return null;
        }

        $path = $this->markerPath($slug);

        if (! $this->disk->exists($path)) {
            return null;
        }

        /** @var mixed $payload */
        $payload = json_decode($this->disk->get($path), true, 512, JSON_THROW_ON_ERROR);

        if (! is_array($payload)) {
            throw new JsonException('Space marker is not an object.');
        }

        $name = $payload['name'] ?? null;
        $createdAt = $payload['created_at'] ?? null;

        return new Space(
            slug: $slug,
            name: is_string($name) && $name !== '' ? $name : $slug,
            createdAt: is_string($createdAt) ? $createdAt : '',
        );
    }

    private function describe(Space $space, string $filename): SpaceFile
    {
        $path = $this->filePath($space, $filename);

        return $this->guard(fn (): SpaceFile => new SpaceFile(
            name: $filename,
            size: $this->disk->size($path),
            updatedAt: $this->disk->lastModified($path),
        ));
    }

    private function isSlug(string $slug): bool
    {
        return $slug !== '' && $slug === Str::slug($slug);
    }

    private function prefix(): string
    {
        return 'spaces';
    }

    private function spacePath(Space $space): string
    {
        return $this->prefix().'/'.$space->slug;
    }

    private function markerPath(string $slug): string
    {
        return $this->prefix().'/'.$slug.'/'.SpaceFilename::MARKER;
    }

    private function filePath(Space $space, string $filename): string
    {
        return $this->spacePath($space).'/'.$filename;
    }

    /**
     * @template T
     *
     * @param  callable(): T  $callback
     * @return T
     */
    private function guard(callable $callback): mixed
    {
        try {
            return $callback();
        } catch (FilesystemException|JsonException $exception) {
            throw new SpaceStorageUnavailable(previous: $exception);
        }
    }
}
