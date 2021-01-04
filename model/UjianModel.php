<?php


namespace model;


use m\Model;
use m\Util;
use model\special\PengaturanModel;

class UjianModel extends Model
{
    const JENIS_SEMINAR_PROPOSAL = 'seminar_proposal';
    const JENIS_UJIAN_AKHIR = 'ujian_akhir';

    public function __construct()
    {
        parent::__construct('v2_ujian');
    }

    public function findCurrentActiveUjianAkhir()
    {
        $sql = "SELECT * FROM {$this->tableName};";

        $result = $this->executeReadSQL($sql);

        if(count($result) < 1)
            return null;

        return $result;
    }

    public function findRekapUjianTerjadwal($jenisUjian, $strColumns = '*', array $filterAssoc = null, $currentPeriodOnly = true)
    {
        $where = "WHERE jenis_ujian = '$jenisUjian' ";

        if($filterAssoc != null)
        {
            Util::arrayAssocRemoveEmptyElements($filterAssoc);

            if(count($filterAssoc) > 0)
            {
                $where .= 'AND ';

                $i = 0;

                foreach ($filterAssoc as $field => $value)
                {
                    if($field == 'judul_proposal')
                        $where .= "$field LIKE '%$value%'";
                    else
                        $where .= "$field = '$value'";

                    if($i < count($filterAssoc) - 1)
                        $where .= ' AND ';

                    $i++;
                }
            }
        }

        // Ganti WHERE =, menjadi WHERE LIKE..
        $where = self::replaceWhereClauseOperand($where, $filterAssoc, 'judul_proposal');
        $where = self::replaceWhereClauseOperand($where, $filterAssoc, 'nama_pengusul');

        if($currentPeriodOnly)
        {
            $periodeProposal = PengaturanModel::getInstance()->getTahunProposalSekarang();

            $where .= "AND periode_proposal = '$periodeProposal'";
        }

        $sql = "SELECT $strColumns FROM v2_rekap_ujian_terjadwal $where;";

        //pre_print($sql, true);

        $result = $this->executeReadSQL($sql);

        if(count($result) < 1)
            return null;

        return $result;
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

    public function findRekapPendaftaranUjianAkhir($filterValues = null)
    {
        $where = '';

        if($filterValues != null)
        {
            Util::arrayAssocRemoveEmptyElements($filterValues);

            $where = $this->createWhereClause($filterValues);

            $where = self::replaceWhereClauseOperand($where, $filterValues, 'judul_proposal');
            $where = self::replaceWhereClauseOperand($where, $filterValues, 'nama_pengusul');

            /*
            if(isset($filterValues['judul_proposal']))
            {
                $where = str_replace(
                    "judul_proposal = '{$filterValues['judul_proposal']}'",
                    "judul_proposal LIKE '%{$filterValues['judul_proposal']}%'",
                    $where
                );
            }

            if(isset($filterValues['nama_pengusul']))
            {
                $where = str_replace(
                    "nama_pengusul = '{$filterValues['nama_pengusul']}'",
                    "nama_pengusul LIKE '%{$filterValues['nama_pengusul']}%'",
                    $where
                );
            }
            */
        }

        $sql = "SELECT id_event, tahap, id_proposal AS `nomor_la_skripsi`, judul_proposal, nama_pengusul, nama_anggota, nama_pembimbing_1, nama_pembimbing_2, status_persetujuan_pembimbing_1, status_persetujuan_pembimbing_2, kode_prodi_pengusul  FROM v2_rekap_pendaftaran_ujian_akhir {$where} ORDER BY id_event ASC;";

        $result = $this->executeReadSQL($sql);

        if(count($result) > 0)
            return $result;

        return null;
    }

    public function addNew($idEvent, $idProposal)
    {
        $sql = "INSERT INTO {$this->tableName} (id_event, id_proposal) VALUES ('$idEvent', '$idProposal');";

        return $this->executeWriteSQL($sql);
    }

    public function findByIdEventAndIdProposal($idEvent, $idProposal)
    {
        $sql = "SELECT * FROM {$this->tableName} WHERE id_event = '$idEvent' AND id_proposal = '$idProposal'";

        $records = $this->executeReadSQL($sql);

        if(count($records) < 1)
            return null;

        return $records;
    }

    public function reloadFullyApprovedPendaftaran()
    {
        $sql = "INSERT INTO {$this->tableName} (id_event, id_proposal) 
SELECT id_event, id_proposal FROM v2_pendaftaran_ujian_akhir 
WHERE status_persetujuan_pembimbing_1 = 'disetujui' 
  AND status_persetujuan_pembimbing_2 = 'disetujui' 
  AND (id_event, id_proposal) NOT IN (SELECT id_event, id_proposal FROM v2_ujian);";

        return $this->executeWriteSQL($sql);
    }

    public function findRekapUjianAkhirByIdUjian($idUjian)
    {
        $sql = "SELECT * FROM v2_rekap_ujian_akhir WHERE nomor_ujian = '$idUjian' LIMIT 1;";

        $records = $this->executeReadSQL($sql);

        if(count($records) < 1)
            return null;

        return $records[0];
    }

    public function countUjianTerjadwalByIdEvent($idEvent)
    {
        $sql = "SELECT COUNT(*) as jumlah FROM v2_rekap_ujian_terjadwal WHERE id_event = '$idEvent';";

        $result = $this->executeReadSQL($sql);

        $count = 0;

        if(count($result) > 0)
        {
            if(isset($result[0]['jumlah']))
                $count = $result[0]['jumlah'];
        }

        return $count;
    }

    public function findRekapSeminarProposalByIdUjian($idUjian)
    {
        $sql = "SELECT * FROM v2_rekap_seminar_proposal WHERE nomor_ujian = '$idUjian' LIMIT 1;";

        $records = $this->executeReadSQL($sql);

        if(count($records) < 1)
            return null;

        return $records[0];
    }
}