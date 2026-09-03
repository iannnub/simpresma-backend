<?php

namespace App\Http\Requests\Tendik;

use Illuminate\Foundation\Http\FormRequest;

class FinalisasiPengajuanRequest extends FormRequest
{
    /**
     * Hanya tendik yang terautentikasi yang boleh akses.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi finalisasi:
     * - nilai_per_mk : array wajib, minimal 1 item
     * - nilai_per_mk.*.mk_id : integer, wajib ada
     * - nilai_per_mk.*.huruf_nilai : string, wajib ada (kesesuaian snapshot dicek di Service)
     * - link_sk_konversi : URL valid, opsional (nullable)
     */
    public function rules(): array
    {
        return [
            'nilai_per_mk'               => ['nullable', 'array'],
            'nilai_per_mk.*.mk_id'       => ['required_with:nilai_per_mk', 'integer', 'exists:mata_kuliah,id'],
            'nilai_per_mk.*.huruf_nilai' => ['required_with:nilai_per_mk', 'string', 'max:5'],
            'link_sk_konversi'           => ['nullable', 'url', 'max:2048'],
            'catatan_tendik'             => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Pesan error kustom.
     */
    public function messages(): array
    {
        return [
            'nilai_per_mk.required'               => 'Data nilai per mata kuliah wajib diisi.',
            'nilai_per_mk.array'                  => 'Data nilai per mata kuliah harus berupa array.',
            'nilai_per_mk.min'                    => 'Minimal 1 mata kuliah harus diinput nilainya.',
            'nilai_per_mk.*.mk_id.required'       => 'ID mata kuliah pada setiap item wajib diisi.',
            'nilai_per_mk.*.mk_id.integer'        => 'ID mata kuliah harus berupa angka.',
            'nilai_per_mk.*.mk_id.exists'         => 'Mata kuliah dengan ID yang diberikan tidak ditemukan.',
            'nilai_per_mk.*.huruf_nilai.required' => 'Huruf nilai pada setiap item mata kuliah wajib diisi.',
            'nilai_per_mk.*.huruf_nilai.string'   => 'Huruf nilai harus berupa teks.',
            'nilai_per_mk.*.huruf_nilai.max'      => 'Huruf nilai maksimal 5 karakter.',
            'link_sk_konversi.url'                => 'Link SK Konversi harus berupa URL yang valid (contoh: https://drive.google.com/...).',
            'link_sk_konversi.max'                => 'Link SK Konversi maksimal 2048 karakter.',
        ];
    }
}
