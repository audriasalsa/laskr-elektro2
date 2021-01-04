-- ------------------------------------------------------------------------
-- v2_rekap_bimbingan
-- Untuk menampilkan nama-nama yang ada di tabel v2_bimbingan
-- ------------------------------------------------------------------------
DROP VIEW IF EXISTS v2_rekap_bimbingan;
CREATE VIEW v2_rekap_bimbingan AS
SELECT
    p.id AS id_proposal,
    p.judul_proposal,
    m.nim,
    m.nama,
    m.kode_prodi,
    dp1.id AS id_dosen_pembimbing_1,
    dp1.nama AS nama_dosen_pembimbing_1,
    dp2.id AS id_dosen_pembimbing_2,
    dp2.nama AS nama_dosen_pembimbing_2
FROM
    v2_proposal p
        INNER JOIN v2_mahasiswa m ON m.nim = p.nim_pengusul
        INNER JOIN v2_bimbingan b ON b.nim_mahasiswa = m.nim
        INNER JOIN v2_dosen dp1 on dp1.id = b.id_pembimbing_1
        INNER JOIN v2_dosen dp2 on dp2.id = b.id_pembimbing_2
UNION ALL
SELECT
    p.id AS id_proposal,
    p.judul_proposal,
    m.nim,
    m.nama,
    m.kode_prodi,
    dp1.id AS id_dosen_pembimbing_1,
    dp1.nama AS nama_dosen_pembimbing_1,
    dp2.id AS id_dosen_pembimbing_2,
    dp2.nama AS nama_dosen_pembimbing_2
FROM
    v2_proposal p
        INNER JOIN v2_mahasiswa m ON m.nim = p.nim_anggota
        INNER JOIN v2_bimbingan b ON b.nim_mahasiswa = m.nim
        INNER JOIN v2_dosen dp1 on dp1.id = b.id_pembimbing_1
        INNER JOIN v2_dosen dp2 on dp2.id = b.id_pembimbing_2;



