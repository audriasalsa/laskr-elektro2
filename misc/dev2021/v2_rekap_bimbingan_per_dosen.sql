DROP VIEW IF EXISTS v2_rekap_bimbingan_per_dosen;
CREATE VIEW v2_rekap_bimbingan_per_dosen AS
SELECT
    d.id                      AS id_dosen,
    d.nama                    AS nama_dosen,
    b.nim_mahasiswa           AS nim_mahasiswa,
    rm.nama                   AS nama_mahasiswa,
    rm.prodi                  AS prodi,
    rm.nomor_ponsel           AS nomor_ponsel,
    rm.nomor_ponsel_orang_tua AS nomor_ponsel_orang_tua,
    rm.email                  AS email,
    p.id                      AS id_proposal,
    p.judul_proposal          AS judul_proposal,
    'Pembimbing-1'            AS status_pembimbingan,
    rm.tahun_proposal         AS tahun_proposal
FROM
    v2_dosen d
        INNER JOIN v2_bimbingan b ON d.id = b.id_pembimbing_1
        INNER JOIN v2_rekap_mahasiswa rm ON rm.nim = b.nim_mahasiswa
        LEFT OUTER JOIN v2_proposal p ON p.nim_pengusul = b.nim_mahasiswa
UNION ALL
select
    d.id                      AS id_dosen,
    d.nama                    AS nama_dosen,
    b.nim_mahasiswa           AS nim_mahasiswa,
    rm.nama                   AS nama_mahasiswa,
    rm.prodi                  AS prodi,
    rm.nomor_ponsel           AS nomor_ponsel,
    rm.nomor_ponsel_orang_tua AS nomor_ponsel_orang_tua,
    rm.email                  AS email,
    p.id                      AS id_proposal,
    p.judul_proposal          AS judul_proposal,
    'Pembimbing-2'            AS status_pembimbingan,
    rm.tahun_proposal         AS tahun_proposal
FROM
    v2_dosen d
        INNER JOIN v2_bimbingan b on d.id = b.id_pembimbing_2
        INNER JOIN v2_rekap_mahasiswa rm on rm.nim = b.nim_mahasiswa
        LEFT OUTER JOIN v2_proposal p on p.nim_pengusul = b.nim_mahasiswa
ORDER BY id_dosen ASC;