<?php

declare(strict_types=1);

namespace App\Actions\Spork\Servers\Manage;

use App\Actions\Spork\CustomAction;
use App\Contracts\ActionInterface;
use App\Models\Server;
use Illuminate\Http\Request;

class StartAction extends CustomAction implements ActionInterface
{
    public function __construct(
        $name = 'Start a server',
        $slug = 'start-servers'
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

        /// Hmm can't exactly ssh to turn these things on...
    }
}
