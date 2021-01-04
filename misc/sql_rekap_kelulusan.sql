CREATE VIEW v2_rekap_kelulusan_ujian
AS
SELECT
    rut.id_event,
    e.kategori,
    e.nama AS tahap,
    rut.nomor_ujian,
    rku.nim,
    rku.nama,
    rut.id_proposal,
    rku.keputusan_penguji_1,
    rku.keputusan_penguji_2,
    bau.waktu_ttd_mahasiswa AS waktu_lulus,
    rua.judul_final,
    rua.status_persetujuan_penguji_1 AS status_revisi_penguji_1,
    rua.status_persetujuan_penguji_2 AS status_revisi_penguji_2
FROM
    v2_rekap_ujian_terjadwal rut
        INNER JOIN v2_event e ON rut.id_event = e.id
        INNER JOIN v2_rekap_keputusan_ujian rku on rku.nomor_ujian = rut.nomor_ujian
        LEFT OUTER JOIN v2_berita_acara_ujian bau ON bau.id_ujian = rut.nomor_ujian AND bau.nim = rku.nim
        LEFT OUTER JOIN v2_revisi_ujian_akhir rua ON rut.nomor_ujian = rua.id_ujian
WHERE
    rku.keputusan_penguji_1 LIKE 'LULUS%' AND rku.keputusan_penguji_2 LIKE 'LULUS%';