<?php


namespace model;


use m\Model;
use m\Util;

class PenilaianUjianModel extends Model
{
    public function __construct()
    {
        parent::__construct('v2_penilaian_ujian');
    }

    public function findOneByNomorUjianIdDosenAndNim($nomorUjian, $idDosen, $nim)
    {
        $sql = "SELECT * FROM {$this->tableName} WHERE id_ujian = '$nomorUjian' AND id_dosen = '$idDosen' AND nim = '$nim';";

        //pre_print($sql);

        $records = $this->executeReadSQL($sql);

        if(count($records) < 1)
            return null;

        return $records[0];
    }

    public function findRekapKeputusanUjianByNomorUjianAndNim($nomorUjian, $nim)
    {
        $sql = "SELECT * FROM v2_rekap_keputusan_ujian WHERE nomor_ujian = '$nomorUjian' AND nim = '$nim';";

        $records = $this->executeReadSQL($sql);

        if(count($records) < 1)
            return null;

        return $records[0];
    }

    public function addNewOrEdit($input)
    {
        $idUjian = $input['id_ujian'];
        $idDosen = $input['id_dosen'];
        $nim = $input['nim'];

        $existing = $this->findOneByNomorUjianIdDosenAndNim($idUjian, $idDosen, $nim);

        //pre_print($existing, true);

        if($existing == null)
        {
            $sql = $this->createSqlInsert($input);
        }
        else
        {
            $whereClause = "WHERE id_ujian = '$idUjian' AND id_dosen = '$idDosen' AND nim = '$nim'";

            $sql = $this->createSqlUpdate($input, $whereClause, ['id_ujian', 'id_dosen', 'nim']);
        }

        //pre_print($sql, true);

        $this->executeWriteSQL($sql);
    }

    public function findLatestRekapKeputusanUjianByNim($nim)
    {
        $sql = "SELECT * FROM v2_rekap_keputusan_ujian WHERE nim = '$nim' ORDER BY nomor_ujian DESC LIMIT 1;";

        $records = $this->executeReadSQL($sql);

        if(count($records) < 1)
            return null;

        return $records[0];
    }

    public function findRekapRevisiUjianMahasiswaByNomorUjian($nomorUjian)
    {
        $sql = "SELECT * FROM v2_rekap_revisi_ujian_mahasiswa WHERE nomor_ujian = '$nomorUjian';";

        $records = $this->executeReadSQL($sql);

        if(count($records) < 1)
            return null;

        return $records;
    }

    /**
     * Menemukan NIM yang terlibat pada ujian di nomor ujian. D3 bisa lebih dari 1 mahasiswa dengan nomor ujian yang sama.
     * @param $nomorUjian int|string Nomor ujian yang dicek
     * @return mixed|null Mengembalikan array 1 dimensi berisi NIM-NIM yang terlibat, jika ada dan NULL jika tidak ada.
     */
    public function findAllNimByNomorUjian($nomorUjian)
    {
        $sql = "SELECT nim FROM v2_rekap_penilaian_ujian WHERE nomor_ujian = $nomorUjian;";

        $records = $this->executeReadSQL($sql);

        if(count($records) < 1)
            return null;

        $allNim = array();

        foreach ($records as $row)
            $allNim[] = $row['nim'];

        return $allNim;
    }

    /**
     * Mengecek apakah semua dosen sudah memberikan nilai kepada NIM tertentu
     * @param $nim string NIM yang akan dicek
     * @param $nomorUjian string|int Ujian yang diikuti NIM tersebut
     * @return string String kosong jika semua nilai lengkap, string berisi info kekurangan nilai jika sebaliknya.
     */
    public function checkStatusNilaiUjianCompletion($nim, $nomorUjian)
    {
        $sql = "SELECT * FROM v2_rekap_penilaian_ujian WHERE nomor_ujian = '$nomorUjian' AND nim = '$nim' LIMIT 1;";

        $records = $this->executeReadSQL($sql);

        if(count($records) < 1)
            return "Mahasiswa dengan NIM: $nim ({$records[0]['nama']}) tercatat belum melaksanakan ujian di tahap ini.";

        if($records[0]['id_penguji_1'] == null || $records[0]['id_penguji_2'] == null)
        {
            $errorMessage = "Mahasiswa dengan NIM: $nim ({$records[0]['nama']}), nilainya belum lengkap!";

            if($records[0]['id_penguji_1'] == null)
                $errorMessage .= ' Penguji 1 belum memberikan nilai.';

            if($records[0]['id_penguji_2'] == null)
                $errorMessage .= ' Penguji 2 juga belum memberikan nilai.';

            $errorMessage .= ' Silahkan Anda hubungi penguji Anda dan ingatkan agar memberikan nilai terlebih dahulu untuk NIM tersebut via sistem!';

            return $errorMessage;
        }

        return '';
    }

    public function findAllRekapKeputusanUjianByNomorUjian($nomorUjian)
    {
        $sql = "SELECT * FROM v2_rekap_keputusan_ujian WHERE nomor_ujian = '$nomorUjian';";

        $records = $this->executeReadSQL($sql);

        if(count($records) < 1)
            return null;

        return $records;
    }

    public function findRekapYudisiumNilaiAkhir($filterValues = null)
    {
        $where = '';

        if($filterValues != null)
        {
            $where = self::filterValuesToWhereClause($filterValues);

            $where = self::replaceWhereClauseOperand($where, $filterValues, 'judul_proposal');
            $where = self::replaceWhereClauseOperand($where, $filterValues, 'nama');
        }

        $sql = "SELECT * FROM v2_rekap_yudisium_nilai_akhir $where ORDER BY nomor_la_skripsi ASC;";

        $records = $this->executeReadSQL($sql);

        if(count($records) < 1)
            return null;

        return $records;
    }

    public static function filterValuesToWhereClause($filterValues, $operand = '=')
    {
        $where = '';

        if($filterValues != null)
        {
            Util::arrayAssocRemoveEmptyElements($filterValues);

            if(count($filterValues) > 0)
            {
                $where = 'WHERE ';

                $i = 0;

                foreach ($filterValues as $field => $value)
                {
                    $where .= "$field $operand '$value'";

                    if($i < count($filterValues) - 1)
                        $where .= ' AND ';

                    $i++;
                }
            }
        }

        //pre_print($where);

        return $where;
    }

    private static function replaceWhereClauseOperand($whereString, array $filterValues, $columnName, $operand = 'LIKE')
    {
        $where = $whereString;

        if(isset($filterValues[$columnName]))
        {
            $where = str_replace(
                "$columnName = '{$filterValues[$columnName]}'",
                "$columnName LIKE '%{$filterValues[$columnName]}%'",
                $whereString
            );
        }

        return $where;
    }
}