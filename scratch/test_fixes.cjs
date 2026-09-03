const API_BASE = 'http://127.0.0.1:8000/api';

async function runTests() {
  console.log('=== TESTING SIMPRESMA CRITICAL BUSINESS LOGIC & FIXES ===\n');

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

  // Helper login
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
    const timestamp = Date.now();

    // 1. Test Login as Mahasiswa SI
    console.log('[1] Testing Auth & Token Generation...');
    const mhsSiToken = await login('mhs.si@test.com');
    assert(!!mhsSiToken, 'Login mhs.si@test.com succeeded');

    const headersSi = {
      Authorization: `Bearer ${mhsSiToken}`,
      'Content-Type': 'application/json',
      Accept: 'application/json',
    };

    // 2. Test Fetch Real Courses for UI/UX (Bidang ID 5, Prodi SI ID 1)
    console.log('\n[2] Testing Real Seeded Courses from Excel (Bidang: UI/UX, Prodi: SI)...');
    const mkRes = await fetch(`${API_BASE}/ref/mata-kuliah?bidang_id=5&prodi_id=1`, { headers: headersSi });
    const mkJson = await mkRes.json();
    const mkList = mkJson.data || [];
    console.log(`Found ${mkList.length} courses for UI/UX in SI:`);
    mkList.forEach(m => console.log(` - [${m.kode_mk}] ${m.nama_mk} (${m.sks} SKS, Sem: ${m.semester})`));

    const imkCourse = mkList.find(m => m.kode_mk === 'KSU1107');
    assert(!!imkCourse, 'KSU1107 - IMK (2 SKS, Sem 1) is present in SI UI/UX mappings');
    assert(imkCourse?.semester === 1, 'KSU1107 has semester = 1 constraint');

    const uiDesignCourse = mkList.find(m => m.kode_mk === 'KMU1010');
    assert(!!uiDesignCourse && uiDesignCourse.sks === 4, 'KMU1010 - UI/UX Design (4 SKS) is present');

    const uiTestingCourse = mkList.find(m => m.kode_mk === 'KMU1042');
    assert(!!uiTestingCourse && uiTestingCourse.sks === 3, 'KMU1042 - UI/UX Testing (3 SKS) is present');

    // 3. Test Zero-SKS Conversion ("Mendaftar") Submission
    console.log('\n[3] Testing Participation Submission (Mendaftar - 0 SKS Conversion)...');
    const submitZeroRes = await fetch(`${API_BASE}/mahasiswa/pengajuan`, {
      method: 'POST',
      headers: headersSi,
      body: JSON.stringify({
        nama_lomba: `Lomba Partisipasi UI/UX ${timestamp}`,
        nama_tim: 'Solo Partisipan',
        no_whatsapp: '081234567890',
        bidang_id: 5,
        tingkatan_id: 1, // Wilayah
        tahapan_id: 1, // Mendaftar (No SKS)
        semester: 2,
        detail_juara: 'Peserta Terdaftar',
        mata_kuliah_ids: [], // No courses required for Mendaftar!
        link_sertifikat: 'https://drive.google.com/file/d/partisipasi/view',
        status_surat_tugas_mahasiswa: false,
        status_surat_tugas_dosen: false,
        link_poster: 'https://drive.google.com/file/d/poster-wajib/view',
        link_sosmed: 'https://instagram.com/p/lomba-resmi',
        keterangan: 'Pencatatan partisipasi lomba mahasiswa',
      }),
    });
    const submitZeroJson = await submitZeroRes.json();
    assert(submitZeroRes.status === 201, 'Submitting Mendaftar (0 SKS, mata_kuliah_ids: []) succeeds with 201 Created');
    assert(submitZeroJson.data?.semester === 2, 'Pengajuan record stores semester = 2');

    // 4. Test Mandatory Poster & Sosmed Validation
    console.log('\n[4] Testing Validation of Mandatory Poster & Sosmed URLs...');
    const invalidRes = await fetch(`${API_BASE}/mahasiswa/pengajuan`, {
      method: 'POST',
      headers: headersSi,
      body: JSON.stringify({
        nama_lomba: `Lomba Tanpa Poster ${timestamp}`,
        no_whatsapp: '081234567890',
        bidang_id: 5,
        tingkatan_id: 2,
        tahapan_id: 2,
        semester: 1,
        mata_kuliah_ids: [imkCourse.id],
        link_sertifikat: 'https://drive.google.com/sertif',
        status_surat_tugas_mahasiswa: false,
        status_surat_tugas_dosen: false,
        // link_poster and link_sosmed missing!
      }),
    });
    const invalidJson = await invalidRes.json();
    assert(invalidRes.status === 422, 'Server correctly rejected missing link_poster and link_sosmed with 422 Unprocessable Content');
    assert(!!invalidJson.errors?.link_poster, 'Validation error returned for link_poster');
    assert(!!invalidJson.errors?.link_sosmed, 'Validation error returned for link_sosmed');

    // 5. Test Conversion SKS Submission for Semester 1 (IMK 2 SKS)
    console.log('\n[5] Testing SKS Conversion Submission for Semester 1...');
    const submitSem1Res = await fetch(`${API_BASE}/mahasiswa/pengajuan`, {
      method: 'POST',
      headers: headersSi,
      body: JSON.stringify({
        nama_lomba: `UI/UX Competition Sem1 ${timestamp}`,
        no_whatsapp: '081234567890',
        bidang_id: 5,
        tingkatan_id: 1, // Wilayah
        tahapan_id: 2, // Lolos Tahap Awal (2-3 SKS)
        semester: 1,
        detail_juara: 'Lolos Tahap 1',
        mata_kuliah_ids: [imkCourse.id], // 2 SKS
        link_sertifikat: 'https://drive.google.com/file/d/sertif-lolos/view',
        status_surat_tugas_mahasiswa: false,
        status_surat_tugas_dosen: false,
        link_poster: 'https://drive.google.com/file/d/poster/view',
        link_sosmed: 'https://instagram.com/p/lomba-sem1',
      }),
    });
    const submitSem1Json = await submitSem1Res.json();
    assert(submitSem1Res.status === 201, 'Semester 1 student can convert KSU1107 (IMK 2 SKS) within 2-3 SKS range');
    const sem1MkSum = submitSem1Json.data?.mata_kuliahs?.reduce((acc, m) => acc + m.sks, 0);
    assert(sem1MkSum === 2, `Total SKS converted is 2 SKS (actual: ${sem1MkSum})`);

    // 6. Test Multi-Course SKS Conversion (e.g. 4 SKS + 2 SKS = 6 SKS for Finalis)
    console.log('\n[6] Testing Multi-Course SKS Conversion within Max SKS Limit...');
    const submitFinalisRes = await fetch(`${API_BASE}/mahasiswa/pengajuan`, {
      method: 'POST',
      headers: headersSi,
      body: JSON.stringify({
        nama_lomba: `National UI/UX Championship ${timestamp}`,
        nama_tim: 'Fasilkom Innovators',
        no_whatsapp: '081234567890',
        bidang_id: 5,
        tingkatan_id: 2, // Nasional
        tahapan_id: 3, // Finalis (4-6 SKS)
        semester: 1,
        detail_juara: 'Finalis 5 Besar',
        mata_kuliah_ids: [imkCourse.id, uiDesignCourse.id], // 2 SKS + 4 SKS = 6 SKS (Valid <= 6 SKS)
        link_sertifikat: 'https://drive.google.com/file/d/sertif-finalis/view',
        status_surat_tugas_mahasiswa: false,
        status_surat_tugas_dosen: false,
        link_poster: 'https://drive.google.com/file/d/poster-finalis/view',
        link_sosmed: 'https://instagram.com/p/finalis-2026',
      }),
    });
    const submitFinalisJson = await submitFinalisRes.json();
    assert(submitFinalisRes.status === 201, 'Multi-course combination (IMK 2 SKS + UI Design 4 SKS = 6 SKS) successfully accepted');
    const finalisMkSum = submitFinalisJson.data?.mata_kuliahs?.reduce((acc, m) => acc + m.sks, 0);
    assert(finalisMkSum === 6, `Total SKS converted is 6 SKS (actual: ${finalisMkSum})`);

    // 7. Test Dashboard 3-Prodi Statistics Distribution
    console.log('\n[7] Testing Dashboard 3-Prodi Distribution API...');
    const statsRes = await fetch(`${API_BASE}/dashboard/statistik`, { headers: headersSi });
    const statsJson = await statsRes.json();
    const stats = statsJson.data;
    assert(!!stats.per_prodi && stats.per_prodi.length === 3, 'Statistik per_prodi contains all 3 program studi (SI, TI, IF)');

    console.log('3-Prodi Distribution:');
    stats.per_prodi.forEach(p => {
      console.log(` - ${p.prodi} (${p.nama_prodi}): ${p.total} Pengajuan (${p.persentase}%)`);
    });
    const totalPercentage = stats.per_prodi.reduce((acc, p) => acc + p.persentase, 0);
    assert(totalPercentage >= 99 && totalPercentage <= 101, `Percentages sum to ~100% (actual: ${totalPercentage}%)`);

    // 8. Test Direktori Verifikator API
    console.log('\n[8] Testing Direktori Verifikator API...');
    const dirRes = await fetch(`${API_BASE}/direktori-verifikator`, { headers: headersSi });
    const dirJson = await dirRes.json();
    const groups = dirJson.data || [];
    assert(Array.isArray(groups) && groups.length === 3, 'Direktori returns 3 prodi groups');
    const siGroup = groups.find(g => g.prodi === 'SI');
    assert(!!siGroup && siGroup.verifikators.length > 0, `SI prodi has ${siGroup?.verifikators?.length || 0} active verifikators listed`);

  } catch (err) {
    console.error('Test Suite Fatal Error:', err);
    failed++;
  }

  console.log(`\n===================================`);
  console.log(`TEST SUMMARY: ${passed} PASSED, ${failed} FAILED`);
  console.log(`===================================`);

  if (failed > 0) {
    process.exit(1);
  }
}

runTests();
