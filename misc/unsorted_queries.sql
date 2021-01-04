USE db_tugasakhir;

SELECT m.*, p.* FROM v2_mahasiswa m INNER JOIN v2_proposal p ON m.nim = p.nim_pengusul
WHERE m.nim = 1741723001;

SELECT * FROM v2_dosen;

SELECT * FROM v2_credential WHERE access_type = 'dosen' ORDER by id_dosen ASC;

SELECT * FROM v2_mahasiswa WHERE nama LIKE '%nuha%';

SELECT * FROM v2_bimbingan WHERE nim_mahasiswa = '1641720136';

SELECT * FROM v2_dosen WHERE nama LIKE '%hendra%';

UPDATE v2_bimbingan SET id_pembimbing_1 = 13 WHERE nim_mahasiswa = '1641720136';

-- Ganti dosen pembimbing
-- UPDATE v2_bimbingan SET id_pembimbing_1 = (SELECT id FROM v2_dosen WHERE nama LIKE '%Budi Harijanto%' LIMIT 1) 
-- WHERE nim_mahasiswa = (SELECT nim FROM v2_mahasiswa WHERE nama LIKE '%Humaidi Al Mujayyidi%' LIMIT 1);

SELECT COUNT(*) FROM v2_bimbingan WHERE id_pembimbing_1 = (SELECT id FROM v2_dosen WHERE nama LIKE '%dwi puspitasari%' LIMIT 1);

-- Rekap bimbingan berdasarkan dosen
SELECT 
	d.nama AS nama_dosen, 
    m.nim,
    m.nama,
    p.judul_proposal,
    b.keterangan
FROM v2_bimbingan b 
INNER JOIN v2_dosen d ON b.id_pembimbing_1 = d.id 
INNER JOIN v2_mahasiswa m ON b.nim_mahasiswa = m.nim 
LEFT OUTER JOIN v2_proposal p ON m.nim = p.nim_pengusul
ORDER BY nama_dosen, m.nim ASC;

SELECT * FROM v2_mahasiswa WHERE nomor_ponsel_orang_tua IS NULL;

SELECT d.nama, count(*) AS jumlah FROM v2_bimbingan b INNER JOIN v2_dosen d ON b.id_pembimbing_1 = d.id GROUP BY (d.nama) ORDER BY jumlah ASC;

SELECT * FROM v2_bimbingan;

SELECT d.*, 'Pembimbing-1' AS `status` FROM v2_bimbingan b INNER JOIN v2_dosen d ON d.id = b.id_pembimbing_1 WHERE b.nim_mahasiswa = '1431140137'
UNION ALL
SELECT d.*, 'Pembimbing-2' AS `status` FROM v2_bimbingan b INNER JOIN v2_dosen d ON d.id = b.id_pembimbing_2 WHERE b.nim_mahasiswa = '1431140137';

SELECT * FROM v2_dosen;

SELECT * FROM v2_mahasiswa;

SHOW COLUMNS FROM v2_proposal;

SELECT * FROM v2_proposal WHERE nim_pengusul = '1541180052';

SELECT * FROM v2_rekap_verifikasi;

SELECT * FROM v2_pendaftaran_sempro WHERE id_proposal = (SELECT id FROM v2_proposal WHERE nim_pengusul = '1641720036' LIMIT 1);

SELECT COUNT(*) FROM v2_rekap_pendaftaran_sempro;

SELECT * FROM v2_mahasiswa WHERE nim = '1641720036';

SELECT * FROM v2_pendaftaran_sempro;

DELETE FROM v2_pendaftaran_sempro WHERE id_proposal = (SELECT id FROM v2_proposal WHERE nim_pengusul = '1641720036' LIMIT 1);

SELECT * FROM v2_rekap_pendaftaran_sempro WHERE nama_dosen_pembimbing_1 LIKE '%Faisal%';
SELECT * FROM v2_rekap_pendaftaran_sempro WHERE nama_mahasiswa LIKE '%Yudistira%';

SELECT * FROM v2_mahasiswa WHERE nim NOT IN (SELECT nim_pengusul FROM v2_proposal);

