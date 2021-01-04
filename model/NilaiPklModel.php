<?php


namespace model;


use m\Model;

class NilaiPklModel extends Model
{
    public function __construct()
    {
        parent::__construct('v2_nilai_pkl');
    }

    public function hasDonePkl($nim)
    {
        $sql = "SELECT * FROM {$this->tableName} WHERE nim = '$nim';";

        $result = $this->executeReadSQL($sql);

        return ($result != null && count($result) > 0);
    }
}