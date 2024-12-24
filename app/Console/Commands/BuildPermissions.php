<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Crud;
use App\Models\User;
use App\Policies\AbstractPolicy;
use App\Services\Code;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Laravel\Scout\Searchable;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class BuildPermissions extends Command
{
    protected $signature = 'build:permissions';

    protected $description = 'Command description';

    public function handle()
    {
        $searchableModels = Code::instancesOf(AbstractPolicy::class)->getClasses();

        $permissions = [
            'view_any_',
            'view_',
            'create_',
            'update_',
            'delete_',
            'delete_any_',
            'force_delete_',
            'force_delete_any_',
            'restore_',
            'restore_any_',
            'replicate_',
            'reorder_',
        ];
        $modelPermissions = [];

        foreach ($searchableModels as $model) {
            foreach ($permissions as $permission) {
                $modelPermissions[] = $permission . $model::MODEL_PERMISSION_NAME;
            }
        }
        $models = Code::instancesOf(Crud::class)->getClasses();

        foreach ($permissions as $permission) {
            foreach ($models as $crudModel) {
                $modelPermissions[] = $permission . Str::singular((new $crudModel)->getTable());
            }
        }

        $modelPermissions = array_values(array_unique($modelPermissions));

        $permissionIds = [];
        foreach ($modelPermissions as $permission) {
            $perm = Permission::firstOrCreate(['name' => $permission]);
            $permissionIds[] = $perm->id;
        }

        $role = Role::firstOrCreate(['name' => 'admin']);
        $role->permissions()->sync($permissionIds);

        $user = User::firstOrFail();

        $user->hasRole('admin') ?: $user->assignRole('admin');

        $role = Role::firstOrCreate(['name' => 'developer']);

        $user->hasRole('developer') ?: $user->assignRole('developer');
    }
}
