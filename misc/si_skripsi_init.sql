-- ------------------------------------------------------------------------------------
-- Step 4: After dumped SQL executed, setup necessary tables and views
-- NOTICE! Beyond this step must always be synchronized with si_skripsi_init.sq in the web app project.
-- ------------------------------------------------------------------------------------

DROP TABLE IF EXISTS v2_credential;

CREATE TABLE v2_credential
(
    username VARCHAR(255) NOT NULL PRIMARY KEY,
    `password` VARCHAR(255) NOT NULL,
    access_type ENUM('mahasiswa', 'dosen', 'panitia') DEFAULT 'mahasiswa',
    id_mahasiswa VARCHAR(50) DEFAULT NULL,
    id_dosen SMALLINT DEFAULT NULL
);

ALTER TABLE v2_credential ADD CONSTRAINT fk_credential_id_mahasiswa FOREIGN KEY (id_mahasiswa) REFERENCES v2_mahasiswa (nim);
ALTER TABLE v2_credential ADD CONSTRAINT fk_credential_id_dosen FOREIGN KEY (id_dosen) REFERENCES v2_dosen (id);

-- Masukkan nim sebagai username dan password default untuk tabel credential
INSERT INTO v2_credential (username, `password`, access_type, id_mahasiswa)
SELECT nim AS username, nim AS `password`, 'mahasiswa', nim AS id_mahasiswa FROM v2_mahasiswa;

-- Masukkan data Pak Arief dan Pak Ahmadi
-- TODO: Perlu dibuat auto_increment di kolom id dosen!
INSERT INTO v2_dosen (id, nama) VALUES
(27, 'Arief Prasetyo, S.Kom., M.Kom.'),
(28, 'Ahmadi Yuli Ananta, S.T., M.M.');

-- Memasukkan data dosen ke kredensial
INSERT INTO v2_credential (username, `password`, access_type, id_dosen) VALUES
('ririd', 'ririd', 'dosen', (SELECT id FROM v2_dosen WHERE nama LIKE '%ririd%' LIMIT 1)),
('budi', 'budi', 'dosen', (SELECT id FROM v2_dosen WHERE nama LIKE '%budi%' LIMIT 1)),
('cahya', 'cahya', 'dosen', (SELECT id FROM v2_dosen WHERE nama LIKE '%cahya%' LIMIT 1)),
('deddy', 'deddy', 'dosen', (SELECT id FROM v2_dosen WHERE nama LIKE '%deddy%' LIMIT 1)),
('dhebys', 'dhebys', 'dosen', (SELECT id FROM v2_dosen WHERE nama LIKE '%dhebys%' LIMIT 1)),
('dimas', 'dimas', 'dosen', (SELECT id FROM v2_dosen WHERE nama LIKE '%dimas%' LIMIT 1)),
('dwi', 'dwi', 'dosen', (SELECT id FROM v2_dosen WHERE nama LIKE '%dwi%' LIMIT 1)),
('laras', 'laras', 'dosen', (SELECT id FROM v2_dosen WHERE nama LIKE '%laras%' LIMIT 1)),
('eko', 'eko', 'dosen', (SELECT id FROM v2_dosen WHERE nama LIKE '%eko%' LIMIT 1)),
('erfan', 'erfan', 'dosen', (SELECT id FROM v2_dosen WHERE nama LIKE '%erfan%' LIMIT 1)),
('faisal', 'faisal', 'dosen', (SELECT id FROM v2_dosen WHERE nama LIKE '%faisal%' LIMIT 1)),
('gunawan', 'gunawan', 'dosen', (SELECT id FROM v2_dosen WHERE nama LIKE '%gunawan%' LIMIT 1)),
('hendra', 'hendra', 'dosen', (SELECT id FROM v2_dosen WHERE nama LIKE '%hendra%' LIMIT 1)),
('imam', 'imam', 'dosen', (SELECT id FROM v2_dosen WHERE nama LIKE '%imam%' LIMIT 1)),
('indra', 'indra', 'dosen', (SELECT id FROM v2_dosen WHERE nama LIKE '%indra%' LIMIT 1)),
('arwin', 'arwin', 'dosen', (SELECT id FROM v2_dosen WHERE nama LIKE '%arwin%' LIMIT 1)),
('luqman', 'luqman', 'dosen', (SELECT id FROM v2_dosen WHERE nama LIKE '%luqman%' LIMIT 1)),
('mungki', 'mungki', 'dosen', (SELECT id FROM v2_dosen WHERE nama LIKE '%mungki%' LIMIT 1)),
('yoga', 'yoga', 'dosen', (SELECT id FROM v2_dosen WHERE nama LIKE '%yoga%' LIMIT 1)),
('prima', 'prima', 'dosen', (SELECT id FROM v2_dosen WHERE nama LIKE '%prima%' LIMIT 1)),
('rawansyah', 'rawansyah', 'dosen', (SELECT id FROM v2_dosen WHERE nama LIKE '%rawansyah%' LIMIT 1)),
('andrie', 'andrie', 'dosen', (SELECT id FROM v2_dosen WHERE nama LIKE '%andrie%' LIMIT 1)),
('rudy', 'rudy', 'dosen', (SELECT id FROM v2_dosen WHERE nama LIKE '%rudy%' LIMIT 1)),
('rosi', 'rosi', 'dosen', (SELECT id FROM v2_dosen WHERE nama LIKE '%rosi%' LIMIT 1)),
('usman', 'usman', 'dosen', (SELECT id FROM v2_dosen WHERE nama LIKE '%usman%' LIMIT 1)),
('yuri', 'yuri', 'dosen', (SELECT id FROM v2_dosen WHERE nama LIKE '%yuri%' LIMIT 1)),
('arief', 'arief', 'dosen', (SELECT id FROM v2_dosen WHERE nama LIKE '%arief%' LIMIT 1)),
('ahmadi', 'ahmadi', 'dosen', (SELECT id FROM v2_dosen WHERE nama LIKE '%ahmadi%' LIMIT 1));