-- ------------------------------------------------------------------------
-- v2_rekap_yudisium
-- Untuk menampilkan nilai-nilai penguji dan pembimbing
-- ------------------------------------------------------------------------
DROP VIEW IF EXISTS v2_rekap_yudisium;
CREATE VIEW v2_rekap_yudisium AS
SELECT
    m.kode_prodi,
    m.nim,
    m.nama,
    p.id AS nomor_la_skripsi,
    rut.nomor_ujian,
    p.judul_proposal,
    rut.tahap AS tahap_ujian,
    rb.nama_dosen_pembimbing_1 AS pembimbing_1,
    COALESCE(pp1.nilai_1, 80.115) AS n1_pembimbing_1,
    COALESCE(pp1.nilai_2, 80.115) AS n2_pembimbing_1,
    COALESCE(pp1.nilai_3, 80.115) AS n3_pembimbing_1,
    COALESCE(pp1.nilai_4, 80.115) AS n4_pembimbing_1,
    COALESCE(pp1.nilai_5, 80.115) AS n5_pembimbing_1,
    COALESCE(pp1.nilai_6, 80.115) AS n6_pembimbing_1,
    rb.nama_dosen_pembimbing_2 AS pembimbing_2,
    COALESCE(pp2.nilai_1, 80.115) AS n1_pembimbing_2,
    COALESCE(pp2.nilai_2, 80.115) AS n2_pembimbing_2,
    COALESCE(pp2.nilai_3, 80.115) AS n3_pembimbing_2,
    COALESCE(pp2.nilai_4, 80.115) AS n4_pembimbing_2,
    COALESCE(pp2.nilai_5, 80.115) AS n5_pembimbing_2,
    COALESCE(pp2.nilai_6, 80.115) AS n6_pembimbing_2,
    (COALESCE(pp1.nilai_1, 80.115) + COALESCE(pp1.nilai_2, 80.115) + COALESCE(pp1.nilai_3, 80.115) + COALESCE(pp1.nilai_4, 80.115) + COALESCE(pp1.nilai_5, 80.115) + COALESCE(pp1.nilai_6, 80.115) +
     COALESCE(pp2.nilai_1, 80.115) + COALESCE(pp2.nilai_2, 80.115) + COALESCE(pp2.nilai_3, 80.115) + COALESCE(pp2.nilai_4, 80.115) + COALESCE(pp2.nilai_5, 80.115) + COALESCE(pp2.nilai_6, 80.115)) / 12 AS rerata_nilai_pembimbing,
    (SELECT nama FROM v2_dosen WHERE id = pu1.id_dosen LIMIT 1) AS penguji_1,
    COALESCE(pu1.nilai_1, 80.115) AS n1_penguji_1,
    COALESCE(pu1.nilai_2, 80.115) AS n2_penguji_1,
    COALESCE(pu1.nilai_3, 80.115) AS n3_penguji_1,
    (SELECT nama FROM v2_dosen WHERE id = pu2.id_dosen LIMIT 1) AS penguji_2,
    COALESCE(pu2.nilai_1, 80.115) AS n1_penguji_2,
    COALESCE(pu2.nilai_2, 80.115) AS n2_penguji_2,
    COALESCE(pu2.nilai_3, 80.115) AS n3_penguji_2,
    ((COALESCE(pu1.nilai_1, 80.115) * 0.30) + (COALESCE(pu1.nilai_2, 80.115) * 0.25) + (COALESCE(pu1.nilai_3, 80.115) * 0.45) +
     (COALESCE(pu2.nilai_1, 80.115) * 0.30) + (COALESCE(pu2.nilai_2, 80.115) * 0.25) + (COALESCE(pu2.nilai_3, 80.115) * 0.45)) / 2 AS rerata_nilai_penguji,
    COALESCE(pu1.kesimpulan, pu2.kesimpulan, 'BELUM_ADA_NILAI') AS status_kelulusan
FROM
    v2_mahasiswa m
        INNER JOIN v2_proposal p ON p.nim_pengusul = m.nim
        INNER JOIN v2_rekap_bimbingan rb ON rb.nim = p.nim_pengusul
        INNER JOIN v2_rekap_ujian_terjadwal rut ON rut.id_proposal = p.id
        LEFT OUTER JOIN v2_penilaian_pembimbing pp1 ON pp1.nim = m.nim AND pp1.status_pembimbing = 'pembimbing_1' AND pp1.id_event = rut.id_event
        LEFT OUTER JOIN v2_penilaian_pembimbing pp2 ON pp2.nim = m.nim AND pp2.status_pembimbing = 'pembimbing_2' AND pp2.id_event = rut.id_event
        LEFT OUTER JOIN v2_penilaian_ujian pu1 ON pu1.nim = m.nim AND pu1.id_ujian = rut.nomor_ujian AND pu1.peran = 'PENGUJI_1'
        LEFT OUTER JOIN v2_penilaian_ujian pu2 ON pu2.nim = m.nim AND pu2.id_ujian = rut.nomor_ujian AND pu2.peran = 'PENGUJI_2'
