<?php

declare(strict_types=1);

namespace App\Http\Controllers\Spork;

use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class DevelopmentController
{
    public function index()
    {
        $path = request('path', '');
        $decoded = base64_decode($path);
        $disk = request()->get('filesystem', config('spork.filesystem.default'));


        return Inertia::render('Development/Index', [
            'title' => 'Settings',
            'settings' => new class()
            {
            },
            'files' => collect(Storage::disk($disk)->directories($decoded))
                ->map(fn ($directory) => [
                    'name' => basename($directory),
                    'file_path' => base64_encode($directory),
                    'is_directory' => true,
                ])
                ->concat(
                    collect(Storage::disk($disk)->files($decoded))
                        ->map(fn ($file) => [
                            'name' => $file,
                            'file_path' => base64_encode($file),
                            'is_directory' => false,
                        ])
                ),

        ]);
    }
}