-- Buat tabel bimbingan
DROP TABLE IF EXISTS v2_bimbingan;

CREATE TABLE v2_bimbingan (
                              nim_mahasiswa VARCHAR(50),
                              id_pembimbing_1 SMALLINT,
                              id_pembimbing_2 SMALLINT,
                              PRIMARY KEY (nim_mahasiswa),
                              CONSTRAINT fk_pk_bimbingan_nim_mahasiswa FOREIGN KEY (nim_mahasiswa) REFERENCES v2_mahasiswa (nim),
                              CONSTRAINT fk_bimbingan_pembimbing_1 FOREIGN KEY (id_pembimbing_1) REFERENCES v2_dosen (id),
                              CONSTRAINT fk_bimbingan_pembimbing_2 FOREIGN KEY (id_pembimbing_1) REFERENCES v2_dosen (id)
);

-- Isi data ke tabel bimbingan
INSERT INTO v2_bimbingan (nim_mahasiswa, id_pembimbing_1)
SELECT nim_pengusul, id_dosen_pembimbing_1 FROM v2_proposal;

-- Tambahkan kolom keterangan di tabel bimbingan
ALTER TABLE v2_bimbingan ADD COLUMN `keterangan` TEXT DEFAULT NULL;

-- --------
-- Tambahkan beberapa kolom yang diperlukan di tabel dosen
-- --------
ALTER TABLE v2_dosen ADD COLUMN nip VARCHAR(20);
ALTER TABLE v2_dosen ADD COLUMN nidn VARCHAR(20);

-- Buat dulu tabel untuk menyimpan file upload
DROP TABLE IF EXISTS v2_uploaded_file;
CREATE TABLE v2_uploaded_file
(
    stored_name VARCHAR(255) UNIQUE NOT NULL PRIMARY KEY,
    `type` VARCHAR(255),
    size BIGINT,
    input_name VARCHAR(255),
    description TEXT DEFAULT NULL
);

ALTER TABLE v2_uploaded_file ADD COLUMN original_name TEXT DEFAULT NULL AFTER stored_name;
ALTER TABLE v2_uploaded_file ADD COLUMN stored_time DATETIME DEFAULT CURRENT_TIMESTAMP AFTER input_name;

-- Buat tabel pendaftaran sempro
DROP TABLE IF EXISTS v2_pendaftaran_sempro;
CREATE TABLE v2_pendaftaran_sempro
(
    id_proposal INTEGER NOT NULL UNIQUE PRIMARY KEY,
    judul TEXT NOT NULL,
    grup_riset ENUM('SISTEM INFORMASI', 'SISTEM CERDAS', 'VISI KOMPUTER', 'JARKOM, ARSITEKTUR DAN KEAMANAN DATA', 'MULTIMEDIA DAN GAME') NOT NULL,
    file_activity_control VARCHAR(255) NOT NULL,
    file_persetujuan_maju VARCHAR(255) NOT NULL,
    file_proposal_revisi VARCHAR(255) NOT NULL,
    CONSTRAINT fk_pendaftaran_sempro_id_proposal FOREIGN KEY (id_proposal) REFERENCES v2_proposal (id),
    CONSTRAINT fk_pendaftaran_sempro_file_activity_control FOREIGN KEY (file_activity_control) REFERENCES v2_uploaded_file (stored_name),
    CONSTRAINT fk_pendaftaran_sempro_file_persetujuan_maju FOREIGN KEY (file_persetujuan_maju) REFERENCES v2_uploaded_file (stored_name),
    CONSTRAINT fk_pendaftaran_sempro_file_proposal_revisi FOREIGN KEY (file_proposal_revisi) REFERENCES v2_uploaded_file (stored_name)
);

-- Buat tabel grup riset
DROP TABLE IF EXISTS v2_grup_riset;
CREATE TABLE v2_grup_riset
(
    kode VARCHAR(50) NOT NULL PRIMARY KEY,
    nama VARCHAR(255),
    nama_internasional VARCHAR(255)
);
INSERT INTO v2_grup_riset VALUES
('SI', 'SISTEM INFORMASI', 'INFORMATION SYSTEM'),
('AI', 'SISTEM CERDAS', 'INTELEGENCE SYSTEM'),
('Visikom', 'VISI KOMPUTER', 'COMPUTER VISION'),
('Jarkom', 'JARKOM, ARSITEKTUR DAN KEAMANAN DATA', 'COMPUTER NETWORK, ARCHITECTURE AND DATA SECURITY'),
('MMG', 'MULTIMEDIA DAN GAME', 'MULTIMEDIA AND GAME');

-- --------
-- Views
-- --------
-- View untuk hasil rekap verifikasi
DROP VIEW IF EXISTS v2_rekap_verifikasi;
CREATE VIEW v2_rekap_verifikasi AS
SELECT m.nim, m.nama, d.nama AS dosen_pembimbing_1, p.judul_proposal, p.nama_grup_riset, v.id AS id_verifikasi, v.saran_revisi
FROM v2_mahasiswa m INNER JOIN v2_proposal p ON m.nim = p.nim_pengusul
                    INNER JOIN v2_verifikasi_proposal v ON p.nim_pengusul = v.nim_pengusul
                    INNER JOIN v2_dosen d ON p.id_dosen_pembimbing_1 = d.id;

