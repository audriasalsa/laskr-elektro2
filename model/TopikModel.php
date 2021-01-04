<?php


namespace model;


use controller\UsulanTopikController;
use lib\AppUtil;
use m\Model;
use m\Util;

class TopikModel extends Model
{
    const STATUS_BEBAS = 'bebas';
    const STATUS_DIKLAIM = 'diklaim';

    public function __construct()
    {
        parent::__construct('v2_topik');
    }

    public function findOneById($id)
    {
        $id = AppUtil::removeQuotes($id);

        $sql = "SELECT * FROM {$this->tableName} WHERE id = '$id' LIMIT 1;";

        $records = $this->executeReadSQL($sql);

        if(count($records) < 1)
            return null;

        return $records[0];
    }

    public function addNew($topicData, $accessType = UsulanTopikController::ACCESS_TYPE_DOSEN)
    {
        $ignored = $accessType == UsulanTopikController::ACCESS_TYPE_DOSEN ? 'nim_mahasiswa_pengusul' : 'id_dosen_pengusul';

        $sql = $this->createSqlInsert($topicData, ['id', $ignored]);

        // pre_print($sql);

        $this->executeWriteSQL($sql);
    }

    public function findAllRekapByIdPengusul($idPengusul, $accessType)
    {
        $pengusulColumn = $accessType == UsulanTopikController::ACCESS_TYPE_DOSEN ? 'id_dosen_pengusul' : 'nim_mahasiswa_pengusul';

        $sql = "SELECT * FROM v2_rekap_topik WHERE $pengusulColumn = '$idPengusul' ORDER BY id ASC";

        $result = $this->executeReadSQL($sql);

        if(count($result) > 0)
            return $result;

        return null;
    }

    public function edit($topicData)
    {
        $sql = $this->createSqlUpdate($topicData, "WHERE id = {$topicData['id']}", ['id', 'id_dosen_pengusul', 'nim_mahasiswa_pengusul', 'status']);

        $this->executeWriteSQL($sql);
    }

    public function findAllJenisPengajuanDosen()
    {
        $diklaim = self::STATUS_DIKLAIM;

        $sql = "SELECT * FROM v2_rekap_topik WHERE jenis_pengusul = 'dosen' AND status <> '$diklaim'";

        //pre_print($sql);

        $records = $this->executeReadSQL($sql);

        if(count($records) > 0)
            return $records;

        return null;
    }

    public function findAllByIdMahasiswaPengusul($nim)
    {
        $nim = AppUtil::removeQuotes($nim);

        $sql = "SELECT * FROM v2_rekap_topik WHERE (jenis_pengusul = 'mahasiswa' OR jenis_pengusul = 'grup_riset' OR jenis_pengusul = 'magang_industri') AND nim_mahasiswa_pengusul = '$nim'";

        //pre_print($sql);

        $records = $this->executeReadSQL($sql);

        if(count($records) > 0)
            return $records;

        return null;
    }

    public function updateStatus($status, $idTopik)
    {
        $sql = "UPDATE {$this->tableName} SET status = '$status' WHERE id = '$idTopik';";

        $this->executeWriteSQL($sql);
    }

    public function removeById($idTopik)
    {
        $sql = "DELETE FROM {$this->tableName} WHERE id = '$idTopik';";

        //pre_print($sql);

        $this->executeWriteSQL($sql);
    }
}