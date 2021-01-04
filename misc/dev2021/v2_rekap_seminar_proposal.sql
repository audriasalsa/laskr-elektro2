DROP VIEW IF EXISTS v2_rekap_seminar_proposal;
CREATE VIEW v2_rekap_seminar_proposal AS
SELECT
    u.id                        AS nomor_ujian,
    mp.kode_prodi               AS kode_prodi,
    u.id_proposal               AS id_proposal,
    p.judul_proposal            AS judul_proposal,
    p.nim_pengusul              AS nim_pengusul,
    mp.nama                     AS nama_pengusul,
    p.nim_anggota               AS nim_anggota,
    ma.nama                     AS nama_anggota,
    ps.file_presentasi          AS file_presentasi,
    ps.file_proposal_revisi     AS file_proposal,
    ps.informasi_tambahan       AS informasi_tambahan
FROM
    v2_ujian u -- Kalau belum di-ACC tidak masuk tabel ujian
        INNER JOIN v2_pendaftaran_sempro ps ON u.id_event = ps.id_event AND u.id_proposal = ps.id_proposal
        INNER JOIN v2_proposal p ON ps.id_proposal = p.id
        INNER JOIN v2_mahasiswa mp ON p.nim_pengusul = mp.nim
        LEFT OUTER JOIN v2_mahasiswa ma ON p.nim_anggota = ma.nim;

