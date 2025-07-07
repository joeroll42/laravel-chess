<?php

namespace App\Http\Requests\Settings;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'chess_com_link' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('users', 'chess_com_link')->ignore($this->user()->id)->whereNotNull('chess_com_link'),
            ],
            'lichess_link' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('users', 'lichess_link')->ignore($this->user()->id)->whereNotNull('lichess_link'),
            ],
        ];
    }
}
