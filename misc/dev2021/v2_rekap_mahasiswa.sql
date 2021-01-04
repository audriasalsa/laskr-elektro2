ALTER VIEW v2_rekap_mahasiswa AS
SELECT m.nim                    AS nim,
       m.nama                   AS nama,
       m.email                  AS email,
       m.nomor_ponsel           AS nomor_ponsel,
       m.nomor_ponsel_orang_tua AS nomor_ponsel_orang_tua,
       m.kode_prodi             AS kode_prodi,
       p.nama                   AS prodi,
       m.kelas                  AS kelas,
       na.tahun_proposal        AS tahun_proposal
FROM
     v2_mahasiswa m
         INNER JOIN v2_prodi p on m.kode_prodi = p.kode
         INNER JOIN v2_nim_aktif na ON m.nim = na.nim;


-- Mengembalikan data nim_aktif yang ketimpa oleh panitia D3
/*
DESC v2_nim_aktif;
SELECT * FROM v2_nim_aktif;
INSERT INTO v2_nim_aktif (SELECT nim, 'non_aktif', '2019' FROM v2_mahasiswa WHERE nim NOT IN (SELECT nim FROM v2_nim_aktif));
SELECT * FROM v2_nim_aktif;
*/