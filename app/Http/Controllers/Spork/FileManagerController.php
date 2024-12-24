<?php

declare(strict_types=1);

namespace App\Http\Controllers\Spork;

use Illuminate\Filesystem\Filesystem;
use Inertia\Inertia;

class FileManagerController
{
    public function __invoke()
    {
        $filesystem = \Illuminate\Support\Facades\Storage::disk(config('spork.filesystem.default'));

        $path = base64_decode(request()->input('path', base64_encode('')));

        return Inertia::render('FileManager', [
            'files' => collect(array_map(
                fn ($file) => [
                    'name' => basename($file),
                    'file_path' => base64_encode('/'.$file),
                    'path' => $file,
                    'is_directory' => false,
                    'type' => 'file',
                    'last_modified' => \Carbon\Carbon::parse($filesystem->lastModified($file)),
                ],
                $filesystem->files($path)
            ))->sortBy('name')->values(),
            'directories' => collect(array_map(
                fn ($file) => [
                    'name' => basename($file),
                    'file_path' => base64_encode('/'.$file),
                    'is_directory' => true,
                    'type' => 'folder',
                    'path' => $file,
                ],
                $filesystem->directories($path)
            ))->sortBy('name')->values(),
        ]);
    }

    public function show($path)
    {
        $decoded = base64_decode($path);

        if (is_dir($decoded)) {
            return collect((new \Illuminate\Filesystem\Filesystem())->directories($decoded))
                ->map(fn ($directory) => [
                    'name' => basename($directory),
                    'file_path' => base64_encode($directory),
                    'is_directory' => true,
                ])
                ->concat(
                    collect((new \Illuminate\Filesystem\Filesystem())->files($decoded))
                        ->map(fn (\SplFileInfo $file) => [
                            'name' => $file->getFilename(),
                            'file_path' => base64_encode($file->getPathname()),
                            'is_directory' => false,
                        ])
                );
        }

        return file_get_contents($decoded);
    }

    public function update($path)
    {
        $decoded = base64_decode($path);

        $filesystem = new Filesystem();

        $content = request()->input('content');
        $existingFile = $filesystem->get($decoded);

        if ($content === $existingFile) {
            return response()->json(['message' => 'No changes made']);
        }

        dd($decoded, $content, $existingFile, request()->all());
        $filesystem->put($decoded, request()->input('content'));

        return response()->json(['message' => 'File updated']);
    }
}
