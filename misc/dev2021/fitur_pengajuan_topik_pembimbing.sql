-- Buat tabel untuk menyimpan global setting.
-- Sementara ini hanya pengaturan tahun aktif dulu.
DROP TABLE IF EXISTS v2_pengaturan;
CREATE TABLE v2_pengaturan
(
    id INT PRIMARY KEY AUTO_INCREMENT,
    tahun_proposal_sekarang INT
);
INSERT INTO v2_pengaturan (tahun_proposal_sekarang) VALUES (2020);
INSERT INTO v2_nim_aktif VALUES(9999, 'aktif', 2020);

SELECT * FROM v2_pengaturan;


-- Tabel untuk menyimpan pengajuan topik baik oleh dosen maupun mahasiswa
DROP TABLE IF EXISTS v2_topik;
-- TODO: ID di tabel v2_grup_riset entah siapa yang bikin, bukan primary key!
CREATE TABLE v2_topik
(
    id INTEGER PRIMARY KEY AUTO_INCREMENT,
    kode_prodi CHAR(5) COLLATE latin1_swedish_ci,
    judul VARCHAR(255) NOT NULL UNIQUE,
    deskripsi TEXT,
    kode_grup_riset VARCHAR(50) NOT NULL COLLATE latin1_swedish_ci,
    jenis ENUM('penelitian', 'pengembangan'),
    id_dosen_pengusul SMALLINT,
    nim_mahasiswa_pengusul VARCHAR(50) COLLATE latin1_swedish_ci,
    status ENUM('bebas', 'diklaim') DEFAULT 'bebas',
    CONSTRAINT fk_topik_kode_prodi FOREIGN KEY (kode_prodi) REFERENCES v2_prodi (kode),
    CONSTRAINT fk_topik_kode_grup_riset FOREIGN KEY (kode_grup_riset) REFERENCES v2_grup_riset (kode),
    CONSTRAINT fk_topik_id_dosen_pengusul FOREIGN KEY (id_dosen_pengusul) REFERENCES v2_dosen (id),
    CONSTRAINT fk_topik_id_mahasiswa_pengusul FOREIGN KEY (nim_mahasiswa_pengusul) REFERENCES v2_mahasiswa (nim)
);

SELECT * FROM v2_topik;


-- View untuk menampilkan rekap topik
DROP VIEW IF EXISTS v2_rekap_topik;

CREATE VIEW v2_rekap_topik AS
SELECT
    t.id,
    t.judul,
    t.deskripsi,
    p.nama AS prodi,
    gr.nama AS grup_riset,
    t.jenis AS jenis_pengerjaan,
    IF(d.nama IS NOT NULL, 'dosen', 'mahasiswa') AS jenis_pengusul,
    t.id_dosen_pengusul,
    d.nama AS dosen_pengusul,
    t.nim_mahasiswa_pengusul,
    m.nama AS mahasiswa_pengusul,
    t.status
FROM
    v2_topik t
        INNER JOIN v2_prodi p on t.kode_prodi = p.kode
        INNER JOIN v2_grup_riset gr on t.kode_grup_riset = gr.kode
        LEFT OUTER JOIN v2_dosen d on t.id_dosen_pengusul = d.id
        LEFT OUTER JOIN v2_mahasiswa m on t.nim_mahasiswa_pengusul = m.nim;


DROP TABlE IF EXISTS v2_pengajuan_pembimbing;
CREATE TABLE v2_pengajuan_pembimbing
(
    nim_pengusul VARCHAR(50) COLLATE latin1_swedish_ci NOT NULL,
    id_topik INTEGER NOT NULL,
    nim_anggota VARCHAR(50) COLLATE latin1_swedish_ci,
    id_pembimbing_utama SMALLINT NOT NULL,
    status ENUM('diajukan', 'ditolak', 'disetujui') DEFAULT 'diajukan',
    CONSTRAINT fk_pengajuan_pembimbing_nim_pengusul FOREIGN KEY (nim_pengusul) REFERENCES v2_mahasiswa (nim),
    CONSTRAINT fk_pengajuan_pembimbing_nim_anggota FOREIGN KEY (nim_anggota) REFERENCES v2_mahasiswa (nim),
    CONSTRAINT fk_pengajuan_pembimbing_id_topik FOREIGN KEY (id_topik) REFERENCES v2_topik (id),
    CONSTRAINT fk_pengajuan_pembimbing_id_pembimbing_utama FOREIGN KEY (id_pembimbing_utama) REFERENCES v2_dosen (id),
    PRIMARY KEY (nim_pengusul, id_topik, id_pembimbing_utama) -- Tidak boleh mengajukan ke dosen yang sama lebih dari sekali
);