UNION ALL
SELECT
    m.kode_prodi,
    m.nim,
    m.nama,
    p.id AS nomor_la_skripsi,
    rut.nomor_ujian,
    p.judul_proposal,
    rut.tahap AS tahap_ujian,
    rb.nama_dosen_pembimbing_1 AS pembimbing_1,
    COALESCE(pp1.nilai_1, 80.115) AS n1_pembimbing_1,
    COALESCE(pp1.nilai_2, 80.115) AS n2_pembimbing_1,
    COALESCE(pp1.nilai_3, 80.115) AS n3_pembimbing_1,
    COALESCE(pp1.nilai_4, 80.115) AS n4_pembimbing_1,
    COALESCE(pp1.nilai_5, 80.115) AS n5_pembimbing_1,
    COALESCE(pp1.nilai_6, 80.115) AS n6_pembimbing_1,
    rb.nama_dosen_pembimbing_2 AS pembimbing_2,
    COALESCE(pp2.nilai_1, 80.115) AS n1_pembimbing_2,
    COALESCE(pp2.nilai_2, 80.115) AS n2_pembimbing_2,
    COALESCE(pp2.nilai_3, 80.115) AS n3_pembimbing_2,
    COALESCE(pp2.nilai_4, 80.115) AS n4_pembimbing_2,
    COALESCE(pp2.nilai_5, 80.115) AS n5_pembimbing_2,
    COALESCE(pp2.nilai_6, 80.115) AS n6_pembimbing_2,
    (COALESCE(pp1.nilai_1, 80.115) + COALESCE(pp1.nilai_2, 80.115) + COALESCE(pp1.nilai_3, 80.115) + COALESCE(pp1.nilai_4, 80.115) + COALESCE(pp1.nilai_5, 80.115) + COALESCE(pp1.nilai_6, 80.115) +
     COALESCE(pp2.nilai_1, 80.115) + COALESCE(pp2.nilai_2, 80.115) + COALESCE(pp2.nilai_3, 80.115) + COALESCE(pp2.nilai_4, 80.115) + COALESCE(pp2.nilai_5, 80.115) + COALESCE(pp2.nilai_6, 80.115)) / 12 AS rerata_nilai_pembimbing,
    (SELECT nama FROM v2_dosen WHERE id = pu1.id_dosen LIMIT 1) AS penguji_1,
    COALESCE(pu1.nilai_1, 80.115) AS n1_penguji_1,
    COALESCE(pu1.nilai_2, 80.115) AS n2_penguji_1,
    COALESCE(pu1.nilai_3, 80.115) AS n3_penguji_1,
    (SELECT nama FROM v2_dosen WHERE id = pu2.id_dosen LIMIT 1) AS penguji_2,
    COALESCE(pu2.nilai_1, 80.115) AS n1_penguji_2,
    COALESCE(pu2.nilai_2, 80.115) AS n2_penguji_2,
    COALESCE(pu2.nilai_3, 80.115) AS n3_penguji_2,
    ((COALESCE(pu1.nilai_1, 80.115) * 0.30) + (COALESCE(pu1.nilai_2, 80.115) * 0.25) + (COALESCE(pu1.nilai_3, 80.115) * 0.45) +
     (COALESCE(pu2.nilai_1, 80.115) * 0.30) + (COALESCE(pu2.nilai_2, 80.115) * 0.25) + (COALESCE(pu2.nilai_3, 80.115) * 0.45)) / 2 AS rerata_nilai_penguji,
    COALESCE(pu1.kesimpulan, pu2.kesimpulan, 'BELUM_ADA_NILAI') AS status_kelulusan
FROM
    v2_mahasiswa m
        INNER JOIN v2_proposal p ON p.nim_anggota = m.nim
        INNER JOIN v2_rekap_bimbingan rb ON rb.nim = p.nim_anggota
        INNER JOIN v2_rekap_ujian_terjadwal rut ON rut.id_proposal = p.id
        LEFT OUTER JOIN v2_penilaian_pembimbing pp1 ON pp1.nim = m.nim AND pp1.status_pembimbing = 'pembimbing_1' AND pp1.id_event = rut.id_event
        LEFT OUTER JOIN v2_penilaian_pembimbing pp2 ON pp2.nim = m.nim AND pp2.status_pembimbing = 'pembimbing_2' AND pp2.id_event = rut.id_event
        LEFT OUTER JOIN v2_penilaian_ujian pu1 ON pu1.nim = m.nim AND pu1.id_ujian = rut.nomor_ujian AND pu1.peran = 'PENGUJI_1'
        LEFT OUTER JOIN v2_penilaian_ujian pu2 ON pu2.nim = m.nim AND pu2.id_ujian = rut.nomor_ujian AND pu2.peran = 'PENGUJI_2';




