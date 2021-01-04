<?php


namespace model;


use m\Model;

class HasilSemproModel extends Model
{
    public function __construct()
    {
        parent::__construct('v2_hasil_sempro');
    }

    public function addNew($idEvent, $nim, $hasil)
    {
        if($this->sudahLulus($nim))
            return false;

        if($hasil != 'Diterima tanpa revisi'
            && $hasil != 'Diterima dengan revisi'
            && $hasil != 'Ditolak')
        {
            $sql = "INSERT INTO {$this->tableName} (id_event, nim, hasil, keterangan) VALUES ($idEvent, '$nim', 'Lain-lain', '$hasil') ON DUPLICATE KEY UPDATE hasil = 'Lain-lain', keterangan = '$hasil';";
        }
        else
        {
            $sql = "INSERT INTO {$this->tableName} (id_event, nim, hasil) VALUES ($idEvent, '$nim', '$hasil') ON DUPLICATE KEY UPDATE hasil = '$hasil', keterangan = NULL;";
        }

        return $this->executeWriteSQL($sql, false);
    }

    public function sudahLulus($nim)
    {
        $sql = "SELECT hasil FROM {$this->tableName} WHERE nim = '$nim' ORDER BY id_event DESC LIMIT 1;";

        // pre_print($sql, true);

        $result = $this->executeReadSQL($sql);

        if($result == null || count($result) < 1)
            return false;

        $hasil = $result[0]['hasil'];

        return $hasil == 'Diterima dengan Revisi' || $hasil == 'Diterima Tanpa Revisi';
    }

    public function findRekapLulusSemproByNim($nim)
    {
        $sql = "SELECT * FROM v2_rekap_lulus_sempro WHERE nim = '$nim'";

        $result = $this->executeReadSQL($sql);

        if(count($result) < 1)
            return null;

        return $result[0];
    }
}