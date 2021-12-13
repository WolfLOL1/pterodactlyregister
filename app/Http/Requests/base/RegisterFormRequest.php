<?php

namespace Pterodactyl\Http\Requests\Base;

use Pterodactyl\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class RegisterFormRequest extends FormRequest
{
    /**
     * Rules to apply to requests for updating or creating a user
     * in the Admin CP.
     */
    public function rules()
    {
        $rules = collect(User::getCreateRules());
        if ($this->method() === 'PATCH') {
            $rules = collect(User::getUpdateRulesForId($this->route()->parameter('user')->id))->merge([
                'ignore_connection_error' => ['sometimes', 'nullable', 'boolean'],
            ]);
        }

        return $rules->only([
            'email', 'username', 'name_first', 'name_last', 'password',
            'language', 'ignore_connection_error', 'root_admin',
        ])->toArray();
    }
    public function normalize(array $only = null)
    {
        return $this->only($only ?? array_keys($this->rules()));
    }
}
