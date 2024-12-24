<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Server;
use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class ServerAccessable
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(request()->hasHeader('Authentication'), 401, 'Unauthorized');
        $token = explode(' ', request()->header('Authentication'), 2);

        abort_unless(count($token) === 2, 401, 'Unauthorized no token');
        $split = explode('|', $token[1], 2);

        abort_unless(count($split) === 2, 401, 'Unauthorized malformed other token');

        $hashedToken = hash('sha256', $split[1]);
        $token = PersonalAccessToken::query()->where('token', $hashedToken)->firstOrFail();

        $tokenable = $token->tokenable;

        $intendedCredential = (int) request()->route('identifier');
        abort_unless($tokenable->credential_id === $intendedCredential, 401, 'Unauthorized, invalid credential');

        abort_unless($token->tokenable_type === Server::class, 404, 'Server not found');
        $request->merge(['server' => $token->tokenable]);

        return $next($request);
    }
}
