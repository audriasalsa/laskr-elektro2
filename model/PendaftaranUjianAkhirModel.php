<?php


namespace model;


use m\Model;
use m\Util;

class PendaftaranUjianAkhirModel extends Model
{
    const STATUS_DIAJUKAN = 'diajukan';
    const STATUS_DISETUJUI = 'disetujui';

    const STATUS_PEMBIMBING_1 = 'Pembimbing-1';
    const STATUS_PEMBIMBING_2 = 'Pembimbing-2';

    public function __construct()
    {
        parent::__construct('v2_pendaftaran_ujian_akhir');
    }

    public function findOneByIdProposalAndIdEvent($idProposal, $idEvent)
    {
        $sql = "SELECT * FROM {$this->tableName} WHERE id_proposal = '$idProposal' AND id_event = '$idEvent' LIMIT 1";

        $result = $this->executeReadSQL($sql);

        if($result != null && count($result) > 0)
            return $result[0];

        return null;
    }

    /*
    Array
    (
        [id_proposal] =>
        [id_event] =>
        [file_laporan_akhir] => a979355b1523ea78a53b8ca35747db20.pdf
        [file_presentasi] => 438314d1c8b2a56638ac0e01c2a61055.pdf
        [file_draft_publikasi] => b90880f64f11a348d3e2c20f0d616340.pdf
        [link_demo] =>
        [status_persetujuan_pembimbing_1] =>
        [status_persetujuan_pembimbing_2] =>
    )
    */
    public function addOrEdit(array $data)
    {
        $idProposal = $data['id_proposal'];
        $idEvent    = $data['id_event'];

        $existing = $this->findOneByIdProposalAndIdEvent($idProposal, $idEvent);

        if($existing == null)
        {
            $sql = $this->createSqlInsert($data);
        }
        else
        {
            $ignores = Util::arrayAssocCollectEmptyFields($data);

            $sql = $this->createSqlUpdate(
                $data,
                "WHERE id_proposal = '$idProposal' AND id_event = '$idEvent'",
                $ignores
            );
        }

        $this->executeWriteSQL($sql);
    }

    public function findRekapPersetujuanPendaftaranUjianAkhirByIdDosen($idDosen)
    {
        $sql = "SELECT id_event, tahap, id_proposal, judul_proposal, nim_pengusul, nama_pengusul, status_persetujuan_pembimbing, status_pembimbing FROM v2_rekap_persetujuan_pendaftaran_ujian_akhir WHERE id_pembimbing = '$idDosen';";

        $result = $this->executeReadSQL($sql);

        if(count($result) > 0)
            return $result;

        else
            return null;
    }

    public function accept($idProposal, $idEvent, $idPembimbing, $statusPembimbing)
    {
        if(!is_numeric($idProposal) || !is_numeric($idEvent) || !is_numeric($idPembimbing))
            return false;

        if($statusPembimbing == null)
            return false;

        $statusPersetujuanColumn = $statusPembimbing == self::STATUS_PEMBIMBING_1 ? 'status_persetujuan_pembimbing_1' : 'status_persetujuan_pembimbing_2';

        $sql = "UPDATE {$this->tableName} SET `$statusPersetujuanColumn` = 'disetujui' WHERE id_proposal = '$idProposal' AND id_event = '$idEvent'";

        pre_print($sql, true);

        //return $this->executeWriteSQL($sql);
    }

    public function findRekapPendaftaranUjianAkhir()
    {
        $sql = "SELECT id_event, tahap, id_proposal AS `nomor_la_skripsi`, judul_proposal, nama_pengusul, nama_anggota, nama_pembimbing_1, nama_pembimbing_2, status_persetujuan_pembimbing_1, status_persetujuan_pembimbing_2, kode_prodi_pengusul  FROM v2_rekap_pendaftaran_ujian_akhir ORDER BY id_event ASC;";

        $result = $this->executeReadSQL($sql);

        if(count($result) > 0)
            return $result;

        return null;
    }

    public function isFullyApproved($idProposal, $idEvent)
    {
        $pendaftaran = $this->findOneByIdProposalAndIdEvent($idProposal, $idEvent);

        if($pendaftaran == null)
            return null;

        $approval1 = $pendaftaran['status_persetujuan_pembimbing_1'];
        $approval2 = $pendaftaran['status_persetujuan_pembimbing_2'];

        return $approval1 == 'disetujui' && $approval2 == 'disetujui';
    }
}