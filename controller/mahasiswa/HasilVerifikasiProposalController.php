<?php


namespace controller\mahasiswa;


use lib\AppController;
use lib\AppUtil;
use m\Application;
use model\CredentialModel;
use model\VerifikasiProposalModel;

class HasilVerifikasiProposalController extends AppController
{
    // Data
    private $_currentMahasiswa;

    public function __construct(Application $application)
    {
        parent::__construct($application);
    }

    public function index()
    {
        $this->accessControl()->inspect();

        $this->_initData();

        $this->_setupView();

        $this->_retrieveData();

        $this->view->render();
    }

    private function _initData()
    {
        $username = AppUtil::getCurrentUsername($this);

        $this->_currentMahasiswa = (new CredentialModel())->findMahasiswa($username);
    }

    private function _setupView()
    {
        $this->view->setContentTemplate('/common/data_display_template.php');

        $pageTitle   = 'Hasil Verifikasi Proposal';
        $description = <<< PHREDOC
<p>Berikut ini adalah hasil verifikasi Proposal oleh Grup Riset</p>
<p>Catatan:</p>
<ul>
    <li>Apabila data proposal Anda sudah muncul di halaman ini, maka selanjutnya Anda sudah dapat mendaftar Seminar Proposal.</li>
    <li>Harap perbaiki dahulu draft proposal Anda sesuai dengan masukan dari verifikator sebelum Anda daftarkan Sempro.</li>
</ul>
PHREDOC;

        $this->view->appendData([
            'page_title'       => $pageTitle,
            'page_description' => $description
        ]);
    }

    private function _retrieveData()
    {
        $dataVerifikasi = (new VerifikasiProposalModel())->findRekapVerifikasiProposalByNim($this->_currentMahasiswa['nim']);

        if($dataVerifikasi == null)
            $this->view->appendData(['error_message' => 'Tidak ada data! Hal ini disebabkan karena, Anda mungkin belum melengkapi data proposal, atau proposal Anda memang belum diverifikasi.']);
        else
            $this->view->appendData(['displayed_data' => AppUtil::toDisplayedData($dataVerifikasi)]);
    }
}