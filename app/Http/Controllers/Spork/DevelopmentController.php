<?php

declare(strict_types=1);

namespace App\Http\Controllers\Spork;

use Inertia\Inertia;

class DevelopmentController
{
    public function index()
    {
        $path = base_path(request('path'));

        return Inertia::render('Development/Index', [
            'title' => 'Settings',
            'settings' => new class()
            {
            },
            'files' => collect((new \Illuminate\Filesystem\Filesystem())->directories($path))
                ->map(fn ($directory) => [
                    'name' => basename($directory),
                    'file_path' => base64_encode($directory),
                    'is_directory' => true,
                ])
                ->concat(
                    collect((new \Illuminate\Filesystem\Filesystem())->files($path))
                        ->map(fn (\SplFileInfo $file) => [
                            'name' => $file->getFilename(),
                            'file_path' => base64_encode($file->getPathname()),
                            'is_directory' => false,
                        ])
                ),

        ]);
    }
}
