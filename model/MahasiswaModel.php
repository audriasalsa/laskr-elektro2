<?php

namespace model;

use m\Model;
use m\Util;

class MahasiswaModel extends Model
{
    public function __construct()
    {
        parent::__construct('v2_mahasiswa');
    }

    public function findAll()
    {
        $sql = "SELECT * FROM {$this->tableName}";

        $records = $this->executeReadSQL($sql);

        return $records;
    }

    public function findByNim($nim)
    {
        $sql = "SELECT * FROM {$this->tableName} WHERE nim = '$nim' LIMIT 1";

        $records = $this->executeReadSQL($sql);

        if(count($records) > 0)
            return $records[0];

        return null;
    }

    public function findByUsername($username)
    {
        // TODO: this is need to be edited when we want to implement the system for D3
        $nim = $username;

        return $this->findByNim($nim);
    }

    // TODO: Fix this method
    public function addNew(array $assocRecord)
    {
        $sql = $this->createSqlInsert($assocRecord);

        //pre_print($sql, true);

        return $this->executeWriteSQL($sql, false);
    }

    public function edit(array $fieldValuePairs)
    {
        /*
         * Array(
               [nama] => NIKO RIZKY
               [nim] => 1431140137
               [email] => XSIRHAAN@GMAIL.COM
               [nomor_ponsel] => 081331993754
               [nomor_ponsel_orang_tua] => 081233183222
               [kode_prodi] => D4-TI
           )
         */
        $fieldValuePairs = Util::sanitizeSqlInjectionArray($fieldValuePairs);

        // TODO: Replace with mysql_real_escape_string
        // Escape nama mahasiswa yang ada tanda petiknya seperti Ma'ruf Amin, dlsb.
        $namaMahasiswa = str_replace("'", "’", $fieldValuePairs['nama']);

        $sql = "UPDATE {$this->tableName} SET 
                 nama = '$namaMahasiswa', 
                 email = '{$fieldValuePairs['email']}', 
                 nomor_ponsel = '{$fieldValuePairs['nomor_ponsel']}', 
                 nomor_ponsel_orang_tua = '{$fieldValuePairs['nomor_ponsel_orang_tua']}',
                 kode_prodi = '{$fieldValuePairs['kode_prodi']}' WHERE nim = '{$fieldValuePairs['nim']}'";

        $this->executeWriteSQL($sql);
    }

    public function findAllBelumVerifikasi($columns = '*')
    {
        $sql = "SELECT $columns FROM v2_mahasiswa WHERE nim NOT IN (SELECT nim_pengusul FROM v2_verifikasi_proposal);";

        $result = $this->executeReadSQL($sql);

        if(count($result) > 0)
            return $result;

        return null;
    }

    public function isSudahVerifikasi($nim)
    {
        $find = $this->findAllBelumVerifikasi('nim');

        $belumVerifikasi = array();

        foreach ($find as $row)
        {
            $belumVerifikasi[] = $row['nim'];
        }

        // pre_print($nim, false);
        // pre_print($belumVerifikasi, true);

        $search = array_search($nim, $belumVerifikasi);

        return $search === false;
    }

    public function isDataDiriLengkap($nim)
    {
        $m = $this->findByNim($nim);

        return self::_allFieldsNotEmpty($m);
    }

    // TODO: Observe this, so it can be upped to Model later
    private static function _allFieldsNotEmpty(array $keyValuePairs)
    {
        //pre_print($keyValuePairs, true);
        foreach ($keyValuePairs as $key => $value)
        {
            if(empty($value) || $value == null || $value == '' || $value == ' ')
                return false;
        }

        return true;
    }

    public function isD3($nim)
    {
        $mahasiswa = $this->findByNim($nim);

        $d3 = substr($mahasiswa['kode_prodi'], 0, 2);

        return $d3 == 'D3';
    }

    public function findByIdProposal($idProposal)
    {
        $sql = "SELECT * FROM v2_mahasiswa WHERE nim = (SELECT nim_pengusul FROM v2_proposal WHERE id = $idProposal LIMIT 1)
UNION ALL
SELECT * FROM v2_mahasiswa WHERE nim = (SELECT nim_anggota FROM v2_proposal WHERE id = $idProposal LIMIT 1);";

        $records = $this->executeReadSQL($sql);

        if(count($records) < 1)
            return null;

        return $records;
    }

    public function findTeamMembers($nim)
    {
        $sql = "SELECT * FROM v2_mahasiswa WHERE nim IN(SELECT nim FROM v2_rekap_kepemilikan_proposal WHERE id_proposal IN (SELECT id FROM v2_proposal WHERE nim_pengusul = '$nim' OR nim_anggota = '$nim'));";

        $records = $this->executeReadSQL($sql);

        if(count($records) < 1)
            return null;

        return $records;
    }
}
