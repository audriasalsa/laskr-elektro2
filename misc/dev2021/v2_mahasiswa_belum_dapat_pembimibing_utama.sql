CREATE VIEW v2_mahasiswa_belum_dapat_pembimbing_utama AS
SELECT prodi, nim, nama, tahun_proposal
FROM v2_rekap_mahasiswa_aktif
WHERE nim NOT IN (SELECT nim_mahasiswa FROM v2_bimbingan) ORDER BY prodi, tahun_proposal;