SELECT * FROM v2_mahasiswa WHERE nama LIKE '%wardatun%';

SELECT * FROM v2_proposal;

UPDATE v2_mahasiswa SET 
                 nama = 'NIKO RIZKY', 
                 email = 'XSIRHAAN@GMAIL.COM', 
                 nomor_ponsel = '081331993754', 
                 nomor_ponsel_orang_tua = '081233183222',
                 kode_prodi = 'D4-TI' WHERE nim = '1431140137';
                 
SELECT * FROM v2_proposal;

SELECT column_name
FROM information_schema.columns
WHERE table_name = 'v2_proposal'
   AND table_schema = 'db_tugasakhir';
   
SELECT * FROM v2_rekap_pendaftaran_sempro;
SELECT DISTINCT(grup_riset_direvisi) FROM v2_rekap_pendaftaran_sempro;

SELECT * FROM v2_uploaded_file;

SHOW COLUMNS FROM v2_uploaded_file;

SELECT * FROM v2_proposal WHERE id NOT IN (SELECT id_proposal FROM v2_pendaftaran_sempro);

SELECT * FROM v2_verifikasi_proposal;

SELECT nim FROM v2_mahasiswa WHERE nim NOT IN (SELECT nim_pengusul FROM v2_verifikasi_proposal);

SET foreign_key_checks = 0;
DELETE FROM v2_proposal WHERE id = 257;
SET foreign_key_checks = 1;

SELECT * FROM v2_proposal WHERE id = 257;

Select * FROM v2_verifikasi_proposal WHERE id_proposal = 257;

SELECT * FROM v2_event;

SELECT * FROM v2_mahasiswa WHERE nama LIKE '%trisna%';

SELECT * FROM v2_credential WHERE username = '1741723003';

SELECT * FROM v2_proposal WHERE judul_proposal LIKE '%coba%';

SELECT * FROM v2_pendaftaran_sempro WHERE id_event = 3;

SELECT * FROM v2_rekap_pendaftaran_sempro;

INSERT INTO v2_pendaftaran_sempro VALUES ('48', 'TES COBA PENERAPAN ALGORITMA APRIORI UNTUK MENENTUKAN LOKASI BARANG DI GUDANG( STUDI KASUS : PG KEBON AGUNG MALANG)', 'SISTEM INFORMASI', '96fd515124f4b2872c6cfdd324b260bc.jpg', '7935a853b46d317674f15216d8c75639.png', 'c21f03de70d9ad48d5fa410d68d0063b.pdf', '3');

SELECT * FROM v2_verifikasi_proposal;

/* REBUILT TABLE v2_pendaftaran_sempro */
/*
CREATE TABLE v2_temp_backup_pendaftaran_sempro AS SELECT * FROM v2_pendaftaran_sempro;
SELECT * FROM v2_temp_backup_pendaftaran_sempro;
DROP TABLE v2_pendaftaran_sempro;
CREATE TABLE `v2_pendaftaran_sempro` (
  `id_proposal` int(11) NOT NULL,
  `judul` text NOT NULL,
  `grup_riset` enum('SISTEM INFORMASI','SISTEM CERDAS','VISI KOMPUTER','JARKOM, ARSITEKTUR DAN KEAMANAN DATA','MULTIMEDIA DAN GAME') NOT NULL,
  `file_activity_control` varchar(255) NOT NULL,
  `file_persetujuan_maju` varchar(255) NOT NULL,
  `file_proposal_revisi` varchar(255) NOT NULL,
  `id_event` int(11) NOT NULL DEFAULT '2',
  PRIMARY KEY (`id_proposal`,`id_event`),
  KEY `fk_pendaftaran_sempro_file_activity_control` (`file_activity_control`),
  KEY `fk_pendaftaran_sempro_file_persetujuan_maju` (`file_persetujuan_maju`),
  KEY `fk_pendaftaran_sempro_file_proposal_revisi` (`file_proposal_revisi`),
  KEY `fk_pendaftaran_sempro_id_event` (`id_event`),
  CONSTRAINT `fk_pendaftaran_sempro_file_activity_control` FOREIGN KEY (`file_activity_control`) REFERENCES `v2_uploaded_file` (`stored_name`),
  CONSTRAINT `fk_pendaftaran_sempro_file_persetujuan_maju` FOREIGN KEY (`file_persetujuan_maju`) REFERENCES `v2_uploaded_file` (`stored_name`),
  CONSTRAINT `fk_pendaftaran_sempro_file_proposal_revisi` FOREIGN KEY (`file_proposal_revisi`) REFERENCES `v2_uploaded_file` (`stored_name`),
  CONSTRAINT `fk_pendaftaran_sempro_id_event` FOREIGN KEY (`id_event`) REFERENCES `v2_event` (`id`),
  CONSTRAINT `fk_pendaftaran_sempro_id_proposal` FOREIGN KEY (`id_proposal`) REFERENCES `v2_proposal` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
INSERT INTO v2_pendaftaran_sempro SELECT * FROM v2_temp_backup_pendaftaran_sempro;
SELECT * FROM v2_pendaftaran_sempro;
DESC v2_pendaftaran_sempro;
*/
/* END REBUILT */