-- ------------------------------------------------------------------------
-- v2_rekap_status_revisi_ujian_akhir
-- Untuk menampilkan secara succinct status revisi dari tiap-tiap mahasiswa
-- ------------------------------------------------------------------------
DROP VIEW IF EXISTS v2_rekap_status_revisi_ujian_akhir;
CREATE VIEW v2_rekap_status_revisi_ujian_akhir AS
SELECT
    rua.id_ujian,
    rua.id_proposal,
    m.nim,
    rua.judul_final,
    rua.status_persetujuan_penguji_1 AS status_revisi_penguji_1,
    rua.status_persetujuan_penguji_2 AS status_revisi_penguji_2,
    IF(rua.status_persetujuan_penguji_1 = 'disetujui' AND rua.status_persetujuan_penguji_2 = 'disetujui', 'selesai', 'belum_selesai') AS status_revisi_akhir
FROM
    v2_revisi_ujian_akhir rua
    INNER JOIN v2_proposal p ON rua.id_proposal = p.id
    INNER JOIN v2_mahasiswa m ON m.nim = p.nim_pengusul
UNION ALL
SELECT
    rua.id_ujian,
    rua.id_proposal,
    m.nim,
    rua.judul_final,
    rua.status_persetujuan_penguji_1 AS status_revisi_penguji_1,
    rua.status_persetujuan_penguji_2 AS status_revisi_penguji_2,
    IF(rua.status_persetujuan_penguji_1 = 'disetujui' AND rua.status_persetujuan_penguji_2 = 'disetujui', 'selesai', 'belum_selesai') AS status_revisi_akhir
FROM
    v2_revisi_ujian_akhir rua
    INNER JOIN v2_proposal p ON rua.id_proposal = p.id
    INNER JOIN v2_mahasiswa m ON m.nim = p.nim_anggota;

-- ------------------------------------------------------------------------
-- v2_rekap_yudisium_nilai_akhir
-- Untuk menampilkan nilai-nilai huruf akhir dari v2_rekap_yudisium
-- ------------------------------------------------------------------------
DROP VIEW IF EXISTS v2_rekap_yudisium_nilai_akhir;
CREATE VIEW v2_rekap_yudisium_nilai_akhir AS
SELECT
    ry.*,
    (0.6 * ry.rerata_nilai_pembimbing + 0.4 * ry.rerata_nilai_penguji) AS nilai_angka_akhir,
    CASE
        WHEN (0.6 * ry.rerata_nilai_pembimbing + 0.4 * ry.rerata_nilai_penguji) > 80 THEN 'A'
        WHEN (0.6 * ry.rerata_nilai_pembimbing + 0.4 * ry.rerata_nilai_penguji) > 73 THEN 'B+'
        WHEN (0.6 * ry.rerata_nilai_pembimbing + 0.4 * ry.rerata_nilai_penguji) > 65 THEN 'B'
        WHEN (0.6 * ry.rerata_nilai_pembimbing + 0.4 * ry.rerata_nilai_penguji) > 60 THEN 'C+'
        WHEN (0.6 * ry.rerata_nilai_pembimbing + 0.4 * ry.rerata_nilai_penguji) > 50 THEN 'C'
        WHEN (0.6 * ry.rerata_nilai_pembimbing + 0.4 * ry.rerata_nilai_penguji) > 39 THEN 'D'
        ELSE 'E'
    END AS nilai_huruf_akhir,
    rsrua.judul_final,
    COALESCE(rsrua.status_revisi_akhir, 'belum_selesai') AS status_revisi
FROM
    v2_rekap_yudisium ry
    LEFT OUTER JOIN v2_rekap_status_revisi_ujian_akhir rsrua on ry.nim = rsrua.nim AND rsrua.id_ujian = ry.nomor_ujian;