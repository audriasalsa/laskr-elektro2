<?php


namespace controller;


use lib\AppController;
use lib\AppUtil;
use m\Application;
use m\extended\Form;
use m\extended\FormValidation;
use m\Util;
use model\AppUploadedFileModel;
use model\DosenModel;
use model\HasilSemproModel;
use model\MahasiswaModel;
use model\ProposalModel;
use model\RevisiSemproModel;

class RevisiSemproController extends AppController
{
    private $_mMahasiswa;
    private $_mHasilSempro;
    private $_mDosen;
    private $_mProposal;
    private $_mRevisiSempro;

    private $_mainFormFields;

    private $_existingData;
    private $_currentNim;

    public function __construct(Application $application)
    {
        parent::__construct($application);

        $this->_mMahasiswa    = new MahasiswaModel();
        $this->_mHasilSempro  = new HasilSemproModel();
        $this->_mDosen        = new DosenModel();
        $this->_mProposal     = new ProposalModel();
        $this->_mRevisiSempro = new RevisiSemproModel();

        /*
            id_proposal INTEGER PRIMARY KEY,
            nim_mahasiswa VARCHAR(50),
            id_event_sempro_terakhir INTEGER,
            judul_final TEXT,
            id_dosen_moderator SMALLINT,
            id_dosen_pembahas_1 SMALLINT,
            revisi_pembahas_1 TEXT,
            id_dosen_pembahas_2 SMALLINT,
            revisi_pembahas_2 TEXT,
            file_berita_acara VARCHAR(255) NULL,
            file_lembar_revisi_1 VARCHAR(255),
            file_lembar_revisi_2 VARCHAR(255),
            file_proposal_final VARCHAR(255),
            file_scan_lembar_pengesahan_proposal VARCHAR(255),
            tanggal_unggah DATETIME DEFAULT NOW(),
         */
        $this->_mainFormFields = ['id_proposal', 'nim_mahasiswa', 'nama', 'id_event_sempro_terakhir', 'hasil_sempro',
            'tahap_terakhir_sempro', 'judul_final', 'id_dosen_moderator', 'id_dosen_pembahas_1', 'revisi_pembahas_1',
            'id_dosen_pembahas_2', 'revisi_pembahas_2', 'file_berita_acara', 'file_lembar_revisi_1',
            'file_lembar_revisi_2', 'file_proposal_final', 'file_scan_lembar_pengesahan_proposal'
        ];
    }

    public function hasilRevisi()
    {
        $this->accessControl()->inspect();

        $this->_setupView('view');

        $this->_currentNim   = AppUtil::getCurrentUsername($this);
        //$this->_existingData = $this->_mRevisiSempro->findOneByNim($this->_currentNim);
        $this->_existingData = $this->_mRevisiSempro->findOneRekapByNim($this->_currentNim);

        $data = $this->_existingData;

        if($data != null)
        {
            $fm = new AppUploadedFileModel();

            $data['file_berita_acara'] = $fm->createFileLink($data['file_berita_acara'], true);
            $data['file_lembar_revisi_1'] = $fm->createFileLink($data['file_lembar_revisi_1'], true);
            $data['file_lembar_revisi_2'] = $fm->createFileLink($data['file_lembar_revisi_2'], true);
            $data['file_proposal_final'] = $fm->createFileLink($data['file_proposal_final'], true);
            $data['file_scan_lembar_pengesahan_proposal'] = $fm->createFileLink($data['file_scan_lembar_pengesahan_proposal'], true);

            $displayedData = AppUtil::toDisplayedData($data);

            $this->view->appendData(['displayed_data' => $displayedData]);
        }
        else
            $this->view->appendData(['error_message' => 'Anda belum mengunggah hasil revisi sempro.']);

        // TODO: change to ActionLink class
        $this->view->appendData(['action_links' => [[
            'url' => $this->homeAddress('/proposal/unggah-hasil-revisi-sempro'),
            'caption' => 'Unggah Revisi'
        ]]]);

        $this->view->render();
    }

