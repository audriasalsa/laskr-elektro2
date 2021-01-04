-- ------------------------------------------------------------------
-- Untuk mengakomodir D3
-- 1 tim 2 mahasiswa, nilainya bisa beda, bisa lulus salah satu saja
-- ------------------------------------------------------------------
-- Tabel penilaian ditambajkan kolom nim
ALTER TABLE v2_penilaian_ujian ADD COLUMN nim VARCHAR(50) collate latin1_swedish_ci NOT NULL AFTER id_dosen;
ALTER TABLE v2_penilaian_ujian ADD CONSTRAINT FOREIGN KEY fk_nim_mahasiswa (nim) REFERENCES v2_mahasiswa (nim);
-- ------------
-- Tabel berita acara juga ditambahkan kolom nim
ALTER TABLE v2_berita_acara_ujian ADD COLUMN nim VARCHAR(50) collate latin1_swedish_ci NOT NULL AFTER id_ujian;
ALTER TABLE v2_berita_acara_ujian ADD CONSTRAINT FOREIGN KEY fk_nim_mahasiswa (nim) REFERENCES v2_mahasiswa (nim);


-- ------------------------------------------------------------------
-- Membuat primary key dari tabel penilaian ujian menjadi 3 kolom berikut.
-- ------------------------------------------------------------------
ALTER TABLE `v2_penilaian_ujian`
  DROP PRIMARY KEY,
   ADD PRIMARY KEY(
     `id_ujian`,
     `id_dosen`,
     `nim`);


-- ------------------------------------------------------------------
-- 1 mahasiswa, 1 berita acara, bisa mengulang di ujian yang berbeda, bisa mengulang salah satu saja
-- ------------------------------------------------------------------
ALTER TABLE v2_berita_acara_ujian DROP FOREIGN KEY fk_berita_acara_ujian_nim_peserta_1;
ALTER TABLE v2_berita_acara_ujian DROP COLUMN nim_peserta_1;
ALTER TABLE v2_berita_acara_ujian DROP FOREIGN KEY fk_berita_acara_ujian_nim_peserta_2;
ALTER TABLE v2_berita_acara_ujian DROP COLUMN nim_peserta_2;
-- ------------
-- Membuat primary key dari tabel penilaian ujian menjadi 3 kolom berikut.
ALTER TABLE `v2_berita_acara_ujian`
    ADD PRIMARY KEY(
        `id_ujian`,
        `nim`
    );
-- ------------
-- Hilangkan kolom waktu_ttd_peserta_2, dan ganti waktu_ttd_peserta_1 menjadi waktu_ttd_mahasiswa
ALTER TABLE v2_berita_acara_ujian CHANGE `waktu_ttd_peserta_1` `waktu_ttd_mahasiswa` DATETIME NULL DEFAULT NULL;
ALTER TABLE v2_berita_acara_ujian DROP COLUMN waktu_ttd_peserta_2;


-- ------------------------------------------------------------------
-- View Rekap keputusan ujian
-- ------------------------------------------------------------------
DROP VIEW IF EXISTS v2_rekap_keputusan_ujian;
CREATE VIEW v2_rekap_keputusan_ujian AS
SELECT /*DISTINCT*/
    rut.nomor_ujian,
    rut.id_proposal,
    rut.judul_proposal,
    e.id AS id_event,
    e.nama AS tahap,
    m.nim,
    m.nama,
    pu_p1.id_dosen AS id_dosen_penguji_1,
    dp1.nama AS nama_dosen_penguji_1,
    pu_p1.kesimpulan AS keputusan_penguji_1,
    pu_p2.id_dosen AS id_dosen_penguji_2,
    dp2.nama AS nama_dosen_penguji_2,
    pu_p2.kesimpulan AS keputusan_penguji_2
