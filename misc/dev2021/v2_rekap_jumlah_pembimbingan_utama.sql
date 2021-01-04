ALTER VIEW v2_rekap_jumlah_pembimbingan_utama AS
SELECT
    d.id AS id_pembimbing_utama,
    d.nama AS nama_pembimbing_utama,
    d.status AS status_kepegawaian,
    IF(jml.prodi_pengusul IS NULL, 'Belum ada pengusul dari semua Prodi', jml.prodi_pengusul) AS prodi_pengusul,
    IF(jml.jumlah IS NULL, 0, jml.jumlah) AS jumlah
FROM
    (SELECT id_pembimbing_utama, nama_pembimbing_utama, prodi_mahasiswa AS prodi_pengusul, status, COUNT(*) AS jumlah FROM v2_rekap_pengajuan_pembimbing GROUP BY id_pembimbing_utama, nama_pembimbing_utama, prodi_mahasiswa, status HAVING status = 'disetujui' ORDER BY id_pembimbing_utama ASC) AS jml
    RIGHT OUTER JOIN v2_dosen d ON jml.id_pembimbing_utama = d.id
WHERE
    d.aktif_membimbing = 'ya'; -- AND (prodi_pengusul LIKE 'D4 Tek%' OR prodi_pengusul LIKE 'Belum%');