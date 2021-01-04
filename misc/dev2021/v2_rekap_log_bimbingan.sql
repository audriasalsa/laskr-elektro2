ALTER VIEW v2_rekap_log_bimbingan AS
SELECT
    rekap_nama.nim                                      AS nim,
    rekap_nama.nama                                     AS nama,
    rekap_diajukan.log_bimbingan_diajukan               AS log_bimbingan_diajukan,
    COALESCE(rekap_disetujui.log_bimbingan_diterima, 0) AS log_bimbingan_disetujui
FROM
    (SELECT DISTINCT lb.nim_mahasiswa AS nim, m.nama AS nama
    FROM v2_log_bimbingan lb INNER JOIN v2_mahasiswa m ON lb.nim_mahasiswa = m.nim) AS rekap_nama
        INNER JOIN
            (SELECT v2_log_bimbingan.nim_mahasiswa AS nim, count(0) AS log_bimbingan_diajukan
            FROM v2_log_bimbingan GROUP BY v2_log_bimbingan.nim_mahasiswa) AS rekap_diajukan
            ON rekap_nama.nim = rekap_diajukan.nim
        LEFT OUTER JOIN
            (SELECT v2_log_bimbingan.nim_mahasiswa AS nim, count(0) AS log_bimbingan_diterima
            FROM v2_log_bimbingan WHERE (v2_log_bimbingan.status = 'disetujui') GROUP BY v2_log_bimbingan.nim_mahasiswa) AS rekap_disetujui
            ON rekap_nama.nim = rekap_disetujui.nim;