SELECT * FROM v2_pengajuan_pembimbing;


-- View rekap pengajuan pembimbing
DROP VIEW IF EXISTS v2_rekap_pengajuan_pembimbing;
CREATE VIEW v2_rekap_pengajuan_pembimbing AS
SELECT
    pp.nim_pengusul,
    mp.nama AS nama_pengusul,
    pp.nim_anggota,
    ma.nama AS nama_anggota,
    pp.id_topik,
    rt.prodi AS prodi_usulan_topik,
    mp.prodi AS prodi_mahasiswa,
    rt.judul,
    rt.deskripsi,
    rt.jenis_pengusul,
    pp.id_pembimbing_utama,
    d.nama AS nama_pembimbing_utama,
    pp.status
FROM
    v2_pengajuan_pembimbing pp
        INNER JOIN v2_rekap_mahasiswa mp ON pp.nim_pengusul = mp.nim
        LEFT OUTER JOIN v2_mahasiswa ma ON pp.nim_anggota = ma.nim
        INNER JOIN v2_rekap_topik rt ON pp.id_topik = rt.id
        INNER JOIN v2_dosen d ON pp.id_pembimbing_utama = d.id;

SELECT * FROM v2_rekap_pengajuan_pembimbing;

-- Penambahan kelas di tabel mahasiswa
ALTER TABLE v2_mahasiswa ADD COLUMN kelas ENUM ('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H') AFTER kode_prodi;

-- Penambahan jenis pengusul di tabel usulan topik
ALTER TABLE v2_topik ADD COLUMN jenis_pengusul ENUM ('mahasiswa', 'grup_riset', 'dosen') DEFAULT 'mahasiswa' AFTER jenis;
-- -- Edit view rekap topik
ALTER VIEW v2_rekap_topik AS
SELECT
    t.id,
    t.judul,
    t.deskripsi,
    p.nama AS prodi,
    gr.nama AS grup_riset,
    t.jenis AS jenis_pengerjaan,
    t.jenis_pengusul,
    t.id_dosen_pengusul,
    d.nama AS dosen_pengusul,
    t.nim_mahasiswa_pengusul,
    m.nama AS mahasiswa_pengusul,
    t.status
FROM
    v2_topik t
        INNER JOIN v2_prodi p on t.kode_prodi = p.kode
        INNER JOIN v2_grup_riset gr on t.kode_grup_riset = gr.kode
        LEFT OUTER JOIN v2_dosen d on t.id_dosen_pengusul = d.id
        LEFT OUTER JOIN v2_mahasiswa m on t.nim_mahasiswa_pengusul = m.nim;

-- Link proposal with topic
ALTER TABLE v2_proposal ADD COLUMN id_topik INTEGER AFTER id;
ALTER TABLE v2_proposal ADD CONSTRAINT fk_proposal_id_topik FOREIGN KEY (id_topik) REFERENCES v2_pengajuan_pembimbing (id_topik);

-- Tambahkan enum 'magang_industri'
ALTER TABLE v2_topik MODIFY COLUMN jenis_pengusul ENUM ('mahasiswa', 'grup_riset', 'magang_industri', 'dosen') DEFAULT 'mahasiswa' AFTER jenis;

-- Memblock dosen yang tidak bisa membimbing tahun ini.
ALTER TABLE v2_dosen ADD COLUMN aktif_membimbing ENUM ('ya', 'tidak') DEFAULT 'ya' AFTER aktif_menguji;
UPDATE v2_dosen SET aktif_menguji = 'ya' WHERE aktif_menguji IS NULL;

SELECT * FROM laskr.v2_dosen;