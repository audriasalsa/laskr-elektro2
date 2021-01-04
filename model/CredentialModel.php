<?php


namespace model;


use http\Exception;
use m\Model;

class CredentialModel extends Model
{
    public function __construct()
    {
        parent::__construct('v2_credential');
    }

    public function findDosen($username)
    {
        $sql = "SELECT c.username, d.* FROM {$this->tableName} c INNER JOIN v2_dosen d on c.id_dosen = d.id WHERE c.username = '$username';";

        $result = $this->executeReadSQL($sql);

        if(count($result) > 0)
            return $result[0];

        return null;
    }

    public function findMahasiswa($username)
    {
        $sql = "SELECT c.username, m.* FROM {$this->tableName} c INNER JOIN v2_mahasiswa m on c.id_mahasiswa = m.nim WHERE c.username = '$username';";

        $result = $this->executeReadSQL($sql);

        if(count($result) > 0)
            return $result[0];

        return null;
    }

    public function addNewInitialStudentCredential($nim)
    {
        // TODO: WARNING! This line is prone to SQL Injection!
        $sql = "INSERT INTO {$this->tableName} (username, password, access_type, id_mahasiswa) VALUES ('$nim', '$nim', 'mahasiswa', '$nim');";

        $success = $this->executeWriteSQL($sql, false);

        return $success;
    }
}