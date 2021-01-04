<?php

namespace controller;

use lib\AppController;
use lib\AppUtil;
use m\Controller;
use m\Application;
use m\extended\AuthPolicy;
use m\extended\Form;
use m\extended\FormValidation;
use m\extended\UploadedFile;
use m\Util;
use model\AppUploadedFileModel;
use model\BimbinganModel;
use model\DosenModel;
use model\EventModel;
use model\HasilSemproModel;
use model\MahasiswaModel;
use model\ProposalModel;

class ProposalController extends AppController
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

    /*
    public function hasilVerifikasi()
    {
        $this->accessControl()->inspect();

        $proposal = $this->_mProposal->findRekapVerifikasiByUsername($this->_getCurrentUsername());

        $viewData = array(
            'page_title'       => 'Hasil Verifikasi Proposal',
            'page_description' => 'Berikut ini adalah hasil verifikasi Proposal oleh Grup Riset',
        );

        if($proposal == null)
            $viewData['error_message'] = 'Tidak ada data! Hal ini disebabkan karena, Anda mungkin belum melengkapi data proposal, atau proposal Anda belum diverifikasi.';
        else
            $viewData['displayed_data'] = AppUtil::toDisplayedData($proposal[0]); //$this->_toDisplayedData($proposal[0]);

        $this->view->setData($viewData);
        $this->view->setContentTemplate('/common/data_display_template.php');
        $this->view->render();
    }
    */

    private function _getCurrentUsername()
    {
        $authModel = $this->accessControl()->findPolicy(AuthPolicy::class)->getModel();

        $currentUsername = $authModel->sessionUsername();

        return $currentUsername;
    }

    private function _toDisplayedData(array $proposal)
    {
        $displayed = array();

        foreach ($proposal as $key => $value)
        {
            $newRow = array();
            $newRow['caption'] = Util::strFormatTableColumnName($key);
            $newRow['content'] = $value;

            $displayed[] = $newRow;
        }

        return $displayed;
    }

    private static function _collectErrorMessages(array $uploadedFiles)
    {
        $errorMessages = '';

        foreach ($uploadedFiles as $uploadedFile)
        {
            if(! $uploadedFile->isGood())
            {
                $errorMessages .= $uploadedFile->isGood(true);
                $errorMessages .= ". ";
            }
        }

        return $errorMessages;
    }

    private function _handleUpload($inputFileName, $allowedTypes = null)
    {
        $uf = new UploadedFile($inputFileName, $this->_mUploadedFile);

        $uf->setAllowedMaximumSizeMegaBytes(5);
        $uf->setAllowedTypes($allowedTypes);

        return $uf;
    }

    public function cetakBuktiPendaftaranSempro()
    {
        $this->accessControl()->inspect();

        $idEvent = isset($_GET['id_event']) ? $_GET['id_event'] : null;

        $rekap = $this->_mProposal->findRekapPendaftaranSemproByUsernameAndIdEvent($this->_getCurrentUsername(), $idEvent);

        $viewData = array(
            'page_title'       => 'Bukti Pendaftaran',
            'page_description' => '<button onclick="window.print();">Cetak!</button>',
        );

        $rekap['status_verifikasi'] = "<b>{$rekap['status_verifikasi']}</b>";

        if($rekap == null)
            $viewData['error_message'] = 'Tidak ada data. Kemungkinan Anda belum mendaftar!';
        else {
            if(empty($rekap['saran_revisi_dari_grup_riset']) || $rekap['saran_revisi_dari_grup_riset'] == '')
            {
                if($rekap['status_verifikasi'] == 'Sudah diverifikasi')
                    $rekap['saran_revisi_dari_grup_riset'] = 'Tidak ada revisi';
                else
                    $rekap['saran_revisi_dari_grup_riset'] = 'Belum diverifikasi';
            }

            $viewData['displayed_data'] = AppUtil::toDisplayedData($rekap);
        }

        $this->view->setData($viewData);
        $this->view->setContentTemplate('/common/data_display_template.php');
        $this->view->render();
    }

    public function gantiProposalSempro()
    {
        $this->accessControl()->inspect();

        $this->view->setContentTemplate('/proposal/ganti_proposal_sempro_template.php');

        // Cek apakah sudah pernah mendaftar
        $terdaftar = $this->_mProposal->findPendaftaranSemproByUsername($this->_getCurrentUsername());

        if($terdaftar == null)
        {
            $this->view->appendData(['error_message' => 'Tidak ada data. Kemungkinan Anda belum mendaftar!']);
            $this->view->appendData(['hide_form' => true]);
            $this->view->render();

            return;
        }

        if(isset($_POST['submit']))
        {
            $proposalRevisiBaru = $this->_handleUpload('file_proposal_sempro_baru', ['application/pdf']);

            // Cek dulu semua files apakah sudah ok
            $errorMessages = self::_collectErrorMessages([$proposalRevisiBaru]);

            // Berarti tidak ada masalah dengan semua files
            if(empty($errorMessages))
            {
                $proposalRevisiBaru->store();
                $proposalRevisiBaru->saveToDatabase();

                $this->_mProposal->editProposalPendaftaranSempro(
                    $terdaftar['id_proposal'],
                    $proposalRevisiBaru->getStoredName()
                );

                $this->redirect('/proposal/pendaftaran-sempro');
            }
            else
            {
                $this->view->appendData(['error_message' => $errorMessages]);
                $this->view->render();

                return;
            }
        }

        $this->view->render();
    }
}