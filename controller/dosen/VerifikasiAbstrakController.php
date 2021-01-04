<?php


namespace controller\dosen;


use lib\AppController;
use lib\AppUtil;
use m\Application;
use model\AppUploadedFileModel;
use model\CredentialModel;
use model\VerifikasiAbstrakModel;

class VerifikasiAbstrakController extends AppController
{
    /**
     * @var VerifikasiAbstrakModel
     */
    private $_mVerifikasiAbstrak;
    private $_currentIdDosen;

    public function __construct(Application $application)
    {
        parent::__construct($application);

        $this->_mVerifikasiAbstrak = new VerifikasiAbstrakModel();
    }

    public function index()
    {
        $this->view->setContentTemplate('/common/data_table_display_template.php');
        $this->view->appendData(['page_title' => 'Verifikasi Abstrak']);
        $this->view->appendData(['page_description' => 'Berikut ini adalah daftar mahasiswa yang mengajukan abstrak untuk Anda verifikasi.']);

        $verificationRequests = $this->_mVerifikasiAbstrak->findAllRekapByIdDosenVerifikator($this->_currentIdDosen());

        if($verificationRequests != null)
        {
            $verificationRequests = $this->_addActionLinks($verificationRequests);

            $headers = AppUtil::toTableDisplayedHeaders($verificationRequests);

            $this->view->appendData(['displayed_data' => $verificationRequests]);
            $this->view->appendData(['headers' => $headers]);
        }

        if(isset($_GET['message']))
            $this->view->appendData(['error_message' => (base64_decode($_GET['message']))]);

        $this->view->render();
    }

    public function accept()
    {
        $this->accessControl()->inspect();

        if(!isset($_GET['id_proposal']))
            $this->renderErrorAndExit('ID Proposal tidak ditemukan!');

        $idProposal = $_GET['id_proposal'];

        $this->_mVerifikasiAbstrak->accept($idProposal);

        $message = "Abstrak dengan ID = $idProposal berhasil disetujui.";

        $this->redirect('/dosen/laporan-akhir/verifikasi-abstrak', ['message' => (base64_encode($message))]);
    }

    private function _currentIdDosen()
    {
        if($this->_currentIdDosen == null)
        {
            $username = AppUtil::getCurrentUsername($this);

            $dosen = (new CredentialModel())->findDosen($username);

            $this->_currentIdDosen = $dosen['id'];
        }

        return $this->_currentIdDosen;
    }

    private function _addActionLinks(array $verificationRequests)
    {
        $result = array();

        foreach ($verificationRequests as $request)
        {
            $acceptURL = $this->_createAcceptUrl($request);

            $request['file_abstrak'] = (new AppUploadedFileModel())->createFileLink(
                $request['file_abstrak'], true);

            $request['aksi'] = $acceptURL;

            $request['status_verifikasi'] = $request['status_verifikasi'] == 'disetujui' ? '<a style="color: darkgreen;">disetujui</a>' : '<a style="color: #a05047;">diajukan</a>';

            $result[] = $request;
        }

        return $result;
    }

    private function _createAcceptUrl($request)
    {
        if($request['status_verifikasi'] == 'disetujui')
            return '√';

        $uri = "/accept?id_proposal={$request['id_proposal']}";

        $acceptURL = $this->application()->getRoute()->toURL($uri);

        return "<a href=\"$acceptURL\">Setujui</a>";
    }
}