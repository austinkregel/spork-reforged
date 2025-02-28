<?php

declare(strict_types=1);

namespace App\Http\Controllers\Spork;

use Illuminate\Filesystem\Filesystem;
use Inertia\Inertia;
use Winter\LaravelConfigWriter\ArrayFile;

class FileManagerController
{
    public function __invoke()
    {
        $filesystem = \Illuminate\Support\Facades\Storage::disk($selectedFilesystem = config('spork.filesystem.default'));

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
            'filesystems' => collect(config('filesystems.disks'))->keys(),
            'selectedFilesystem' => $selectedFilesystem,
        ]);
    }

    public function updateFileManager()
    {
        $config = ArrayFile::open(config_path('spork.php'));

        $config->set('filesystem.default', request()->input('value'))->write();

        return Inertia::location(route('file-manager.index'));
    }

    public function show($path)
    {
        $decoded = base64_decode($path);
        $filesystem = \Illuminate\Support\Facades\Storage::disk($selectedFilesystem = config('spork.filesystem.default'));

        $exists = $filesystem->exists($decoded);



        if ($exists && !$contents = $filesystem->get($decoded)) {
            return collect($filesystem->directories($decoded))
                ->map(fn ($directory) => [
                    'name' => basename($directory),
                    'file_path' => base64_encode($directory),
                    'is_directory' => true,
                ])
                ->concat(
                    collect($filesystem->files($decoded))
                        ->map(fn ($file) => [
                            'name' => $file,
                            'file_path' => base64_encode($file),
                            'is_directory' => false,
                        ])
                );
        }

        return $contents;
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
