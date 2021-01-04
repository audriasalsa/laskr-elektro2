DROP VIEW IF EXISTS v2_rekap_ujian_terjadwal;
CREATE VIEW v2_rekap_ujian_terjadwal AS
SELECT
	u.id                 AS nomor_ujian,
    mp.kode_prodi        AS kode_prodi,
    u.id_event           AS id_event,
    e.kategori           AS jenis_ujian,
    e.periode_proposal   AS periode_proposal,
    e.nama               AS tahap,
    u.id_proposal        AS id_proposal,
    p.judul_proposal     AS judul_proposal,
    p.nim_pengusul       AS nim_pengusul,
    mp.nama              AS nama_pengusul,
    p.nim_anggota        AS nim_anggota,
    ma.nama              AS nama_anggota,
    u.id_dosen_moderator AS id_dosen_moderator,
    dm.nama              AS nama_dosen_moderator,
    u.id_dosen_penguji_1 AS id_dosen_penguji_1,
    dp1.nama             AS nama_dosen_penguji_1,
    u.id_dosen_penguji_2 AS id_dosen_penguji_2,
    dp2.nama             AS nama_dosen_penguji_2,
    u.id_sesi            AS id_sesi,
    s.waktu_mulai        AS waktu_mulai,
    s.waktu_selesai      AS waktu_selesai,
    u.tanggal            AS tanggal,
    u.id_ruang           AS id_ruang,
    r.kode               AS kode_ruang,
    r.nama               AS nama_ruang,
    r.keterangan         AS keterangan_ruang
from
    v2_ujian u
        INNER JOIN v2_event e ON u.id_event = e.id
        INNER JOIN v2_proposal p ON u.id_proposal = p.id
        INNER JOIN v2_mahasiswa mp ON p.nim_pengusul = mp.nim
        LEFT OUTER JOIN v2_mahasiswa ma ON p.nim_anggota = ma.nim
        LEFT OUTER JOIN v2_dosen dm ON u.id_dosen_moderator = dm.id
        LEFT OUTER JOIN v2_dosen dp1 ON u.id_dosen_penguji_1 = dp1.id
        LEFT OUTER JOIN v2_dosen dp2 ON u.id_dosen_penguji_2 = dp2.id
        LEFT OUTER JOIN v2_sesi s ON u.id_sesi = s.id
        LEFT OUTER JOIN v2_ruang r ON u.id_ruang = r.id;


SELECT * FROM v2_rekap_ujian_terjadwal;