SELECT * FROM v2_pendaftaran_sempro WHERE id_event = 3;
DELETE FROM v2_pendaftaran_sempro WHERE id_event = 3;

SELECT * FROM v2_mahasiswa WHERE nama LIKE '%triska%';

SELECT * FROM v2_credential WHERE username = '1641720062';

/* [Maintenance] Add new bimbingan */
/* BEGIN MAINTENANCE */
SELECT id, nama FROM v2_dosen WHERE nama LIKE '%Yoga%';
SELECT nim, nama FROM v2_mahasiswa WHERE nama LIKE '%galih maulana%';
SELECT * FROM v2_bimbingan;
INSERT INTO v2_bimbingan (nim_mahasiswa, id_pembimbing_1, keterangan) VALUES
	('1641720131', '19', 'Terlambat Mendaftar');
SELECT * FROM v2_bimbingan;
/* END MAINTENANCE */

/* [Maintenance] INSERT new mahasiswa */
/* BEGIN MAINTENANCE */
INSERT INTO v2_mahasiswa (nim, nama) VALUES
	('1641720131', 'Galih Maulana Adji');
	-- ('1641720121', 'Wardatun Nafisah');
/* END MAINTENANCE */
/* INSERT mahasiswa with no credential */
/*
INSERT INTO v2_credential (username, `password`, access_type, id_mahasiswa)
SELECT 
	nim AS username, 
	nim AS `password`, 
    'mahasiswa', 
    nim AS id_mahasiswa 
FROM v2_mahasiswa WHERE nim NOT IN (SELECT username FROM v2_credential);
*/
/* END INSERT */

SELECT * FROM v2_mahasiswa WHERE nama LIKE '%niko%';
SELECT * FROM v2_credential WHERE username = '1641720068';

SELECT DISTINCT nama_grup_riset FROM v2_proposal;

SELECT DISTINCT grup_riset FROM v2_pendaftaran_sempro;

SELECT * FROM v2_pendaftaran_sempro WHERE judul LIKE '%Resep Masakan%';

-- UPDATE v2_pendaftaran_sempro SET judul = 'Sistem Rekomendasi Resep Masakan Berdasarkan Bahan Masakan Menggunakan Metode Adjusted Cosine Similarity'
-- WHERE id_proposal = '77' AND id_event = '3';

-- ----------------------------------------------------------------
-- [IMOPS] Menghitung jumlah pendaftar sempro tahap 2
-- ----------------------------------------------------------------
SELECT COUNT(*) FROM v2_pendaftaran_sempro WHERE id_event = 3 ORDER BY judul;

-- ----------------------------------------------------------------
-- [IMOPS] List mahasiswa yang belum daftar semrpo tahap 2
-- ----------------------------------------------------------------
SELECT 
	m.nim, m.nama, p.id as nomor_skripsi, ps.tanggal_daftar, ps.id_event
FROM
	v2_mahasiswa m 
		INNER JOIN v2_proposal p ON m.nim = p.nim_pengusul
        LEFT OUTER JOIN v2_pendaftaran_sempro ps ON p.id = ps.id_proposal
