<?php

namespace Database\Seeders;

use App\Models\MataKuliah;
use App\Models\Prodi;
use Illuminate\Database\Seeder;

class MataKuliahSeeder extends Seeder
{
    public function run(): void
    {
        $prodiSI = Prodi::where('singkatan', 'SI')->first()->id;
        $prodiTI = Prodi::where('singkatan', 'TI')->first()->id;
        $prodiIF = Prodi::where('singkatan', 'IF')->first()->id;

        $data = [
            ['prodi_id' => $prodiSI, 'kode_mk' => 'MKI9007', 'nama_mk' => 'Manajemen dan Kewirausahaan', 'sks' => 2, 'semester' => 3],
            ['prodi_id' => $prodiSI, 'kode_mk' => 'KSI1408', 'nama_mk' => 'Rekayasa Proses Bisnis', 'sks' => 3, 'semester' => 5],
            ['prodi_id' => $prodiTI, 'kode_mk' => 'MKI9007', 'nama_mk' => 'Manajemen dan Kewirausahaan', 'sks' => 2, 'semester' => 3],
            ['prodi_id' => $prodiTI, 'kode_mk' => 'KTU1053', 'nama_mk' => 'Model Bisnis Digital', 'sks' => 3, 'semester' => null],
            ['prodi_id' => $prodiTI, 'kode_mk' => 'KTU1055', 'nama_mk' => 'Analisi Pasar dan Validasi Produk', 'sks' => 3, 'semester' => null],
            ['prodi_id' => $prodiIF, 'kode_mk' => 'MKI9007', 'nama_mk' => 'Manajemen dan Kewirausahaan', 'sks' => 2, 'semester' => 3],
            ['prodi_id' => $prodiSI, 'kode_mk' => 'KSI1413', 'nama_mk' => 'Profesional Issue', 'sks' => 2, 'semester' => 4],
            ['prodi_id' => $prodiTI, 'kode_mk' => 'KSI1413', 'nama_mk' => 'Profesional Issue', 'sks' => 2, 'semester' => 4],
            ['prodi_id' => $prodiIF, 'kode_mk' => 'KSI1413', 'nama_mk' => 'Profesional Issue', 'sks' => 2, 'semester' => 4],
            ['prodi_id' => $prodiSI, 'kode_mk' => 'KSU1107', 'nama_mk' => 'IMK', 'sks' => 2, 'semester' => 1],
            ['prodi_id' => $prodiSI, 'kode_mk' => 'KMU1010', 'nama_mk' => 'UI/UX Design', 'sks' => 4, 'semester' => null],
            ['prodi_id' => $prodiSI, 'kode_mk' => 'KMU1042', 'nama_mk' => 'UI/UX Testing', 'sks' => 3, 'semester' => null],
            ['prodi_id' => $prodiSI, 'kode_mk' => 'KIU1047', 'nama_mk' => 'UI/UX Design', 'sks' => 3, 'semester' => null],
            ['prodi_id' => $prodiTI, 'kode_mk' => 'KST1205', 'nama_mk' => 'IMK', 'sks' => 3, 'semester' => 1],
            ['prodi_id' => $prodiTI, 'kode_mk' => 'KST1304', 'nama_mk' => 'UI/UX Desain', 'sks' => 3, 'semester' => 3],
            ['prodi_id' => $prodiTI, 'kode_mk' => 'KMU1010', 'nama_mk' => 'UI/UX Design', 'sks' => 4, 'semester' => null],
            ['prodi_id' => $prodiTI, 'kode_mk' => 'KMU1042', 'nama_mk' => 'UI/UX Testing', 'sks' => 3, 'semester' => null],
            ['prodi_id' => $prodiIF, 'kode_mk' => 'KSU1107', 'nama_mk' => 'IMK', 'sks' => 2, 'semester' => 1],
            ['prodi_id' => $prodiIF, 'kode_mk' => 'KMU1010', 'nama_mk' => 'UI/UX Design', 'sks' => 4, 'semester' => null],
            ['prodi_id' => $prodiIF, 'kode_mk' => 'KMU1042', 'nama_mk' => 'UI/UX Testing', 'sks' => 3, 'semester' => null],
            ['prodi_id' => $prodiSI, 'kode_mk' => 'KSU1104', 'nama_mk' => 'Algoritma dan Pemrograman I', 'sks' => 3, 'semester' => 1],
            ['prodi_id' => $prodiSI, 'kode_mk' => 'KSU1206', 'nama_mk' => 'Algoritma dan Pemrograman II', 'sks' => 3, 'semester' => 2],
            ['prodi_id' => $prodiSI, 'kode_mk' => 'KSU1208', 'nama_mk' => 'Pemrograman Berorientasi Obyek', 'sks' => 4, 'semester' => 3],
            ['prodi_id' => $prodiSI, 'kode_mk' => 'KSI1412', 'nama_mk' => 'Pemrograman Berbasis Web', 'sks' => 4, 'semester' => 4],
            ['prodi_id' => $prodiSI, 'kode_mk' => 'KMU1012', 'nama_mk' => 'Perancangan Website Lanjut', 'sks' => 4, 'semester' => null],
            ['prodi_id' => $prodiSI, 'kode_mk' => 'KSI1409', 'nama_mk' => 'Pemrograman Berbasis Web', 'sks' => 3, 'semester' => null],
            ['prodi_id' => $prodiTI, 'kode_mk' => 'KST1102', 'nama_mk' => 'Algoritma dan Pemrograman', 'sks' => 3, 'semester' => 1],
            ['prodi_id' => $prodiTI, 'kode_mk' => 'KST1204', 'nama_mk' => 'Pemrograman Berorientasi Obyek', 'sks' => 3, 'semester' => 2],
            ['prodi_id' => $prodiTI, 'kode_mk' => 'KST1407', 'nama_mk' => 'Pemrograman Berbasis Web', 'sks' => 3, 'semester' => 3],
            ['prodi_id' => $prodiTI, 'kode_mk' => 'KST1510', 'nama_mk' => 'Pemrograman Berbasis Mobile', 'sks' => 3, 'semester' => 4],
            ['prodi_id' => $prodiTI, 'kode_mk' => 'KMU1012', 'nama_mk' => 'Perancangan Website Lanjut', 'sks' => 4, 'semester' => null],
            ['prodi_id' => $prodiIF, 'kode_mk' => 'KSF1102', 'nama_mk' => 'Bahasa Pemrograman', 'sks' => 3, 'semester' => 1],
            ['prodi_id' => $prodiIF, 'kode_mk' => 'KSF1205', 'nama_mk' => 'Algoritma dan Pemrograman', 'sks' => 3, 'semester' => 2],
            ['prodi_id' => $prodiIF, 'kode_mk' => 'KSU1208', 'nama_mk' => 'Pemrograman Berorientasi Obyek', 'sks' => 4, 'semester' => 3],
            ['prodi_id' => $prodiIF, 'kode_mk' => 'KSF1409', 'nama_mk' => 'Pemrograman Berbasis Website', 'sks' => 3, 'semester' => 4],
            ['prodi_id' => $prodiIF, 'kode_mk' => 'KMU1012', 'nama_mk' => 'Perancangan Website Lanjut', 'sks' => 4, 'semester' => null],
            ['prodi_id' => $prodiIF, 'kode_mk' => 'KSI1409', 'nama_mk' => 'Pemrograman Berbasis Web', 'sks' => 3, 'semester' => null],
            ['prodi_id' => $prodiIF, 'kode_mk' => 'KSF1418', 'nama_mk' => 'Pemrograman Berbasis Mobile', 'sks' => 3, 'semester' => 4],
            ['prodi_id' => $prodiSI, 'kode_mk' => 'KSU1205', 'nama_mk' => 'Pengantar Rekayasa Perangkat Lunak', 'sks' => 2, 'semester' => 2],
            ['prodi_id' => $prodiSI, 'kode_mk' => 'KSI1302', 'nama_mk' => 'Analisa dan Perancangan Sistem', 'sks' => 3, 'semester' => 3],
            ['prodi_id' => $prodiSI, 'kode_mk' => 'KSI1306', 'nama_mk' => 'Object Oriented Design', 'sks' => 3, 'semester' => 3],
            ['prodi_id' => $prodiSI, 'kode_mk' => 'KSU1501', 'nama_mk' => 'Pengembangan PL Untuk Agroindustri Modern', 'sks' => 3, 'semester' => 4],
            ['prodi_id' => $prodiTI, 'kode_mk' => 'KSU1205', 'nama_mk' => 'Pengantar Rekayasa Perangkat Lunak', 'sks' => 2, 'semester' => 2],
            ['prodi_id' => $prodiTI, 'kode_mk' => 'KST1404', 'nama_mk' => 'Analisa dan Desain Perangkat Lunak', 'sks' => 3, 'semester' => 4],
            ['prodi_id' => $prodiTI, 'kode_mk' => 'KSU1501', 'nama_mk' => 'Pengembangan PL Untuk Agroindustri Modern', 'sks' => 3, 'semester' => 4],
            ['prodi_id' => $prodiIF, 'kode_mk' => 'KSU1205', 'nama_mk' => 'Pengantar Rekayasa Perangkat Lunak', 'sks' => 2, 'semester' => 2],
            ['prodi_id' => $prodiIF, 'kode_mk' => 'KSF1401', 'nama_mk' => 'Analisa dan Desain Perangkat Lunak', 'sks' => 3, 'semester' => 3],
            ['prodi_id' => $prodiIF, 'kode_mk' => 'KSU1501', 'nama_mk' => 'Pengembangan PL Untuk Agroindustri Modern', 'sks' => 3, 'semester' => 5],
            ['prodi_id' => $prodiSI, 'kode_mk' => 'KSU1601', 'nama_mk' => 'Metodologi Penelitian', 'sks' => 2, 'semester' => 3],
            ['prodi_id' => $prodiTI, 'kode_mk' => 'KSU1601', 'nama_mk' => 'Metodologi Penelitian', 'sks' => 2, 'semester' => 3],
            ['prodi_id' => $prodiIF, 'kode_mk' => 'KSF1513', 'nama_mk' => 'Metodologi Penelitian dan Proposal 1', 'sks' => 4, 'semester' => 5],
            ['prodi_id' => $prodiSI, 'kode_mk' => 'KSU1207', 'nama_mk' => 'Matematika Diskrit', 'sks' => 2, 'semester' => 2],
            ['prodi_id' => $prodiSI, 'kode_mk' => 'KSU1101', 'nama_mk' => 'Matematika Dasar', 'sks' => 3, 'semester' => 1],
            ['prodi_id' => $prodiTI, 'kode_mk' => 'KSU1207', 'nama_mk' => 'Matematika Diskrit', 'sks' => 2, 'semester' => 2],
            ['prodi_id' => $prodiTI, 'kode_mk' => 'KSU1101', 'nama_mk' => 'Matematika Dasar', 'sks' => 3, 'semester' => 1],
            ['prodi_id' => $prodiTI, 'kode_mk' => 'KST1410', 'nama_mk' => 'Statistika', 'sks' => 2, 'semester' => 2],
            ['prodi_id' => $prodiIF, 'kode_mk' => 'KSU1207', 'nama_mk' => 'Matematika Diskrit', 'sks' => 2, 'semester' => 2],
            ['prodi_id' => $prodiIF, 'kode_mk' => 'KSU1101', 'nama_mk' => 'Matematika Dasar', 'sks' => 3, 'semester' => 1],
            ['prodi_id' => $prodiIF, 'kode_mk' => 'KSF1412', 'nama_mk' => 'Statistika', 'sks' => 2, 'semester' => 3],
            ['prodi_id' => $prodiIF, 'kode_mk' => 'KSF1101', 'nama_mk' => 'Aljabar Linier', 'sks' => 2, 'semester' => 1],
            ['prodi_id' => $prodiSI, 'kode_mk' => 'KFU1016', 'nama_mk' => 'Augmented Reality', 'sks' => 3, 'semester' => null],
            ['prodi_id' => $prodiTI, 'kode_mk' => 'KTU1042', 'nama_mk' => 'Augmented Reality', 'sks' => 3, 'semester' => null],
            ['prodi_id' => $prodiTI, 'kode_mk' => 'KTU1044', 'nama_mk' => 'Game Engine Design & Development', 'sks' => 3, 'semester' => null],
            ['prodi_id' => $prodiIF, 'kode_mk' => 'KFU1527', 'nama_mk' => 'Teknologi Imersi', 'sks' => 3, 'semester' => null],
            ['prodi_id' => $prodiSI, 'kode_mk' => 'UNU9003', 'nama_mk' => 'KKN', 'sks' => 3, 'semester' => null],
            ['prodi_id' => $prodiTI, 'kode_mk' => 'UNU9003', 'nama_mk' => 'KKN', 'sks' => 3, 'semester' => null],
            ['prodi_id' => $prodiIF, 'kode_mk' => 'UNU9003', 'nama_mk' => 'KKN', 'sks' => 3, 'semester' => null],
            ['prodi_id' => $prodiSI, 'kode_mk' => 'KTU1024', 'nama_mk' => 'IoT dalam Agroindustri', 'sks' => 3, 'semester' => null],
            ['prodi_id' => $prodiSI, 'kode_mk' => 'KTU1025', 'nama_mk' => 'Sistem Cerdas IoT', 'sks' => 3, 'semester' => null],
            ['prodi_id' => $prodiSI, 'kode_mk' => 'KTU1026', 'nama_mk' => 'Jaringan IoT', 'sks' => 3, 'semester' => null],
            ['prodi_id' => $prodiSI, 'kode_mk' => 'KTU1027', 'nama_mk' => 'Sistem Keamanan IoT', 'sks' => 3, 'semester' => null],
            ['prodi_id' => $prodiSI, 'kode_mk' => 'KTU1028', 'nama_mk' => 'Packaging Produk IoT', 'sks' => 2, 'semester' => null],
            ['prodi_id' => $prodiTI, 'kode_mk' => 'KTU1024', 'nama_mk' => 'IoT dalam Agroindustri', 'sks' => 3, 'semester' => null],
            ['prodi_id' => $prodiTI, 'kode_mk' => 'KTU1025', 'nama_mk' => 'Sistem Cerdas IoT', 'sks' => 3, 'semester' => null],
            ['prodi_id' => $prodiTI, 'kode_mk' => 'KTU1026', 'nama_mk' => 'Jaringan IoT', 'sks' => 3, 'semester' => null],
            ['prodi_id' => $prodiTI, 'kode_mk' => 'KTU1027', 'nama_mk' => 'Sistem Keamanan IoT', 'sks' => 3, 'semester' => null],
            ['prodi_id' => $prodiTI, 'kode_mk' => 'KTU1028', 'nama_mk' => 'Packaging Produk IoT', 'sks' => 2, 'semester' => null],
            ['prodi_id' => $prodiTI, 'kode_mk' => 'KTU1031', 'nama_mk' => 'System Device', 'sks' => 3, 'semester' => null],
            ['prodi_id' => $prodiIF, 'kode_mk' => 'KTU1024', 'nama_mk' => 'IoT dalam Agroindustri', 'sks' => 3, 'semester' => null],
            ['prodi_id' => $prodiIF, 'kode_mk' => 'KTU1025', 'nama_mk' => 'Sistem Cerdas IoT', 'sks' => 3, 'semester' => null],
            ['prodi_id' => $prodiIF, 'kode_mk' => 'KTU1026', 'nama_mk' => 'Jaringan IoT', 'sks' => 3, 'semester' => null],
            ['prodi_id' => $prodiIF, 'kode_mk' => 'KTU1027', 'nama_mk' => 'Sistem Keamanan IoT', 'sks' => 3, 'semester' => null],
            ['prodi_id' => $prodiIF, 'kode_mk' => 'KTU1028', 'nama_mk' => 'Packaging Produk IoT', 'sks' => 2, 'semester' => null],
            ['prodi_id' => $prodiSI, 'kode_mk' => 'KSI1308', 'nama_mk' => 'Manajemen Keamanan Sistem Informasi', 'sks' => 3, 'semester' => null],
            ['prodi_id' => $prodiSI, 'kode_mk' => 'KSU1303', 'nama_mk' => 'Jaringan Komputer', 'sks' => 3, 'semester' => null],
            ['prodi_id' => $prodiTI, 'kode_mk' => 'KTU1011', 'nama_mk' => 'Pemrograman Jaringan', 'sks' => 3, 'semester' => null],
            ['prodi_id' => $prodiTI, 'kode_mk' => 'KTU1012', 'nama_mk' => 'Blockchain', 'sks' => 2, 'semester' => null],
            ['prodi_id' => $prodiTI, 'kode_mk' => 'KTU1014', 'nama_mk' => 'Kriptografi', 'sks' => 3, 'semester' => null],
            ['prodi_id' => $prodiTI, 'kode_mk' => 'KTU1015', 'nama_mk' => 'Digital Forensik', 'sks' => 3, 'semester' => null],
            ['prodi_id' => $prodiTI, 'kode_mk' => 'KTU1016', 'nama_mk' => 'Basis Data Terdistribusi', 'sks' => 3, 'semester' => null],
            ['prodi_id' => $prodiTI, 'kode_mk' => 'KTU1017', 'nama_mk' => 'Steganografi', 'sks' => 3, 'semester' => null],
            ['prodi_id' => $prodiIF, 'kode_mk' => 'MKI1309', 'nama_mk' => 'Manajemen Keamanan Sistem Informasi', 'sks' => 2, 'semester' => null],
            ['prodi_id' => $prodiIF, 'kode_mk' => 'KFU1508', 'nama_mk' => 'Forensik Digital', 'sks' => 3, 'semester' => null],
            ['prodi_id' => $prodiIF, 'kode_mk' => 'KSF1206', 'nama_mk' => 'Jaringan Komputer', 'sks' => 2, 'semester' => null],
            ['prodi_id' => $prodiIF, 'kode_mk' => 'KFU1515', 'nama_mk' => 'Kriptografi Modern', 'sks' => 3, 'semester' => null],
            ['prodi_id' => $prodiTI, 'kode_mk' => 'KSF1506', 'nama_mk' => 'Implementasi dan Pengujian Perangkat Lunak', 'sks' => 2, 'semester' => 4],
            ['prodi_id' => $prodiIF, 'kode_mk' => 'KFU1204', 'nama_mk' => 'Deep Learning', 'sks' => 3, 'semester' => null],
            ['prodi_id' => $prodiIF, 'kode_mk' => 'KSF1506', 'nama_mk' => 'Implementasi dan Pengujian Perangkat Lunak', 'sks' => 2, 'semester' => 4],
            ['prodi_id' => $prodiSI, 'kode_mk' => 'KIU1023', 'nama_mk' => 'E-Business', 'sks' => 3, 'semester' => null],
        ];

        foreach ($data as $mk) {
            MataKuliah::updateOrCreate(
                ['kode_mk' => $mk['kode_mk'], 'prodi_id' => $mk['prodi_id']],
                $mk
            );
        }
    }
}
