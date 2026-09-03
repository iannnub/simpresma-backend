<?php

namespace App\Http\Requests\Wadek;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateMatriksRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi update matriks:
     * - min_sks: integer nullable (null = kombinasi tidak valid/tidak menghasilkan konversi)
     * - max_sks: integer nullable, wajib >= min_sks jika keduanya diisi
     * - huruf_nilai: string nullable max 5
     */
    public function rules(): array
    {
        return [
            'min_sks'     => ['nullable', 'integer', 'min:0'],
            'max_sks'     => ['nullable', 'integer', 'min:0'],
            'huruf_nilai' => ['nullable', 'string', 'max:5'],
        ];
    }

    /**
     * Validasi after: max_sks >= min_sks jika keduanya tidak null.
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                $minSks = $this->input('min_sks');
                $maxSks = $this->input('max_sks');

                if (!is_null($minSks) && !is_null($maxSks) && (int) $maxSks < (int) $minSks) {
                    $validator->errors()->add('max_sks', 'Nilai max_sks harus lebih besar atau sama dengan min_sks.');
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'min_sks.integer'     => 'min_sks harus berupa bilangan bulat.',
            'min_sks.min'         => 'min_sks tidak boleh negatif.',
            'max_sks.integer'     => 'max_sks harus berupa bilangan bulat.',
            'max_sks.min'         => 'max_sks tidak boleh negatif.',
            'huruf_nilai.string'  => 'huruf_nilai harus berupa teks.',
            'huruf_nilai.max'     => 'huruf_nilai maksimal 5 karakter.',
        ];
    }
}
