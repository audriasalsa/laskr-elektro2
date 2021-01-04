<?php


namespace controller\mahasiswa;


use lib\AppController;
use lib\AppUtil;
use m\Application;
use m\extended\Form;
use m\extended\FormValidation;
use m\Util;
use model\CredentialModel;
use model\DosenModel;
use model\PengajuanPembimbingModel;
use model\ProdiModel;
use model\TopikModel;

class PengajuanPembimbingController extends AppController
{
    private $_mPengajuanPembimbing;
    private $_mDosen;

    // Data
    private $_formFields;
    private $_currentMahasiswa;
    private $_selectedTopic;
    private $_eligibilityCheckErrorMessage;

    public function __construct(Application $application)
    {
        parent::__construct($application);

        $this->_mPengajuanPembimbing = new PengajuanPembimbingModel();
        $this->_mDosen = new DosenModel();

        $this->_formFields = $this->_mPengajuanPembimbing->getColumnNames();
    }

    public function index()
    {
        $this->accessControl()->inspect();

        // Check whether there is a topic ID already selected
        $this->_determineSelectedTopic();

        // Get current account holder data
        $this->_determineCurrentMahasiswa();

        // Eksekusi simpan
        $this->_handleAction();

        // View ketika action sudah selesai
        $this->_setupView();

        // Tampilkan form
        $this->_setupForm();

        // Tampilkan data yang di tabel di bawah form
        $this->_populateDisplayedData();

        $this->view->render();
    }

    private function _setupView()
    {
        $this->view->setContentTemplate('/common/data_crud_template.php');
        $pageTitle = 'Pengajuan Pembimbing';
        $description = <<< PHREDOC
<p> 
    Pilih topik Anda, kemudian pilih dosen yang Anda ajukan sebagai pembimbing utama Anda. Lalu klik 'Simpan'.
</p>
<a>Catatan: </a>
<ul>
    <li><a style="color: darkred;">[PERHATIAN!]</a> Pastikan Anda sudah berkonsultasi dahulu dengan dosen yang ingin Anda jadikan pembimbing utama.</li>
    <li>Apabila TA Anda berkelompok (D3):
        <ol>
            <li>Pastikan NIM rekan Anda sudah <strong>terdaftar di sistem</strong>. Artinya, teman Anda sudah register. Jika belum, pada saat disimpan, pengajuan akan error.</li>
            <li>Pastikan <strong>1 orang</strong> saja yang mengajukan. Jika topiknya bukan topik dari dosen, maka pastikan yang membuat pengajuan adalah mahasiswa yang membuat topik tersebut.</li></li>
            <li><strong>TIDAK ADA perbedaan</strong> antara Pengusul dan Anggota. Kedua istilah tersebut hanya untuk memudahkan query di sistem saja.</li>
        </ol>
    </li>
    <li>Perhatikan dengan baik pengajuan Anda, karena setelah disimpan pengajuan <strong>tidak bisa diedit maupun dihapus</strong>.</li>
    <li>Ketika pengajuan statusnya masih <strong>diajukan</strong>, maka Anda belum boleh mengentrikan pengajuan lainnya.</li>
    <li>Apabila Anda membuat pengajuan dosen pembimbing dengan topik yang Anda buat sendiri, pastikan dosen yang Anda pilih <strong>grup risetnya sesuai</strong> dengan grup riset topik Anda tersebut.</li>
    <li>Apabila pengajuan pembimbing Anda ditolak, Anda masih dapat mengajukan lagi ke dosen yang sama tetapi dengan <strong>topik yang berbeda</strong>.</li>
</ul>
        
PHREDOC;

        $this->view->appendData([
            'page_title'       => $pageTitle,
            'page_description' => $description
        ]);
    }

