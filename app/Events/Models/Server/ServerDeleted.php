<?php

declare(strict_types=1);

namespace App\Events\Models\Server;

use App\Events\AbstractLogicalEvent;
use App\Models\Server;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class ServerDeleted extends AbstractLogicalEvent implements ShouldBroadcastNow
{
    public function __construct(
        public Server $model,
    ) {}

    public function broadcastOn()
    {
        $this->model->load('credential');

        return [
            new PrivateChannel('App.Models.User.'.$this->model->credential->user_id),
        ];
    }
}
