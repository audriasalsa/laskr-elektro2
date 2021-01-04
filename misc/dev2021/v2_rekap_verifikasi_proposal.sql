DROP VIEW IF EXISTS v2_rekap_verifikasi_proposal;
CREATE VIEW v2_rekap_verifikasi_proposal AS
SELECT
    p.id              AS id_proposal,
    mp.nim            AS nim_pengusul,
    mp.nama           AS nama_pengusul,
    ma.nim            AS nim_anggota,
    ma.nama           AS nama_anggota,
    d.nama            AS dosen_pembimbing_1,
    p.judul_proposal  AS judul_proposal,
    mp.kode_prodi     AS kode_prodi,
    p.nama_grup_riset AS nama_grup_riset,
    v.id              AS id_verifikasi,
    v.saran_revisi    AS saran_revisi
FROM
    v2_mahasiswa mp
        INNER JOIN v2_proposal p ON mp.nim = p.nim_pengusul
        INNER JOIN v2_verifikasi_proposal v ON p.id = v.id_proposal
        INNER JOIN v2_dosen d ON p.id_dosen_pembimbing_1 = d.id
        LEFT OUTER JOIN v2_mahasiswa ma ON ma.nim = p.nim_anggota;