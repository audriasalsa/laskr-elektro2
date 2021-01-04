CREATE VIEW v2_rekap_pengajuan_pembimbing_belum_disetujui AS
SELECT * FROM v2_rekap_pengajuan_pembimbing WHERE nim_pengusul NOT IN (
    SELECT DISTINCT nim_pengusul
    FROM v2_pengajuan_pembimbing
    WHERE status = 'disetujui'
    UNION ALL
    SELECT DISTINCT nim_anggota
    FROM v2_pengajuan_pembimbing
    WHERE status = 'disetujui'
      AND nim_anggota IS NOT NULL
);