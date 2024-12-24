<?php

declare(strict_types=1);

namespace Tests;

use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Spatie\Permission\Models\Permission;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $app = require Application::inferBasePath().'/bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

    public function getProperty($object, string $property)
    {
        $reflection = new \ReflectionClass($object);
        $property = $reflection->getProperty($property);
        $property->setAccessible(true);
        return $property->getValue($object);
    }

    public function createUserWithRole(string $role)
    {
        $user = User::factory()->create();
        $user->assignRole($role);
        return $user;
    }

    public function createUserWithPermissions(array $permissions)
    {
        $user = User::factory()->create();

        foreach ($permissions as $permission) {
            $user->givePermissionTo(Permission::firstOrCreate(['name' => $permission]));
        }
        return $user;
    }
}
