<?php


namespace model;


use m\Model;
use m\Util;

class PenilaianPembimbingModel extends Model
{
    public function __construct()
    {
        parent::__construct('v2_penilaian_pembimbing');
    }

    public function addNewRecords(array $multipleNilaiAssoc)
    {
        foreach ($multipleNilaiAssoc as $nilaiAssoc)
        {
            // Remove unused nilai.
            Util::arrayAssocRemoveEmptyElements($nilaiAssoc);

            $this->addNewOrUpdate($nilaiAssoc);
        }
    }

    public function addNew(array $nilaiAssoc)
    {
        //pre_print('ADD');
        //pre_print($nilaiAssoc);

        // Remove unused nilai.
        Util::arrayAssocRemoveEmptyElements($nilaiAssoc);

        $sql = $this->createSqlInsert($nilaiAssoc);

        //pre_print($sql);

        $this->executeWriteSQL($sql);
    }

    public function findByIdEventIdProposalAndIdPembimbing($idEvent, $idProposal, $idPembimbing, $orderByNim = false)
    {
        $order = $orderByNim == false ? '' : ' ORDER BY nim ASC';

        $sql = "SELECT * FROM {$this->tableName} WHERE id_event = '$idEvent' AND id_proposal = '$idProposal' AND id_pembimbing = '$idPembimbing' $order;";

        $records = $this->executeReadSQL($sql);

        if(count($records) < 1)
            return null;

        return $records;
    }

    public function addNewOrUpdate(array $nilaiAssoc)
    {
        $idEvent = $nilaiAssoc['id_event'];
        $nim = $nilaiAssoc['nim'];
        $idPembimbing = $nilaiAssoc['id_pembimbing'];

        $existing = $this->findOneByIdEventNimAndIdPembimbing($idEvent, $nim, $idPembimbing);

        //pre_print($existing);

        if($existing == null)
            $this->addNew($nilaiAssoc);
        else
            $this->edit($nilaiAssoc);
    }

    public function edit(array $nilaiAssoc)
    {
        //pre_print('EDIT');
        //pre_print($nilaiAssoc);

        $where = $this->createWhereClause(Util::arrayAssocTakeSomeKeys($nilaiAssoc, ['id_event', 'nim', 'id_pembimbing']));

        $sql = $this->createSqlUpdate($nilaiAssoc, $where, ['id_event', 'nim', 'id_pembimbing']);

        //pre_print($sql);

        $this->executeWriteSQL($sql);
    }

    public function findOneByIdEventNimAndIdPembimbing($idEvent, $nim, $idPembimbing)
    {
        $sql = "SELECT * FROM {$this->tableName} WHERE id_event = '$idEvent' AND nim = '$nim' AND id_pembimbing = '$idPembimbing' LIMIT 1;";

        $records = $this->executeReadSQL($sql);

        if(count($records) < 1)
            return null;

        return $records[0];
    }
}