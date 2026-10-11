<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            // Ignore this user's own row so keeping the same email is allowed.
            'email' => [
                'required', 'string', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($this->route('user')),
            ],
            // Optional: an empty password means "leave it unchanged".
            'password' => ['nullable', 'string', Password::min(8)],
            'role' => ['required', Rule::in(User::ROLES)],
        ];
    }
}
