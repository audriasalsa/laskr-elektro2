-- Tabel penilaian pembimbing
DROP TABLE IF EXISTS v2_penilaian_pembimbing;
CREATE TABLE v2_penilaian_pembimbing
(
    id_event          INTEGER,
    id_proposal       INTEGER,
    nim               VARCHAR(50) COLLATE latin1_swedish_ci,
    id_pembimbing     SMALLINT,
    status_pembimbing ENUM ('pembimbing_1', 'pembimbing_2'),
    nilai_1           FLOAT,
    nilai_2           FLOAT,
    nilai_3           FLOAT,
    nilai_4           FLOAt,
    nilai_5           FLOAT,
    nilai_6           FLOAT,
    CONSTRAINT PRIMARY KEY (id_event, id_pembimbing, nim)
);

ALTER TABLE v2_penilaian_pembimbing ADD CONSTRAINT fk_penilaian_pembimbing_id_event FOREIGN KEY (id_event) REFERENCES v2_event(id);
ALTER TABLE v2_penilaian_pembimbing ADD CONSTRAINT fk_penilaian_pembimbing_id_proposal FOREIGN KEY (id_proposal) REFERENCES v2_proposal(id);
ALTER TABLE v2_penilaian_pembimbing ADD CONSTRAINT fk_penilaian_pembimbing_nim FOREIGN KEY (nim) REFERENCES v2_mahasiswa(nim);
ALTER TABLE v2_penilaian_pembimbing ADD CONSTRAINT fk_penilaian_pembimbing_id_pembimbing FOREIGN KEY (id_pembimbing) REFERENCES v2_dosen(id);
