<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Deployment extends Model
{
    public $table = 'deployment';

    public function domain()
    {
        return $this->belongsTo(Domain::class, 'primary_domain_id', 'id');
    }

    public function server()
    {
        return $this->belongsTo(Server::class, 'primary_server_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function domains()
    {
        return $this->morphedByMany(Domain::class, 'resource', 'deployment_resources')->withPivot('settings');
    }

    public function servers()
    {
        return $this->morphedByMany(Server::class, 'resource', 'deployment_resources');
    }
}
