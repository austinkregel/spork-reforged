<?php

declare(strict_types=1);

namespace App\Http\Requests\Dynamic;

use App\Models\Crud;
use App\Models\User;
use App\Policies\AbstractPolicy;
use App\Services\Code;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class AbstractRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $route = str_replace('api/crud/', '', $this->path());

        $modelsBySingular = array_reduce(
            Code::instancesOf(Crud::class)->getClasses(),
            fn ($carry, $item) => array_merge($carry, [(new $item)->getTable() => $item]),
            []
        );

        $singular = Str::singular((new $modelsBySingular[$route])->getTable());

        /** @var User $user */
        $user = auth()->user();

        $permissions = $user->getAllPermissions()->map(fn ($permission) => $permission->name);

        return $permissions->contains('view_any_'.$singular);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [];
    }
}
