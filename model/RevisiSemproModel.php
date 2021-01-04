<?php


namespace model;


use m\Model;
use m\Util;

class RevisiSemproModel extends Model
{
    public function __construct()
    {
        parent::__construct('v2_revisi_sempro');
    }

    public function findOneByNim($nim)
    {
        $result = $this->find(array('nim_mahasiswa' => $nim));

        if($result != null && count($result) > 0)
            return $result[0];

        return null;
    }

    public function findOneRekapByNim($nim)
    {
        $sql = "SELECT * FROM v2_rekap_revisi_sempro WHERE nim_mahasiswa = '$nim'";

        $result = $this->executeReadSQL($sql);

        if($result != null && count($result) > 0)
            return $result[0];

        return null;
    }

    public function addOrEdit(array $data)
    {
        $existing = $this->findOneByNim($data['nim_mahasiswa']);

        $ignore = ['nama', 'hasil_sempro', 'tahap_terakhir_sempro'];

        if($existing == null)
        {
            if($data['file_berita_acara'] == null || $data['file_berita_acara'] == '')
                unset($data['file_berita_acara']);

            $sql = $this->createSqlInsert($data, $ignore);
        }
        else
        {
            $extraIgnore = Util::arrayAssocCollectEmptyFields($data);

            $ignore = array_merge($ignore, $extraIgnore);

            $idProposal = $data['id_proposal'];

            $sql = $this->createSqlUpdate($data, "WHERE id_proposal = '$idProposal'", $ignore);
        }

        $this->executeWriteSQL($sql);
    }

    public function hasDoneRevisi($idProposal)
    {
        $sql = "SELECT id_proposal FROM {$this->tableName} WHERE id_proposal = '$idProposal';";

        $result = $this->executeReadSQL($sql);

        return ($result != null && count($result) > 0);
    }

    public function findOneRekapByIdProposal($idProposal)
    {
        $sql = "SELECT * FROM v2_rekap_revisi_sempro WHERE id_proposal = '$idProposal'";

        $result = $this->executeReadSQL($sql);

        if($result != null && count($result) > 0)
            return $result[0];

        return null;
    }

    public function findRekapKelulusanUjianByNim($nim, $field = '*')
    {
        if(!is_numeric($nim))
            return null;
        
        //$sql = 'SELECT * FROM v2_rekap_kelulusan_ujian WHERE nim = :nim';
        //$statement = $this->getDb()->prepare($sql);
        //$records = $statement->execute(['nim' => $nim]);

        $sql = "SELECT $field FROM v2_rekap_kelulusan_ujian WHERE nim = '$nim' LIMIT 1";

        $records = $this->executeReadSQL($sql);

        if(count($records) < 1)
            return null;

        return $records;
    }
}