-- Untuk melihat data bimbingan per dosen
DROP VIEW IF EXISTS v2_bimbingan_dosen;
CREATE VIEW v2_bimbingan_dosen AS
SELECT d.id, d.nama AS nama_dosen_pembimbing_1, p.nim_pengusul AS nim, m.nama AS nama_mahasiswa, p.judul_proposal
FROM v2_mahasiswa m INNER JOIN v2_proposal p ON p.nim_pengusul = m.nim
                    INNER JOIN v2_dosen d ON d.id = p.id_dosen_pembimbing_1;

-- Untuk melihat data pendaftaran proposal
DROP VIEW IF EXISTS v2_rekap_pendaftaran_sempro;
CREATE VIEW v2_rekap_pendaftaran_sempro AS
SELECT
    e.id AS id_event,
    e.nama AS keterangan,
    m.nim,
    m.nama AS nama_mahasiswa,
    d.nama AS nama_dosen_pembimbing_1,
    m.kode_prodi,
    p.id AS id_proposal,
    p.judul_proposal AS judul_proposal_awal,
    p.nama_grup_riset AS grup_riset_awal,
    IF(rv.id_verifikasi IS NULL, 'Belum diverifikasi', 'Sudah diverifikasi') AS status_verifikasi,
    rv.saran_revisi AS saran_revisi_dari_grup_riset,
    ps.judul AS judul_direvisi,
    ps.grup_riset AS grup_riset_direvisi,
    ps.file_activity_control,
    ps.file_persetujuan_maju,
    ps.file_proposal_revisi
FROM v2_mahasiswa m
         INNER JOIN v2_proposal p ON m.nim = p.nim_pengusul
         LEFT OUTER JOIN v2_rekap_verifikasi rv ON m.nim = rv.nim
         INNER JOIN v2_pendaftaran_sempro ps ON p.id = ps.id_proposal
         INNER JOIN v2_dosen d ON p.id_dosen_pembimbing_1 = d.id
         INNER JOIN v2_event e ON ps.id_event = e.id;

-- --------------------------------------------------------------------
-- PENYESUAIAN UNTUK MENGAKOMODIR TAHAP-2 SEMPRO
-- --------------------------------------------------------------------
DROP TABLE IF EXISTS v2_event;

CREATE TABLE v2_event
(
    id INTEGER PRIMARY KEY AUTO_INCREMENT,
    nama VARCHAR(255) NOT NULL,
    deskripsi TEXT,
    kategori ENUM('verifikasi_grup_riset', 'seminar_proposal', 'ujian_akhir'),
    tanggal_mulai DATE DEFAULT '2001-01-01',
    tanggal_selesai DATE DEFAULT '2099-12-31'
);

INSERT INTO v2_event (nama, kategori) VALUES
('Verifikasi Grup Riset', 'verifikasi_grup_riset'),
('Seminar Proposal Tahap 1', 'seminar_proposal'),
('Seminar Proposal Tahap 2', 'seminar_proposal');
-- Tambahkan kolom untuk mencatat waktu pendaftaran
ALTER TABLE v2_pendaftaran_sempro ADD COLUMN tanggal_daftar DATETIME DEFAULT NOW();
ALTER TABLE v2_pendaftaran_sempro ADD COLUMN id_event INTEGER DEFAULT 2;
ALTER TABLE v2_pendaftaran_sempro ADD CONSTRAINT fk_pendaftaran_sempro_id_event FOREIGN KEY (id_event) REFERENCES v2_event(id);
ALTER TABLE v2_pendaftaran_sempro DROP PRIMARY KEY, ADD PRIMARY KEY(id_proposal, id_event);
-- WARNING: Harus re-create table pendaftaran_sempro setelah ditambahkan ini! --

-- --------------------------------------------------------------------
-- TABLE BARU UNTUK IMPORT HASIL SEMPRO
-- --------------------------------------------------------------------
CREATE TABLE v2_hasil_sempro
(
    id_event INTEGER NOT NULL,
    nim VARCHAR(50) NOT NULL,
    hasil ENUM('Diterima Tanpa Revisi', 'Diterima dengan Revisi', 'Ditolak', 'Lain-lain') NOT NULL,
    keterangan TEXT NULL,
    PRIMARY KEY(id_event, nim),
    CONSTRAINT fk_id_event FOREIGN KEY (id_event) REFERENCES v2_event (id),
    CONSTRAINT fk_nim FOREIGN KEY (nim) REFERENCES v2_mahasiswa (nim)
);

-- --------------------------------------------------------------------
-- VIEW HISTORY SEMPRO
-- --------------------------------------------------------------------
CREATE VIEW v2_histori_sempro AS
SELECT hs.nim, m.nama, e.nama AS tahap_lulus, hs.hasil as keputusan FROM v2_mahasiswa m
                                                                             INNER JOIN v2_hasil_sempro hs ON hs.nim = m.nim
                                                                             INNER JOIN v2_event e ON e.id = hs.id_event;

-- --------------------------------------------------------------------
-- VIEW Rekap Lulus Sempro
-- --------------------------------------------------------------------
CREATE VIEW v2_rekap_lulus_sempro AS
SELECT hs.nim, m.nama, hs.hasil, e.nama AS tahap_terkahir_sempro, e.id AS id_event_terakhir_sempro FROM v2_hasil_sempro hs
                                                                                                            INNER JOIN v2_mahasiswa m ON hs.nim = m.nim
                                                                                                            INNER JOIN v2_event e ON hs.id_event = e.id
WHERE e.kategori = 'seminar_proposal' AND hs.hasil LIKE 'Diterima%';

-- --------------------------------------------------------------------
-- Ubah kolom nama di tabel v2_dosen jadi unique
-- --------------------------------------------------------------------
ALTER TABLE v2_dosen ADD CONSTRAINT unique_name UNIQUE (nama);

-- Tambah foreign key untuk pembimbing 2
ALTER TABLE v2_bimbingan ADD  CONSTRAINT fk_bimbingan_pembimbing_dua FOREIGN KEY (id_pembimbing_2) REFERENCES v2_dosen(id);

