DROP VIEW IF EXISTS v2_rekap_detail_log_bimbingan;
CREATE VIEW v2_rekap_detail_log_bimbingan AS
SELECT
    lb.id,
    rp.id AS id_proposal,
    rp.nim_pengusul AS nim_pengusul,
    rp.nim_anggota AS nim_anggota,
    lb.tanggal,
    lb.id_dosen_pembimbing,
    lb.materi_bimbingan,
    lb.status,
    lb.jenis
FROM
    v2_log_bimbingan lb
        INNER JOIN v2_rekap_proposal rp ON (rp.nim_pengusul = lb.nim_mahasiswa OR rp.nim_anggota = lb.nim_mahasiswa);
