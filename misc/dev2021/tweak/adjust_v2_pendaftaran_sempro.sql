/*
 01. Ubah kolom grup riset menjadi foreign key dari tabel v2_grup_riset
 */
DESC v2_pendaftaran_sempro;
ALTER TABLE v2_pendaftaran_sempro MODIFY COLUMN grup_riset VARCHAR(255);
SELECT DISTINCT grup_riset FROM v2_pendaftaran_sempro ORDER BY grup_riset ASC;
SELECT * FROM v2_grup_riset ORDER BY nama ASC;
UPDATE v2_pendaftaran_sempro SET grup_riset = 'Jarkom' WHERE grup_riset LIKE 'JARKOM%';
UPDATE v2_pendaftaran_sempro SET grup_riset = 'MMG' WHERE grup_riset LIKE 'MULTIMEDIA%';
UPDATE v2_pendaftaran_sempro SET grup_riset = 'AI' WHERE grup_riset LIKE 'SISTEM CER%';
UPDATE v2_pendaftaran_sempro SET grup_riset = 'SI' WHERE grup_riset LIKE 'SISTEM INF%';
UPDATE v2_pendaftaran_sempro SET grup_riset = 'Visikom' WHERE grup_riset LIKE 'VISI%';
SELECT * FROM v2_pendaftaran_sempro;
SHOW FULL COLUMNS FROM v2_grup_riset;
ALTER TABLE v2_pendaftaran_sempro MODIFY COLUMN grup_riset VARCHAR(50) COLLATE latin1_swedish_ci;
ALTER TABLE v2_pendaftaran_sempro CHANGE grup_riset kode_grup_riset VARCHAR(50) COLLATE latin1_swedish_ci;
DESC v2_pendaftaran_sempro;
ALTER TABLE v2_pendaftaran_sempro ADD CONSTRAINT fk_pendaftaran_sempro_kode_grup_riset FOREIGN KEY (kode_grup_riset) REFERENCES v2_grup_riset (kode);
DESC v2_pendaftaran_sempro;
ALTER TABLE v2_pendaftaran_sempro MODIFY COLUMN  kode_grup_riset VARCHAR(50) COLLATE latin1_swedish_ci NOT NULL;
DESC v2_pendaftaran_sempro;

/*
 02. Buat beberapa kolom menjadi tidak wajib
 */
SHOW FULL COLUMNS FROM v2_pendaftaran_sempro;
ALTER TABLE v2_pendaftaran_sempro MODIFY COLUMN file_activity_control VARCHAR(255) NULL COLLATE latin1_swedish_ci;
ALTER TABLE v2_pendaftaran_sempro MODIFY COLUMN file_persetujuan_maju VARCHAR(255) NULL COLLATE latin1_swedish_ci;
DESC v2_pendaftaran_sempro;