WHERE 
	ps.tanggal_daftar IS NOT NULL
ORDER BY nomor_skripsi ASC;

SELECT * FROM v2_rekap_pendaftaran_sempro WHERE id_event = 4 AND nama_mahasiswa LIKE '%wardatun%';
SELECT count(*) FROM v2_rekap_pendaftaran_sempro WHERE id_event = 3 -- AND nama_mahasiswa LIKE '%habib%';

-- DELETE FROM v2_pendaftaran_sempro WHERE id_proposal = 284;

SELECT * FROM v2_mahasiswa WHERE nama LIKE '%ud%';

SELECT COUNT(*) FROM v2_pendaftaran_sempro WHERE id_event = 4 ORDER BY judul;

SELECT * FROM v2_event WHERE id = 4;


SELECT * FROM v2_credential;

SELECT * FROM v2_mahasiswa WHERE nama LIKE '%Devan%';

SELECT COUNT(*) FROM v2_hasil_sempro;

SET SQL_SAFE_UPDATES = 0;
DELETE FROM v2_hasil_sempro;
SET SQL_SAFE_UPDATES = 1;

SELECT * FROM v2_hasil_sempro WHERE nim = '17411723006';

SELECT COUNT(*) FROM v2_bimbingan;

SELECT m.nim, m.nama  FROM v2_hasil_sempro WHERE hasil LIKE '%diterima%';

SELECT COUNT(*) FROM v2_mahasiswa;

SELECT * FROM v2_rekap_pendaftaran_sempro;

SELECT COUNT(*) FROM v2_hasil_sempro;

SELECT hs.nim, m.nama, e.nama AS tahap_lulus, rps.judul_direvisi AS judul_proposal, hs.hasil as `status` FROM v2_mahasiswa m
	INNER JOIN v2_hasil_sempro hs ON hs.nim = m.nim
    INNER JOIN v2_event e ON e.id = hs.id_event
    INNER JOIN v2_rekap_pendaftaran_sempro rps ON rps.nim = hs.nim ORDER BY `status`;
    

    
SELECT * FROM v2_rekap_pendaftaran_sempro;
SELECT * FROM v2_pendaftaran_sempro;

SELECT * FROM v2_histori_sempro;

SELECT * FROM v2_hasil_sempro WHERE nim = '1641720197';

SELECT nim, nama FROM v2_mahasiswa WHERE nim NOT IN (SELECT DISTINCT nim FROM v2_histori_sempro);

SELECT * FROM v2_mahasiswa WHERE nama LIKE '%Fikriansyah%';

SELECT nim, nama FROM v2_mahasiswa WHERE nim NOT IN (SELECT nim FROM v2_histori_sempro WHERE keputusan LIKE '%diterima%');

SELECT * FROM v2_histori_sempro WHERE nama LIKE '%devri%' OR nama LIKE '%eko prasetyo%';

SELECT COUNT(DISTINCT nim) FROM v2_histori_sempro;

SELECT COUNT(*) FROM v2_mahasiswa;

SELECT * FROM v2_rekap_lulus_sempro WHERE nama LIKE '%Niko%';

SELECT COUNT(DISTINCT nim) FROM v2_rekap_lulus_sempro WHERE nama LIKE '%Niko%';

SELECT * FROM v2_bimbingan;

SELECT COUNT(DISTINCT id_pembimbing_1) FROM v2_bimbingan;

SELECT * FROM v2_dosen WHERE id NOT IN (SELECT DISTINCT id_pembimbing_1 FROM v2_bimbingan)

SELECT * FROM data_bimbingan WHERE pembimbing1 LIKE '%usman%' OR pembimbing2 LIKE '%usman%';


DELETE FROM v2_dosen WHERE id NOT IN (SELECT DISTINCT id_pembimbing_1 FROM v2_bimbingan)


SELECT COUNT(*) FROM v2_dosen ORDER BY nama ASC;

SELECT * FROM v2_dosen;

SELECT * FROM v2_bimbingan;

SELECT * FROM data_dosen;

