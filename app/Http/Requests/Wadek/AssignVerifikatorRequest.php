<?php

namespace App\Http\Requests\Wadek;

use Illuminate\Foundation\Http\FormRequest;

class AssignVerifikatorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id'  => ['required', 'integer', 'exists:users,id'],
            'prodi_id' => ['required', 'integer', 'exists:prodi,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required'  => 'user_id wajib diisi.',
            'user_id.exists'    => 'User dengan ID tersebut tidak ditemukan.',
            'prodi_id.required' => 'prodi_id wajib diisi.',
            'prodi_id.exists'   => 'Program studi dengan ID tersebut tidak ditemukan.',
        ];
    }
}
