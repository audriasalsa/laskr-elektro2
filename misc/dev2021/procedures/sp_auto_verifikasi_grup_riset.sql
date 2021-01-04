DROP PROCEDURE IF EXISTS sp_auto_verifikasi_grup_riset;

DELIMITER //

CREATE PROCEDURE sp_auto_verifikasi_grup_riset(IN param_pesan VARCHAR(255), IN param_kode_prodi VARCHAR(50))
BEGIN
    INSERT INTO v2_verifikasi_proposal (nim_pengusul, id_pembimbing_1, saran_revisi, grup_riset_verifikator, id_proposal)
    SELECT
        nim_pengusul, id_pembimbing_1, param_pesan, grup_riset, id
    FROM
        v2_rekap_proposal
    WHERE
        id NOT IN (SELECT DISTINCT id_proposal FROM v2_verifikasi_proposal)
            AND prodi = param_kode_prodi
            AND tahun_pengajuan = (SELECT tahun_proposal_sekarang FROM v2_pengaturan LIMIT 1);
END//

DELIMITER ;