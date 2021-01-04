<?php


namespace model;


use m\Model;

class BeritaAcaraUjianModel extends Model
{
    public function __construct()
    {
        parent::__construct('v2_berita_acara_ujian');
    }

    public function findOneByNomorUjian($nomorUjian)
    {
        $sql = "SELECT * FROM {$this->tableName} WHERE id_ujian = '$nomorUjian';";

        $records = $this->executeReadSQL($sql);

        if(count($records) < 1)
            return null;

        return $records[0];
    }

    public function findOneByNomorUjianAndNim($nomorUjian, $nim)
    {
        $sql = "SELECT * FROM {$this->tableName} WHERE id_ujian = '$nomorUjian' AND nim = '$nim';";

        $records = $this->executeReadSQL($sql);

        if(count($records) < 1)
            return null;

        return $records[0];
    }

    public function findAllByNomorUjian($nomorUjian)
    {
        $sql = "SELECT * FROM {$this->tableName} WHERE id_ujian = '$nomorUjian';";

        $records = $this->executeReadSQL($sql);

        if(count($records) < 1)
            return null;

        return $records;
    }

    public function addNewKosongan($nomorUjian, $nim)
    {
        $sql = "INSERT INTO {$this->tableName} (id_ujian, nim) VALUES ('$nomorUjian', '$nim');";

        $this->executeWriteSQL($sql);
    }

    public function ttd($nomorUjian, $nim, $type = 'mahasiswa', $idDosen = null)
    {
        $existing = $this->findOneByNomorUjianAndNim($nomorUjian, $nim);

        if($existing == null) // Tambah baru
        {
            $this->addNewKosongan($nomorUjian, $nim);

            $existing = $this->findOneByNomorUjianAndNim($nomorUjian, $nim);
        }

        $now = date('Y-m-d H:i:s');

        if($type != 'mahasiswa')
        {
            if($idDosen == null)
                throw new \Exception('Berita Acara akan ditandatangani oleh dosen, namun ID dosennya tidak ada.');

            $sql = "UPDATE {$this->tableName} SET ";

            switch ($type)
            {
                case 'penguji_1':
                    $sql .= "id_penguji_1_riil = '$idDosen', waktu_ttd_penguji_1 = '$now'";
                    break;
                case 'penguji_2':
                    $sql .= "id_penguji_2_riil = '$idDosen', waktu_ttd_penguji_2 = '$now'";
                    break;
                case 'moderator':
                    $sql .= "id_moderator_riil = '$idDosen', waktu_ttd_moderator = '$now'";
                    break;
                default:
                    throw new \Exception("Tipe tanda tangan [$type] tidak dikenali!");
            }
        }
        else
        {
            $sql = "UPDATE {$this->tableName} SET waktu_ttd_mahasiswa = '$now'";
        }

        $sql .= " WHERE id_ujian = '$nomorUjian' AND nim = '$nim';";

        //pre_print($sql, true);

        $this->executeWriteSQL($sql);

        //$updated = $this->findOneByNomorUjianAndNim($nomorUjian, $nim);

        //return $updated;
    }

    public function findRekapBeritaAcaraUjianByNim($nim, $columnSelections = '*', array $filter = array())
    {
        $filterClauses = $this->createFilterClause($filter);

        if($filterClauses != '')
            $filterClauses = "AND $filterClauses";

        $sql = "SELECT {$columnSelections} FROM v2_rekap_berita_acara_ujian WHERE nim = '$nim' {$filterClauses};";

        $records = $this->executeReadSQL($sql);

        if(count($records) < 1)
            return null;

        return $records;
    }

    protected function createFilterClause(array $keyValuePairs, $operand = '=', $logic = 'AND')
    {
        $count = count($keyValuePairs);

        if($keyValuePairs == null || $count < 1)
            return '';

        $filter = '';

        $i = 0;

        foreach ($keyValuePairs as $key => $value)
        {
            $filter .= "$key $operand $value";

            if($i < ($count - 1))
                $filter .= " $logic ";

            $i++;
        }

        return $filter;
    }

    public function findOneRekapKelulusanUjianByIdProposalAndNim($idProposal, $nim)
    {
        $sql = "SELECT * FROM v2_rekap_kelulusan_ujian WHERE id_proposal = '$idProposal' AND nim = '$nim' LIMIT 1;";

        $records = $this->executeReadSQL($sql);

        if(count($records) < 1)
            return null;

        return $records[0];
    }
}