<?php


namespace controller\mahasiswa;


use lib\AppController;
use lib\AppUtil;
use m\Application;
use m\extended\Form;
use m\extended\FormValidation;
use m\Util;
use model\AppUploadedFileModel;
use model\BimbinganModel;
use model\CredentialModel;
use model\DosenModel;
use model\GrupRisetModel;
use model\MahasiswaModel;
use model\PengajuanPembimbingModel;
use model\ProposalModel;
use model\TopikModel;
use model\VerifikasiProposalModel;

class DraftProposalController extends AppController
{
    private $_mProposal;
    private $_mUploadedFile;
    private $_mDosen;
    private $_mMahasiswa;
    private $_mBimbingan;
    private $_mVerifikasiProposal;
    private $_mPengajuanPembimbing;
    private $_mTopik;

    // Data
    /**
     * @var array The fields that'll be shown in the form
     */
    private $formFields; // TODO: rename to $_formFields
    /**
     * @var mixed|null Mahasiswa data in corresponding with current account
     */
    private $_currentMahasiswa;
    /**
     * @var mixed|null Current proposal data owned by current mahasiswa
     */
    private $_currentProposal;
    /**
     * @var mixed|null The data of current mahasiswa's pembimbing utama
     */
    private $_pembimbing1;
    /**
     * @var mixed|null The data of current mahasiswa's pengajuan pembimbing utama
     */
    private $_currentPengajuanPembimbing;
    /**
     * @var mixed|null Current mahasiswa's topik
     */
    private $_currentTopik;

    public function __construct(Application $application)
    {
        parent::__construct($application);

        $this->_mProposal = new ProposalModel();
        $this->_mUploadedFile = new AppUploadedFileModel();
        $this->_mDosen = new DosenModel();
        $this->_mMahasiswa = new MahasiswaModel();
        $this->_mBimbingan = new BimbinganModel();
        $this->_mVerifikasiProposal = new VerifikasiProposalModel();
        $this->_mPengajuanPembimbing = new PengajuanPembimbingModel();
        $this->_mTopik = new TopikModel();
    }

    public function index()
    {
        // Check login
        $this->accessControl()->inspect();

        // Siapkan data yang dibutuhkan di awal
        $this->_initData();

        // Eksekusi simpan
        $this->_handleAction();

        // Apakah sudah verifikasi? Kalau sudah, berarti data hanya ditampilkan saja tidak bisa diedit
        $this->_determineReadOnly();

        // View ketika action sudah selesai
        $this->_setupView();

        // Tampilkan form
        $this->_setupForm();

        $this->view->render();
    }

    private function _setupForm()
    {
        // Current account's proposal
        $currentProposal = $this->_retrieveCurrentProposal();
        $currentNim = AppUtil::getCurrentUsername($this);

        //comment $dosenOptions = array($this->_pembimbing1['id'] => $this->_pembimbing1['nama']);
        $dosenOptions = $this->_mDosen->findAllDosen();

        $form = new Form($this->formFields);
        $form->setEnctype('multipart/form-data');

        $form->getInput('id')->setReadOnly(true)->addAdditionalAttribute('placeholder', 'Dibangkitkan otomatis');
        $form->getInput('nim_pengusul')->setReadOnly(true)->setValue($currentNim);
        $form->getInput('nim_anggota')->setReadOnly(true)->addAdditionalAttribute('placeholder', 'Tugas Akhir D4 tidak boleh memiliki anggota');;
        $form->getInput('judul_proposal')->setType('textarea')->setExtras('style="min-height: 100px; width: 100%"');;
        //$form->getInput('catatan_khusus')->setType('textarea');

        //comment $form->getInput('deskripsi')->setType('textarea')->setExtras('style="min-height: 250px; width: 100%"');;
        //comment $form->getInput('nama_grup_riset')->setType('select');
        $form->getInput('id_dosen_pembimbing_1')->setType('select')->setOptionsFromList($dosenOptions)->setValue($this->_pembimbing1['id'])->setLabel('Dosen yang merekomendasi'); //->setDisabled(true); // Dont disabled! it will cause the value missing in $_POST
        //$form->getInput('form_kesediaan')->setType('file');
        $form->getInput('draft')->setType('file');
        $form->getInput('revisi_draft_1')->setType('file')->setLabel('Form kerjasama mitra');
        //$form->getInput('revisi_draft_2')->setType('file');
        //comment $form->getInput('literatur_utama')->addAdditionalAttribute('placeholder', 'Isikan dengan tautan (URL) ke jurnal rujukan utama Anda');
        //comment $form->getInput('literatur_penunjang')->addAdditionalAttribute('placeholder', 'Tautan (URL) ke jurnal rujukan utama lainnya bila ada');
        $form->getSubmit()->setValue('Simpan Proposal!');

        $this->_prefillForm($form);

        if($currentProposal != null)
        {
            $currentProposal['draft'] = (new AppUploadedFileModel())->createFileLink($currentProposal['draft'], true);
            $form->applyValues($currentProposal);
        }

        $this->view->modifyData('form', $form);
    }

