<?php


namespace controller\dosen;


use lib\AppController;
use lib\AppUtil;
use m\Application;
use model\CredentialModel;
use model\LogBimbinganModel;

class VerifikasiLogBimbinganController extends AppController
{
    private $_mLogBimbingan;
    private $_mCredential;

    public function __construct(Application $application)
    {
        parent::__construct($application);

        $this->_mLogBimbingan = new LogBimbinganModel();
        $this->_mCredential = new CredentialModel();
    }

    public function _currentDosenId()
    {
        $username = AppUtil::getCurrentUsername($this);

        $dosen = $this->_mCredential->findDosen($username);

        return $dosen['id'];
    }

    public function index()
    {
        $this->accessControl()->inspect();

        $this->_setupView();

        $actionResult = $this->_handleAction();

        $this->view->appendData(array('error_message' => $actionResult));

        $existing = $this->_mLogBimbingan->findAllByDosenId($this->_currentDosenId());

        if($existing != null)
        {
            $existing = $this->_addActionLinks($existing);

            $this->view->appendData(array(
                'headers'        => AppUtil::toTableDisplayedHeaders($existing),
                'displayed_data' => $existing
            ));
        }

        $this->view->render();
    }

    private function _handleAction()
    {
        $action = isset($_GET['action']) ? $_GET['action'] : null;
        $logId  = isset($_GET['id']) ? $_GET['id'] : null;

        $actionResult = '';

        if($action == null || $logId == null)
            return $actionResult;

        if($action == 'reject')
        {
            if ($this->_mLogBimbingan->changeStatus($logId, LogBimbinganModel::STATUS_DITOLAK) !== false)
                $actionResult = "Status log bimbingan dengan ID $logId telah diubah menjadi: <strong>ditolak</strong>";
            else
                $actionResult = "Status log bimbingan gagal diubah!";
        }
        else if($action == 'accept')
        {
            if($this->_mLogBimbingan->changeStatus($logId, LogBimbinganModel::STATUS_DISETUJUI) !== false)
                $actionResult = "Status log bimbingan dengan ID $logId telah diubah menjadi: <strong>diterima</strong>";
            else
                $actionResult = "Status log bimbingan gagal diubah!";
        }
        else
            $actionResult = 'Aksi gagal dijalankan!';

        return $actionResult;
    }

    private function _setupView()
    {
        $this->view->setContentTemplate('/common/data_table_display_template.php');
        $pageTitle = 'Verifikasi Log Bimbingan';
        $description = "Mahasiswa yang sudah terverifikasi bimbingan sebanyak 8 kali oleh kedua dosen pembimbing, berhak maju ujian tahap-1 (Sebelum pendaftaran tahap tersebut ditutup)";

        $this->view->appendData([
            'page_title'       => $pageTitle,
            'page_description' => $description
        ]);
    }

    private function _addActionLinks($records)
    {
        $result = array();

        foreach ($records as $row)
        {
            $rejectURL = $this->application()->getRoute()->toURL("?action=reject&id={$row['id']}");
            $acceptURL = $this->application()->getRoute()->toURL("?action=accept&id={$row['id']}");

            $row['aksi'] = '';

            if($row['status'] != LogBimbinganModel::STATUS_DISETUJUI)
            {
                $row['aksi'] = <<< PHPHREDOC
<a href="$rejectURL">Tolak</a>&nbsp;<a href="$acceptURL">Terima</a>
PHPHREDOC;
            }

            $result[] = $row;
        }

        return $result;
    }
}