<?php

declare(strict_types=1);

namespace Tests\Integration\ConditionalLogic;

use App\Models\User;
use App\Services\ConditionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConditionalServiceTest extends TestCase
{
    use RefreshDatabase;

    public function testWeShowLoginRouteWhenUnauthenticated(): void
    {
        $service = new ConditionService();

        $navigation = $service->navigation();

        $this->assertCount(1, $navigation);

        $items = $navigation->toArray()[''];

        $this->assertCount(1, $items);

        $login = $items[0];

        $this->assertSame('Login', $login['name']);
        $this->assertSame('/login', $login['href']);
    }

    public function testWeShowDashboardRoutesWhenLoggedIn(): void
    {
        $service = new ConditionService();
        $user = User::factory()->create();
        auth()->login($user);

        $navigation = $service->navigation();

        $items = $navigation->toArray()[''];

        //        $this->assertCount(8, $items);

        $this->assertSame([
            0 => 'Dashboard',
            1 => 'Projects',
        ], array_map(fn ($item) => $item['name'], $items));
    }

    public function testWeShowLogicRouteWhenLoggedInAndLocal(): void
    {
        config(['app.env' => 'local']);

        $service = new ConditionService();
        $user = User::factory()->create();
        auth()->login($user);

        $navigation = $service->navigation();

        $items = $navigation->toArray();

        $this->assertCount(5, $items);
        $this->assertCount(2, $items['']);

        $this->assertSame([
            0 => 'Dashboard',
            1 => 'Projects',
        ], array_map(fn ($item) => $item['name'], $items['']));
    }
}
