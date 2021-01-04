<?php


namespace controller\mahasiswa;


use lib\AppController;
use lib\AppUtil;
use m\Application;
use m\extended\AuthPolicy;
use m\extended\FormValidation;
use model\AppUploadedFileModel;
use model\BimbinganModel;
use model\DosenModel;
use model\EventModel;
use model\HasilSemproModel;
use model\MahasiswaModel;
use model\ProposalModel;

class PendaftaranSemproNewController extends AppController
{
    private $_mProposal;
    private $_mUploadedFile;
    private $_mDosen;
    private $_mMahasiswa;
    private $_mBimbingan;
    private $_mEvent;
    private $_mHasilSempro;

    public function __construct(Application $application)
    {
        parent::__construct($application);

        $this->_mProposal = new ProposalModel();
        $this->_mUploadedFile = new AppUploadedFileModel();
        $this->_mDosen = new DosenModel();
        $this->_mMahasiswa = new MahasiswaModel();
        $this->_mBimbingan = new BimbinganModel();
        $this->_mEvent = new EventModel();
        $this->_mHasilSempro = new HasilSemproModel();
    }

    public function index()
    {
        $this->accessControl()->inspect();

        $viewData = array(
            'page_title'       => 'Pendaftaran Seminar Proposal',
            'page_description' => 'Klik pada tombol <i>Daftar</i> untuk melakukan pendaftaran',
        );

        $events = $this->_mEvent->findByKategori('seminar_proposal');

        // $actionRootUrl = $this->view->homeAddress('/proposal/tes');

        // $events = AppUtil::tableLikeArrayAddActionLink($events, $actionRootUrl, ['id'], 'Daftar');

        if($events != null)
        {
            $events = $this->_addActionLinkToSemproEvents($events);
            $viewData['headers'] = AppUtil::toTableDisplayedHeaders($events);
            $viewData['displayed_data'] = $events;
        }

        $this->view->setData($viewData);
        $this->view->setContentTemplate('/common/data_table_display_template.php');
        $this->view->render();
    }

    private function _addActionLinkToSemproEvents($semproEvents)
    {
        $updated = array();

        foreach ($semproEvents as $event)
        {
            $detailUrl = $this->view->homeAddress('/proposal/detail-pendaftaran-sempro');

            $eventFinished = AppUtil::dateIsPassed($event['tanggal_selesai']);

            if($eventFinished)
            {
                $detailUrl .= "?id_event={$event['id']}&action=view";

                $event['action'] = AppUtil::createActionLink($detailUrl, 'Lihat Detail');
            }
            else
            {
                $detailUrl .= "?id_event={$event['id']}&action=edit";

                $event['action'] = AppUtil::createActionLink($detailUrl, 'Daftar Sekarang!');
            }

            $updated[] = $event;
        }

        return $updated;
    }

    public function detail()
    {
        $this->accessControl()->inspect();

        $this->view->setContentTemplate('/proposal/pendaftaran_sempro_template.php');

        // Check event Id-nya valid apa tidak
        // Bisa ada di URL (GET) ketika dibuka dari URL, bisa POST ketika submit form
        $idEvent = null;
        if(isset($_GET['id_event'])) $idEvent = $_GET['id_event'];
        if(isset($_POST['hid_id_event'])) $idEvent = $_POST['hid_id_event'];

        // Ambil info event sempro saat ini.
        $eventData = $this->_mEvent->findById($idEvent);

        // Jika tidak valid langsung STOP!
        if($eventData == null) $this->_forceRenderErrorMessage(
            'Kode event tidak valid! Jangan mengedit-edit parameter URL. Jika data Anda corrupt, jangan salahkan panitia!',//
            array('hide_forms' => true)
        );

        $this->view->appendData(['action_url_param' => "?id_event=$idEvent"]);
        $this->view->appendData(['event_data' => $eventData]);

        // TODO: WARNING! NIM by Username!
        $currentNim = AppUtil::getCurrentUsername($this);

        // Cek apakah user sudah mengentrikan data proposal
        //$proposal = $this->_mProposal->findByNimPengusul($currentNim);
        $proposal = $this->_retrieveCurrentProposal();

        // Jika user belum punya proposal, STOP!
        if($proposal == null) $this->_forceRenderErrorMessage(
            'Anda tidak bisa mendaftar, karena Anda belum mengentrikan data proposal. Entrilah terlebih dahulu di menu Proposal -> Entri Data Proposal.',
            array('hide_forms' => true)
        );

        // Cek apakah sudah pernah mendaftar
        $terdaftar = $this->_mProposal->findPendaftaranSemproByUsernameAndIdEvent($this->_getCurrentUsername(), $idEvent);

        // Cek event active atau tidak
        $eventIsActive = EventModel::isActive($eventData);

        if($eventIsActive)
        {
            // Cek apakah sudah lulus
            $pass = $this->_mHasilSempro->sudahLulus($currentNim);

            // Jika sudah pernah lulus, STOP!
            if($pass) $this->_forceRenderErrorMessage(
                'Anda tidak bisa mendaftar lagi karena Anda sudah pernah lulus sempro. Apakah Anda ingin ujian lagi? Jika ya, maka silahkan hubungi panitia.',
                array('hide_forms' => true)
            );

            $existingJudul     = $proposal['judul_proposal'];
            $existingGrupRiset = ProposalModel::translateGrupRiset($proposal['nama_grup_riset']);

            $this->view->appendData(['existing_judul' => $existingJudul]);
            $this->view->appendData(['existing_grup_riset' => $existingGrupRiset]);

            // Simpan pendaftaran sempro pada event ini.
            if(isset($_POST['submit']))
            {
                // Pada titik ini $terdaftar masih null karena user belum mendaftar pada tahap yang aktif saat ini
                $this->_savePendaftaranSempro($proposal, $idEvent);

                // Untuk itu, setelah pendaftaran berhasil, perlu direfresh agar form tidak muncul lagi, melainkan diganti dengan data display biasa.
                $terdaftar = $this->_mProposal->findPendaftaranSemproByUsernameAndIdEvent($this->_getCurrentUsername(), $idEvent);
                // TODO: Ini bisa digunakan sebagai flag untuk menentukan apakah daftarnya berhasil atau gagal.
            }
        }
        else
        {
            if($terdaftar == null) $this->_forceRenderErrorMessage(
                'Anda tidak mendaftar pada tahap ini.',
                array('hide_forms' => true)
            );
        }

        $this->view->appendData(['proposal_terdaftar' => $terdaftar]);
        $this->view->appendData(['upload_directory' => $this->_mUploadedFile->getRelativeUploadDirectory()]);

        $this->view->render();
    }

