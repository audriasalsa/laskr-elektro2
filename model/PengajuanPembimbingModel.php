<?php


namespace model;


use controller\mahasiswa\PengajuanPembimbingController;
use m\Model;
use m\Util;

class PengajuanPembimbingModel extends Model
{
    const STATUS_DIAJUKAN = 'diajukan';
    const STATUS_DITOLAK = 'ditolak';
    const STATUS_DISETUJUI = 'disetujui';

    public function __construct()
    {
        parent::__construct('v2_pengajuan_pembimbing');
    }

    public function addNew(array $assocData)
    {
        Util::arrayAssocRemoveElementByKeyIfEmpty($assocData, 'nim_anggota');

        $sql = $this->createSqlInsert($assocData);

        //pre_print($sql);

        $this->executeWriteSQL($sql);
    }

    public function findAllRekapByNimPengusulOrAnggota($nim)
    {
        $sql = "SELECT * FROM v2_rekap_pengajuan_pembimbing WHERE nim_pengusul = '$nim' OR nim_anggota = '$nim'";

        $result = $this->executeReadSQL($sql);

        if(count($result) > 0)
            return $result;

        return null;
    }

    public function hasPendingPengajuan($nimPengusulOrAnggota)
    {
        // Beware the OR. Always use (xxx OR yyy) AND zzz
        $sql = "SELECT * FROM v2_rekap_pengajuan_pembimbing WHERE (nim_pengusul = '$nimPengusulOrAnggota' OR nim_anggota = '$nimPengusulOrAnggota') AND status = 'diajukan'";

        $result = $this->executeReadSQL($sql);

        //pre_print($result);

        return (count($result) > 0);
    }

    public function hasAcceptedPengajuan($nimPengusulOrAnggota)
    {
        $sql = "SELECT * FROM v2_rekap_pengajuan_pembimbing WHERE (nim_pengusul = '$nimPengusulOrAnggota' OR nim_anggota = '$nimPengusulOrAnggota') AND status = 'disetujui'";

        $result = $this->executeReadSQL($sql);

        return (count($result) > 0);
    }

    public function findAllRekapByDosenId($id)
    {
        $sql = "SELECT * FROM v2_rekap_pengajuan_pembimbing WHERE id_pembimbing_utama = '$id'";

        //pre_print($sql);

        $result = $this->executeReadSQL($sql);

        if(count($result) > 0)
            return $result;

        return null;
    }

    public function rejectPengajuan($nimPengusul, $idTopik, $idPembimbingUtama)
    {
        $sql = "UPDATE {$this->tableName} SET status = 'ditolak' WHERE nim_pengusul = '$nimPengusul' AND id_topik = '$idTopik' AND id_pembimbing_utama = '$idPembimbingUtama';";

        //pre_print($sql);

        $this->executeWriteSQL($sql);
    }

    public function acceptPengajuan($nimPengusul, $idTopik, $idPembimbingUtama)
    {
        $sql = "UPDATE {$this->tableName} SET status = 'disetujui' WHERE nim_pengusul = '$nimPengusul' AND id_topik = '$idTopik' AND id_pembimbing_utama = '$idPembimbingUtama';";

        //pre_print($sql);

        $this->executeWriteSQL($sql);
    }

    public function findAcceptedCount($idPembimbingUtama)
    {
        $statusDisetujui = self::STATUS_DISETUJUI;

        $sql = "SELECT COUNT(*) AS accepted FROM {$this->tableName} WHERE id_pembimbing_utama = '$idPembimbingUtama' AND status = '{$statusDisetujui}';";

        //pre_print($sql);

        $records = $this->executeReadSQL($sql);

        if(count($records) < 1)
            return 0;

        //pre_print($records);

        return $records[0]['accepted'];
    }

    public function findOnePengajuanByPrimaryKeys($nimPengusul, $idTopik, $idPembimbingUtama)
    {
        $sql = "SELECT * FROM {$this->tableName} WHERE nim_pengusul = '$nimPengusul' AND id_topik = '$idTopik' AND id_pembimbing_utama = '$idPembimbingUtama'";

        $records = $this->executeReadSQL($sql);

        if(count($records) < 1)
            return null;

        return $records[0];
    }

    public function findPengajuanDisetujuiByNimPengusulOrAnggota($idPembimbingUtama, $nimPengusulOrAnggota)
    {
        $disetujui = self::STATUS_DISETUJUI;

        $sql = "SELECT * FROM {$this->tableName} WHERE status = '$disetujui' AND id_pembimbing_utama = '$idPembimbingUtama' AND (nim_pengusul = '$nimPengusulOrAnggota' OR nim_anggota = '$nimPengusulOrAnggota');";

        $records = $this->executeReadSQL($sql);

        if(count($records) < 1)
            return null;

        return $records[0];
    }

    public function findRekapJumlahPembimbinganUtama()
    {
        $sql = "SELECT * FROM v2_rekap_jumlah_pembimbingan_utama;";

        $records = $this->executeReadSQL($sql);

        if(count($records) < 1)
            return null;

        return $records;
    }
}