    private function _prefillForm(Form &$form)
    {
        if($this->_currentTopik != null)
        {
            $ct = $this->_currentTopik;

            $form->getInput('id_topik')->setValue($ct['id'])->setReadOnly(true);
            $form->getInput('judul_proposal')->setValue($ct['judul']);

            //comment    $form->getInput('deskripsi')->setValue($ct['deskripsi']);

            $grupRiset = (new GrupRisetModel())->find(['kode' => $ct['kode_grup_riset']]);

            if($grupRiset != null && count($grupRiset) > 0)
            {
                $gr = $grupRiset[0];

                // TODO: In proposal table, nama_grup_riset is filled with kode that has not given proper relationship to v2_grup_riset table
                $form->getInput('nama_grup_riset')->setOptionsFromList([$gr['kode'] => $gr['nama']]);
            }
        }

        if($this->_currentPengajuanPembimbing != null)
        {
            $cpp = $this->_currentPengajuanPembimbing;

            $form->getInput('nim_pengusul')->setValue($cpp['nim_pengusul']);
            $form->getInput('nim_anggota')->setValue($cpp['nim_anggota']);
        }
    }

    private function _handleAction()
    {
        $fv = new FormValidation($this->formFields, true);

        if($fv->submitted())
        {
            // Jangan diproses jika data diri belum lengkap;
            if(!$this->_mMahasiswa->isDataDiriLengkap($this->_currentMahasiswa['nim']))
                $this->view->modifyData('error_message', 'PROPOSAL GAGAL DISIMPAN: Data diri Anda belum lengkap! Lengkapi dahulu data diri Anda sesuai dengan petunjuk di atas.');
            else
            {
                $fv->setUploadedFileModel($this->_mUploadedFile);

                $required = Util::arrayDeleteElementsByValues(['nim_anggota', 'literatur_utama', 'literatur_penunjang'], $this->formFields);

                if($this->_currentProposal == null)
                    $required = Util::arrayDeleteElementsByValues(['id'], $required);
                else
                    $required = Util::arrayDeleteElementsByValues(['draft'], $required);

                $fv->addRequiredInputs($required);

                $fv->getUploadedFile('draft')->setAllowedTypes(['application/pdf']);
                $fv->getUploadedFile('revisi_draft_1')->setAllowedTypes(['application/pdf']);

                // TODO: Take care of these unnecessary fields on the database
                //
                //$fv->getUploadedFile('revisi_draft_2')->setAllowedTypes(['application/pdf']);
                //$fv->getUploadedFile('form_kesediaan')->setAllowedTypes(['image/jpeg', 'image/png', 'image/jpg']);

                $fv->processUploadedFiles();

                if ($fv->uploadedFilesError())
                    $this->view->modifyData('error_message', $fv->getUploadedFilesErrorMessages());
                else
                {
                    if ($fv->isValid())
                    {
                        $entireData = $fv->getEntireData();

                        $this->_mProposal->addOrEdit($entireData);
                        $this->view->modifyData('error_message', 'Data berhasil disimpan!');

                        // Reload form with latest data
                        $this->_currentProposal = $this->_retrieveCurrentProposal();
                    }
                    else
                    {
                        $this->view->modifyData('error_message', $fv->getInvalidMessage());
                    }
                }
            }
        }
    }

