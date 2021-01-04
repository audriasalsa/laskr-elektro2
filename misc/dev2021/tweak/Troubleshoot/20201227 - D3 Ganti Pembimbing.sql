/*
1831710161 dan 1831710133 Unggul -> Cahya
1831710045 (single) Ridwan -> Banni
1831710003 dan 1831710105 Rizky -> Gunawan
1831710160 dan    1831710141 Yushintia -> Gunawan
1831710125 dan 1831710164 Yushintia -> Imam
 */
-- Unggul --> 328;

-- All NIM:
SELECT * FROM v2_bimbingan WHERE nim_mahasiswa IN (1831710161, 1831710133, 1831710045, 1831710003, 1831710105, 1831710160, 1831710141, 1831710125, 1831710164);


SELECT * FROM v2_dosen
WHERE
    nama LIKE '%unggul%' OR -- 328
    nama LIKE '%Ridwan%' OR -- 339
    nama LIKE '%Rizky%' OR -- 299
    nama LIKE '%Yushintia%' OR -- 338
    nama LIKE '%Cahya%' OR -- 3
    nama LIKE '%Banni%' OR -- 340
    nama LIKE '%Gunawan%' OR -- 12
    nama LIKE '%Imam%'; -- 14

CALL sp_batalkan_pengajuan_pembimbing_utama('1831710161', 328); -- Unggul
CALL sp_batalkan_pengajuan_pembimbing_utama('1831710133', 328); -- Unggul

CALL sp_batalkan_pengajuan_pembimbing_utama('1831710045', 339); -- Ridwan

CALL sp_batalkan_pengajuan_pembimbing_utama('1831710003', 299); -- Rizky
CALL sp_batalkan_pengajuan_pembimbing_utama('1831710105', 299); -- Rizky

CALL sp_batalkan_pengajuan_pembimbing_utama('1831710160', 338); -- Yushintia
CALL sp_batalkan_pengajuan_pembimbing_utama('1831710141', 338); -- Yushintia

CALL sp_batalkan_pengajuan_pembimbing_utama('1831710125', 338); -- Yushintia
CALL sp_batalkan_pengajuan_pembimbing_utama('1831710164', 338); -- Yushintia


SELECT * FROM v2_event;

SELECT id, judul_proposal, nama_pengusul FROM v2_rekap_proposal
WHERE
    tahun_pengajuan = 2020 AND
    prodi = 'D3-MI' AND
    id NOT IN (SELECT id_proposal FROM v2_pendaftaran_sempro WHERE status_persetujuan_pembimbing = 'disetujui');

SELECT * FROM v2_proposal WHERE nim_pengusul = '1831710045';
SELECT * FROM v2_pengajuan_pembimbing WHERE nim_pengusul = '1831710067';
SELECT * FROM v2_verifikasi_proposal WHERE nim_pengusul = '1831710045';

SELECT * FROM v2_dosen WHERE nama LIKE '%Budi%';
SELECT * FROM v2_proposal WHERE nim_pengusul = 1831710163 OR nim_anggota = 1831710163;
SELECT * FROM v2_verifikasi_proposal WHERE nim_pengusul = 1831710163;
SELECT * FROM v2_verifikasi_proposal WHERE nim_pengusul = 1831710039;

DELETE FROM v2_verifikasi_proposal WHERE nim_pengusul = 1831710163;

SELECT * FROM v2_verifikasi_proposal WHERE nim_pengusul = 1831710132;



/*
 GANTI bimbingan Bu Ririd
 Pak yop, yang bu ririd sudah ada keputusn, mohon bantuan reset nim
 1831710127 dan 1831710081
 1831710094 dan 1831710112
 */

SELECT * FROM v2_dosen WHERE nama LIKE '%ririd%';
CALL sp_batalkan_pengajuan_pembimbing_utama('1831710127', 1);
CALL sp_batalkan_pengajuan_pembimbing_utama('1831710081', 1);
CALL sp_batalkan_pengajuan_pembimbing_utama('1831710094', 1);
CALL sp_batalkan_pengajuan_pembimbing_utama('1831710112', 1);

SELECT * FROM v2_proposal -- 510
WHERE
    nim_anggota = 1831710127 OR
    nim_pengusul = 1831710127 OR
    nim_anggota = 1831710081 OR
    nim_pengusul = 1831710081;

DELETE FROM v2_proposal -- 545
WHERE
    nim_anggota = 1831710094 OR
    nim_pengusul = 1831710094 OR
    nim_anggota = 1831710112 OR
    nim_pengusul = 1831710112;

SELECT * FROM v2_pendaftaran_sempro WHERE id_proposal = 510;
SELECT * FROM v2_pendaftaran_sempro WHERE id_proposal = 545;

SELECT * FROM v2_log_bimbingan;