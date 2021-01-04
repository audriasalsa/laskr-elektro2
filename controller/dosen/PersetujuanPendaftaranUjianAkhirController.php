<?php


namespace controller\dosen;


use lib\AppController;
use lib\AppUtil;
use m\Application;
//use model\BimbinganModel;
use model\CredentialModel;
//use model\LogBimbinganModel;
use model\PendaftaranUjianAkhirModel;
//use model\UjianModel;

class PersetujuanPendaftaranUjianAkhirController extends AppController
{
    const STATUS_DIAJUKAN = 'diajukan';
    const STATUS_DISETUJUI = 'disetujui';
    const ACTION_TYPE_VIEW_NILAI = 'view_nilai';
    const ACTION_TYPE_EDIT_NILAI = 'edit_nilai';
    
    //private $_mPendaftaranUjianAkhir;
    private $_mCredential;
    //private $_mBimbingan;
    //private $_mUjian;

    public function __construct(Application $application)
    {
        parent::__construct($application);

        //$this->_mPendaftaranUjianAkhir = new PendaftaranUjianAkhirModel();
        $this->_mCredential = new CredentialModel();
        //$this->_mBimbingan = new BimbinganModel();
        //$this->_mUjian = new UjianModel();
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

        $this->setupView();

        //$actionResult = $this->_handleAction();

        //$this->view->appendData(array('error_message' => $actionResult));

        $existing = $this->retrievePendaftaranData();

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

    // TODO: Clean commented blocks! This is due to the blocks have been migrated to PenilaianMajuUjianAkhirController.
    /*
    private function _handleAction()
    {
        $action           = isset($_GET['action']) ? $_GET['action'] : null;
        $idProposal       = isset($_GET['id_proposal']) ? $_GET['id_proposal'] : null;
        $idEvent          = isset($_GET['id_event']) ? $_GET['id_event'] : null;
        $statusPembimbing = isset($_GET['status_pembimbing']) ? $_GET['status_pembimbing'] : null;

        $actionResult = '';

        if($action == null || $idProposal == null || $idEvent == null)
            return $actionResult;

        if($action == 'accept')
        {
            $this->_eligibilityCheck($idProposal, $statusPembimbing);

            if($this->_mPendaftaranUjianAkhir->accept($idProposal, $idEvent, $this->_currentDosenId(), $statusPembimbing) !== false)
            {
                $actionResult = "Proposal dengan id = $idProposal telah disetujui untuk mendaftar maju ujian akhir.";

                // Jika pendaftaran ini sudah diapprove oleh semua pembimbing, masukkan ke tabel ujian..
                if($this->_mPendaftaranUjianAkhir->isFullyApproved($idProposal, $idEvent))
                {
                    $this->_mUjian->addNew($idEvent, $idProposal);

                    $actionResult .= "<br/>Pendaftaran sudah disetujui oleh kedua pembimbing dan akan segera dijadwalkan.";
                }
                else
                    $actionResult .= "<br/>Pendaftaran menunggu persetujuan dosen pembimbing lainnya untuk bisa dijadwalkan.";
            }
            else
                $actionResult = "Proposal gagal disetujui maju ujian akhir!";
        }
        else
            $actionResult = 'Aksi gagal dijalankan!';

        return $actionResult;
    }
    */

    /*
    private function _eligibilityCheck($idProposal, $statusPembimbingCheck)
    {
        $statusPembimbingRiil = $this->_mBimbingan->findStatusDosenPembimbing($this->_currentDosenId(), $idProposal);

        if(!$statusPembimbingRiil == $statusPembimbingCheck)
            $this->renderErrorAndExit("Anda bukan pembimbing dari proposal dengan id: $idProposal.");
    }
    */

    private function _addActionLinks($records)
    {
        $result = array();

        foreach ($records as $row)
        {
            //$acceptURL = $this->application()->getRoute()->toURL("?action=accept&id_proposal={$row['id_proposal']}&id_event={$row['id_event']}&status_pembimbing={$row['status_pembimbing']}");
            if($row['status_persetujuan_pembimbing'] == self::STATUS_DISETUJUI)
            {
                $action = self::ACTION_TYPE_VIEW_NILAI;
                $urlCaption = 'Lihat Nilai';
            }
            else
            {
                $action = self::ACTION_TYPE_EDIT_NILAI;
                $urlCaption = 'Izinkan Maju';
            }

            $penilaianUrl = $this->createActionUrl($action, $row);

            $row['aksi'] = '<a href="' . $penilaianUrl . '">' . $urlCaption . '</a>';

            // TODO: Uncomment below lines when the implementation of penilaian penguji is finished!
            /*
            $row['aksi'] = '√';

            if($row['status_persetujuan_pembimbing'] != PendaftaranUjianAkhirModel::STATUS_DISETUJUI)
            {
                $row['aksi'] = <<< PHPHREDOC
<a href="$acceptURL">Izinkan Maju</a>
PHPHREDOC;
            }
            */

            // Add color to status
            $row['status_persetujuan_pembimbing'] = self::_colorizeStatusPersetujuan($row['status_persetujuan_pembimbing']);

            $result[] = $row;
        }

        return $result;
    }

    private static function _colorizeStatusPersetujuan($status)
    {
        if($status == 'diajukan')
            return "<a style='color: #C62828;'>$status</a>";
        else
            return "<a style='color: #419c5d;'>$status</a>";
    }

    protected function retrievePendaftaranData()
    {
        return (new PendaftaranUjianAkhirModel())->findRekapPersetujuanPendaftaranUjianAkhirByIdDosen($this->_currentDosenId());
    }

    protected function setupView()
    {
        $this->view->setContentTemplate('/common/data_table_display_template.php');
        $pageTitle = 'Persetujuan Pendaftaran Ujian Akhir';
        $description = 'Berikut ini adalah daftar mahasiswa yang akan mendaftar ujian akhir. Apabila tidak Anda setujui, maka mahasiswa tersebut tidak akan dijadwalkan untuk maju ujian akhir.';

        $this->view->appendData([
            'page_title'       => $pageTitle,
            'page_description' => $description
        ]);
    }

    protected function createActionUrl($actionType, $row)
    {
        $actionParam = "action=$actionType&id_proposal={$row['id_proposal']}&id_event={$row['id_event']}&status_pembimbing={$row['status_pembimbing']}";

        $penilaianUrl = $this->application()->getRoute()->toURL("/penilaian-maju-ujian?{$actionParam}");

        return $penilaianUrl;
    }
}