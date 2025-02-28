<?php

declare(strict_types=1);

namespace App\Events\Models\Email;

use App\Events\AbstractLogicalEvent;
use App\Models\Email;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class EmailCreated extends AbstractLogicalEvent implements ShouldBroadcast
{
    public function __construct(
        public Email $model,
    ) {
    }

    public function broadcastOn(): array
    {
        $this->model->load('credential');

        return [
            new PrivateChannel('App.Models.User.'.$this->model->credential->user_id),
        ];
    }
}
