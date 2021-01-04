USE db_tugasakhir;

-- ----------------------------------------------------------------
-- [IMOPS] Cancel pendaftaran
-- ----------------------------------------------------------------
/*
SELECT * FROM v2_pendaftaran_sempro WHERE 1;
SELECT * FROM v2_proposal WHERE 1;
SELECT id_proposal FROM v2_rekap_pendaftaran_sempro WHERE nim = '' LIMIT 1;
SELECT * FROM v2_pendaftaran_sempro WHERE id_proposal = 284;
*/

-- ----------------------------------------------------------------
-- [IMOPS] List mahasiswa yang belum daftar sempro sama sekali
-- ----------------------------------------------------------------
SELECT 
	m.nim, m.nama, p.id as nomor_skripsi, ps.tanggal_daftar, ps.id_event
FROM
	v2_mahasiswa m 
		INNER JOIN v2_proposal p ON m.nim = p.nim_pengusul
        LEFT OUTER JOIN v2_pendaftaran_sempro ps ON p.id = ps.id_proposal
WHERE 
	ps.tanggal_daftar IS NULL
ORDER BY nomor_skripsi ASC;
-- --------------------------------------------

-- ----------------------------------------------------------------
-- [IMOPS] Menambahkan mahasiswa yang belum terdaftar sama sekali
-- ----------------------------------------------------------------
/* [01.] INSERT new mahasiswa */
INSERT INTO v2_mahasiswa (nim, nama) VALUES
	('1641720092', 'Aditya Fikriansyah')
	-- ('1641720198', 'Andre Prayogo');
	-- ('1641720131', 'Galih Maulana Adji');
	-- ('1641720121', 'Wardatun Nafisah');
/* END [01.] */

/* [02.] Tambahin ke credential */
INSERT INTO v2_credential (username, `password`, access_type, id_mahasiswa)
SELECT 
	nim AS username, 
	nim AS `password`, 
    'mahasiswa', 
    nim AS id_mahasiswa 
FROM v2_mahasiswa WHERE nim NOT IN (SELECT username FROM v2_credential);
/* END [02.] */

/* [03. ] Tambahin bimbingan */
SELECT id, nama FROM v2_dosen WHERE nama LIKE '%Ahmadi%';
SELECT nim, nama FROM v2_mahasiswa WHERE nama LIKE '%Andre Prayogo%';
SELECT * FROM v2_bimbingan;
INSERT INTO v2_bimbingan (nim_mahasiswa, id_pembimbing_1, keterangan) VALUES
	('1641720198', '28', 'Terlambat Mendaftar');
SELECT * FROM v2_bimbingan WHERE nim_mahasiswa = '1641720198';
/* END [03.] */

-- ----------------------------------------------------------------
-- [IMOPS] Mengganti bimbingan
-- ----------------------------------------------------------------
SELECT * FROM v2_dosen WHERE nama LIKE '%larasati%';
SELECT * FROM v2_bimbingan WHERE nim_mahasiswa = '1641720033';
UPDATE v2_bimbingan SET id_pembimbing_1 = 8 WHERE nim_mahasiswa = '1641720033';
UPDATE v2_bimbingan SET keterangan = 'Diplot pembimbing oleh panitia. Awalnya diplot Pak Faisal, lalu diganti atas rekomendasi manajemen jurusan' WHERE nim_mahasiswa = '1641720033';

-- ----------------------------------------------------------------
-- [IMOPS] List mahasiswa berikut pembimbingnya
-- ----------------------------------------------------------------
SELECT m.nim, m.nama, d.nama, rps.judul_direvisi as judul_proposal FROM v2_bimbingan b
	INNER JOIN v2_mahasiswa m ON b.nim_mahasiswa = m.nim
    INNER JOIN v2_dosen d ON b.id_pembimbing_1 = d.id
    INNER JOIN v2_rekap_pendaftaran_sempro rps ON m.nim = rps.nim;

-- ----------------------------------------------------------------
-- [IMOPS] List mahasiswa yang statusnya belum lulus di tahap 3
-- ----------------------------------------------------------------
SELECT nim, nama FROM v2_mahasiswa WHERE nim IN (
SELECT nim FROM v2_hasil_sempro WHERE hasil NOT LIKE '%Diterima%' AND id_event >= 3);

-- ----------------------------------------------------------------
-- [IMOPS] Menghitung Mahasiswa yang sudah lulus
-- ----------------------------------------------------------------
SELECT * FROM v2_hasil_sempro WHERE hasil LIKE '%Diterima%';
SELECT COUNT(DISTINCT nim) FROM v2_hasil_sempro WHERE hasil LIKE '%Diterima%';

-- ----------------------------------------------------------------
-- [IMOPS] List mahasiswa yang belum lulus
-- ----------------------------------------------------------------
SELECT * FROM v2_mahasiswa WHERE nim NOT IN (SELECT nim FROM v2_rekap_lulus_sempro);

