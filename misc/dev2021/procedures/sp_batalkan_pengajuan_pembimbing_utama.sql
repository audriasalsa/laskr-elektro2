DROP PROCEDURE IF EXISTS sp_batalkan_pengajuan_pembimbing_utama;

DELIMITER //

CREATE PROCEDURE sp_batalkan_pengajuan_pembimbing_utama(IN param_nim_mahasiswa VARCHAR(50), IN param_id_pembimbing_utama INT)
BEGIN
	SET @check_mahasiswa = (SELECT nim_mahasiswa FROM v2_bimbingan WHERE nim_mahasiswa = param_nim_mahasiswa);
	IF @check_mahasiswa IS NOT NULL THEN
        SET @check_pembimbing = (SELECT id_pembimbing_1 FROM v2_bimbingan WHERE nim_mahasiswa = param_nim_mahasiswa);
        IF @check_pembimbing = param_id_pembimbing_utama THEN
            DELETE FROM v2_bimbingan WHERE nim_mahasiswa = param_nim_mahasiswa AND id_pembimbing_1 = param_id_pembimbing_utama;
            UPDATE v2_pengajuan_pembimbing SET status = 'ditolak' WHERE id_pembimbing_utama = param_id_pembimbing_utama AND status = 'disetujui' AND ((nim_pengusul = param_nim_mahasiswa) OR (nim_anggota = param_nim_mahasiswa));
	    ELSE
	        SELECT '[Error] ID pembimbing tidak ditemukan di tabel bimbingan' AS 'hasil';
	    END IF;
    ELSE
	    SELECT '[Error] NIM mahasiswa tidak ditemukan di tabel bimbingan' AS 'hasil';
	END IF;
END//

DELIMITER ;