SELECT nama, judul, pembimbing1, pembimbing2 FROM data_bimbingan WHERE pembimbing1 LIKE '%Usman%' AND pembimbing2 LIKE 'Anu%';

USE db_tugasakhir;
SELECT nama, judul, pembimbing1, pembimbing2 FROM data_bimbingan WHERE pembimbing1 LIKE '%Hendra Pra%' OR pembimbing2 LIKE '%Hendra Pra%' ORDER BY pembimbing1, pembimbing2;
SELECT nama, judul, pembimbing1, pembimbing2 FROM data_bimbingan WHERE pembimbing1 LIKE '%Usman%' OR pembimbing2 LIKE '%Usman%' ORDER BY pembimbing1, pembimbing2;


SELECT * FROM data_bimbingan WHERE nama = 'Gandha Wicaksono' OR nama = 'Alwan Ghozi Kurnia Amrullah';

'Rakhmat Arianto'
'Anugrah Nur Rahmanto, S.Sn., M.Ds.'


UPDATE data_bimbingan SET pembimbing2 = 'Anugrah Nur Rahmanto, S.Sn., M.Ds.' WHERE nama = 'Gandha Wicaksono' AND `no` = 229;
UPDATE data_bimbingan SET pembimbing2 = 'Rakhmat Arianto' WHERE nama = 'Alwan Ghozi Kurnia Amrullah' AND `no` = 16;

SELECT * FROM v2_mahasiswa WHERE nama LIKE '%Devan Pra%';

SELECT * FROM v2_temp_bimbingan;

SELECT * FROM v2_dosen WHERE nama LIKE '%Ahmadi%';

-- Ahmadi Yuli Ananta, ST., M.M.
-- Ahmadi Yuli Ananta, S.T., M.M.

SELECT * FROM v2_mahasiswa WHERE nama LIKE '%piping adel%';

SELECT 
	d.id,
    d.nama,
    b.nim_mahasiswa, 
    m.nama,
    'Pembimbing-1' AS status_pembimbingan
FROM 
	v2_dosen d
		INNER JOIN v2_bimbingan b ON d.id = b.id_pembimbing_1
        INNER JOIN v2_mahasiswa m ON m.nim = b.nim_mahasiswa WHERE id = 7
UNION ALL
SELECT 
	d.id,
    d.nama,
    b.nim_mahasiswa, 
    m.nama,
    'Pembimbing-2' AS status_pembimbingan
FROM 
	v2_dosen d
		INNER JOIN v2_bimbingan b ON d.id = b.id_pembimbing_2
        INNER JOIN v2_mahasiswa m ON m.nim = b.nim_mahasiswa WHERE id = 7;
        
SELECT * FROM v2_rekap_bimbingan_per_dosen;

DROP VIEW IF EXISTS v2_rekap_bimbingan_per_dosen;
        
SELECT nama_mahasiswa, COUNT(*) AS jml FROM v2_rekap_bimbingan_per_dosen GROUP BY nama_mahasiswa  HAVING jml < 2 ORDER BY nama_mahasiswa ASC;

SELECT * FROM v2_dosen WHERE nama LIKE '%mentari%';

INSERT INTO v2_credential (username, `password`, access_type, id_dosen)
    VALUES ('mentari', 'sunqueen', 'dosen', 306);
    
SELECT * FROM v2_event;

SELECT * FROM v2_mahasiswa;
SELECT * FROM v2_rekap_pendaftaran_sempro;
SELECT * FROM v2_dosen;
SELECT * FROM v2_hasil_sempro;
SELECT * FROM v2_histori_sempro;

SHOW TABLES;

SELECT hs.nim, m.nama, e.nama AS tahap_sempro FROM v2_hasil_sempro WHERE nim = '1641720063' ORDER BY id_event DESC LIMIT 1;
SELECT * FROM v2_rekap_hasil_sempro;
SELECT nim, COUNT(*) AS jumlah_maju FROM v2_hasil_sempro GROUP BY nim ORDER BY jumlah_maju DESC;

SELECT hasil FROM v2_hasil_sempro WHERE nim = '1641720063' ORDER BY id_event DESC LIMIT 1;

SELECT * FROM v2_event;