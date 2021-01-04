/* SQLEditor (Generic SQL)*/


CREATE TABLE v2_ruang
(
id SMALLINT AUTO_INCREMENT ,
kode VARCHAR(50) UNIQUE ,
nama VARCHAR(255) NOT NULL UNIQUE ,
keterangan TEXT,
CONSTRAINT v2_ruang_pkey PRIMARY KEY (id)
);

CREATE TABLE v2_sesi
(
id SMALLINT,
waktu_mulai TIME NOT NULL UNIQUE ,
waktu_selesai TIME NOT NULL UNIQUE ,
CONSTRAINT v2_sesi_pkey PRIMARY KEY (id)
);

CREATE TABLE v2_ujian
(
id INTEGER NOT NULL AUTO_INCREMENT ,
id_proposal INTEGER,
id_event INTEGER,
id_dosen_penguji_1 SMALLINT,
id_dosen_penguji_2 SMALLINT,
id_dosen_moderator SMALLINT,
id_ruang SMALLINT,
id_sesi SMALLINT,
tanggal DATE,
CONSTRAINT v2_ujian_pkey PRIMARY KEY (id)
);

CREATE TABLE v2_penilaian_ujian
(
id_ujian INTEGER NOT NULL,
id_dosen SMALLINT NOT NULL,
peran ENUM('PENGUJI_1', 'PENGUJI_2') NOT NULL,
nilai_1 FLOAT NOT NULL,
nilai_2 FLOAT NOT NULL,
nilai_3 FLOAT NOT NULL,
nilai_4 FLOAT NOT NULL,
nilai_5 FLOAT NOT NULL,
revisi TEXT DEFAULT NULL,
kesimpulan ENUM('LULUS_TANPA_REVISI', 'LULUS_DENGAN_REVISI', 'MENGULANG') DEFAULT 'lulus_dengan_revisi',
CONSTRAINT v2_penilaian_ujian_pkey PRIMARY KEY (id_ujian)
);

DROP TABLE IF EXISTS v2_berita_acara_ujian;
CREATE TABLE v2_berita_acara_ujian
(
id_ujian INTEGER,
id_penguji_1_riil SMALLINT,
id_penguji_2_riil SMALLINT,
id_moderator_riil SMALLINT,
nim_peserta_1 VARCHAR(50),
nim_peserta_2 VARCHAR(50),
waktu_ttd_penguji_1 DATETIME,
waktu_ttd_penguji_2 DATETIME,
waktu_ttd_moderator DATETIME,
waktu_ttd_peserta_1 DATETIME,
waktu_ttd_peserta_2 DATETIME
) DEFAULT CHARSET=latin1; -- Beda charset, tidak bisa dijadikan foreign key

-- FK for v2_ujian
ALTER TABLE v2_ujian ADD CONSTRAINT fk_ujian_id_proposal FOREIGN KEY (id_proposal) REFERENCES v2_proposal (id);
ALTER TABLE v2_ujian ADD CONSTRAINT fk_ujian_id_event FOREIGN KEY (id_event) REFERENCES v2_event (id);
ALTER TABLE v2_ujian ADD CONSTRAINT fk_ujian_id_dosen_penguji_1 FOREIGN KEY (id_dosen_penguji_1) REFERENCES v2_dosen (id);
ALTER TABLE v2_ujian ADD CONSTRAINT fk_ujian_id_dosen_penguji_2 FOREIGN KEY (id_dosen_penguji_2) REFERENCES v2_dosen (id);
ALTER TABLE v2_ujian ADD CONSTRAINT fk_ujian_id_dosen_moderator FOREIGN KEY (id_dosen_moderator) REFERENCES v2_dosen (id);
ALTER TABLE v2_ujian ADD CONSTRAINT fk_ujian_id_ruang FOREIGN KEY (id_ruang) REFERENCES v2_ruang (id);
ALTER TABLE v2_ujian ADD CONSTRAINT fk_ujian_id_sesi FOREIGN KEY (id_sesi) REFERENCES v2_sesi (id);

-- FK for v2_penilaian_ujian
ALTER TABLE v2_penilaian_ujian ADD CONSTRAINT fk_penilaian_ujian_id_ujian FOREIGN KEY (id_ujian) REFERENCES v2_ujian (id);
ALTER TABLE v2_penilaian_ujian ADD CONSTRAINT fk_penilaian_ujian_id_dosen FOREIGN KEY (id_dosen) REFERENCES v2_dosen (id);

-- FK for v2_berita_acara_ujian
ALTER TABLE v2_berita_acara_ujian ADD CONSTRAINT fk_berita_acara_ujian_id_ujian FOREIGN KEY (id_ujian) REFERENCES v2_ujian (id);
ALTER TABLE v2_berita_acara_ujian ADD CONSTRAINT fk_berita_acara_ujian_id_penguji_1_riil FOREIGN KEY (id_penguji_1_riil) REFERENCES v2_dosen (id);
ALTER TABLE v2_berita_acara_ujian ADD CONSTRAINT fk_berita_acara_ujian_id_penguji_2_riil FOREIGN KEY (id_penguji_2_riil) REFERENCES v2_dosen (id);
ALTER TABLE v2_berita_acara_ujian ADD CONSTRAINT fk_berita_acara_ujian_id_moderator_riil FOREIGN KEY (id_moderator_riil) REFERENCES v2_dosen (id);
ALTER TABLE v2_berita_acara_ujian ADD CONSTRAINT fk_berita_acara_ujian_nim_peserta_1 FOREIGN KEY (nim_peserta_1) REFERENCES v2_mahasiswa (nim);
ALTER TABLE v2_berita_acara_ujian ADD CONSTRAINT fk_berita_acara_ujian_nim_peserta_2 FOREIGN KEY (nim_peserta_2) REFERENCES v2_mahasiswa (nim);
