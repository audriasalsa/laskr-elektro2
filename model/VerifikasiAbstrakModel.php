<?php


namespace model;


use m\Model;
use m\Util;

class VerifikasiAbstrakModel extends Model
{
    public function __construct()
    {
        parent::__construct('v2_verifikasi_abstrak');
    }

    public function findOneByIdProposal($idProposal)
    {
        $sql = "SELECT * FROM {$this->tableName} WHERE id_proposal = '$idProposal' LIMIT 1";

        $records = $this->executeReadSQL($sql);

        if(count($records) < 1)
            return null;

        return $records[0];
    }

    public function addNewOrUpdate(array $submittedAbstract)
    {
        $exists = $this->findOneByIdProposal($submittedAbstract['id_proposal']);

        if($exists != null)
        {
            // Edit
            // If file abstract is empty, let it be
            Util::arrayAssocRemoveElementByKeyIfEmpty($submittedAbstract, 'file_abstrak');

            $this->edit($submittedAbstract);
        }
        else
        {
            $this->addNew($submittedAbstract);
        }
    }

    public function addNew(array $submittedAbstract)
    {
        $sql = $this->createSqlInsert($submittedAbstract);

        //pre_print($sql, true);

        $this->executeWriteSQL($sql);
    }

    public function edit(array $submittedAbstract)
    {
        $sql = $this->createSqlUpdate($submittedAbstract, "WHERE id_proposal = '{$submittedAbstract['id_proposal']}'");

        //pre_print($sql, true);

        $this->executeWriteSQL($sql);
    }

    public function findAllRekapByIdDosenVerifikator($idDosenVerifikator)
    {
        $sql = "SELECT * FROM v2_rekap_verifikasi_abstrak WHERE id_dosen_verifikator = '$idDosenVerifikator'";

        //pre_print($sql);

        $records = $this->executeReadSQL($sql);

        if(count($records) < 1)
            return null;

        return $records;
    }

    public function accept($idProposal)
    {
        $sql = "UPDATE {$this->tableName} SET status_verifikasi = 'disetujui' WHERE id_proposal = '$idProposal';";

        $this->executeWriteSQL($sql);
    }
}