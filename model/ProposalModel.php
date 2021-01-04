<?php


namespace model;


use m\Model;
use m\Util;

class ProposalModel extends Model
{
    public function __construct()
    {
        parent::__construct('v2_proposal');
    }

    public function findAll()
    {
        $sql = "SELECT * FROM {$this->tableName};";

        return $this->executeReadSQL($sql);
    }

    // TODO: Delete this if there are no problem in some times
    /*
    public function findVerifiedProposalByUsername($username)
    {
        $verified = count($this->findRekapVerifikasiByUsername($username)) > 0;

        if(!$verified)
            return null;

        return $this->findByNimPengusul($username);
    }
    */

    public function findByNimPengusul($nim)
    {
        $sql = "SELECT * FROM {$this->tableName} WHERE nim_pengusul = '$nim' LIMIT 1";

        $results = $this->executeReadSQL($sql);

        if(count($results) > 0)
            return $results[0];

        return null;
    }

    public function findByNimAnggota($nim)
    {
        $sql = "SELECT * FROM {$this->tableName} WHERE nim_anggota = '$nim' LIMIT 1";

        $results = $this->executeReadSQL($sql);

        if(count($results) > 0)
            return $results[0];

        return null;
    }

    /*
    public function findRekapVerifikasiByUsername($username)
    {
        $sql = "SELECT * FROM v2_rekap_verifikasi WHERE nim = '$username' LIMIT 1";

        return $this->executeReadSQL($sql);
    }
    */

    public function findNimPengusulByUsername($username)
    {
        $sql = "SELECT nim_pengusul FROM {$this->tableName} WHERE nim_pengusul = '$username' OR nim_anggota = '$username'";

        $record = $this->executeReadSQL($sql);

        if(count($record) < 1)
            return null;

        return $record[0]['nim_pengusul'];
    }

    public function addToPendaftaranSempro($idProposal, $judul, $grupRiset, $fileActivityControl, $filePersetujuanMaju, $fileProposalRevisi, $idEvent)
    {
        $judul = Util::sanitizeSqlInjection($judul);
        $grupRiset = Util::sanitizeSqlInjection($grupRiset);

        $sql = "INSERT INTO v2_pendaftaran_sempro VALUES ('$idProposal', '$judul', '$grupRiset', '$fileActivityControl', '$filePersetujuanMaju', '$fileProposalRevisi', '$idEvent', NOW());";

        //pre_print($sql, true);

        $this->executeWriteSQL($sql);
    }

    public function findPendaftaranSemproByUsername($username)
    {
        $sql = "SELECT * FROM v2_pendaftaran_sempro WHERE id_proposal = (SELECT id FROM v2_proposal WHERE nim_pengusul = '$username' LIMIT 1)";

        $result = $this->executeReadSQL($sql);

        if(count($result) < 1)
            return null;

        return $result[0];
    }

    public function findPendaftaranSemproByUsernameAndIdEvent($username, $idEvent)
    {
        $sql = "SELECT * FROM v2_pendaftaran_sempro WHERE id_proposal = (SELECT id FROM v2_proposal WHERE nim_pengusul = '$username' AND id_event = '$idEvent' LIMIT 1)";

        $result = $this->executeReadSQL($sql);

        if(count($result) < 1)
            return null;

        return $result[0];
    }

    public function findRekapPendaftaranSemproByUsername($username)
    {
        $sql = "SELECT * FROM v2_rekap_pendaftaran_sempro WHERE nim = '$username'";

        $result = $this->executeReadSQL($sql);

        if(count($result) < 1)
            return null;

        return $result[0];
    }

    public function findRekapPendaftaranSemproByUsernameAndIdEvent($username, $idEvent)
    {
        $sql = "SELECT * FROM v2_rekap_pendaftaran_sempro WHERE nim = '$username' AND id_event = '$idEvent'";

        $result = $this->executeReadSQL($sql);

        if(count($result) < 1)
            return null;

        return $result[0];
    }

    public static function translateGrupRiset($namaGrupRiset)
    {
        if($namaGrupRiset == 'INFORMATION SYSTEM')
            return 'SISTEM INFORMASI';

        if($namaGrupRiset == 'COMPUTER VISION')
            return 'VISI KOMPUTER';

        if($namaGrupRiset == 'SISTEM CERDAS')
            return 'SISTEM CERDAS';

        if($namaGrupRiset == 'COMPUTER NETWORK, ARCHITECTURE AND DATA SECURITY')
            return 'JARKOM, ARSITEKTUR DAN KEAMANAN DATA';

        if($namaGrupRiset == 'MULTIMEDIA AND GAME')
            return 'MULTIMEDIA DAN GAME';

        return null; // TODO: What will happen if the name does not need to be translated?
    }

    public function editProposalPendaftaranSempro($idProposal, $newFile)
    {
        $sql = "UPDATE v2_pendaftaran_sempro SET file_proposal_revisi = '$newFile' WHERE id_proposal = '$idProposal'";

        $this->executeWriteSQL($sql);
    }

    public function addOrEdit(array $columnValuePairs)
    {
        /*
        Array
        (
            [nim_pengusul] => 1641720173
            [judul_proposal] => Proposal Percobaan
            [nama_grup_riset] => JARKOM, ARSITEKTUR DAN KEAMANAN DATA
            [literatur_utama] =>
            [literatur_penunjang] =>
            [catatan_khusus] =>
            [id_dosen_pembimbing_1] => 15
            [deskripsi] =>
            [form_kesediaan] => b6e679122a91706b47135b3f01cabdf7.png
            [draft] => 87160770582a737a1153717b7ce877a6.pdf
            [revisi_draft_1] =>
            [revisi_draft_2] =>
            [id] =>
        )
        */

        $id  = $columnValuePairs['id'];

        Util::arrayAssocRemoveElementByKeyIfEmpty($columnValuePairs, 'nim_anggota');

        if(empty($id))
            $sql = $this->createSqlInsert($columnValuePairs, ['id']);
        else
        {
            Util::arrayAssocRemoveEmptyElements($columnValuePairs);

            $sql = $this->createSqlUpdate($columnValuePairs, "WHERE id = '$id'", ['id']);
        }

        $this->executeWriteSQL($sql);
    }

    public function findByNimPengusulOrAnggota($nim)
    {
        $sql = "SELECT * FROM {$this->tableName} WHERE nim_pengusul = '$nim' OR nim_anggota = '$nim' LIMIT 1";

        $results = $this->executeReadSQL($sql);

        if(count($results) > 0)
            return $results[0];

        return null;
    }

    public function findOneById($id)
    {
        $sql = "SELECT * FROM {$this->tableName} WHERE id = '$id' LIMIT 1";

        $results = $this->executeReadSQL($sql);

        if(count($results) > 0)
            return $results[0];

        return null;
    }
}