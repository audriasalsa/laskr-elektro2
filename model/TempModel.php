<?php


namespace model;


use m\Model;

class TempModel extends Model
{
    public function __construct()
    {
        parent::__construct('temp');
    }

    public function loadAllToTempImportJadwalSempro($data)
    {
        $this->tableName = 'v2_temp_impor_jadwal_sempro';

        $this->executeWriteSQL("DELETE FROM {$this->tableName}");

        foreach ($data as $row)
        {
            $sql = $this->createSqlInsert($row);

            $this->executeWriteSQL($sql);
        }
    }
}