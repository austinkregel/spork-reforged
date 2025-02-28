<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServerRequest;
use App\Models\Credential;
use App\Models\Server;
use Illuminate\Http\JsonResponse;
use Laravel\Sanctum\NewAccessToken;

class ServerController extends Controller
{
    public function store(StoreServerRequest $request): JsonResponse
    {
        /** @var Credential $credential */
        $credential = $request->input('credential');
        /** @var Server $server */
        $server = $credential->servers()->create($request->all());

        /** @var NewAccessToken $token */
        $token = $server->createToken(
            $server->name.' Access Token',
            [
                'update_server',
                'delete_server',
                'view_server',
            ]
        );

        $server->setAttribute('access_token', $token->plainTextToken);

        $server->setRelation('tokens', [$token->accessToken]);

        return response()->json($server);
    }
}
