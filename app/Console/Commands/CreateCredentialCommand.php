<?php

namespace App\Console\Commands;

use App\Models\Credential;
use Illuminate\Console\Command;

class CreateCredentialCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:credential';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new credential';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Creating a new credential');
        $data = [
            'type' => $this->choice('What type of credential is this?', [
                Credential::TYPE_MATRIX,
                Credential::TYPE_DOMAIN,
                Credential::TYPE_EMAIL,
                Credential::TYPE_FINANCE,
                Credential::TYPE_SSH,
                Credential::TYPE_SOURCE,
                Credential::TYPE_DEVELOPMENT,
                Credential::TYPE_REGISTRAR,
                Credential::TYPE_SERVER,
            ]),
            'username' => $this->ask('What is the username?'),
            'password' => $this->secret('What is the password?'),
        ];

        Credential::forceCreate($data);

        $this->info('Credential created successfully');
    }
}
