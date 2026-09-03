const API_BASE = 'http://127.0.0.1:8000/api';

async function runTests() {
  console.log('=== TESTING FLEXIBLE SKS CONVERSION & OPTIONAL SELECTION ===\n');

  let passed = 0;
  let failed = 0;

  function assert(condition, message) {
    if (condition) {
      console.log(`✅ PASS: ${message}`);
      passed++;
    } else {
      console.error(`❌ FAIL: ${message}`);
      failed++;
    }
  }

  async function login(email, password = 'password') {
    const res = await fetch(`${API_BASE}/auth/login`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify({ email, password }),
    });
    const json = await res.json();
    return json.data?.token;
  }

  try {
    const token = await login('mhs.si@test.com');
    assert(!!token, 'Mahasiswa SI logged in successfully');

    const headers = {
      Authorization: `Bearer ${token}`,
      'Content-Type': 'application/json',
      Accept: 'application/json',
    };

    // Get courses for UI/UX in SI
    const mkRes = await fetch(`${API_BASE}/ref/mata-kuliah?bidang_id=5&prodi_id=1`, { headers });
    const mkJson = await mkRes.json();
    const mkList = mkJson.data || [];

    const imkCourse = mkList.find(m => m.kode_mk === 'KSU1107'); // 2 SKS
    const uiDesignCourse = mkList.find(m => m.kode_mk === 'KMU1010'); // 4 SKS
    const uiTestingCourse = mkList.find(m => m.kode_mk === 'KMU1042'); // 3 SKS

    assert(!!imkCourse && !!uiDesignCourse && !!uiTestingCourse, 'UI/UX courses identified (2 SKS, 4 SKS, 3 SKS)');

    const ts = Date.now();

    // 1. SCENARIO A: Finalis (quota 4-6 SKS), but student chooses only 1 course of 3 SKS (< min_sks 4)
    console.log('\n[Scenario A] Student chooses only 3 SKS when quota is 4-6 SKS (remaining course in curriculum)...');
    const resA = await fetch(`${API_BASE}/mahasiswa/pengajuan`, {
      method: 'POST',
      headers,
      body: JSON.stringify({
        nama_lomba: `Finalis UX Single MK ${ts}`,
        no_whatsapp: '081234567890',
        bidang_id: 5,
        tingkatan_id: 2, // Nasional
        tahapan_id: 3, // Finalis (4-6 SKS)
        semester: 3, // Semester 3
        mata_kuliah_ids: [uiTestingCourse.id], // Only 3 SKS!
        link_sertifikat: 'https://drive.google.com/sertif',
        status_surat_tugas_mahasiswa: false,
        status_surat_tugas_dosen: false,
        link_poster: 'https://drive.google.com/poster',
        link_sosmed: 'https://instagram.com/p/lomba',
      }),
    });
    const jsonA = await resA.json();
    assert(resA.status === 201, 'Backend successfully accepts 3 SKS submission for Finalis 4-6 SKS quota (201 Created)');
    const sumA = jsonA.data?.mata_kuliahs?.reduce((a, b) => a + b.sks, 0);
    assert(sumA === 3, `Converted SKS is exactly 3 SKS (actual: ${sumA})`);

    // 2. SCENARIO B: Finalis (quota 4-6 SKS), but student has already taken all courses -> SKIP conversion (0 SKS)
    console.log('\n[Scenario B] Student skips SKS conversion completely (0 SKS, mata_kuliah_ids: [])...');
    const resB = await fetch(`${API_BASE}/mahasiswa/pengajuan`, {
      method: 'POST',
      headers,
      body: JSON.stringify({
        nama_lomba: `Finalis UX Skip MK ${ts}`,
        no_whatsapp: '081234567890',
        bidang_id: 5,
        tingkatan_id: 2, // Nasional
        tahapan_id: 3, // Finalis (4-6 SKS)
        semester: 5,
        mata_kuliah_ids: [], // Skipped!
        link_sertifikat: 'https://drive.google.com/sertif',
        status_surat_tugas_mahasiswa: false,
        status_surat_tugas_dosen: false,
        link_poster: 'https://drive.google.com/poster',
        link_sosmed: 'https://instagram.com/p/lomba',
      }),
    });
    const jsonB = await resB.json();
    assert(resB.status === 201, 'Backend successfully accepts skipped conversion with 0 SKS (201 Created)');
    assert(jsonB.data?.mata_kuliahs?.length === 0, 'No courses attached to pengajuan');

    // 3. SCENARIO C: Student chooses courses exceeding max_sks (4 SKS + 3 SKS = 7 SKS > 6 SKS)
    console.log('\n[Scenario C] Student chooses courses exceeding max_sks (7 SKS > 6 SKS)...');
    const resC = await fetch(`${API_BASE}/mahasiswa/pengajuan`, {
      method: 'POST',
      headers,
      body: JSON.stringify({
        nama_lomba: `Finalis UX Exceed SKS ${ts}`,
        no_whatsapp: '081234567890',
        bidang_id: 5,
        tingkatan_id: 2, // Nasional
        tahapan_id: 3, // Finalis (4-6 SKS)
        semester: 3,
        mata_kuliah_ids: [uiDesignCourse.id, uiTestingCourse.id], // 4 + 3 = 7 SKS!
        link_sertifikat: 'https://drive.google.com/sertif',
        status_surat_tugas_mahasiswa: false,
        status_surat_tugas_dosen: false,
        link_poster: 'https://drive.google.com/poster',
        link_sosmed: 'https://instagram.com/p/lomba',
      }),
    });
    assert(resC.status === 422, 'Backend correctly rejects courses exceeding max_sks with 422 Unprocessable Content');

    // 4. SCENARIO D: Student chooses within full range (IMK 2 SKS + UI Design 4 SKS = 6 SKS)
    console.log('\n[Scenario D] Student chooses full range within max_sks (6 SKS)...');
    const resD = await fetch(`${API_BASE}/mahasiswa/pengajuan`, {
      method: 'POST',
      headers,
      body: JSON.stringify({
        nama_lomba: `Finalis UX Full Range ${ts}`,
        no_whatsapp: '081234567890',
        bidang_id: 5,
        tingkatan_id: 2, // Nasional
        tahapan_id: 3, // Finalis (4-6 SKS)
        semester: 1,
        mata_kuliah_ids: [imkCourse.id, uiDesignCourse.id], // 2 + 4 = 6 SKS
        link_sertifikat: 'https://drive.google.com/sertif',
        status_surat_tugas_mahasiswa: false,
        status_surat_tugas_dosen: false,
        link_poster: 'https://drive.google.com/poster',
        link_sosmed: 'https://instagram.com/p/lomba',
      }),
    });
    const jsonD = await resD.json();
    assert(resD.status === 201, 'Backend successfully accepts full 6 SKS combination (201 Created)');
    const sumD = jsonD.data?.mata_kuliahs?.reduce((a, b) => a + b.sks, 0);
    assert(sumD === 6, `Total converted SKS is 6 SKS (actual: ${sumD})`);

  } catch (err) {
    console.error('Test Error:', err);
    failed++;
  }

  console.log(`\n===================================`);
  console.log(`TEST SUMMARY: ${passed} PASSED, ${failed} FAILED`);
  console.log(`===================================`);

  if (failed > 0) process.exit(1);
}

runTests();
