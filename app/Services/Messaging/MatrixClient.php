<?php

declare(strict_types=1);

namespace App\Services\Messaging;

use Illuminate\Support\Facades\Http;

class MatrixClient
{
    public function __construct(
        protected string $homeserver = 'beeper.com',
    ) {
    }

    public function discover()
    {
        //        $types = Http::withHeaders([
        //                 'Accept' => 'application/json',
        //                 'Content-type' => 'application/json',
        //             ])->get('https://matrix.'.$this->homeserver.'/_matrix/client/r0/login')->json()
        $rooms = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '.env('MATRIX_ACCESS_TOKEN'),

        ])->get('https://matrix.'.$this->homeserver.'/_matrix/client/v3/joined_rooms', [
            //            'type' => 'm.login.application_service',
            //            'identifier' => [
            //                "type" => "m.id.user",
            //                'user' => 'austinkregel',
            //
            //            ]
        ])->json('joined_rooms');

        dd(array_reduce($rooms, function ($all, $room) {
            $roomAliases = cache()->rememberForever('room-cache'.$room, fn () => Http::withHeaders([
                'Accept' => 'application/json',
                'Authorization' => 'Bearer '.env('MATRIX_ACCESS_TOKEN'),
            ])->get('https://matrix.'.$this->homeserver.'/_matrix/client/v3/rooms/'.$room.'/aliases')->json('aliases'));

            echo $room."\n";

            return array_merge(
                $all,
                [
                    $room => $roomAliases,
                ],
            );
        }, []));
    }

    public function requestCodeForBeeper(string $email): string
    {
        return cache()->remember(md5(json_encode([
            'beeper', '|',
            $email,
        ])), now()->addMinutes(30), function () use ($email) {
            $request = Http::withHeaders([
                'Accept' => 'application/json',
                'Authorization' => 'Bearer BEEPER-PRIVATE-API-PLEASE-DONT-USE',
            ])->post('https://api.beeper.com/user/login', [
            ])->json('request');

            Http::withHeaders([
                'Accept' => 'application/json',
                'Authorization' => 'Bearer BEEPER-PRIVATE-API-PLEASE-DONT-USE',
            ])->post('https://api.beeper.com/user/login/email', [
                'request' => $request,
                'email' => $email,
            ])->body();

            return $request;
        });
    }

    public function loginWithPassword(string $username, string $password): array
    {
        $request = Http::withHeaders([
            'Accept' => 'application/json',
        ])->post(config('services.matrix.url').'/_matrix/client/v3/login', [
            'identifier' => [
                'type' => 'm.id.user',
                'user' => $username,
            ],
            'initial_device_display_name' => config('app.name'),
            'password' => $password,
            'type' => 'm.login.password',
        ])->json();

        return $request;
    }

    public function loginWithBeeperCode(string $email, string $code): array
    {
        if (! cache()->has(md5(json_encode([
            'beeper', '|',
            $email,
        ])))) {
            abort(404);
        }

        return Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer BEEPER-PRIVATE-API-PLEASE-DONT-USE',
        ])->post('https://api.beeper.com/user/login/response', [
            'request' => cache()->get(md5(json_encode([
                'beeper', '|',
                $email,
            ]))),
            'response' => $code,
        ])->json();
    }

    public function loginWithJwt(string $jwt): array
    {
        $login = Http::withHeaders([
            'Accept' => 'application/json',
        ])->post('https://matrix.'.$this->homeserver.'/_matrix/client/v3/login', [
            'type' => 'org.matrix.login.jwt',
            'token' => $jwt,
        ])->json();

        return $login;
    }

    public function devices(string $jwt): array
    {
        dd(Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '.$jwt,
        ])->get('https://matrix.'.$this->homeserver.'/_matrix/client/v3/devices')->json());
    }

    public function sendMessage(
        string $body,
        string $room,
        string $jwt,
    ) {
        $eventId = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '.$jwt,
        ])->post('https://matrix.'.$this->homeserver.'/_matrix/client/r0/rooms/'.$room.'/send/m.room.message', [
            'msgtype' => 'm.text',
            'body' => $body,
        ])->json();
    }
}
