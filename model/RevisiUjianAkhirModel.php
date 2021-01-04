<?php


namespace model;


use m\Model;
use m\Util;

class RevisiUjianAkhirModel extends Model
{
    public function __construct()
    {
        parent::__construct('v2_revisi_ujian_akhir');
    }

    public function findOneByIdUjian($idUjian)
    {
        $sql = "SELECT * FROM {$this->tableName} WHERE id_ujian = '$idUjian' LIMIT 1;";

        $records = $this->executeReadSQL($sql);

        if(count($records) < 1)
            return null;

        return $records[0];
    }

    public function addOrEdit(array $revisiData)
    {
        $existing = $this->findOneByIdUjian($revisiData['id_ujian']);

        if($existing == null) // INSERT
        {
            $sql = $this->createSqlInsert($revisiData);
        }
        else // UPDATE
        {
            $where = $this->createWhereClause(Util::arrayAssocTakeSomeKeys($revisiData, ['id_ujian', 'id_proposal']));

            Util::arrayAssocRemoveEmptyElements($revisiData);

            $sql = $this->createSqlUpdate($revisiData, $where, ['id_ujian', 'id_proposal']);
        }

        $this->executeWriteSQL($sql);
    }

    public function findAllByIdPenguji($idPenguji)
    {
        // TODO: Make it to be a view rather than explicit JOIN statement
        $sql = "SELECT t.id_ujian, rut.tahap AS tahap_ujian_akhir, t.id_proposal, rp.nim_pengusul, rp.nama_pengusul, t.judul_final, t.file_laporan_final, t.file_draft_publikasi_final, t.id_dosen_penguji_1, t.id_dosen_penguji_2, t.status_persetujuan_penguji_1, t.status_persetujuan_penguji_2 FROM {$this->tableName} t INNER JOIN v2_rekap_ujian_terjadwal rut ON t.id_ujian = rut.nomor_ujian INNER JOIN v2_rekap_proposal rp ON rp.id = t.id_proposal WHERE t.id_dosen_penguji_1 = '$idPenguji' OR t.id_dosen_penguji_2 = '$idPenguji'";
        //$sql = "SELECT t.*, e.nama AS tahap_ujian FROM {$this->tableName} t INNER JOIN WHERE t.id_dosen_penguji_1 = '$idPenguji' OR t.id_dosen_penguji_2 = '$idPenguji'";

        $records = $this->executeReadSQL($sql);

        if(count($records) < 1)
            return null;

        return $records;
    }

    public function approve($idUjian, $idDosen, $statusPenguji)
    {
        switch ($statusPenguji)
        {
            case 'penguji_1':
                {
                    $columnSet = 'status_persetujuan_penguji_1';
                    $columnWhere = 'id_dosen_penguji_1';
                }
                break;
            case 'penguji_2':
                {
                    $columnSet = 'status_persetujuan_penguji_2';
                    $columnWhere = 'id_dosen_penguji_2';
                }
                break;
            default:
            {
                $columnSet = 'undefined_column';
                $columnWhere = 'undefined_column';
            }
        }

        $sql = "UPDATE {$this->tableName} SET $columnSet = 'disetujui' WHERE id_ujian = '$idUjian' AND $columnWhere = '$idDosen'";

        $this->executeWriteSQL($sql);
    }
}