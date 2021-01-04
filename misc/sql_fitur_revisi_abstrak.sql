-- ---------------------------------------------------------------------------
-- Fitur verifikasi abstrak dan tata tulis
-- ---------------------------------------------------------------------------
DROP TABLE IF EXISTS v2_verifikasi_abstrak;
CREATE TABLE v2_verifikasi_abstrak
(
    id_proposal INTEGER PRIMARY KEY,
    id_dosen_verifikator SMALLINT(6) NOT NULL,
    file_abstrak VARCHAR(255) NOT NULL COLLATE latin1_swedish_ci,
    status_verifikasi ENUM ('diajukan', 'disetujui') DEFAULT 'diajukan'
);

ALTER TABLE v2_verifikasi_abstrak ADD CONSTRAINT fk_verifikasi_abstrak_id_proposal FOREIGN KEY (id_proposal) REFERENCES v2_proposal(id);
ALTER TABLE v2_verifikasi_abstrak ADD CONSTRAINT fk_verifikasi_abstrak_id_dosen_verifikator FOREIGN KEY (id_dosen_verifikator) REFERENCES v2_dosen(id);
ALTER TABLE v2_verifikasi_abstrak ADD CONSTRAINT fk_verifikasi_abstrak_file_abstrak FOREIGN KEY (file_abstrak) REFERENCES v2_uploaded_file(stored_name);

--
-- Mengakomodir dosen yang menjadi verifikator abstrak
--
DROP TABLE IF EXISTS v2_peran_khusus;
CREATE TABLE v2_peran_khusus
(
    kode VARCHAR(50) PRIMARY KEY,
    nama VARCHAR(255) NOT NULL ,
    deskripsi TEXT
);

INSERT INTO v2_peran_khusus VALUES ('panitia_d3', 'Panitia D3', 'Panitia yang menangani proses LA.');
INSERT INTO v2_peran_khusus VALUES ('panitia_d4', 'Panitia D4', 'Panitia yang menangani proses Skripsi.');
INSERT INTO v2_peran_khusus VALUES ('surveyor_progres', 'Surveyor Progres', 'Dosen yang bertugas menghubungi mahasiswa dan/atau orang tua mereka untuk mengetahui proses pengerjaan LA/Skripsi');
INSERT INTO v2_peran_khusus VALUES ('verifikator_abstrak', 'Verifikator Abstrak', 'Dosen bahasa inggris yang bertugas melaksanakan proses bimbingan abstrak.');
INSERT INTO v2_peran_khusus VALUES ('verifikator_tata_tulis', 'Verifikator Tata Tulis', 'Dosen yang bertugas melaksanakan proses bimbingan penulisan laporan.');

DROP TABLE IF EXISTS v2_peran_khusus_dosen;
CREATE TABLE v2_peran_khusus_dosen
(
    id_dosen SMALLINT,
    kode_peran_khusus VARCHAR(50),
    PRIMARY KEY (id_dosen, kode_peran_khusus),
    CONSTRAINT fk_peran_khusus_dosen_id_dosen FOREIGN KEY (id_dosen) REFERENCES v2_dosen(id),
    CONSTRAINT fk_peran_khusus_kode_peran_khusus FOREIGN KEY (kode_peran_khusus) REFERENCES v2_peran_khusus(kode)
);

INSERT INTO v2_dosen
    (nama, aktif_menguji)
VALUES
    -- ('Atiqah Nurul Asri, S.Pd, M.Pd', 'tidak'),
    ('Faiz Usbah Mubarok, S.Pd, M.Pd', 'tidak'),
    ('Farida Ulfa, S.Pd, M.Pd', 'tidak'),
    ('Satrio Binusa Suryadi, SS, M.Pd', 'tidak');

INSERT INTO v2_credential (username, password, access_type, id_dosen)
VALUES
    -- ('atiqah', 'atiqah', 'dosen', (SELECT id FROM v2_dosen WHERE nama LIKE '%Atiqah%' LIMIT 1)),
    ('faiz', 'faiz', 'dosen', (SELECT id FROM v2_dosen WHERE nama LIKE '%Faiz Usbah%' LIMIT 1)),
    ('farida', 'farida', 'dosen', (SELECT id FROM v2_dosen WHERE nama LIKE '%Farida Ulfa%' LIMIT 1)),
    ('satrio', 'satrio', 'dosen', (SELECT id FROM v2_dosen WHERE nama LIKE '%Satrio Binusa%' LIMIT 1));

INSERT INTO v2_peran_khusus_dosen VALUES
    -- ((SELECT id FROM v2_dosen WHERE nama LIKE '%Atiqah%' LIMIT 1), 'verifikator_abstrak'),
    ((SELECT id FROM v2_dosen WHERE nama LIKE '%Faiz Usbah%' LIMIT 1), 'verifikator_abstrak'),
    ((SELECT id FROM v2_dosen WHERE nama LIKE '%Farida Ulfa%' LIMIT 1), 'verifikator_abstrak'),
    ((SELECT id FROM v2_dosen WHERE nama LIKE '%Satrio Binusa%' LIMIT 1), 'verifikator_abstrak');

DROP VIEW IF EXISTS v2_rekap_dosen_verifikator_abstrak;
CREATE VIEW v2_rekap_dosen_verifikator_abstrak AS
SELECT
    d.id AS id_dosen_verifikator,
    d.nama,
    pkd.kode_peran_khusus,
    pk.nama AS nama_peran_khusus,
    pk.deskripsi
FROM
    v2_dosen d
        INNER JOIN v2_peran_khusus_dosen pkd ON pkd.id_dosen = d.id AND pkd.kode_peran_khusus = 'verifikator_abstrak'
        INNER JOIN v2_peran_khusus pk ON pk.kode = pkd.kode_peran_khusus;


-- ----
DROP VIEW IF EXISTS v2_rekap_verifikasi_abstrak;
CREATE VIEW v2_rekap_verifikasi_abstrak AS
SELECT
    va.id_proposal,
    mp.kode_prodi as prodi,
    mp.nama AS nama_pengusul,
    ma.nama AS nama_anggota,
    va.id_dosen_verifikator,
    d.nama AS nama_dosen_verifikator,
    va.file_abstrak,
    va.status_verifikasi
FROM
    v2_verifikasi_abstrak va
        INNER JOIN v2_dosen d ON d.id = va.id_dosen_verifikator
        INNER JOIN v2_proposal p ON p.id = va.id_proposal
        INNER JOIN v2_mahasiswa mp ON mp.nim = p.nim_pengusul
        LEFT OUTER JOIN v2_mahasiswa ma ON ma.nim = p.nim_anggota;