-- --------------------------------------------------------------------
-- Menyalin tabel v2_bimbingan untuk cadangan
-- --------------------------------------------------------------------
CREATE TABLE v2_temp_bimbingan AS SELECT * FROM v2_bimbingan;
-- --------------------------------------------------------------------
-- VIEW v2_rekap_bimbingan_per_dosen
-- --------------------------------------------------------------------
DROP VIEW IF EXISTS v2_rekap_bimbingan_per_dosen;
CREATE VIEW v2_rekap_bimbingan_per_dosen AS
SELECT
    d.id AS id_dosen,
    d.nama AS nama_dosen,
    b.nim_mahasiswa,
    m.nama AS nama_mahasiswa,
    m.nomor_ponsel,
    m.nomor_ponsel_orang_tua,
    m.email,
    p.id AS id_proposal,
    p.judul_proposal as judul_proposal,
    'Pembimbing-1' AS status_pembimbingan
FROM
    v2_dosen d
        INNER JOIN v2_bimbingan b ON d.id = b.id_pembimbing_1
        INNER JOIN v2_mahasiswa m ON m.nim = b.nim_mahasiswa
        INNER JOIN v2_proposal p ON p.nim_pengusul = b.nim_mahasiswa
UNION ALL
SELECT
    d.id AS id_dosen,
    d.nama AS nama_dosen,
    b.nim_mahasiswa,
    m.nama AS nama_mahasiswa,
    m.nomor_ponsel,
    m.nomor_ponsel_orang_tua,
    m.email,
    p.id AS id_proposal,
    p.judul_proposal as judul_proposal,
    'Pembimbing-2' AS status_pembimbingan
FROM
    v2_dosen d
        INNER JOIN v2_bimbingan b ON d.id = b.id_pembimbing_2
        INNER JOIN v2_mahasiswa m ON m.nim = b.nim_mahasiswa
        INNER JOIN v2_proposal p ON p.nim_pengusul = b.nim_mahasiswa;

-- --------------------------------------------------------------------
-- TABLE BARU UNTUK UPLOAD REVISI SEMPRO
-- --------------------------------------------------------------------
DROP TABLE IF EXISTS v2_revisi_sempro;

CREATE TABLE v2_revisi_sempro
(
    id_proposal INTEGER PRIMARY KEY,
    nim_mahasiswa VARCHAR(50),
    id_event_sempro_terakhir INTEGER,
    judul_final TEXT,
    id_dosen_moderator SMALLINT,
    id_dosen_pembahas_1 SMALLINT,
    revisi_pembahas_1 TEXT,
    id_dosen_pembahas_2 SMALLINT,
    revisi_pembahas_2 TEXT,
    file_berita_acara VARCHAR(255) NULL,
    file_lembar_revisi_1 VARCHAR(255),
    file_lembar_revisi_2 VARCHAR(255),
    file_proposal_final VARCHAR(255),
    file_scan_lembar_pengesahan_proposal VARCHAR(255),
    tanggal_unggah DATETIME DEFAULT NOW(),
    CONSTRAINT fk_revisi_sempro_id_proposal FOREIGN KEY (id_proposal) REFERENCES v2_proposal(id),
    CONSTRAINT fk_revisi_sempro_nim_mahasiswa FOREIGN KEY (nim_mahasiswa) REFERENCES v2_mahasiswa(nim),
    CONSTRAINT fk_revisi_sempro_id_event_sempro_terakhir FOREIGN KEY (id_event_sempro_terakhir) REFERENCES v2_event(id),
    CONSTRAINT fk_revisi_sempro_id_dosen_moderator FOREIGN KEY (id_dosen_moderator) REFERENCES v2_dosen(id),
    CONSTRAINT fk_revisi_sempro_id_dosen_pembahas_1 FOREIGN KEY (id_dosen_pembahas_1) REFERENCES v2_dosen(id),
    CONSTRAINT fk_revisi_sempro_id_dosen_pembahas_2 FOREIGN KEY (id_dosen_pembahas_2) REFERENCES v2_dosen(id),
    CONSTRAINT fk_revisi_sempro_file_berita_acara FOREIGN KEY (file_berita_acara) REFERENCES v2_uploaded_file(stored_name),
    CONSTRAINT fk_revisi_sempro_file_lembar_revisi_1 FOREIGN KEY (file_lembar_revisi_1) REFERENCES v2_uploaded_file(stored_name),
    CONSTRAINT fk_revisi_sempro_file_lembar_revisi_2 FOREIGN KEY (file_lembar_revisi_2) REFERENCES v2_uploaded_file(stored_name),
    CONSTRAINT fk_revisi_sempro_file_proposal_final FOREIGN KEY (file_proposal_final) REFERENCES v2_uploaded_file(stored_name),
    CONSTRAINT fk_revisi_sempro_file_scan_lembar_pengesahan_proposal FOREIGN KEY (file_scan_lembar_pengesahan_proposal) REFERENCES v2_uploaded_file(stored_name)
);

-- --------------------------------------------------------------------
-- VIEW v2_rekap_revisi_sempro
-- --------------------------------------------------------------------
DROP VIEW IF EXISTS v2_rekap_revisi_sempro;

CREATE VIEW v2_rekap_revisi_sempro AS
SELECT
    rs.id_proposal,
    rs.nim_mahasiswa,
    m.nama  AS nama_mahasiswa,
    e.nama AS tahap_lulus_sempro,
    rs.judul_final,
    dm.nama AS nama_dosen_moderator,
    dp1.nama AS nama_dosen_pembahas_1,
    rs.revisi_pembahas_1,
    dp2.nama AS nama_dosen_pembahas_2,
    rs.revisi_pembahas_2,
    rs.file_berita_acara,
    rs.file_lembar_revisi_1,
    rs.file_lembar_revisi_2,
    rs.file_proposal_final,
    rs.file_scan_lembar_pengesahan_proposal,
    rs.tanggal_unggah
