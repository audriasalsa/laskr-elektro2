-- 01. Tambahkan kolom periode proposal pada tabel event
ALTER TABLE v2_event ADD COLUMN periode_proposal SMALLINT DEFAULT 2020 AFTER deskripsi;
UPDATE v2_event SET periode_proposal = 2020 WHERE v2_event.periode_proposal IS NULL;
SELECT * FROM v2_event;

-- 02. Tambahkan kolom jenis pada tabel log bimbingan
SELECT * FROM v2_log_bimbingan;
ALTER TABLE v2_log_bimbingan ADD COLUMN jenis ENUM('pra_proposal', 'pasca_proposal') DEFAULT 'pasca_proposal' AFTER id;
UPDATE v2_log_bimbingan SET jenis = 'pra_proposal' WHERE tanggal > '2020-10-01';
SELECT * FROM v2_log_bimbingan;