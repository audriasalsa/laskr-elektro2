<?php


namespace model;


use m\Model;
use m\Util;

class DosenModel extends Model
{
    const TIDAK = 'tidak';
    const YA = 'ya';

    public function __construct()
    {
        parent::__construct('v2_dosen');
    }

    public function findOneById($idDosen)
    {
        $sql = "SELECT * FROM {$this->tableName} WHERE id = '$idDosen' LIMIT 1;";

        $records = $this->executeReadSQL($sql);

        if(count($records) < 1)
            return null;

        return $records[0];
    }

    public function getSelectOptionPairs()
    {
        $sql = "SELECT id, nama FROM {$this->tableName}";

        $result = $this->executeReadSQL($sql);

        /*
        Array
        (
            [0] => Array
            (
                [id] => 1
                [nama] => Ariadi Retno Ririd, S.Kom., M.Kom.
            )
            [1] => Array
            (
                [id] => 2
                [nama] => Budi Harijanto, ST., M.MKom.
            )
        )
        */

        $optionsData = array();

        foreach ($result as $row)
        {
            $id   = $row['id'];
            $nama = $row['nama'];

            $optionsData[$id] = $nama;
        }

        return $optionsData;
    }

    public function addNewDosenByName($dosenName)
    {
        $dosenName = Util::strReplaceSingleQuoteWithMsWordQuote($dosenName);

        /**
         * CAUTION: Column 'nama' in the imported table must be UNIQUE!
         */
        $sql = "INSERT INTO {$this->tableName} (nama) VALUES ('$dosenName')";

        return $this->executeWriteSQL($sql, false);
    }

    public function findByUsername($username)
    {
        $sql = "SELECT * FROM {$this->tableName} WHERE id IN (SELECT id_dosen FROM v2_credential WHERE username = '$username' AND access_type = 'dosen')";

        $result = $this->executeReadSQL($sql);

        if(count($result) > 0)
            return $result[0];

        return null;
    }

    public function findAllIdTidakAktifMembimbingAs1DArray()
    {
        $tidak = self::TIDAK;

        $sql = "SELECT id FROM {$this->tableName} WHERE aktif_membimbing = '$tidak';";

        $records = $this->executeReadSQL($sql);

        if(count($records) < 1)
            return null;

        $idList = array();

        foreach ($records as $row)
        {
            $idList[] = $row['id'];
        }

        return $idList;
    }

    public function findAllDosen(){
        $sql = "SELECT * FROM {$this->tableName}";

        $records = $this->executeReadSQL($sql);

        if(count($records) > 0)
        {
            return Util::arrayTableToKeyValuePairs('id', 'nama', $records, '', '-- Pilih salah satu --');
        }

        return null;
    }
}