FROM v2_revisi_sempro rs
         INNER JOIN v2_mahasiswa m ON rs.nim_mahasiswa = m.nim
         INNER JOIN v2_event e on rs.id_event_sempro_terakhir = e.id
         INNER JOIN v2_dosen dm ON dm.id = rs.id_dosen_moderator
         INNER JOIN v2_dosen dp1 ON dp1.id = rs.id_dosen_pembahas_1
         INNER JOIN v2_dosen dp2 ON dp2.id = rs.id_dosen_pembahas_2;


-- --------------------------------------------------------------------
-- Tabel log bimbingan
-- --------------------------------------------------------------------
USE db_tugasakhir;

DROP TABLE IF EXISTS v2_log_bimbingan;

CREATE TABLE v2_log_bimbingan
(
    id INTEGER PRIMARY KEY AUTO_INCREMENT,
    nim_mahasiswa VARCHAR(30) NOT NULL,
    id_dosen_pembimbing SMALLINT NOT NULL,
    tanggal DATE NOT NULL ,
    materi_bimbingan TEXT NOT NULL,
    status ENUM('diajukan', 'disetujui', 'ditolak') DEFAULT 'diajukan',
    CONSTRAINT fk_nim_mahasiswa FOREIGN KEY (nim_mahasiswa) REFERENCES v2_mahasiswa(nim),
    CONSTRAINT fk_id_dosen_pembimbing FOREIGN KEY (id_dosen_pembimbing) REFERENCES v2_dosen(id)
);

-- --------------------------------------------------------------------
-- View rekap log bimbingan
-- --------------------------------------------------------------------
CREATE VIEW v2_rekap_log_bimbingan AS
SELECT
    rekap_nama.*,
    rekap_diajukan.log_bimbingan_diajukan,
    COALESCE(rekap_disetujui.log_bimbingan_diterima, 0) AS log_bimbingan_disetujui
FROM
    (
        SELECT DISTINCT lb.nim_mahasiswa AS nim, m.nama
        FROM v2_log_bimbingan lb
        INNER JOIN v2_mahasiswa m ON lb.nim_mahasiswa = m.nim
    ) AS rekap_nama
        INNER JOIN
    (
        SELECT nim_mahasiswa AS nim, count(*) AS log_bimbingan_diajukan
        FROM v2_log_bimbingan
        GROUP BY nim_mahasiswa
    ) AS rekap_diajukan
    ON rekap_nama.nim = rekap_diajukan.nim
        LEFT OUTER JOIN
    (
        SELECT nim_mahasiswa AS nim, count(*) AS log_bimbingan_diterima
        FROM v2_log_bimbingan
        WHERE status = 'disetujui'
        GROUP BY nim_mahasiswa
    ) AS rekap_disetujui
    ON rekap_nama.nim = rekap_disetujui.nim;

-- --------------------------------------------------------------------
-- View rekap nomor telepon log bimbingan
-- --------------------------------------------------------------------
CREATE VIEW v2_rekap_ponsel_log_bimbingan AS
SELECT
    m.nim, m.nama, m.nomor_ponsel, m.nomor_ponsel_orang_tua,
    COALESCE(v2rlb.log_bimbingan_diajukan, 0) AS jumlah_log_bimbingan,
    COALESCE(v2rlb.log_bimbingan_disetujui, 0) AS jumlah_log_diterima
FROM v2_mahasiswa m
         LEFT OUTER JOIN v2_rekap_log_bimbingan v2rlb on m.nim = v2rlb.nim;

-- --------------------------------------------------------------------
-- View rekap log bimbingan per dosen
-- --------------------------------------------------------------------
ALTER TABLE v2_dosen ADD COLUMN nomor_ponsel VARCHAR(50) AFTER nidn;
CREATE VIEW v2_rekap_log_bimbingan_dosen AS
SELECT
    lb.id_dosen_pembimbing AS id_dosen,
    d.nama,
    d.nomor_ponsel,
    count(*) AS total_log,
    (SELECT COUNT(*) FROM v2_log_bimbingan lb1 WHERE lb1.id_dosen_pembimbing = lb.id_dosen_pembimbing AND lb1.status = 'disetujui') AS log_disetujui,
    (SELECT COUNT(*) FROM v2_log_bimbingan lb2 WHERE lb2.id_dosen_pembimbing = lb.id_dosen_pembimbing AND lb2.status = 'ditolak') AS log_ditolak,
    (SELECT COUNT(*) FROM v2_log_bimbingan lb3 WHERE lb3.id_dosen_pembimbing = lb.id_dosen_pembimbing AND lb3.status = 'diajukan') AS log_pending,
    CONCAT(CEIL((SELECT COUNT(*) FROM v2_log_bimbingan lb3 WHERE lb3.id_dosen_pembimbing = lb.id_dosen_pembimbing AND lb3.status = 'diajukan') / count(*) * 100), '%') AS persen_pending
FROM
    v2_log_bimbingan lb
    INNER JOIN v2_dosen d ON d.id = lb.id_dosen_pembimbing
GROUP BY lb.id_dosen_pembimbing, d.nama;


