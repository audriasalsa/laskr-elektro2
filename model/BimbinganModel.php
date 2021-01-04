<?php


namespace model;


use m\Model;
use m\Util;
use model\special\PengaturanModel;

class BimbinganModel extends Model
{
    const STATUS_DOSEN_PEMBIMBING_1 = 'Pembimbing-1';
    const STATUS_DOSEN_PEMBIMBING_2 = 'Pembimbing-2';

    public function __construct()
    {
        parent::__construct('v2_bimbingan');
    }

    public function findByNim($nim)
    {
        $sql = "SELECT d.*, 'Pembimbing-1' AS `status` FROM v2_bimbingan b INNER JOIN v2_dosen d ON d.id = b.id_pembimbing_1 WHERE b.nim_mahasiswa = '$nim'
                UNION ALL
                SELECT d.*, 'Pembimbing-2' AS `status` FROM v2_bimbingan b INNER JOIN v2_dosen d ON d.id = b.id_pembimbing_2 WHERE b.nim_mahasiswa = '$nim';";

        return $this->executeReadSQL($sql);
    }

    public function findPembimbingAsKeyValuePairs($nim)
    {
        $sql = "SELECT d.id, d.nama FROM v2_bimbingan b INNER JOIN v2_dosen d ON d.id = b.id_pembimbing_1 WHERE b.nim_mahasiswa = '$nim'
                UNION ALL
                SELECT d.id, d.nama FROM v2_bimbingan b INNER JOIN v2_dosen d ON d.id = b.id_pembimbing_2 WHERE b.nim_mahasiswa = '$nim';";

        $records = $this->executeReadSQL($sql);

        if(count($records) > 0)
        {
            return Util::arrayTableToKeyValuePairs('id', 'nama', $records, '', '-- Pilih salah satu --');
        }

        return null;
    }

    public function findPembimbing1ByNim($nim)
    {
        $all = $this->findByNim($nim);

        foreach ($all as $pembimbing)
        {
            if($pembimbing['status'] == 'Pembimbing-1')
                return $pembimbing;
        }

        return null;
    }

    public function editOrAdd($nim, $namaPembimbing1, $namaPembimbing2)
    {
        $nimExists = $this->nimEligible($nim);

        if($nimExists)
        {
            $np1 = Util::strReplaceSingleQuoteWithMsWordQuote($namaPembimbing1);
            $np2 = Util::strReplaceSingleQuoteWithMsWordQuote($namaPembimbing2);

            $idPembimbing1 = (new DosenModel())->findOneRowOneColumnValue('id', ['nama' => $np1]);
            $idPembimbing2 = (new DosenModel())->findOneRowOneColumnValue('id', ['nama' => $np2]);

            $sql = "INSERT INTO {$this->tableName} (nim_mahasiswa, id_pembimbing_1, id_pembimbing_2) VALUES('$nim', '$idPembimbing1', '$idPembimbing2') ON DUPLICATE KEY UPDATE id_pembimbing_1 = '$idPembimbing1', id_pembimbing_2 = '$idPembimbing2'";

            // pre_print($sql, true);

            return $this->executeWriteSQL($sql, false);
        }

        $this->setLastWriteErrorMessage("NIM $nim tidak eligible!");

        return false;
    }

    public function nimEligible($nim)
    {
        $sql = "SELECT * FROM v2_rekap_lulus_sempro WHERE nim = '$nim'";

        // pre_print($sql);

        $result = $this->executeReadSQL($sql);

        return count($result) > 0;
    }

    public function findRekapBimbinganPerDosen($idDosen, $currentPeriodOnly = true)
    {
        $sql = "SELECT * FROM v2_rekap_bimbingan_per_dosen WHERE id_dosen = '$idDosen' " . PengaturanModel::createPredicateTahunProposalSekarang('tahun_proposal');

        return $this->executeReadSQL($sql);
    }

    public function findAll()
    {
        $sql = "SELECT * FROM {$this->tableName};";

        return $this->executeReadSQL($sql);
    }

    public function findStatusDosenPembimbing($idDosen, $idProposal)
    {
        $sql = "SELECT status_pembimbingan FROM v2_rekap_bimbingan_per_dosen WHERE id_dosen = '$idDosen' AND id_proposal = '$idProposal' LIMIT 1;";

        $records = $this->executeReadSQL($sql);

        if(count($records) > 0)
        {
            return $records[0]['status_pembimbingan'];
        }

        return null; // Bukan pembimbing judul tersebut.
    }

    public function addNewPembimbingUtama($idDosenPembimbing1, $idMahasiswaPengusul, $idMahasiswaAnggota = null)
    {
        $sql = "INSERT INTO {$this->tableName} (nim_mahasiswa, id_pembimbing_1) VALUES ('$idMahasiswaPengusul', $idDosenPembimbing1)";

        if($idMahasiswaAnggota != null)
            $sql .= ", ('$idMahasiswaAnggota', $idDosenPembimbing1)";

        //pre_print($sql);

        $this->executeWriteSQL($sql);
    }
}