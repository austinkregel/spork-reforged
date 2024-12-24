<?php

declare(strict_types=1);

namespace App\Events\Models\Server;

use App\Events\AbstractLogicalEvent;
use App\Models\Credential;
use App\Models\Server;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class ServerCreating extends AbstractLogicalEvent implements ShouldBroadcastNow
{
    public function __construct(
        public Server $model,
    ) {
    }

    public function broadcastOn()
    {
        $this->model->load('credential');
        $credential = Credential::find($this->model->credential_id);

        if (empty($credential)) {
            return [];
        }

        return [
            new PrivateChannel('App.Models.User.'.$credential->user_id),
        ];
    }
}
