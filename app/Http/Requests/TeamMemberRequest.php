<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TeamMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $teamId = $this->route('team')?->id;

        return [
            'user_id' => ['nullable', 'exists:users,id', Rule::unique('team_members', 'user_id')->ignore($teamId)],
            'name' => ['required', 'string', 'max:255'],
            'role' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'telegram_username' => ['nullable', 'string', 'max:255', 'regex:/^@?[A-Za-z0-9_]{5,}$/'],
            'description' => ['nullable', 'string', 'max:2000'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'photo_focus_x' => ['nullable', 'integer', 'min:0', 'max:100'],
            'photo_focus_y' => ['nullable', 'integer', 'min:0', 'max:100'],
            'position' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ];
    }
}
