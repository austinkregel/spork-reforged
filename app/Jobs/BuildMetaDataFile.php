<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Domain\Media;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Process\Process;

class BuildMetaDataFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected string $path
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $process = new Process([
            '/usr/bin/mediainfo',
            '--Output=JSON',
            '--Full',
            $this->path,
        ]);
        $process->run(function ($e, $o) {
            $file = new \SplFileInfo($this->path);
            // Will only be executed on supported types.
            $name = $file->getBasename();
            $split = explode('.', $name);
            $extension = end($split);
            $path = $file->getPathname();

            $data = json_decode($o, true);

            $newFile = str_replace('/media/Downloads', '', $this->path);


            $metaPath = storage_path('meta'.str_replace('.'.$extension, '.json', $newFile));

            if (!file_exists((new Filesystem)->dirname($metaPath))) {
                (new Filesystem)
                    ->makeDirectory((new Filesystem)->dirname($metaPath), 0755, true);
            }

            $media = new Media(
                $this->path,
                false,
                array_values(array_filter($data['media']['track'], fn($track) => $track['@type'] === 'Audio')),
                array_values(array_filter($data['media']['track'], fn($track) => $track['@type'] === 'Text')),
            );

            $data['name'] = $name;
            $data['path'] = $path;
            // bytes
            $data['size'] = $file->getSize();
            $data['type'] = $file->getType();

            file_put_contents($metaPath, json_encode($media, JSON_PRETTY_PRINT));
        });
    }
}
