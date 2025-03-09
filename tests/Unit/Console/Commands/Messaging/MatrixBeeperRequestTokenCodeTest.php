<?php

declare(strict_types=1);

namespace Tests\Unit\Console\Commands\Messaging;

use App\Console\Commands\Messaging\MatrixBeeperRequestTokenCode;
use App\Services\Messaging\MatrixClient;
use Illuminate\Console\Command;
use Mockery;
use Tests\TestCase;

class MatrixBeeperRequestTokenCodeTest extends TestCase
{
    public function test_handle(): void
    {
        $email = 'test@example.com';
        $code = '123456';
        $host = 'beeper.com';

        $matrixClientMock = Mockery::mock(MatrixClient::class);
        $matrixClientMock->shouldReceive('loginWithJwt')
            ->with($code)
            ->once()
            ->andReturn('mocked_response');

        $this->app->instance(MatrixClient::class, $matrixClientMock);

        $command = Mockery::mock(MatrixBeeperRequestTokenCode::class.'[argument,option,info]', [
            'argument' => $email,
            'option' => $host,
        ]);

        $command->shouldReceive('argument')
            ->with('email')
            ->andReturn($email);
        $command->shouldReceive('argument')
            ->with('code')
            ->andReturn($code);
        $command->shouldReceive('option')
            ->with('host')
            ->andReturn($host);
        $command->shouldReceive('info')
            ->with('Login successful')
            ->once();

        $command->handle();
    }
}
