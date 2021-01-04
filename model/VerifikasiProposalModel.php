<?php


namespace model;


use m\Model;

class VerifikasiProposalModel extends Model
{
    public function __construct()
    {
        parent::__construct('v2_verifikasi_proposal');
    }

    public function findByIdProposal($idProposal)
    {
        $sql = "SELECT * FROM {$this->tableName} WHERE id_proposal = '$idProposal'";

        $records = $this->executeReadSQL($sql);

        if(count($records) < 1)
            return null;

        return $records;
    }

    public function proposalIsVerified($idProposal)
    {
        return ($this->findByIdProposal($idProposal)) != null;
    }

    public function findRekapVerifikasiProposalByNim($nim)
    {
        $sql = "SELECT * FROM v2_rekap_verifikasi_proposal WHERE nim_pengusul = '$nim' OR nim_anggota = '$nim';";

        $records = $this->executeReadSQL($sql);

        if(count($records) < 1)
            return null;

        return $records[0];
    }
}