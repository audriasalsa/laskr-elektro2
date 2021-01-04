ALTER VIEW v2_rekap_kepemilikan_proposal AS
SELECT
    rm.nim,
    p.id AS id_proposal,
    rm.prodi,
    rm.tahun_proposal,
    'pengusul' AS peran
FROM
    v2_rekap_mahasiswa rm
        INNER JOIN v2_proposal p ON rm.nim = p.nim_pengusul
UNION ALL
SELECT
    rm.nim,
    p.id AS id_proposal,
    rm.prodi,
    rm.tahun_proposal,
    'anggota' AS peran
FROM
    v2_rekap_mahasiswa rm
        INNER JOIN v2_proposal p ON rm.nim = p.nim_anggota
ORDER BY id_proposal, nim;