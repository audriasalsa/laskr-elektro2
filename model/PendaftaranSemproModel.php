<?php


namespace model;


use m\Model;
use m\Util;

class PendaftaranSemproModel extends Model
{
    public function __construct()
    {
        parent::__construct('v2_pendaftaran_sempro');
    }

    public function findOneByIdProposalAndIdEvent($idProposal, $idEvent)
    {
        $sql = "SELECT * FROM {$this->tableName} WHERE id_proposal = '$idProposal' AND id_event = '$idEvent'";

        $records = $this->executeReadSQL($sql);

        if(count($records) < 1)
            return null;

        return $records[0];
    }

    public function addNew(array $pendaftaranSemproData)
    {
        $sql = $this->createSqlInsert($pendaftaranSemproData);

        $this->executeWriteSQL($sql);
    }

    public function edit(array $pendaftaranSemproData)
    {
        Util::arrayAssocRemoveElementByKeyIfEmpty($pendaftaranSemproData, 'file_presentasi');
        Util::arrayAssocRemoveElementByKeyIfEmpty($pendaftaranSemproData, 'file_proposal_revisi');

        $idProposal = $pendaftaranSemproData['id_proposal'];
        $idEvent    = $pendaftaranSemproData['id_event'];

        $where = "WHERE id_proposal = '$idProposal' AND id_event = '$idEvent'";

        $sql = $this->createSqlUpdate($pendaftaranSemproData, $where, array('id_proposal', 'id_event'));

        //pre_print($sql);

        $this->executeWriteSQL($sql);
    }

    public function findRekapPendaftaranSemproByIdDosen($idDosen)
    {
        $sql = "SELECT id_event, tahap, id_proposal, judul_proposal_awal, judul_direvisi, nim_pengusul, nama_pengusul, nama_pembimbing_utama, kode_prodi, status_persetujuan_pembimbing FROM v2_rekap_pendaftaran_sempro WHERE id_pembimbing_utama = '$idDosen';";

        $result = $this->executeReadSQL($sql);

        if(count($result) > 0)
            return $result;

        else
            return null;
    }

    public function accept($idProposal, $idEvent, $idPembimbing)
    {
        if(!is_numeric($idProposal) || !is_numeric($idEvent) || !is_numeric($idPembimbing))
            return false;

        $sql = "UPDATE {$this->tableName} SET status_persetujuan_pembimbing = 'disetujui' WHERE id_proposal = '$idProposal' AND id_event = '$idEvent'";

        return $this->executeWriteSQL($sql);
    }
}