FROM
    v2_rekap_ujian_terjadwal rut
        INNER JOIN v2_event e ON rut.id_event = e.id
        INNER JOIN v2_mahasiswa m ON rut.nim_pengusul = m.nim
        LEFT OUTER JOIN v2_penilaian_ujian pu_p1 ON m.nim = pu_p1.nim AND pu_p1.peran = 'PENGUJI_1'
        LEFT OUTER JOIN v2_penilaian_ujian pu_p2 ON m.nim = pu_p2.nim AND pu_p2.peran = 'PENGUJI_2'
        LEFT OUTER JOIN v2_dosen dp1 ON pu_p1.id_dosen = dp1.id
        LEFT OUTER JOIN v2_dosen dp2 ON pu_p2.id_dosen = dp2.id
UNION ALL
SELECT /*DISTINCT*/
    rut.nomor_ujian,
    rut.id_proposal,
    rut.judul_proposal,
    e.id AS id_event,
    e.nama AS tahap,
    m.nim,
    m.nama,
    pu_p1.id_dosen AS id_dosen_penguji_1,
    dp1.nama AS nama_dosen_penguji_1,
    pu_p1.kesimpulan AS keputusan_penguji_1,
    pu_p2.id_dosen AS id_dosen_penguji_2,
    dp2.nama AS nama_dosen_penguji_2,
    pu_p2.kesimpulan AS keputusan_penguji_2
FROM
    v2_rekap_ujian_terjadwal rut
        INNER JOIN v2_event e ON rut.id_event = e.id
        INNER JOIN v2_mahasiswa m ON rut.nim_anggota = m.nim
        LEFT OUTER JOIN v2_penilaian_ujian pu_p1 ON m.nim = pu_p1.nim AND pu_p1.peran = 'PENGUJI_1'
        LEFT OUTER JOIN v2_penilaian_ujian pu_p2 ON m.nim = pu_p2.nim AND pu_p2.peran = 'PENGUJI_2'
        LEFT OUTER JOIN v2_dosen dp1 ON pu_p1.id_dosen = dp1.id
        LEFT OUTER JOIN v2_dosen dp2 ON pu_p2.id_dosen

/* All above codes have been ran ok on the server on 2020/06/18 at 10:59 PM. */

-- ------------------------------------------------------------------
-- Buat agar nilainya bisa 6, tetapi yang 4-6 tidak apa2 tidak diisi.
-- (Run OK on the server on 2020/06/19 at 01:26 AM)
-- ------------------------------------------------------------------
ALTER TABLE `v2_penilaian_ujian` CHANGE `nilai_4` `nilai_4` FLOAT NULL DEFAULT '0';
ALTER TABLE `v2_penilaian_ujian` CHANGE `nilai_5` `nilai_5` FLOAT NULL DEFAULT '0';
ALTER TABLE `v2_penilaian_ujian` ADD COLUMN `nilai_6` FLOAT NULL DEFAULT '0' AFTER nilai_5;

-- ------------------------------------------------------------------
-- View rekap berita acara
-- (Run OK on the server on 2020/06/20 at 10:46 PM)
-- ------------------------------------------------------------------
CREATE VIEW v2_rekap_berita_acara AS
SELECT
    rku.nomor_ujian,
    rku.nim,
    rku.nama AS nama_mahasiswa,
    m.kode_prodi AS prodi,
    rku.tahap,
    dm.nama AS moderator,
    rku.nama_dosen_penguji_1 AS penguji_1,
    rku.keputusan_penguji_1,
    rku.nama_dosen_penguji_2 AS penguji_2,
    rku.keputusan_penguji_2,
    ba.waktu_ttd_mahasiswa AS ttd_berita_acara
FROM
    v2_rekap_keputusan_ujian rku
        INNER JOIN v2_mahasiswa m ON rku.nim = m.nim
        LEFT OUTER JOIN v2_berita_acara_ujian ba ON ba.nim = rku.nim
        LEFT OUTER JOIN v2_dosen dm ON dm.id = ba.id_moderator_riil
ORDER BY nomor_ujian, kode_prodi ASC;




-- Modifikasi VIEW v2_rekap_keputusan_ujian dan v2_rekap_berita_acara karena ada kesalahan yang menyebabkan mhs yang mengulang di tahap sebelumnya yang mendafttar kembali, statusnya langsung MENGULANG