-- --------------------------------------------------------------------
-- Pendaftaran Ujian Akhir
-- --------------------------------------------------------------------
DROP TABLE IF EXISTS v2_pendaftaran_ujian_akhir;
CREATE TABLE v2_pendaftaran_ujian_akhir
(
    id_proposal INTEGER NOT NULL,
    id_event INTEGER NOT NULL,
    file_laporan_akhir VARCHAR (255) NOT NULL,
    file_presentasi VARCHAR (255) NOT NULL,
    file_draft_publikasi VARCHAR (255) NOT NULL,
    link_demo VARCHAR (255),
    status_persetujuan_pembimbing_1 ENUM('diajukan', 'disetujui') DEFAULT 'diajukan',
    status_persetujuan_pembimbing_2 ENUM('diajukan', 'disetujui') DEFAULT 'diajukan',
    PRIMARY KEY (id_proposal, id_event),
    CONSTRAINT fk_pendaftaran_ujian_akhir_id_proposal FOREIGN KEY (id_proposal) REFERENCES v2_proposal (id),
    CONSTRAINT fk_pendaftaran_ujian_akhir_event FOREIGN KEY (id_event) REFERENCES v2_event (id)
);

-- --------------------------------------------------------------------
-- Modifikasi proposal untuk mengakomodir 1 proposal 2 authors
-- --------------------------------------------------------------------
ALTER TABLE v2_proposal ADD COLUMN nim_anggota VARCHAR(50) AFTER nim_pengusul;
ALTER TABLE v2_proposal MODIFY COLUMN nim_anggota VARCHAR(50) DEFAULT NULL;


-- --------------------------------------------------------------------
-- Rekap Pendaftaran Ujian Akhir, digunakan untuk persetujuan dosen
-- --------------------------------------------------------------------
DROP VIEW IF EXISTS v2_rekap_pendaftaran_ujian_akhir;
CREATE VIEW v2_rekap_pendaftaran_ujian_akhir AS
SELECT
    pua.id_event,
    e.nama AS tahap,
    pua.id_proposal,
    p.judul_proposal,
    p.nim_pengusul,
    (SELECT nama FROM v2_mahasiswa WHERE nim = p.nim_pengusul LIMIT 1) AS nama_pengusul,
    p.nim_anggota,
    (SELECT nama FROM v2_mahasiswa WHERE nim = p.nim_anggota LIMIT 1) AS nama_anggota,
    b.id_pembimbing_1,
    (SELECT nama FROM v2_dosen WHERE id = b.id_pembimbing_1 LIMIT 1) AS nama_pembimbing_1,
    b.id_pembimbing_2,
    (SELECT nama FROM v2_dosen WHERE id = b.id_pembimbing_2 LIMIT 1) AS nama_pembimbing_2,
    (SELECT kode_prodi FROM v2_mahasiswa WHERE nim = p.nim_pengusul LIMIT 1) AS kode_prodi_pengusul,
    pua.status_persetujuan_pembimbing_1,
    pua.status_persetujuan_pembimbing_2,
    pua.file_laporan_akhir,
    pua.file_presentasi,
    pua.file_draft_publikasi,
    pua.link_video_demo,
    pua.link_instalasi_aplikasi
FROM
    v2_proposal p
        INNER JOIN v2_pendaftaran_ujian_akhir pua ON p.id = pua.id_proposal
        INNER JOIN v2_event e ON e.id = pua.id_event
        INNER JOIN v2_bimbingan b ON p.nim_pengusul = b.nim_mahasiswa;

-- --------------------------------------------------------------------
-- Rekap Persetujuan Pendaftaran Ujian Akhir, ditampilkan untuk disetujui masing-masing dosen.
-- --------------------------------------------------------------------
DROP VIEW IF EXISTS v2_rekap_persetujuan_pendaftaran_ujian_akhir;
CREATE VIEW v2_rekap_persetujuan_pendaftaran_ujian_akhir AS
SELECT
    id_event,
    tahap,
    id_proposal,
    judul_proposal,
    nim_pengusul,
    nama_pengusul,
    id_pembimbing_1 AS id_pembimbing,
    nama_pembimbing_1 AS nama_pembimbing,
    status_persetujuan_pembimbing_1 AS status_persetujuan_pembimbing,
    'Pembimbing-1' AS status_pembimbing
FROM v2_rekap_pendaftaran_ujian_akhir
UNION ALL
SELECT
    id_event,
    tahap,
    id_proposal,
    judul_proposal,
    nim_pengusul,
    nama_pengusul,
    id_pembimbing_2 AS id_pembimbing,
    nama_pembimbing_2 AS nama_pembimbing,
    status_persetujuan_pembimbing_2 AS status_persetujuan_pembimbing,
    'Pembimbing-2' AS status_pembimbing
FROM v2_rekap_pendaftaran_ujian_akhir;

-- --------------------------------------------------------------------
-- Memastikan support untuk D3 (Run OK on server at 2020/06/04)
-- --------------------------------------------------------------------
-- Menjadikan id pembimbing agar bisa tidak diisi.
ALTER TABLE `v2_proposal` CHANGE `id_dosen_pembimbing_1` `id_dosen_pembimbing_1` SMALLINT(6) NULL DEFAULT NULL;
-- Kolom nim_anggota dijadikan UNIQUE agar tidak bisa 1 nim menjadi 2 anggota
ALTER TABLE v2_proposal ADD UNIQUE (nim_anggota);
-- Pindahkan kolom ID ke depan
ALTER TABLE `v2_proposal` CHANGE `id` `id` INT(11) NOT NULL AUTO_INCREMENT FIRST;
-- Jadkan nim_anggota sebagai foreign key
ALTER TABLE v2_proposal ADD CONSTRAINT fk_nim_anggota_mahasiswa FOREIGN KEY (nim_anggota) REFERENCES v2_mahasiswa (nim);

