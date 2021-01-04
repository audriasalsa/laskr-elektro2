-- View untuk merangkum berita acara per mahasiswa
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
        LEFT OUTER JOIN v2_berita_acara_ujian ba ON ba.nim = rku.nim
        LEFT OUTER JOIN v2_dosen dm ON dm.id = ba.id_moderator_riil;

-- Tabel untuk menampung hasil revisi.
DROP TABLE IF EXISTS v2_revisi_ujian_akhir;
CREATE TABLE v2_revisi_ujian_akhir
(
    id_ujian INTEGER,
    id_proposal INTEGER,
    judul_final text NOT NULL ,
    file_proposal_final VARCHAR(255) NOT NULL ,
    file_draft_publikasi_final VARCHAR(255) NOT NULL,
    id_dosen_penguji_1 SMALLINT NOT NULL,
    id_dosen_penguji_2 SMALLINT NOT NULL,
    waktu_persetujuan_penguji_1 DATETIME,
    waktu_persetujuan_penguji_2 DATETIME,
    PRIMARY KEY (id_ujian, id_proposal),
    CONSTRAINT fk_id_ujian FOREIGN KEY (id_ujian) REFERENCES v2_ujian(id),
    CONSTRAINT fk_id_proposal FOREIGN KEY (id_proposal) REFERENCES v2_proposal(id),
    CONSTRAINT fk_id_dosen_penguji_1 FOREIGN KEY (id_dosen_penguji_1) REFERENCES v2_berita_acara_ujian(id_penguji_1_riil),
    CONSTRAINT fk_id_dosen_penguji_2 FOREIGN KEY (id_dosen_penguji_2) REFERENCES v2_berita_acara_ujian(id_penguji_2_riil)
);


-- View untuk menampilkan semua nilai dan revisi dari penguji 1 dan 2 untuk mahasiswa dengan nim tertentu
DROP VIEW IF EXISTS v2_rekap_penilaian_ujian;
CREATE VIEW v2_rekap_penilaian_ujian AS
SELECT
    rku.nomor_ujian,
    rku.tahap,
    rku.id_proposal,
    rku.nim,
    rku.nama,
    pu_dp1.id_dosen AS id_penguji_1,
    pu_dp1.revisi AS revisi_penguji_1,
    pu_dp1.nilai_1 AS nilai_1_penguji_1,
    pu_dp1.nilai_2 AS nilai_2_penguji_1,
    pu_dp1.nilai_3 AS nilai_3_penguji_1,
    pu_dp2.id_dosen AS id_penguji_2,
    pu_dp2.revisi AS revisi_penguji_2,
    pu_dp2.nilai_1 AS nilai_1_penguji_2,
    pu_dp2.nilai_2 AS nilai_2_penguji_2,
    pu_dp2.nilai_3 AS nilai_3_penguji_2
FROM v2_rekap_keputusan_ujian rku
         LEFT OUTER JOIN v2_penilaian_ujian pu_dp1 on rku.nomor_ujian = pu_dp1.id_ujian AND rku.nim = pu_dp1.nim AND pu_dp1.peran = 'PENGUJI_1'
         LEFT OUTER JOIN v2_penilaian_ujian pu_dp2 on rku.nomor_ujian = pu_dp2.id_ujian AND rku.nim = pu_dp2.nim AND pu_dp2.peran = 'PENGUJI_2';



-- VIEW rekap revisi ujian untuk ditampilkan di mahasiswa
DROP VIEW IF EXISTS v2_rekap_revisi_ujian_mahasiswa;
CREATE VIEW v2_rekap_revisi_ujian_mahasiswa AS
SELECT rpu.nomor_ujian, rpu.tahap, rpu.nim, m.nama, dp1.nama AS penguji_1, revisi_penguji_1, dp2.nama AS penguji_2, revisi_penguji_2
FROM v2_rekap_penilaian_ujian rpu
         INNER JOIN v2_mahasiswa m ON rpu.nim = m.nim
         LEFT OUTER JOIN v2_dosen dp1 ON dp1.id = rpu.id_penguji_1
         LEFT OUTER JOIN v2_dosen dp2 ON dp2.id = rpu.id_penguji_2;

/* All above codes have been ran ok on the server on 2020/06/23 at 11:31 PM. */


-- Ubah kolom proposal final
ALTER TABLE `v2_revisi_ujian_akhir` CHANGE `file_proposal_final` `file_laporan_final` VARCHAR(255) NOT NULL;
ALTER TABLE v2_revisi_ujian_akhir ADD COLUMN status_persetujuan_penguji_1 ENUM('diajukan', 'disetujui') DEFAULT 'diajukan';
ALTER TABLE v2_revisi_ujian_akhir ADD COLUMN status_persetujuan_penguji_2 ENUM('diajukan', 'disetujui') DEFAULT 'diajukan';
ALTER TABLE v2_revisi_ujian_akhir DROP COLUMN waktu_persetujuan_penguji_1;
ALTER TABLE v2_revisi_ujian_akhir DROP COLUMN waktu_persetujuan_penguji_2;
-- -----

/* All above codes have been ran ok on the server on 2020/06/25 at 18:53 PM. */