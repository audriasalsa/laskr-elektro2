DROP VIEW IF EXISTS v2_rekap_ujian_akhir;
CREATE VIEW v2_rekap_ujian_akhir AS
SELECT
    u.id                        AS nomor_ujian,
    mp.kode_prodi               AS kode_prodi,
    u.id_proposal               AS id_proposal,
    p.judul_proposal            AS judul_proposal,
    p.nim_pengusul              AS nim_pengusul,
    mp.nama                     AS nama_pengusul,
    p.nim_anggota               AS nim_anggota,
    ma.nama                     AS nama_anggota,
    pua.file_presentasi         AS file_presentasi,
    pua.file_laporan_akhir      AS file_laporan_akhir,
    pua.file_draft_publikasi    AS file_draft_publikasi,
    pua.link_video_demo         AS link_video_demo,
    pua.link_instalasi_aplikasi AS link_instalasi_aplikasi,
    pua.informasi_tambahan      AS informasi_tambahan
FROM
    v2_ujian u
        INNER JOIN v2_pendaftaran_ujian_akhir pua ON u.id_event = pua.id_event AND u.id_proposal = pua.id_proposal
        INNER JOIN v2_proposal p ON pua.id_proposal = p.id
        INNER JOIN v2_mahasiswa mp ON p.nim_pengusul = mp.nim
        LEFT OUTER JOIN v2_mahasiswa ma ON p.nim_anggota = ma.nim;

