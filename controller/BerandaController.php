<?php


namespace controller;


use lib\AppController;
use lib\AppUtil;
use m\Application;
use m\Controller;

use m\extended\AuthModel;
use m\extended\AuthPolicy;
use m\extended\Form;
use m\extended\FormValidation;
use m\Session;
use model\CredentialModel;
use model\MahasiswaModel;
use model\NimAktifModel;

class BerandaController extends AppController
{
    private $_mMahasiswa;
    private $_mCredential;
    private $_mNimAktif;

    public function __construct(Application $application)
    {
        parent::__construct($application);

        $this->_mMahasiswa = new MahasiswaModel();
        $this->_mCredential = new CredentialModel();
        $this->_mNimAktif = new NimAktifModel();
    }

    public function index()
    {
        $this->accessControl()->inspect();

        $viewData = array();
        $viewData['username'] = Session::getInstance()->read(AuthModel::SESSION_KEY_AUTH_USERNAME);

        $this->view->setData($viewData);
        $this->view->setContentTemplate('/beranda/index_template.php');
        $this->view->render();
    }

    public function dataDiri()
    {
        $this->accessControl()->inspect();

        $this->view->setContentTemplate('common/data_entry_template.php');

        $viewData = array(
            'page_title'       => 'Data Diri',
            'page_description' => 'Pastikan data diri Anda benar untuk menghindari kesalahan pada administrasi Tugas Akhir Anda.'
        );

        $fields = array('nama', 'nim', 'email', 'nomor_ponsel', 'nomor_ponsel_orang_tua', 'kode_prodi', 'kelas');

        $fv = new FormValidation($fields);

        if($fv->submitted())
        {
            $fv->addRequiredInputs(['nama', 'nim', 'nomor_ponsel', 'nomor_ponsel_orang_tua']);

            if ($fv->isValid())
            {
                $this->_mMahasiswa->edit($fv->getData());

                $viewData['error_message'] = 'Data berhasil disimpan.';
            }
            else
                $viewData['error_message'] = $fv->getInvalidMessage();
        }

        $prodiOptions = array(
            ''      => '-- Pilih Salah Satu --',
            'D3-MI' => 'D3 Manajemen Informatika',
            'D4-TI' => 'D4 Teknik Informatika'
        );

        $form = new Form($fields);

        $prodiInput = $form->getInput('kode_prodi')->setType('select')->setOptionsFromList($prodiOptions)->setValue('D4-TI');
        $namaInput  = $form->getInput('nama')->setReadOnly(true);
        $nimInput   = $form->getInput('nim')->setReadOnly(true);
        $submit     = $form->getSubmit()->setValue('Simpan!');

        $form->setInput('kode_prodi', $prodiInput);
        $form->setInput('nama', $namaInput);
        $form->setInput('nim', $nimInput);
        $form->setSubmit($submit);

        $form->getInput('kelas')->setType('select')->setOptionsFromList(array(
            ''  => '-- Pilih Salah Satu --',
            'A' => 'Tingkat Akhir Kelas-A',
            'B' => 'Tingkat Akhir Kelas-B',
            'C' => 'Tingkat Akhir Kelas-C',
            'D' => 'Tingkat Akhir Kelas-D',
            'E' => 'Tingkat Akhir Kelas-E',
            'F' => 'Tingkat Akhir Kelas-F',
            'G' => 'Tingkat Akhir Kelas-G',
            'H' => 'Tingkat Akhir Kelas-H',
        ));

        $form->applyValues($this->_mMahasiswa->findByNim(AppUtil::getCurrentUsername($this)));

        // TODO: Disable edit for kode Prodi, to prevent incidental change like Mbak Elok's bimbingan on 23 June 2020. Don't know why, below snippet does not work.
        //$form->getInput('kode_prodi')->setReadOnly(true);

        $viewData['form'] = $form;

        $this->view->setData($viewData);
        $this->view->render();
    }

    public function login()
    {
        if(isset($_POST['submit']))
        {
            $authModel = $this->accessControl()->findPolicy(AuthPolicy::class)->getModel();

            $user = $authModel->getUser($_POST['username'], $_POST['password']);

            if($user != null)
            {
                $authModel->sessionStore($user['username']);
                $authModel->sessionStoreAccessType($user['access_type']);

                $this->redirect('/');

                return;
            }

            $this->view->setData(['error_message' => 'Username dan/atau password Anda salah!']);
        }

        $this->view->setContentTemplate('beranda/login_template.php');
        $this->view->render();
    }

    public function logout()
    {
        $authPolicy = $this->accessControl()->findPolicy(AuthPolicy::class);

        $authPolicy->getModel()->sessionClear();

        $this->redirect('/index/login');
    }

    public function pendaftaran()
    {
        $this->view->setContentTemplate('common/data_entry_template.php');

        $fields = array('nama', 'nim', 'email', 'nomor_ponsel', 'nomor_ponsel_orang_tua', 'kode_prodi', 'kelas');

        $fv = new FormValidation($fields);

        if($fv->submitted())
        {
            $errMessage = 'Terjadi kesalahan. Data tidak dapat ditambahkan!';

            $fv->addRequiredInputs($fields);

            if ($fv->isValid())
            {
                $mahasiswa = $fv->getData();

                $this->_daftarEligibilityCheck($mahasiswa);

                if($this->_mMahasiswa->addNew($mahasiswa))
                {
                    if($this->_mCredential->addNewInitialStudentCredential($mahasiswa['nim']))
                        $errMessage = "Data berhasil ditambahkan. Silahkan <a href='{$this->homeAddress()}'>login</a> dengan menggunakan NIM sebagai username & password.";
                }
                else
                    $errMessage .= ' Detail: ' . $this->_mMahasiswa->getLastWriteErrorMessage();
            }
            else
                $errMessage .= ' Detail: ' . $fv->getInvalidMessage();

            $this->view->appendData(['error_message' => $errMessage]);
        }

        $form = new Form($fields);

        $form->getInput('kode_prodi')->setType('select')->setOptionsFromList(array(
            ''  => '-- Pilih Salah Satu --',
            'D3-MI' => 'D3 Manajemen Informatika',
            'D4-TI' => 'D4 Teknik Informatika'
        ));

        $form->getInput('kelas')->setType('select')->setOptionsFromList(array(
            ''  => '-- Pilih Salah Satu --',
            'A' => 'Tingkat Akhir Kelas-A',
            'B' => 'Tingkat Akhir Kelas-B',
            'C' => 'Tingkat Akhir Kelas-C',
            'D' => 'Tingkat Akhir Kelas-D',
            'E' => 'Tingkat Akhir Kelas-E',
            'F' => 'Tingkat Akhir Kelas-F',
            'G' => 'Tingkat Akhir Kelas-G',
            'H' => 'Tingkat Akhir Kelas-H',
        ));

        $description = <<< PHREDOC
Lengkapi form berikut ini dengan data yang sesuai.
<ul>
    <li>Harap isikan data dengan sebenar-benarnya, karena akan digunakan sebagai data Ujian.</li>
    <li>Semua field wajib diisi.</li>
</ul> 
PHREDOC;

        $viewData = array(
            'page_title'       => 'Pendaftaran Akun',
            'page_description' => $description,
            'form'             => $form
        );

        $this->view->appendData($viewData);
        $this->view->render();
    }

    private function _daftarEligibilityCheck(array $mahasiswa)
    {
        $nimExists = $this->_mNimAktif->isActive($mahasiswa['nim']);

        if(!$nimExists)
            $this->renderErrorAndExit('NIM yang Anda inputkan tidak terdaftar. Akun gagal dibuat!');
    }
}