    public function unggahHasilRevisi()
    {
        $this->accessControl()->inspect();

        $this->_setupView();

        $this->_currentNim   = AppUtil::getCurrentUsername($this);
        $this->_existingData = $this->_mRevisiSempro->findOneByNim($this->_currentNim);

        $this->_eligibilityCheck();

        $form = new Form($this->_mainFormFields);
        $fv = new FormValidation($this->_mainFormFields, true);

        if($fv->submitted())
            $this->_processData($fv);

        $form = $this->_configureForm($form);
        $form = $this->_preFillForm($form);

        $this->view->appendData(['form' => $form]);
        $this->view->render();
    }

    private function _configureForm(Form $form)
    {
        $form->setEnctype('multipart/form-data');

        $dosenOptions = $this->_mDosen->getAsKeyValuePairs('id', 'nama', 'nama', '', '-- Pilih Salah Satu --');

        $form->getInput('id_proposal')->setType('hidden');
        $form->getInput('id_event_sempro_terakhir')->setType('hidden');
        $form->getInput('nim_mahasiswa')->setReadOnly(true);
        $form->getInput('nama')->setReadOnly(true);
        $form->getInput('hasil_sempro')->setReadOnly(true);
        $form->getInput('tahap_terakhir_sempro')->setReadOnly(true);
        $form->getInput('judul_final')->setType('textarea')->setExtras('style="min-height: 100px; width: 100%"');
        $form->getInput('revisi_pembahas_1')->setType('textarea')->setExtras('style="min-height: 200px; width: 100%"');
        $form->getInput('revisi_pembahas_2')->setType('textarea')->setExtras('style="min-height: 200px; width: 100%"');
        $form->getInput('file_berita_acara')->setType('file');
        $form->getInput('file_lembar_revisi_1')->setType('file');
        $form->getInput('file_lembar_revisi_2')->setType('file');
        $form->getInput('file_proposal_final')->setType('file');
        $form->getInput('file_scan_lembar_pengesahan_proposal')->setType('file');

        $form->getInput('id_dosen_moderator')->setType('select')->setOptionsFromList($dosenOptions)->setValue('');
        $form->getInput('id_dosen_pembahas_1')->setType('select')->setOptionsFromList($dosenOptions)->setValue('');
        $form->getInput('id_dosen_pembahas_2')->setType('select')->setOptionsFromList($dosenOptions)->setValue('');

        return $form;
    }

    private function _processData(FormValidation $fv)
    {
        $fv->setUploadedFileModel(new AppUploadedFileModel());

        // Semua wajib diisi kecuali file berita acara
        $required = Util::arrayDeleteElementByValue('file_berita_acara', $this->_mainFormFields);

        // Apabila update semua input file tidak apa-apa jika tidak diisi
        if($this->_existingData != null)
        {
            $required = Util::arrayDeleteElementsByValues([
                'file_berita_acara',
                'file_lembar_revisi_1',
                'file_lembar_revisi_2',
                'file_proposal_final',
                'file_scan_lembar_pengesahan_proposal'
            ], $required);
        }

        // Pasang validasi inputan wajib
        $fv->addRequiredInputs($required);

        // Jenis-jenis file yang diperbolehkan
        $fv->getUploadedFile('file_berita_acara')->setAllowedTypes(['image/jpeg', 'image/png', 'image/jpg']);
        $fv->getUploadedFile('file_lembar_revisi_1')->setAllowedTypes(['image/jpeg', 'image/png', 'image/jpg']);
        $fv->getUploadedFile('file_lembar_revisi_2')->setAllowedTypes(['image/jpeg', 'image/png', 'image/jpg']);
        $fv->getUploadedFile('file_proposal_final')->setAllowedTypes(['application/pdf']);
        $fv->getUploadedFile('file_scan_lembar_pengesahan_proposal')->setAllowedTypes(['image/jpeg', 'image/png', 'image/jpg']);

        $fv->processUploadedFiles();

        if ($fv->uploadedFilesError())
            $this->view->modifyData('error_message', $fv->getUploadedFilesErrorMessages());
        else
        {
            if ($fv->isValid())
            {
                $entireData = $fv->getEntireData();

                $this->_mRevisiSempro->addOrEdit($entireData);

                $this->view->modifyData('error_message', 'Data berhasil disimpan!');
            }
            else
                $this->view->modifyData('error_message', $fv->getInvalidMessage());
        }
    }