-- --------------------------------------------------------------------
-- Update syarat pendaftaran sesuai keputusan rapat online terbaru (Run OK on server at 2020/06/04)
-- --------------------------------------------------------------------
-- Mengubah nama kolom link_demo menjadi link_video_demo (Wajib diisi), tipe data menjadi TEXT
ALTER TABLE `v2_pendaftaran_ujian_akhir` CHANGE `link_demo` `link_video_demo` TEXT NOT NULL;
-- Menambahkan kolom SKLA (Wajib diisi)
ALTER TABLE v2_pendaftaran_ujian_akhir ADD COLUMN file_skla VARCHAR(255) NOT NULL AFTER id_event;
-- Menambahkan kolom link_instalasi_aplikasi (Wajib diisi)
ALTER TABLE v2_pendaftaran_ujian_akhir ADD COLUMN link_instalasi_aplikasi TEXT NOT NULL AFTER link_video_demo;

-- --------------------------------------------------------------------
-- Mem-filter pendaftar agat hanya NIM yang diconfirm saja yang bisa (Run OK on server at 2020/06/05)
-- --------------------------------------------------------------------
DROP TABLE IF EXISTS v2_nim_aktif;
CREATE TABLE v2_nim_aktif
(
    nim VARCHAR(50) PRIMARY KEY NOT NULL,
    status ENUM('aktif', 'non_aktif') DEFAULT 'aktif',
    tahun_proposal INT(4) DEFAULT 2019
);

-- --------------------------------------------------------------------
-- Modifikasi View Rekap Pendaftaran Ujian Akhir. (Run OK on server at 2020/06/13)
-- Mahasiswa yang belum ada data pembimbing agar bisa tetap ditampilkan.
-- --------------------------------------------------------------------

DROP VIEW IF EXISTS v2_rekap_pendaftaran_ujian_akhir;
CREATE VIEW v2_rekap_pendaftaran_ujian_akhir AS
SELECT
    pua.id_event,
    e.nama AS tahap,
    pua.id_proposal,
    p.judul_proposal,
    p.nim_pengusul,
    (SELECT nama FROM v2_mahasiswa WHERE nim = p.nim_pengusul LIMIT 1) AS nama_pengusul,
    p.nim_anggota,
    (SELECT nama FROM v2_mahasiswa WHERE nim = p.nim_anggota LIMIT 1) AS nama_anggota,
    b.id_pembimbing_1,
    (SELECT nama FROM v2_dosen WHERE id = b.id_pembimbing_1 LIMIT 1) AS nama_pembimbing_1,
    b.id_pembimbing_2,
    (SELECT nama FROM v2_dosen WHERE id = b.id_pembimbing_2 LIMIT 1) AS nama_pembimbing_2,
    (SELECT kode_prodi FROM v2_mahasiswa WHERE nim = p.nim_pengusul LIMIT 1) AS kode_prodi_pengusul,
    pua.status_persetujuan_pembimbing_1,
    pua.status_persetujuan_pembimbing_2,
    pua.file_laporan_akhir,
    pua.file_presentasi,
    pua.file_draft_publikasi,
    pua.link_video_demo,
    pua.link_instalasi_aplikasi
FROM
    v2_proposal p
        INNER JOIN v2_pendaftaran_ujian_akhir pua ON p.id = pua.id_proposal
        INNER JOIN v2_event e ON e.id = pua.id_event
        LEFT OUTER JOIN v2_bimbingan b ON p.nim_pengusul = b.nim_mahasiswa;



-- --------------------------------------------------------------------
-- View rekap ujian akhir (Run OK on server at 2020/06/14)
-- Untuk menampilkan data tiap judul saat ujian
-- --------------------------------------------------------------------
DROP VIEW IF EXISTS v2_rekap_ujian_akhir;
CREATE VIEW v2_rekap_ujian_akhir AS
SELECT
    u.id as nomor_ujian,
    mp.kode_prodi,
    u.id_proposal,
    p.judul_proposal,
    p.nim_pengusul,
    mp.nama AS nama_pengusul,
    p.nim_anggota,
    ma.nama AS nama_anggota,
    pua.file_presentasi,
    pua.file_laporan_akhir,
    pua.file_draft_publikasi,
    pua.link_video_demo,
    pua.link_instalasi_aplikasi
FROM
    v2_ujian u
        INNER JOIN v2_pendaftaran_ujian_akhir pua on u.id_event = pua.id_event AND u.id_proposal = pua.id_proposal
        INNER JOIN v2_proposal p ON pua.id_proposal = p.id
        INNER JOIN v2_mahasiswa mp on p.nim_pengusul = mp.nim
        LEFT OUTER JOIN v2_mahasiswa ma ON p.nim_anggota = ma.nim;



-- --------------------------------------------------------------------
-- View rekap ujian terjadwal (Run OK on server at 2020/06/14)
-- Untuk menampilkan data jadwal ujian di halaman Ujian di akun dosen.
-- --------------------------------------------------------------------
DROP VIEW IF EXISTS v2_rekap_ujian_terjadwal;
CREATE VIEW v2_rekap_ujian_terjadwal AS
SELECT
    u.id AS nomor_ujian,
    mp.kode_prodi,
    u.id_event,
    e.nama AS tahap,
    u.id_proposal,
    p.judul_proposal,
    p.nim_pengusul,
    mp.nama AS nama_pengusul,
    p.nim_anggota,
    ma.nama AS nama_anggota,
    u.id_dosen_moderator,
    dm.nama AS nama_dosen_moderator,
    u.id_dosen_penguji_1,
    dp1.nama AS nama_dosen_penguji_1,
    u.id_dosen_penguji_2,
    dp2.nama AS nama_dosen_penguji_2,
    u.id_sesi,
    s.waktu_mulai,
    s.waktu_selesai,
    u.tanggal,
    u.id_ruang,
    r.kode AS kode_ruang,
    r.nama AS nama_ruang,
    r.keterangan AS keterangan_ruang