    private function _setupForm()
    {
        $originalPath = $this->getCurrentRoute()->getPathWithoutParams();

        $form = new Form($this->_formFields);

        $form->setAction($this->homeAddress($originalPath));

        // Create 'Pilih' Links
        $pilihTopikPath = $this->getCurrentRoute()->findPathOf(PilihTopikController::class, 'index');

        $topikDosen = $this->homeAddress($pilihTopikPath) . '?type=' . PilihTopikController::SELECT_TYPE_DOSEN;
        $topikOwn   = $this->homeAddress($pilihTopikPath) . '?type=' . PilihTopikController::SELECT_TYPE_OWN;

        $idTopikLabel = 'Topik yang diajukan:<ul><li><a href="' . $topikOwn . '">Pilih Topik Sendiri</a></li>&nbsp;<li><a href="' . $topikDosen . '">Pilih Topik Dosen</a></li></ul>';

        $form->getInput('id_topik')->setLabel($idTopikLabel)->setReadonly(true)->addAdditionalAttribute('placeholder', 'Klik salah satu link di sebelah kiri field ini..');

        $form->getInput('nim_pengusul')->setReadonly(true)->setValue($this->_currentMahasiswa['nim']);

        if(ProdiModel::isD3($this->_currentMahasiswa['kode_prodi']))
            $form->getInput('nim_anggota')->addAdditionalAttribute('placeholder', 'Entrikan NIM rekan Anda yang sudah terdaftar');
        else
            $form->getInput('nim_anggota')->addAdditionalAttribute('placeholder', 'Diploma IV tidak boleh memiliki anggota')->setReadonly(true);

        // Get all dosen for combo box
        $dosenOptions = $this->_mDosen->getAsKeyValuePairs('id', 'nama', 'nama', '', '-- Pilih salah satu --');

        // Remove dosen not eligible as pembimbing
        $dosenOptions = Util::arrayAssocRemoveSomeKeys($dosenOptions, $this->_mDosen->findAllIdTidakAktifMembimbingAs1DArray());

        $form->getInput('id_pembimbing_utama')->setType('select')->setOptionsFromList($dosenOptions);

        if($this->_selectedTopic != null && $this->_selectedTopic['id_dosen_pengusul'] != null)
        {
            Util::arrayAssocRemoveAllByKeyExcept($this->_selectedTopic['id_dosen_pengusul'], $dosenOptions);

            $form->getInput('id_pembimbing_utama')->setOptionsFromList($dosenOptions)->setValue($this->_selectedTopic['id_dosen_pengusul'])->setReadonly(true);
        }

        $form->getInput('status')->setValue('diajukan')->setReadonly(true);

        $this->_prefillForm($form);

        $this->view->appendData(['form'=> $form]);
    }

    private function _prefillForm(Form $form)
    {
        if($this->_selectedTopic != null)
            $form->getInput('id_topik')->setValue($this->_selectedTopic['id']);
    }

    private function _determineSelectedTopic()
    {
        if(isset($_GET['id_topik']))
            $this->_selectedTopic = (new TopikModel())->findOneById($_GET['id_topik']);
        else
            $this->_selectedTopic = null;
    }

    private function _determineCurrentMahasiswa()
    {
        $username = AppUtil::getCurrentUsername($this);

        $this->_currentMahasiswa = (new CredentialModel())->findMahasiswa($username);
    }

    private function _handleAction()
    {
        $fv = new FormValidation($this->_formFields, false);

        $fv->addRequiredInputs(['nim_pengusul', 'id_topik', 'id_pembimbing_utama']);

        if($fv->submitted())
        {
            if ($fv->isValid())
            {
                $savedData = $fv->getData();

                $passed = $this->_eligibilityCheck();

                if($passed)
                {
                    $this->_mPengajuanPembimbing->addNew($savedData);

                    $this->view->modifyData('error_message', 'Data berhasil disimpan.');
                }
                else
                {
                    $this->view->modifyData('error_message', $this->_eligibilityCheckErrorMessage);
                }
            }
            else
            {
                $invalidMessage = $fv->getInvalidMessage();

                $this->view->modifyData('error_message', $invalidMessage);
            }
        }
    }

    private function _populateDisplayedData()
    {
        $existing = $this->_mPengajuanPembimbing->findAllRekapByNimPengusulOrAnggota($this->_currentMahasiswa['nim']);

        if($existing != null)
        {
            $headers = AppUtil::toTableDisplayedHeaders($existing);

            $this->view->appendData(array(
                'headers'        => $headers,
                'displayed_data' => $existing
            ));
        }
    }

    private function _eligibilityCheck()
    {
        if($this->_mPengajuanPembimbing->hasPendingPengajuan($this->_currentMahasiswa['nim']))
        {
            $this->_eligibilityCheckErrorMessage = 'Tidak bisa mengajukan pembimbing karena Anda masih memiliki pengajuan yang belum ditanggapi! Anda baru boleh mengajukan lagi ketika pengajuan Anda yang sebelumnya sudah ditolak.';

            return false;
        }

        if($this->_mPengajuanPembimbing->hasAcceptedPengajuan($this->_currentMahasiswa['nim']))
        {
            $this->_eligibilityCheckErrorMessage = 'Tidak bisa mengajukan pembimbing karena pengajuan Anda sudah ada yang disetujui.';

            return false;
        }

        return true;
    }
}