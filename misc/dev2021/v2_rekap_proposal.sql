ALTER VIEW v2_rekap_proposal AS
SELECT
       p.id                AS id,
       p.id_topik          AS id_topik,
       rt.jenis_pengerjaan AS jenis_pengerjaan,
       rt.jenis_pengusul   AS jenis_pengusul,
       rt.grup_riset       AS grup_riset,
       p.judul_proposal    AS judul_proposal,
       p.nim_pengusul      AS nim_pengusul,
       rmp.nama            AS nama_pengusul,
       p.nim_anggota       AS nim_anggota,
       rma.nama            AS nama_anggota,
       rmp.kode_prodi      AS prodi,
       b.id_pembimbing_1   AS id_pembimbing_1,
       dp1.nama            AS nama_pembimbing_1,
       b.id_pembimbing_2   AS id_pembimbing_2,
       dp2.nama            AS nama_pembimbing_2,
       rmp.tahun_proposal  AS tahun_pengajuan
FROM
    v2_proposal p
        LEFT OUTER JOIN v2_rekap_topik rt ON p.id_topik = rt.id
        INNER JOIN v2_rekap_mahasiswa rmp ON p.nim_pengusul = rmp.nim
        LEFT OUTER JOIN v2_rekap_mahasiswa rma ON p.nim_anggota = rma.nim
        INNER JOIN v2_bimbingan b ON p.nim_pengusul = b.nim_mahasiswa
        INNER JOIN v2_dosen dp1 ON dp1.id = b.id_pembimbing_1
        LEFT OUTER JOIN v2_dosen dp2 ON dp2.id = b.id_pembimbing_2;