DROP VIEW IF EXISTS v2_rekap_keputusan_ujian;
CREATE VIEW v2_rekap_keputusan_ujian AS
SELECT /*DISTINCT*/
    rut.nomor_ujian,
    rut.id_proposal,
    rut.judul_proposal,
    e.id AS id_event,
    e.nama AS tahap,
    m.nim,
    m.nama,
    pu_p1.id_dosen AS id_dosen_penguji_1,
    dp1.nama AS nama_dosen_penguji_1,
    pu_p1.kesimpulan AS keputusan_penguji_1,
    pu_p2.id_dosen AS id_dosen_penguji_2,
    dp2.nama AS nama_dosen_penguji_2,
    pu_p2.kesimpulan AS keputusan_penguji_2
FROM
    v2_rekap_ujian_terjadwal rut
        INNER JOIN v2_event e ON rut.id_event = e.id
        INNER JOIN v2_mahasiswa m ON rut.nim_pengusul = m.nim
        LEFT OUTER JOIN v2_penilaian_ujian pu_p1 ON m.nim = pu_p1.nim AND pu_p1.peran = 'PENGUJI_1' AND rut.nomor_ujian = pu_p1.id_ujian
        LEFT OUTER JOIN v2_penilaian_ujian pu_p2 ON m.nim = pu_p2.nim AND pu_p2.peran = 'PENGUJI_2' AND rut.nomor_ujian = pu_p2.id_ujian
        LEFT OUTER JOIN v2_dosen dp1 ON pu_p1.id_dosen = dp1.id
        LEFT OUTER JOIN v2_dosen dp2 ON pu_p2.id_dosen = dp2.id
UNION ALL
SELECT /*DISTINCT*/
    rut.nomor_ujian,
    rut.id_proposal,
    rut.judul_proposal,
    e.id AS id_event,
    e.nama AS tahap,
    m.nim,
    m.nama,
    pu_p1.id_dosen AS id_dosen_penguji_1,
    dp1.nama AS nama_dosen_penguji_1,
    pu_p1.kesimpulan AS keputusan_penguji_1,
    pu_p2.id_dosen AS id_dosen_penguji_2,
    dp2.nama AS nama_dosen_penguji_2,
    pu_p2.kesimpulan AS keputusan_penguji_2
FROM
    v2_rekap_ujian_terjadwal rut
        INNER JOIN v2_event e ON rut.id_event = e.id
        INNER JOIN v2_mahasiswa m ON rut.nim_anggota = m.nim
        LEFT OUTER JOIN v2_penilaian_ujian pu_p1 ON m.nim = pu_p1.nim AND pu_p1.peran = 'PENGUJI_1' AND rut.nomor_ujian = pu_p1.id_ujian
        LEFT OUTER JOIN v2_penilaian_ujian pu_p2 ON m.nim = pu_p2.nim AND pu_p2.peran = 'PENGUJI_2' AND rut.nomor_ujian = pu_p2.id_ujian
        LEFT OUTER JOIN v2_dosen dp1 ON pu_p1.id_dosen = dp1.id
        LEFT OUTER JOIN v2_dosen dp2 ON pu_p2.id_dosen = dp2.id;




DROP VIEW IF EXISTS v2_rekap_berita_acara_ujian;
CREATE VIEW v2_rekap_berita_acara_ujian AS
SELECT
    rku.nomor_ujian,
    rku.nim,
    rku.nama AS nama_mahasiswa,
    m.kode_prodi AS prodi,
    rku.tahap,
    dm.nama AS moderator,
    rku.nama_dosen_penguji_1 AS penguji_1,
    rku.keputusan_penguji_1,
    rku.nama_dosen_penguji_2 AS penguji_2,
    rku.keputusan_penguji_2,
    ba.waktu_ttd_mahasiswa AS ttd_berita_acara
FROM
    v2_rekap_keputusan_ujian rku
        INNER JOIN v2_mahasiswa m ON rku.nim = m.nim
        LEFT OUTER JOIN v2_berita_acara_ujian ba ON ba.nim = rku.nim AND ba.id_ujian = rku.nomor_ujian
        LEFT OUTER JOIN v2_dosen dm ON dm.id = ba.id_moderator_riil AND ba.id_ujian = rku.nomor_ujian;