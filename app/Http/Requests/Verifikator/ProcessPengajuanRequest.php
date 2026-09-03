<?php

namespace App\Http\Requests\Verifikator;

use Illuminate\Foundation\Http\FormRequest;

class ProcessPengajuanRequest extends FormRequest
{
    /**
     * Hanya verifikator yang terautentikasi yang boleh akses.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi:
     * - feedback_verifikator: wajib diisi dan tidak boleh kosong jika aksi = 'tolak'
     */
    public function rules(): array
    {
        return [
            'feedback_verifikator' => [
                'required',
                'string',
                'min:1',
                'max:1000',
            ],
        ];
    }

    /**
     * Pesan error kustom.
     */
    public function messages(): array
    {
        return [
            'feedback_verifikator.required' => 'Alasan penolakan (feedback) wajib diisi.',
            'feedback_verifikator.string'   => 'Feedback harus berupa teks.',
            'feedback_verifikator.min'      => 'Feedback tidak boleh kosong.',
            'feedback_verifikator.max'      => 'Feedback maksimal 1000 karakter.',
        ];
    }
}