    private function _savePendaftaranSempro($etriedProposal, $idEvent)
    {
        pre_print($_POST);
        /*
         * Array
           (
               [txt_judul] => tessst
               [cbx_grup_riset] =>
               [submit] => Daftar!
               [hid_id_event] => 16
           )
         */

        $fv = new FormValidation($this->_formFields, true);

        if($fv->submitted())
        {
            $fv->setUploadedFileModel($this->_mUploadedFile);

            //$required = array()
        }
    }

    private function _savePendaftaranSempro_old($entriedProposal, $idEvent)
    {
        $allowedScan = ['image/png', 'image/jpg', 'image/jpeg'];
        $allowedPdf  = ['application/pdf'];

        // Bikin object m\UploadedFile dari $_FILES
        $activityControl = $this->_handleUpload('file_scan_activiy_control_bimbingan_proposal', $allowedScan);
        $persetujuanMaju = $this->_handleUpload('file_scan_persetujuan_mengikuti_sempro', $allowedScan);
        $proposalRevisi  = $this->_handleUpload('file_proposal_sempro', $allowedPdf);

        // Cek dulu semua files apakah sudah ok
        $errorMessages = self::_collectErrorMessages([$activityControl, $persetujuanMaju, $proposalRevisi]);

        // Berarti tidak ada masalah dengan semua files
        if(empty($errorMessages))
        {
            // Store files
            $activityControl->store();
            $persetujuanMaju->store();
            $proposalRevisi->store();

            // Fallback if error
            if($activityControl->hasError()
                || $persetujuanMaju->hasError() || $proposalRevisi->hasError())
                die("[Error!] Salah satu atau lebih file yang diupload gagal disimpan!");

            // Save files to databases
            $activityControl->saveToDatabase();
            $persetujuanMaju->saveToDatabase();
            $proposalRevisi->saveToDatabase();

            $judul = $_POST['txt_judul'];
            if(empty($judul)) $this->_forceRenderErrorMessage('Judul tidak boleh kosong!');

            $grupRiset = $_POST['cbx_grup_riset'];
            if(empty($grupRiset)) $this->_forceRenderErrorMessage('Grup riset boleh kosong!');

            $this->_mProposal->addToPendaftaranSempro(
                $entriedProposal['id'],
                $judul,
                $grupRiset,
                $activityControl->getStoredName(),
                $persetujuanMaju->getStoredName(),
                $proposalRevisi->getStoredName(),
                $idEvent
            );
        }
        else
            $this->_forceRenderErrorMessage($errorMessages);
    }

    private function _retrieveCurrentProposal()
    {
        $currentNim = AppUtil::getCurrentUsername($this);

        $currentProposal = $this->_mProposal->findByNimPengusul($currentNim);

        // To check whether this is edit or insert
        if($currentProposal == null)
            $currentProposal = $this->_mProposal->findByNimAnggota($currentNim);

        return $currentProposal;
    }

    private function _forceRenderErrorMessage($errMessage, array $extraViewData = null)
    {
        $this->view->appendData(array('error_message' => $errMessage));

        if($extraViewData != null)
            $this->view->appendData($extraViewData);

        //pre_print($this->view);

        $this->view->setContentTemplate('/proposal/pendaftaran_sempro_template.php');
        $this->view->render();

        exit(0);
    }

    private function _getCurrentUsername()
    {
        return AppUtil::getCurrentUsername($this);
    }
}