FROM
    v2_ujian u
        INNER JOIN v2_event e on u.id_event = e.id
        INNER JOIN v2_proposal p ON u.id_proposal = p.id
        INNER JOIN v2_mahasiswa mp on p.nim_pengusul = mp.nim
        LEFT OUTER JOIN v2_mahasiswa ma on p.nim_anggota = ma.nim
        LEFT OUTER JOIN v2_dosen dm ON u.id_dosen_moderator = dm.id
        LEFT OUTER JOIN v2_dosen dp1 ON u.id_dosen_penguji_1 = dp1.id
        LEFT OUTER JOIN v2_dosen dp2 ON u.id_dosen_penguji_2 = dp2.id
        LEFT OUTER JOIN v2_sesi s ON u.id_sesi = s.id
        LEFT OUTER JOIN v2_ruang r ON u.id_ruang = r.id;

-- --------------------------------------------------------------------
-- Untuk mengakomodir D3 (Run OK on server at 2020/06/17)
-- 1 tim 2 mahasiswa, nilainya bisa beda, bisa lulus salah satu saja
-- --------------------------------------------------------------------
ALTER TABLE v2_penilaian_ujian ADD COLUMN nim VARCHAR(50) collate latin1_swedish_ci NOT NULL AFTER id_dosen;
ALTER TABLE v2_penilaian_ujian ADD CONSTRAINT FOREIGN KEY fk_nim_mahasiswa (nim) REFERENCES v2_mahasiswa (nim);

ALTER TABLE v2_berita_acara_ujian ADD COLUMN nim VARCHAR(50) collate latin1_swedish_ci NOT NULL AFTER id_ujian;
ALTER TABLE v2_berita_acara_ujian ADD CONSTRAINT FOREIGN KEY fk_nim_mahasiswa (nim) REFERENCES v2_mahasiswa (nim);



-- --------------------------------------------------------------------
-- Modifikasi view terkait ujian akhir.
-- Untuk menampilkan field informasi tambahan (Run OK on server at xxxx/xx/xx)
-- --------------------------------------------------------------------
-- Tambahkan kolom untuk memasukkan informasi tambahan untuk menyimpan credential aplikasi demo jika ada username/passwordnya
ALTER TABLE v2_pendaftaran_ujian_akhir ADD COLUMN informasi_tambahan TEXT NULL AFTER link_instalasi_aplikasi;

-- Tambahkan kolom informasi_tambahan
DROP VIEW IF EXISTS v2_rekap_pendaftaran_ujian_akhir;
CREATE VIEW v2_rekap_pendaftaran_ujian_akhir AS
SELECT
    pua.id_event,
    e.nama AS tahap,
    pua.id_proposal,
    p.judul_proposal,
    p.nim_pengusul,
    (SELECT nama FROM v2_mahasiswa WHERE nim = p.nim_pengusul LIMIT 1) AS nama_pengusul,
    p.nim_anggota,
    (SELECT nama FROM v2_mahasiswa WHERE nim = p.nim_anggota LIMIT 1) AS nama_anggota,
    b.id_pembimbing_1,
    (SELECT nama FROM v2_dosen WHERE id = b.id_pembimbing_1 LIMIT 1) AS nama_pembimbing_1,
    b.id_pembimbing_2,
    (SELECT nama FROM v2_dosen WHERE id = b.id_pembimbing_2 LIMIT 1) AS nama_pembimbing_2,
    (SELECT kode_prodi FROM v2_mahasiswa WHERE nim = p.nim_pengusul LIMIT 1) AS kode_prodi_pengusul,
    pua.status_persetujuan_pembimbing_1,
    pua.status_persetujuan_pembimbing_2,
    pua.file_laporan_akhir,
    pua.file_presentasi,
    pua.file_draft_publikasi,
    pua.link_video_demo,
    pua.link_instalasi_aplikasi,
    pua.informasi_tambahan
FROM
    v2_proposal p
        INNER JOIN v2_pendaftaran_ujian_akhir pua ON p.id = pua.id_proposal
        INNER JOIN v2_event e ON e.id = pua.id_event
        LEFT OUTER JOIN v2_bimbingan b ON p.nim_pengusul = b.nim_mahasiswa;

-- Tambahkan juga disini supaya tampil dihalaman detail ujian
DROP VIEW IF EXISTS v2_rekap_ujian_akhir;
CREATE VIEW v2_rekap_ujian_akhir AS
SELECT
    u.id as nomor_ujian,
    mp.kode_prodi,
    u.id_proposal,
    p.judul_proposal,
    p.nim_pengusul,
    mp.nama AS nama_pengusul,
    p.nim_anggota,
    ma.nama AS nama_anggota,
    pua.file_presentasi,
    pua.file_laporan_akhir,
    pua.file_draft_publikasi,
    pua.link_video_demo,
    pua.link_instalasi_aplikasi,
    pua.informasi_tambahan
FROM
    v2_ujian u
        INNER JOIN v2_pendaftaran_ujian_akhir pua on u.id_event = pua.id_event AND u.id_proposal = pua.id_proposal
        INNER JOIN v2_proposal p ON pua.id_proposal = p.id
        INNER JOIN v2_mahasiswa mp on p.nim_pengusul = mp.nim
        LEFT OUTER JOIN v2_mahasiswa ma ON p.nim_anggota = ma.nim;


-- Modifikasi table dosen, tambahkan 3 kolom baru.
ALTER TABLE v2_dosen ADD COLUMN status VARCHAR(10);
ALTER TABLE v2_dosen ADD COLUMN golongan VARCHAR(10);
ALTER TABLE v2_dosen ADD COLUMN jabatan_fungsional VARCHAR(10);
ALTER TABLE v2_dosen ADD COLUMN kode VARCHAR(10);
ALTER TABLE v2_dosen ADD COLUMN aktif_menguji ENUM('ya', 'tidak');