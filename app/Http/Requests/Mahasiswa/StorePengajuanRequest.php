<?php

namespace App\Http\Requests\Mahasiswa;

use Illuminate\Foundation\Http\FormRequest;

class StorePengajuanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole('mahasiswa');
    }

    public function rules(): array
    {
        return [
            'nama_tim'                     => 'nullable|string|max:150',
            'no_whatsapp'                  => 'required|string|max:20',
            'nama_lomba'                   => 'required|string|max:200',
            'bidang_id'                    => 'required|integer|exists:bidang_lomba,id',
            'tingkatan_id'                 => 'required|integer|exists:tingkatan_lomba,id',
            'tahapan_id'                   => 'required|integer|exists:tahapan_lomba,id',
            'semester'                     => 'required|integer|min:1|max:14',
            'detail_juara'                 => 'nullable|string|max:50',
            'mata_kuliah_ids'              => 'nullable|array',
            'mata_kuliah_ids.*'            => 'integer|exists:mata_kuliah,id',
            // Dokumen link / URL (zero upload file) - WAJIB (Mandatory)
            'link_sertifikat'              => 'required|url|max:500',
            'status_surat_tugas_mahasiswa' => 'nullable|boolean',
            'link_surat_tugas_mahasiswa'   => 'required|url|max:500',
            'status_surat_tugas_dosen'     => 'nullable|boolean',
            'link_surat_tugas_dosen'       => 'required|url|max:500',
            'link_poster'                  => 'required|url|max:500',
            'link_sosmed'                  => 'required|url|max:500',
            'keterangan'                   => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'semester.required'                   => 'Semester mahasiswa wajib dipilih.',
            'link_sertifikat.required'            => 'Tautan bukti sertifikat / piagam wajib diisi.',
            'link_sertifikat.url'                 => 'Format tautan sertifikat tidak valid (harus diawali http:// atau https://).',
            'link_surat_tugas_mahasiswa.required' => 'Tautan Surat Keputusan (SK) / Surat Tugas Mahasiswa wajib diisi.',
            'link_surat_tugas_mahasiswa.url'      => 'Format tautan surat tugas mahasiswa tidak valid (harus diawali http:// atau https://).',
            'link_surat_tugas_dosen.required'     => 'Tautan Surat Keputusan (SK) / Surat Tugas Dosen Pembimbing wajib diisi.',
            'link_surat_tugas_dosen.url'          => 'Format tautan surat tugas dosen pembimbing tidak valid (harus diawali http:// atau https://).',
            'link_poster.required'                => 'Tautan poster kegiatan lomba wajib diisi.',
            'link_poster.url'                     => 'Format tautan poster tidak valid.',
            'link_sosmed.required'                => 'Tautan publikasi media sosial wajib diisi.',
            'link_sosmed.url'                     => 'Format tautan publikasi media sosial tidak valid.',
        ];
    }
}