    private function _preFillForm(Form $form)
    {
        $p = $this->_mProposal->findByNimPengusul($this->_currentNim);

        $form->getInput('id_proposal')->setValue($p['id']);

        $m = $this->_mMahasiswa->findByUsername($this->_currentNim);

        $form->getInput('nim_mahasiswa')->setValue($m['nim']);
        $form->getInput('nama')->setValue($m['nama']);

        $rls = $this->_mHasilSempro->findRekapLulusSemproByNim($m['nim']);

        $form->getInput('hasil_sempro')->setValue($rls['hasil']);
        $form->getInput('tahap_terakhir_sempro')->setValue($rls['tahap_terkahir_sempro']);
        $form->getInput('id_event_sempro_terakhir')->setValue($rls['id_event_terakhir_sempro']);

        $existing = $this->_mRevisiSempro->findOneByNim($this->_currentNim);

        //pre_print($existing, true);

        if($existing != null)
            $form->applyValues($existing);

        return $form;
    }

    private function _eligibilityCheck()
    {
        // TODO: [WARNING] NIM by username!
        $nim = AppUtil::getCurrentUsername($this);

        // Cek sudah lulus sempro atau belum
        $lulusSempro = $this->_mHasilSempro->sudahLulus($nim);

        // Apabila belum, STOP
        if(! $lulusSempro)
            $this->renderErrorAndExit('Anda tidak diperkenankan mengunggah revisi, karena belum lulus sempro.');
    }

    private function _setupView($type = 'edit')
    {
        if($type == 'view')
        {
            $this->view->setContentTemplate('/common/data_display_template.php');
            $pageTitle = 'Revisi Sempro';
            $description = "Berikut ini adalah hasil revisi Anda. Jika Anda belum mengunggah hasil revisi atau ingin mengubah data yang sudah Anda kirim sebelumnya, klik link '<b>Unggah Revisi</b>' di bawah.";
        }
        else
        {
            $this->view->setContentTemplate('/common/data_entry_template.php');
            $pageTitle = 'Unggah Revisi';
            $backLink = $this->homeAddress('/proposal/hasil-revisi-sempro');
            $description = <<< PHREDOC
Lengkapi semua form berikut dan klik 'Submit' dengan catatan:
<ul>
    <li>Semua field wajib diisi kecuali scan berita acara.</li>
    <li>Setelah disubmit, form ini masih dapat diubah hingga sampai pada deadline yang telah ditentukan panitia.</li>
    <li>Jika Anda ingin mengubah data tetapi tidak ingin mengubah field-field file, maka biarkan saja kosong.</li>
    <li>[PENTING!] Harap unggah revisi sebelum deadline yang ditentukan panitia, karena apabila deadline sudah sampai, form akan didisable otomatis sehingga Anda tidak akan lagi bisa mengunggah revisi. Jika ini yang terjadi, maka akun Anda akan diberi flag oleh sistem sehingga Anda tidak akan dapat mendaftar ujian akhir, karena dianggap <b>belum lulus ujian Sempro</b>.</li>
    <li>[PENTING!] Harap isikan data dengan <b>sebenar-benarnya</b>. Ingat-ingat dengan baik siapa penguji 1 dan penguji 2 pada saat Anda melaksanakan ujian Sempro. Ingat-ingat betul <b>moderatornya</b> juga, karena jika terjadi kesalahan penginputan disini, maka akan berpotensi menunda saat Anda dijadwalkan maju <b>Ujian Akhir</b>.</li>
    <li>[PENTING!] Apabila moderator yang hadir pada saat ujian berbeda dengan yang di jadwal, maka yang diinput adalah dosen yang riilnya <b>HADIR PADA SAAT UJIAN</b>.</li>
    <li>Format file yang diunggah semua JPEG/PNG kecuali file proposal final dalam format PDF.</li>           
</ul>
<br/>
<a href="$backLink"><< Kembali</a>
PHREDOC;
        }

        $this->view->appendData([
            'page_title'       => $pageTitle,
            'page_description' => $description
        ]);
    }
}