    private function _setupView()
    {
        // Set template
        $this->view->setContentTemplate('/common/data_entry_template.php');

        $pageTitle = 'Entri Draft Proposal';
        $description = <<< PHREDOC
<a>Lengkapi data proposal Anda di sini dengan ketentuan sebagai berikut:</a>
<ul> 
    <li>Unggah draft proposal hanya bisa dilakukan bagi mahasiswa yang <strong>Pengajuan Pembimbingnya sudah diterima.</strong>
    <li>ID proposal akan di-generate otomatis, dan ID tersebut adalah ID Skripsi/Tugas Akhir Anda untuk seterusnya.</li>
    <li>Terlebih dahulu, pastikan data diri Anda telah lengkap. Untuk melakukannya, pilih menu <b>Akun >> Data Diri</b>.</li>
    <li>Apabila data proposal baru ditambahkan, isian <b>Draft</b>, WAJIB diisi.</li>
    <li>Apabila mengedit data proposal dan ingin membiarkan file yang diupload sebelumnya tetap sama, biarkan saja field-field upload teteap kosong.</li>
    <li>Draft, jenis filenya: <b>pdf</b>.</li>
    <li>Isikan <strong>Literatur Utama</strong> dengan tautan (URL) ke jurnal yang Anda gunakan sebagai rujukan utama dalam Tugas Akhir Anda.</li>
    <li>Tautan harus dapat diakses dan mengarah pada situs jurnal aslinya. Dan jurnal yang dijadikan rujukan utama minimal harus terakreditasi <strong>SINTA-3</strong>.</li> 
    <li>Contoh pengisian tautan literatur: <a href="https://ieeexplore.ieee.org/document/7763223">https://ieeexplore.ieee.org/document/7763223</a></li>
    <li>Isikan <strong>Literatur Penunjang</strong> jika ada jurnal lain yang penting yang Anda gunakan sebagai rujukan utama selain 'Literatur Utama'</li>
    <li>Setelah proposal diverifikasi grup riset, maka tidak akan bisa diedit kembali.</li>
</ul>
<a><u style="color: #bb534d;">Penting</u> untuk diperhatikan bila prodi Anda <b>D3</b></a>:
<ul>
    <li>Pastikan hanya <b>salah 1 mahasiswa saja</b> yang mengentrikan proposal, sebagai pengusul.</li>
    <li>NIM Anggota akan terisi otomatis sesuai dengan pengajuan pembimbing yang telah diterima.</li>
    <li><strong>Tidak ada perbedaan</strong> perlakuan dalam hal penilaian antara pengusul ataupun anggota. Istilah ini hanya untuk mempermudah pengolahan data pada sistem saja. Selain itu semua dianggap sama.</li>
    <li>Perihal literatur utama dan penunjang, Anda <strong>tidak wajib</strong> mengentrikannya.</li>
</ul>
PHREDOC;

        $this->view->appendData([
            'page_title'       => $pageTitle,
            'page_description' => $description
        ]);

        // Exit if current account still don't have any pembimbing utama

        //comment  if($this->_pembimbing1 == null)
        //comment AppUtil::forceRenderErrorMessage($this->view, 'Anda tidak bisa membuat draft proposal karena belum memiliki pembimbing utama.');
    }

    private function _initData()
    {
        /*
         * Original fields
        Array
        (
            [0] => id
            [1] => id_topik
            [2] => nim_pengusul
            [3] => nim_anggota
            [4] => judul_proposal
            [5] => nama_grup_riset
            [6] => literatur_utama
            [7] => literatur_penunjang
            [8] => catatan_khusus
            [9] => id_dosen_pembimbing_1
            [10] => deskripsi
            [11] => form_kesediaan
            [12] => draft
            [13] => revisi_draft_1
            [14] => revisi_draft_2
        )
        */

        // Fields untuk name di form dan untuk validasi
        $this->formFields = [
            'id',
            //comment 'id_topik',
            'nim_pengusul',
            'nim_anggota',
            'id_dosen_pembimbing_1',
            //comment  'nama_grup_riset',
            'judul_proposal',
            //comment      'deskripsi',
            'draft',
            'revisi_draft_1',
            //comment        'literatur_utama',
            //comment        'literatur_penunjang'
        ];

        // Current account's data as mahasiswa
        $this->_currentMahasiswa = (new CredentialModel())->findMahasiswa(AppUtil::getCurrentUsername($this));

        // Current account's proposal
        $this->_currentProposal = $this->_retrieveCurrentProposal();

        // Current mahasiswa's pembimbing utama
        $this->_pembimbing1 = $this->_mBimbingan->findPembimbing1ByNim($this->_currentMahasiswa['nim']);

        // Current mahasiswa's pengajuan pembimbing
        $this->_currentPengajuanPembimbing = $this->_mPengajuanPembimbing
            ->findPengajuanDisetujuiByNimPengusulOrAnggota(
                $this->_pembimbing1['id'],
                $this->_currentMahasiswa['nim']
            );

        // Current mahasiswa's topik
        $this->_currentTopik = $this->_mTopik->findOneById($this->_currentPengajuanPembimbing['id_topik']);
    }

    private function _retrieveCurrentProposal()
    {
        $currentNim = AppUtil::getCurrentUsername($this);

        $currentProposal = $this->_mProposal->findByNimPengusulOrAnggota($currentNim);

        // To check whether this is edit or insert
        if($currentProposal == null)
            $currentProposal = $this->_mProposal->findByNimAnggota($currentNim);

        return $currentProposal;
    }

    private function _determineReadOnly()
    {
        if($this->_currentProposal != null)
        {
            $isVerified = $this->_mVerifikasiProposal->proposalIsVerified($this->_currentProposal['id']);

            if ($isVerified)
            {
                // Display as read only
                $viewData['page_description'] = 'Proposal Anda telah diverifikasi oleh grup riset. Berikut informasi proposal Anda:';
                $viewData['displayed_data'] = AppUtil::toDisplayedData($this->_currentProposal);

                $this->view->setData($viewData);
                $this->view->setContentTemplate('/common/data_display_template.php');
                $this->view->render();

                exit(0); // Exit success.
            }
        }
    }
}