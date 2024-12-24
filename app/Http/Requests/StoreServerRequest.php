<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Credential;
use App\Models\Server;
use App\Services\Development\DescribeTableService;
use Illuminate\Foundation\Http\FormRequest;

class StoreServerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if (! $this->hasHeader('Authentication')) {
            info('no auth header');

            return false;
        }

        if (! $this->hasHeader('User-agent')) {
            info('no user agent header', [
                'user_agent' => $this->header('User-agent'),

            ]);

            return false;
        }
        // Does the user agent match the pattern (\w|\d)+@(\w|\d)+:installer:
        preg_match('/^([\w|\d]+)@([\w|\d|\-|\_]+):(installer)$/i', $this->header('User-agent'), $matches);

        if (empty($matches)) {
            info('no matches on installer user agent', [
                'user_agent' => $this->header('User-agent'),
            ]);

            return false;
        }
        if (count($matches) !== 4) {
            info('not enough matches with user agent', [
                'user_agent' => $this->header('User-agent'),
                'matches' => $matches,
            ]);

            return false;
        }

        [$full, $user, $host, $type] = $matches;

        if ($type !== 'installer') {
            info('no not installer type, must beinstaller');

            return false;
        }

        [$bearer, $token] = explode(' ', $this->header('Authentication'), 2);
        $credential = Credential::firstWhere('api_key', $token);

        if (! $credential) {
            info('no credential');

            return false;
        }

        $this->merge([
            'credential' => $credential,
        ]);

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $description = new DescribeTableService();
        $fields = $description->describe(new Server());

        return array_reduce($fields['required'], function ($carry, $field) {
            return array_merge($carry, [
                $field => 'required',
            ]);
        }, []);
    }
}
