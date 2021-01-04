<?php


namespace controller\dosen;


use lib\AppUtil;
use lib\CommonTemplateViewData;
use lib\DataViewerController;
use model\BimbinganModel;
use model\CredentialModel;
use model\PengajuanPembimbingModel;
use model\TopikModel;

class PersetujuanPengajuanPembimbingController extends DataViewerController
{
    const ACTION_TYPE_REJECT = 'reject';
    const ACTION_TYPE_ACCEPT = 'accept';

    // Model
    private $_mPengajuanPembimbing;
    private $_mTopik;
    private $_mBimbingan;

    // Data
    private $_currentDosen;

    // Action Params
    private $_actionType;
    private $_selectedNimPengusul;
    private $_selectedIdTopik;

    protected function getIndexData($filterValues = null)
    {
        $this->_mPengajuanPembimbing = new PengajuanPembimbingModel();
        $this->_mTopik = new TopikModel();
        $this->_mBimbingan = new BimbinganModel();

        $this->_resolveActionParams();

        // Perform accept or reject if necessary
        $this->_handleAction();

        $data = $this->_mPengajuanPembimbing->findAllRekapByDosenId($this->_currentDosen['id']);

        if($data != null)
            $data = $this->_addActionLinks($data);

        return $data;
    }

    protected function getIndexViewData()
    {
        // getIndexViewData is called earlier than getIndexData. So this line is put here..
        $this->_determineCurrentDosen();

        $acceptedCount = (new PengajuanPembimbingModel())->findAcceptedCount($this->_currentDosen['id']);

        $vd = new CommonTemplateViewData();

        $vd->setPageTitle('Persetujuan Pengajuan Pembimbing');
        $vd->setPageDescription('
Berikut ini adalah pengajuan dosen pembimbing yang ditujukan kepada Anda.
<p>
<b><u>Catatan</u></b>:
<ul>
<li>Mohon untuk tidak menerima pengajuan bimbingan melebihi kuota yang telah ditentukan jurusan, karena untuk saat ini belum ada validasi jumlah kuota di fitur ini.</li>
<li>Ketika sebuah pengajuan yang topiknya adalah topik usulan Anda disetujui, maka status topik Anda tersebut akan diubah menjadi <strong>diklaim</strong>. Sehingga tidak akan bisa diajukan oleh kelompok lain.</li>
<li>Apabila sebuah pengajuan tidak ditanggapi, maka mahasiswa yang mengajukan tersebut <strong>tidak akan bisa membuat pengajuan baru</strong>. Oleh karena itu harap segera tanggapi pengajuan tersebut, baik itu ditolak maupun disetujui.</li>
</ul>
</p>
<p>
Jumlah permohonan yang sudah Anda setujui: <strong>' . $acceptedCount . '</strong>
</p>
');

        return $vd;
    }

    private function _determineCurrentDosen()
    {
        $username = AppUtil::getCurrentUsername($this);

        $this->_currentDosen = (new CredentialModel())->findDosen($username);
    }

    private function _addActionLinks(array $data)
    {
        $result = array();

        $currentUrl = $this->getCurrentRoute()->toURL();

        $rejectUrl = "$currentUrl?action=reject";
        $acceptUrl = "$currentUrl?action=accept";

        foreach ($data as $row)
        {
            if($row['status'] == 'diajukan')
            {
                $rejectAction = "$rejectUrl&nim_pengusul={$row['nim_pengusul']}&id_topik={$row['id_topik']}";
                $acceptAction = "$acceptUrl&nim_pengusul={$row['nim_pengusul']}&id_topik={$row['id_topik']}";

                $action = '<a class="red-action-button" style="width: 50px;" href="' . $rejectAction . '">Tolak</a><br /><br /><a class="green-action-button" style="width: 50px;" href="' . $acceptAction . '">Setujui</a>';

                $row['aksi'] = $action;
            }

            $result[] = $row;
        }

        return $result;
    }

    private function _resolveActionParams()
    {
        if(isset($_GET['action']))
            $this->_actionType = $_GET['action'];

        if(isset($_GET['nim_pengusul']))
            $this->_selectedNimPengusul = $_GET['nim_pengusul'];

        if(isset($_GET['id_topik']))
            $this->_selectedIdTopik = $_GET['id_topik'];
    }

    private function _handleAction()
    {
        if($this->_actionType == self::ACTION_TYPE_REJECT)
        {
            $this->_mPengajuanPembimbing->rejectPengajuan($this->_selectedNimPengusul, $this->_selectedIdTopik, $this->_currentDosen['id']);

            $this->view->modifyData('error_message', "Pengajuan oleh NIM: {$this->_selectedNimPengusul} dengan ID Topik: {$this->_selectedIdTopik} telah Anda tolak.");
        }
        elseif($this->_actionType == self::ACTION_TYPE_ACCEPT)
        {
            // Change the status of the pengajuan pembimbing
            $this->_mPengajuanPembimbing->acceptPengajuan($this->_selectedNimPengusul, $this->_selectedIdTopik, $this->_currentDosen['id']);

            // Update status of the corresponding topic to become 'diklaim'
            $this->_mTopik->updateStatus(TopikModel::STATUS_DIKLAIM, $this->_selectedIdTopik);

            // Auto-insert to table v2_bimbingan
            $this->_registerBimbingan();

            $this->view->modifyData('error_message', "Pengajuan oleh NIM: {$this->_selectedNimPengusul} dengan ID Topik: {$this->_selectedIdTopik} telah Anda SETUJUI.");
        }
    }

    private function _registerBimbingan()
    {
        $selectedPengajuan = $this->_mPengajuanPembimbing->findOnePengajuanByPrimaryKeys($this->_selectedNimPengusul, $this->_selectedIdTopik, $this->_currentDosen['id']);

        $nimAnggota = $selectedPengajuan['nim_anggota'];

        $this->_mBimbingan->addNewPembimbingUtama($this->_currentDosen['id'], $this->_selectedNimPengusul, $nimAnggota);
    }
}