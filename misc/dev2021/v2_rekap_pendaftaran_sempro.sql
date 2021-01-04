DROP VIEW IF EXISTS v2_rekap_pendaftaran_sempro;
CREATE VIEW v2_rekap_pendaftaran_sempro AS
SELECT
    e.id                                                                      AS id_event,
    e.nama                                                                    AS tahap,
    p.id                                                                      AS id_proposal,
    p.judul_proposal                                                          AS judul_proposal_awal,
    ps.judul                                                                  AS judul_direvisi,
    rm.nim                                                                    AS nim_pengusul,
    rm.nama                                                                   AS nama_pengusul,
    d.id                                                                      AS id_pembimbing_utama,
    d.nama                                                                    AS nama_pembimbing_utama,
    rm.kode_prodi                                                             AS kode_prodi,
    p.nama_grup_riset                                                         AS grup_riset_awal,
    if(isnull(rvp.id_verifikasi), 'Belum diverifikasi', 'Sudah diverifikasi') AS status_verifikasi,
    rvp.saran_revisi                                                          AS saran_revisi_dari_grup_riset,
    ps.kode_grup_riset                                                        AS grup_riset_direvisi,
    ps.status_persetujuan_pembimbing                                          AS status_persetujuan_pembimbing,
    ps.file_activity_control                                                  AS file_activity_control,
    ps.file_persetujuan_maju                                                  AS file_persetujuan_maju,
    ps.file_presentasi                                                        AS file_presentasi,
    ps.file_proposal_revisi                                                   AS file_proposal_revisi,
    ps.informasi_tambahan                                                     AS informasi_tambahan
FROM
    v2_rekap_mahasiswa rm
        INNER JOIN v2_proposal p ON rm.nim = p.nim_pengusul
        LEFT OUTER JOIN v2_rekap_verifikasi_proposal rvp ON rm.nim = rvp.nim_pengusul
        INNER JOIN v2_pendaftaran_sempro ps ON p.id = ps.id_proposal
        INNER JOIN v2_dosen d ON p.id_dosen_pembimbing_1 = d.id
        INNER JOIN v2_event e ON ps.id_event = e.id;