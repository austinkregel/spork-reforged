<?php

declare(strict_types=1);

namespace App\Actions\Spork\Servers\Manage;

use App\Actions\Spork\CustomAction;
use App\Contracts\ActionInterface;
use App\Models\Server;
use App\Services\SshService;
use Illuminate\Http\Request;

class StopAction extends CustomAction implements ActionInterface
{
    public function __construct(
        $name = 'Stop a server',
        $slug = 'stop-servers'
    ) {
        parent::__construct($name, $slug, models: [Server::class]);
    }

    /**
     * @throws \Exception
     */
    public function __invoke(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
        ]);

        $servers = Server::query()
            ->whereIn('id', $request->input('items'))
            ->get();

        foreach ($servers as $server) {
            $sshService = new SshService(
                $server->ip,
                $server->username,
                $server->credential->public_key,
                $server->credential->private_key,
                $server->port,
                decrypt($server->credential->pass_key)
            );
            $sshService->execute('shutdown -h');
        }
    }
}
