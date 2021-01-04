<?php


namespace controller\dosen;


use lib\AppController;
use lib\AppUtil;
use m\Application;
use model\AppUploadedFileModel;
use model\CredentialModel;
use model\RevisiUjianAkhirModel;

class VerifikasiRevisiUjianAkhirController extends AppController
{
    // Param Data
    private $_action;
    private $_idUjian;
    private $_statusPenguji;

    private $_mRevisiUjianAkhir;
    private $_currentIdDosen;
    /**
     * @var array|null
     */
    private $_pendingRevisiList;

    public function __construct(Application $application)
    {
        parent::__construct($application);

        $this->_mRevisiUjianAkhir = new RevisiUjianAkhirModel();
    }

    public function index()
    {
        $this->accessControl()->inspect();

        $this->_retrieveParamData();

        $this->_retrieveData();

        $this->_handleAction();

        $this->_renderView();
    }

    private function _retrieveParamData()
    {
        $this->_action = isset($_GET['action']) ? $_GET['action'] : null;
        $this->_idUjian = isset($_GET['id_ujian']) ? $_GET['id_ujian'] : null;
        $this->_statusPenguji = isset($_GET['status_penguji']) ? $_GET['status_penguji'] : null;
    }

    private function _retrieveData()
    {
        $username = AppUtil::getCurrentUsername($this);

        $dosen = (new CredentialModel())->findDosen($username);

        $this->_currentIdDosen = $dosen['id'];

        $this->_pendingRevisiList = $this->_mRevisiUjianAkhir->findAllByIdPenguji($this->_currentIdDosen);
    }

    private function _renderView()
    {
        if($this->_pendingRevisiList != null)
            $this->_pendingRevisiList = $this->_toNicerTableArray($this->_pendingRevisiList);

        if($this->_pendingRevisiList != null)
        {
            $headers = AppUtil::toTableDisplayedHeaders($this->_pendingRevisiList);

            $this->view->appendData(['headers' => $headers]);
            $this->view->appendData(['displayed_data' => $this->_pendingRevisiList]);
        }

        $this->view->appendData(['page_title' => 'Verifikasi Revisi Ujian Akhir']);
        $this->view->appendData(['page_description' => 'Berikut ini adalah judul yang telah Anda uji. Berikan persetujuan dengan cara mengklik link <b>Setujui</b> di setiap baris pada kolom Aksi. Jika link tersebut tidak muncul (√) maka berarti revisi tersebut sudah Anda verifikasi.']);
        $this->view->setContentTemplate('/common/data_table_display_template.php');
        $this->view->render();
    }

    private function _toNicerTableArray(array $pendingRevisiList)
    {
        $new = array();

        $ufm = new AppUploadedFileModel();

        foreach ($pendingRevisiList as $row)
        {
            $row['file_laporan_final'] = $ufm->createFileLink($row['file_laporan_final'], true);
            $row['file_draft_publikasi_final'] = $ufm->createFileLink($row['file_draft_publikasi_final'], true);

            $statusPenguji = $this->_determineStatusPenguji($row);
            $acceptURL = $this->_createAcceptUrl($statusPenguji, $row);
            $row['aksi'] = $acceptURL;

            $new[] = $row;
        }

        return $new;
    }

    private function _determineStatusPenguji($row)
    {
        if($this->_currentIdDosen == $row['id_dosen_penguji_1'])
            return 'penguji_1';

        if($this->_currentIdDosen == $row['id_dosen_penguji_2'])
            return 'penguji_2';

        return null;
    }

    private function _handleAction()
    {
        if($this->_action == 'accept')
        {
            $this->_mRevisiUjianAkhir->approve($this->_idUjian, $this->_currentIdDosen, $this->_statusPenguji);

            $this->view->appendData(['error_message' => "Revisi dengan nomor {$this->_idUjian} sudah disetujui!"]);

            // Refresh list right after the action link has clicked
            $this->_pendingRevisiList = $this->_mRevisiUjianAkhir->findAllByIdPenguji($this->_currentIdDosen);
        }
    }

    private function _createAcceptUrl($statusPenguji, $row)
    {
        $column = 'status_persetujuan_' . $statusPenguji;

        if($row[$column] != 'disetujui')
        {
            $acceptURL = $this->application()->getRoute()->toURL("?action=accept&id_ujian={$row['id_ujian']}&status_penguji={$statusPenguji}");

            $acceptURL = '<a href="' . $acceptURL . '">Setujui</a>';
        }
        else
            $acceptURL = '√';

        return $acceptURL;
    }

}