-- USE laskr;
USE db_tugasakhir;

CREATE VIEW v2_rekap_mahasiswa_aktif AS
SELECT rm.*
FROM
    v2_rekap_mahasiswa rm
WHERE
    rm.tahun_proposal IN (SELECT tahun_proposal_sekarang FROM v2_pengaturan WHERE id = 1) OR
    rm.nim NOT IN (SELECT nim FROM v2_rekap_yudisium_nilai_akhir WHERE status_kelulusan LIKE 'LULUS%');