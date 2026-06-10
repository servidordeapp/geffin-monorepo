<?php

declare(strict_types=1);

namespace App\Modules\Tenancy\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-tenants') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'lowercase',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique('tenants', 'slug'),
            ],
            'domain' => [
                'required',
                'string',
                Rule::unique('domains', 'domain'),
            ],
        ];
    }
}
