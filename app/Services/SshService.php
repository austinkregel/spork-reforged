<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Credential;
use App\Models\Server;
use App\Models\Spork\Script;
use App\Models\User;
use Exception;
use Illuminate\Support\Str;

class SshService
{
    protected mixed $connection;

    public function __construct(
        protected string $host,
        protected string $username,
        protected string $publicKeyFile,
        protected string $privateKeyFile,
        protected int $port = 22,
        protected ?string $passKey = null,
    ) {
        $this->connection = ssh2_connect($this->host, $this->port);

        if (! $this->connection) {
            throw new Exception('Connection failed.');
        }

        //        try {
        ssh2_auth_pubkey_file(
            $this->connection,
            $username,
            $publicKeyFile,
            $privateKeyFile,
            $passKey
        );
        //        } catch (\Throwable $e) {
        //            throw new Exception('Public key authentication failed. ' . $this->username . '@' . $this->host . ':' . $this->port);
        //        }
    }

    public function __destruct()
    {
        if (is_resource($this->connection)) {
            ssh2_disconnect($this->connection);
        }
    }

    public function execute(string $command, string $directory = ''): string
    {
        if (! empty($directory)) {
            $command = 'cd '.escapeshellarg($directory).' && '.$command;
        }

        $stream = ssh2_exec($this->connection, $command);

        if (! $stream) {
            throw new Exception('Command execution failed.');
        }

        stream_set_blocking($stream, true);
        $stream_out = ssh2_fetch_stream($stream, SSH2_STREAM_STDIO);

        return stream_get_contents($stream_out);
    }

    public function run(Script $script, string $directory = ''): array
    {
        $localFilePath = storage_path('scripts/'.Str::slug($script->name).'_server.sh');
        @mkdir(dirname($localFilePath), 0755, true);
        file_put_contents($localFilePath, $script->script);

        // Ensure our .spork folder exists
        $exec = ssh2_exec($this->connection, 'mkdir /tmp/.spork -f');
        stream_set_blocking($exec, true);

        // Create a local copy of our script to make sure it runs like we'd expect.
        ssh2_scp_send($this->connection, $localFilePath, $file = '/tmp/.spork/'.Str::random(32).'.sh', 0644);

        unlink($localFilePath);
        try {
            // Run a command that will probably write to stderr (unless you have a folder named /hom)
            $stream_out = ssh2_exec($this->connection, 'bash '.escapeshellcmd($file).' 2>&1');
            stream_set_blocking($stream_out, true);

            $stream_error = ssh2_fetch_stream($stream_out, SSH2_STREAM_STDERR);
            ssh2_exec($this->connection, "rm $file -f");
        } catch (\Throwable $e) {
            return [
                'stdout' => '',
                'stderr' => $e->getMessage()."\n".$e->getTraceAsString(),
            ];
        }

        return [
            'stdout' => stream_get_contents($stream_out),
            'stderr' => stream_get_contents($stream_error),
        ];
    }

    public static function fromServer(Server $server): static
    {
        // Try the internal server first, if that doesn't work, we need to go in the front door.
        try {
            return new static(
                $server->internal_ip_address,
                'root',
                $server->credential->settings['pub_key_file'],
                $server->credential->settings['private_key_file'],
                22,
                $server->credential->settings['pass_key'] ?? null
            );
        } catch (\Throwable $e) {
            return new static(
                $server->ip_address,
                'root',
                $server->credential->settings['pub_key_file'],
                $server->credential->settings['private_key_file'],
                22,
                $server->credential->settings['pass_key'] ?? null
            );
        }
    }

    /**
     * @throws Exception
     */
    public static function factory(string $host, User $user): Credential
    {
        $credential = $user->credentials()->where(array_merge([
            'service' => Credential::TYPE_SSH,
            'type' => Credential::TYPE_SSH,
        ]))->first();

        if (isset($credential)) {
            return $credential;
        }

        [$privateKey, $publicKey] = app(SshKeyGeneratorService::class)->generate('');

        $randomName = Str::random(16);
        $publicKeyFile = storage_path('app/keys/'.$randomName.'.pub');
        $privateKeyFile = storage_path('app/keys/'.$randomName);

        file_put_contents($publicKeyFile, $publicKey);
        chmod($publicKeyFile, 0600);
        file_put_contents($privateKeyFile, $privateKey);
        chmod($privateKeyFile, 0600);

        return $user->credentials()->create([
            'service' => Credential::TYPE_SSH,
            'type' => Credential::TYPE_SSH,
            'name' => 'SSH '.$host,
            'api_key' => Str::random(32),
            'settings' => [
                'pub_key' => $publicKey,
                'pub_key_file' => $publicKeyFile,
                'private_key' => encrypt($privateKey),
                'private_key_file' => $privateKeyFile,
                'pass_key' => ! empty($passKey) ? encrypt($passKey) : '',
            ],
        ]);
    }
}
