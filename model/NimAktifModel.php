<?php


namespace model;


use m\Model;

class NimAktifModel extends Model
{
    public function __construct()
    {
        parent::__construct('v2_nim_aktif');
    }

    public function isActive($nim)
    {
        $sql = "SELECT * FROM {$this->tableName} WHERE nim = '$nim'";

        $records = $this->executeReadSQL($sql);

        return count($records) > 0;
    }
}