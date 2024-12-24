<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Code;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Laravel\Scout\Searchable;

class BulkScoutImport extends Command
{
    protected $signature = 'scout:bulk-import';

    protected $description = 'Command description';

    public function handle()
    {
        $searchableModels = Code::instancesOf(Searchable::class)
            ->getClasses();

        foreach ($searchableModels as $model) {
            Artisan::call('scout:import', [
                'model' => $model,
            ], $this->output);
        }
    }
}
