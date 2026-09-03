<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Models\Prodi;
use App\Models\VerifikatorProdi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VerifikatorDirektoriController extends Controller
{
    /**
     * GET /api/direktori-verifikator
     * List verifikator aktif dikelompokkan per prodi.
     * Accessible oleh semua role yang terautentikasi.
     */
    public function index(Request $request): JsonResponse
    {
        // Ambil semua prodi sebagai base (prodi tanpa verifikator tetap muncul)
        $prodis = Prodi::orderBy('id')->get();

        // Ambil semua verifikator aktif beserta relasinya
        $verifikators = VerifikatorProdi::with([
            'user:id,nim_nip,nama,email,no_whatsapp',
            'prodi:id,kode,singkatan,nama',
        ])
            ->where('is_active', 1)
            ->orderBy('prodi_id')
            ->get()
            ->groupBy('prodi_id');

        // Susun output grouped by prodi
        $data = $prodis->map(function (Prodi $prodi) use ($verifikators) {
            $timVerifikator = $verifikators->get($prodi->id, collect());

            return [
                'prodi_id'      => $prodi->id,
                'prodi'         => $prodi->singkatan,
                'nama_prodi'    => $prodi->nama,
                'verifikators'  => $timVerifikator->map(fn($vp) => [
                    'id'          => $vp->id,
                    'user_id'     => $vp->user_id,
                    'nama'        => $vp->user->nama,
                    'nim_nip'     => $vp->user->nim_nip,
                    'email'       => $vp->user->email,
                    'no_whatsapp' => $vp->user->no_whatsapp,
                ])->values(),
                'jumlah'        => $timVerifikator->count(),
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data'    => $data,
        ]);
    }
}
