<?php


namespace model;


use m\Model;

class LogBimbinganModel extends Model
{
    const STATUS_DIAJUKAN = 'diajukan';
    const STATUS_DISETUJUI = 'disetujui';
    const STATUS_DITOLAK = 'ditolak';
    const JENIS_PASCA_PROPOSAL = 'pasca_proposal';
    const JENIS_PRA_PROPOSAL = 'pra_proposal';

    public function __construct()
    {
        parent::__construct('v2_log_bimbingan');
    }

    public function addNew(array $logBimbinganData)
    {
        $sql = $this->createSqlInsert($logBimbinganData, array('id'));

        $this->executeWriteSQL($sql);
    }

    public function editExisting(array $logBimbinganData)
    {
        $sql = $this->createSqlUpdate($logBimbinganData, "WHERE id = '{$logBimbinganData['id']}'", ['id']);

        $this->executeWriteSQL($sql);
    }

    public function findAllByNim($nim)
    {
        $sql = "SELECT lb.id, lb.jenis, d.nama AS nama_dosen_pembimbing, lb.tanggal, lb.materi_bimbingan, lb.status FROM {$this->tableName} lb INNER JOIN v2_dosen d ON lb.id_dosen_pembimbing = d.id  WHERE lb.nim_mahasiswa = '$nim' ORDER BY d.nama ASC";

        $result = $this->executeReadSQL($sql);

        if(count($result) > 0)
            return $result;

        return null;
    }

    public function findOneById($id)
    {
        if(!is_numeric($id))
            return null;

        $sql = "SELECT * FROM {$this->tableName} WHERE id = '$id' LIMIT 1";

        $result = $this->executeReadSQL($sql);

        if(count($result) < 1)
            return null;

        return $result[0];
    }

    public function removeById($id)
    {
        if(!is_numeric($id))
            return false;

        $sql = "DELETE FROM {$this->tableName} WHERE id = '$id'";

        $this->executeWriteSQL($sql);

        return true;
    }

    public function findAllByDosenId($dosenId, $mahasiswaAktifOnly = true)
    {
        $sql = "SELECT lb.id, m.nama AS nama_mahasiswa, lb.tanggal, lb.materi_bimbingan, lb.status FROM {$this->tableName} lb INNER JOIN v2_mahasiswa m ON lb.nim_mahasiswa = m.nim WHERE lb.id_dosen_pembimbing = '$dosenId'";

        if($mahasiswaAktifOnly)
            $sql .= " AND lb.nim_mahasiswa IN (SELECT nim FROM v2_rekap_mahasiswa_aktif)";

        $result = $this->executeReadSQL($sql);

        if(count($result) > 0)
            return $result;

        else
            return null;
    }

    public function changeStatus($logId, $newStatus = 'diajukan')
    {
        if(!is_numeric($logId))
            return false;

        $sql = "UPDATE {$this->tableName} SET `status` = '$newStatus' WHERE id = '$logId'";

        return $this->executeWriteSQL($sql);
    }

    public function findRekapLogBimbingan($wherePairs = null)
    {
        $where = $this->createWhereClause($wherePairs);

        $sql = "SELECT * FROM v2_rekap_log_bimbingan $where ORDER BY log_bimbingan_disetujui DESC;";

        return $this->executeReadSQL($sql);
    }

    public function findRekapLogBimbinganDiajukanWithCountBetween($floor, $ceil)
    {
        $sql = "SELECT * FROM v2_rekap_log_bimbingan WHERE log_bimbingan_diajukan BETWEEN $floor AND $ceil ORDER BY log_bimbingan_disetujui DESC;";

        return $this->executeReadSQL($sql);
    }

    public function findRekapPonselLogBimbingan($wherePairs = null)
    {
        $where = $this->createWhereClause($wherePairs);

        $sql = "SELECT * FROM v2_rekap_ponsel_log_bimbingan $where ORDER BY jumlah_log_diterima DESC;";

        return $this->executeReadSQL($sql);
    }

    public function findRekapPonselLogBimbinganDiajukanWithCountBetween($floor, $ceil)
    {
        $sql = "SELECT * FROM v2_rekap_ponsel_log_bimbingan WHERE jumlah_log_bimbingan BETWEEN $floor AND $ceil ORDER BY jumlah_log_diterima DESC;";

        return $this->executeReadSQL($sql);
    }

    public function findRekapLogBimbinganDosen($wherePairs = null)
    {
        $where = $wherePairs == null ? '' : $this->createWhereClause($wherePairs);

        $sql = "SELECT * FROM v2_rekap_log_bimbingan_dosen $where ORDER BY log_pending DESC;";

        return $this->executeReadSQL($sql);
    }

    public function findRekapLogBimbinganDosenWithWhereClause($whereClause)
    {
        $sql = "SELECT * FROM v2_rekap_log_bimbingan_dosen $whereClause ORDER BY log_pending DESC;";

        return $this->executeReadSQL($sql);
    }


    /**
     * @param $nim
     * @return array
     */
    public function findLogBimbinganCount($nim)
    {
        $sql = "SELECT nim_mahasiswa, id_dosen_pembimbing, COUNT(*) AS jumlah FROM v2_log_bimbingan WHERE status = 'disetujui' GROUP BY nim_mahasiswa, id_dosen_pembimbing HAVING nim_mahasiswa = '$nim';";

        $result = $this->executeReadSQL($sql);

        if($result == null || count($result) < 1)
            return array(0, 0);

        $count = array();

        foreach ($result as $row)
        {
            $count[] = $row['jumlah'];
        }

        //pre_print($count, true);

        return $count;
    }

    public function determineCurrentJenis($nim)
    {
        $sql = "SELECT id_proposal FROM v2_revisi_sempro WHERE id_proposal IN (SELECT id_proposal FROM v2_rekap_kepemilikan_proposal WHERE nim = '$nim');";

        $records = $this->executeReadSQL($sql);

        if(count($records) > 0)
            return self::JENIS_PASCA_PROPOSAL;
        else
            return self::JENIS_PRA_PROPOSAL;
    }

    public function getCount($idProposal, $jenis, $disetujuiOnly = true)
    {
        $sql = "SELECT COUNT(*) AS jumlah FROM v2_rekap_detail_log_bimbingan WHERE jenis = '$jenis' AND id_proposal = '$idProposal'";

        if($disetujuiOnly)
            $sql .= " AND status = 'disetujui'";

        $records = $this->executeReadSQL($sql);

        return $records[0]['jumlah'];
    }
}