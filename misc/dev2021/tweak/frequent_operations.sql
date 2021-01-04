USE db_tugasakhir;

/*
 Rutin-00: Ganti nim_anggota pada v2_pengusulan_pembimbing
 */
SET @nim_pengusul = '1831710009';
SET @nim_anggota = '1831710001';
SELECT * FROM v2_pengajuan_pembimbing WHERE nim_pengusul = @nim_pengusul;
UPDATE v2_pengajuan_pembimbing
SET nim_anggota = @nim_anggota WHERE nim_pengusul = @nim_pengusul;
SELECT * FROM v2_pengajuan_pembimbing WHERE nim_pengusul = @nim_pengusul;
/*
 --------------------------------------------------------------------------------
 */


/*
 Rutin-01: Perbaiki salah nim di v2_proposal karena pengajuan/entri oleh anggota
 */
SET @checked_nim = '1831710047';
SET @id_proposal = (SELECT id FROM v2_proposal p WHERE p.nim_pengusul = @checked_nim OR p.nim_anggota = @checked_nim LIMIT 1);
SET @nim_pengusul = (SELECT nim_pengusul FROM v2_pengajuan_pembimbing pp WHERE pp.nim_pengusul = @checked_nim OR pp.nim_anggota = @checked_nim LIMIT 1);
SET @nim_anggota = (SELECT nim_anggota FROM v2_pengajuan_pembimbing pp WHERE pp.nim_pengusul = @checked_nim OR pp.nim_anggota = @checked_nim LIMIT 1);
-- Update Pengusul
UPDATE v2_proposal
SET nim_pengusul = @nim_pengusul, nim_anggota = @nim_anggota
WHERE id = @id_proposal;
SELECT * FROM v2_proposal WHERE id = @id_proposal;
/*
 --------------------------------------------------------------------------------
 */


/*
 Rutin-01: TOLAK pengajuan yang sudah terlanjur disetujui (WARNING: KHUSUS D3!!!)
 */
SET @checked_nim = '1741720211';
SET @nama_dosen = '%cahya%';
SET @id_topik = 140;
SELECT * FROM v2_dosen WHERE nama LIKE @nama_dosen LIMIT 1;
SET @id_dosen = (SELECT id FROM v2_dosen WHERE nama LIKE @nama_dosen LIMIT 1);
SELECT * FROM v2_pengajuan_pembimbing WHERE (nim_pengusul = @checked_nim OR nim_anggota = @checked_nim) AND id_pembimbing_utama = @id_dosen AND id_topik = @id_topik;
UPDATE v2_pengajuan_pembimbing SET status = 'ditolak' WHERE (nim_pengusul = @checked_nim OR nim_anggota = @checked_nim) AND id_pembimbing_utama = @id_dosen AND id_topik = @id_topik;
SET @nim_pengusul = (SELECT nim_pengusul FROM v2_pengajuan_pembimbing WHERE (nim_pengusul = @checked_nim OR nim_anggota = @checked_nim) AND id_pembimbing_utama = @id_dosen AND id_topik = @id_topik);
SET @nim_anggota = (SELECT nim_anggota FROM v2_pengajuan_pembimbing WHERE (nim_pengusul = @checked_nim OR nim_anggota = @checked_nim) AND id_pembimbing_utama = @id_dosen AND id_topik = @id_topik);
SELECT * FROM v2_bimbingan WHERE nim_mahasiswa IN (@nim_pengusul, @nim_anggota);



/*
 Rutin-02: TOLAK pengajuan D4 yang sudah terlanjur disetujui (WARNING: KHUSUS D4!!!)
 */

-- SET @checked_nim = '1741720176';
-- SET @nama_dosen = '%deddy%';
-- SET @id_topik = 232;
SET @checked_nim = '1741720217';
SET @nama_dosen = '%deddy%';
SET @id_topik = 80;
SET @id_dosen = (SELECT id FROM v2_dosen WHERE nama LIKE @nama_dosen);
SELECT * FROM v2_pengajuan_pembimbing WHERE nim_pengusul = @checked_nim AND id_pembimbing_utama = @id_dosen;
SELECT * FROM v2_bimbingan WHERE nim_mahasiswa = @checked_nim;
UPDATE v2_pengajuan_pembimbing SET status = 'ditolak' WHERE nim_pengusul = @checked_nim AND id_pembimbing_utama = @id_dosen;
DELETE FROM v2_bimbingan WHERE nim_mahasiswa = @checked_nim AND id_pembimbing_1 = @id_dosen;

SELECT * FROM v2_mahasiswa WHERE nim IN(1741720176, 1741720217);