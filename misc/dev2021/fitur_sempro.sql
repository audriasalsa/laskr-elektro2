-- Tambahkan kolom status persetujuan
-- ALTER TABLE v2_pendaftaran_sempro DROP COLUMN status_persetujuan_pembimbing;
ALTER TABLE v2_pendaftaran_sempro
    ADD COLUMN status_persetujuan_pembimbing ENUM ('diajukan', 'disetujui')
    DEFAULT 'diajukan'
    AFTER kode_grup_riset;

UPDATE v2_pendaftaran_sempro
    SET v2_pendaftaran_sempro.status_persetujuan_pembimbing = 'disetujui'
    WHERE v2_pendaftaran_sempro.status_persetujuan_pembimbing = 'diajukan';


-- Drop view v2_rekap_verifikasi karena sudah digantikan dengan v2_rekap_verifikasi_proposal
DROP VIEW IF EXISTS v2_rekap_verifikasi;


-- Tambahkan kolom untuk upload file presentasi dan kolom informasi tambahan
ALTER TABLE v2_pendaftaran_sempro ADD COLUMN file_presentasi VARCHAR(255) COLLATE latin1_swedish_ci AFTER file_persetujuan_maju;
ALTER TABLE v2_pendaftaran_sempro ADD CONSTRAINT fk_pendaftaran_sempro_file_presentasi FOREIGN KEY (file_presentasi) REFERENCES v2_uploaded_file(stored_name);
ALTER TABLE v2_pendaftaran_sempro ADD COLUMN informasi_tambahan TEXT COLLATE latin1_swedish_ci AFTER file_proposal_revisi;

-- Tambahkan field nilai tambahan di penilaian pembimbing.
SELECT * FROM v2_penilaian_pembimbing;
DESC v2_penilaian_pembimbing;
ALTER TABLE v2_penilaian_pembimbing ADD COLUMN  nilai_7 float AFTER nilai_6;
ALTER TABLE v2_penilaian_pembimbing ADD COLUMN  nilai_8 float AFTER nilai_7;
ALTER TABLE v2_penilaian_pembimbing ADD COLUMN  nilai_9 float AFTER nilai_8;
ALTER TABLE v2_penilaian_pembimbing ADD COLUMN  nilai_10 float AFTER nilai_9;
ALTER TABLE v2_penilaian_pembimbing ADD COLUMN  nilai_11 float AFTER nilai_10;
ALTER TABLE v2_penilaian_pembimbing ADD COLUMN  nilai_12 float AFTER nilai_11;