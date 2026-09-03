const API_BASE = 'http://127.0.0.1:8000/api';

async function test() {
  const loginRes = await fetch(`${API_BASE}/auth/login`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
    body: JSON.stringify({ email: 'mhs.si@test.com', password: 'password' }),
  });
  const loginJson = await loginRes.json();
  const token = loginJson.data?.token;

  // Fetch IMK
  const mkRes = await fetch(`${API_BASE}/ref/mata-kuliah?bidang_id=5&prodi_id=1`, {
    headers: { Authorization: `Bearer ${token}` }
  });
  const mkJson = await mkRes.json();
  const imk = mkJson.data.find(m => m.kode_mk === 'KSU1107');

  const ts = Date.now();
  const res = await fetch(`${API_BASE}/mahasiswa/pengajuan`, {
    method: 'POST',
    headers: {
      Authorization: `Bearer ${token}`,
      'Content-Type': 'application/json',
      Accept: 'application/json',
    },
    body: JSON.stringify({
      nama_lomba: `UI/UX Competition ${ts}`,
      no_whatsapp: '081234567890',
      bidang_id: 5,
      tingkatan_id: 1, // Wilayah
      tahapan_id: 2, // Lolos Tahap Awal (2-3 SKS)
      semester: 1,
      detail_juara: 'Lolos Tahap 1',
      mata_kuliah_ids: [imk.id],
      link_sertifikat: 'https://drive.google.com/file/d/sertif-lolos/view',
      status_surat_tugas_mahasiswa: false,
      status_surat_tugas_dosen: false,
      link_poster: 'https://drive.google.com/file/d/poster/view',
      link_sosmed: 'https://instagram.com/p/lomba-sem1',
    }),
  });

  console.log('Status:', res.status);
  const json = await res.json();
  console.log('Response keys:', Object.keys(json.data || {}));
  console.log('Mata kuliah:', json.data?.mata_kuliah);
}

test();
