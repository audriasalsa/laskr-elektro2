<?php


namespace controller\mahasiswa;


use lib\AppController;
use m\Application;
use m\Controller;
use m\extended\Form;
use m\extended\FormValidation;
use m\Util;
use model\AppUploadedFileModel;
use model\PenilaianUjianModel;
use model\RevisiUjianAkhirModel;

class UnggahRevisiUjianAkhirController extends AppController
{
    // Param data
    private $_currentNomorUjian;

    // Data
    private $_rekapKeputusanUjian;
    private $_mPenilaianUjian;
    /**
     * @var RevisiUjianAkhirModel
     */
    private $_mRevisiUjianAkhir;
    private $_mAppUploadedFile;
    /**
     * @var Form
     */
    private $_mainForm;
    private $_existingRevisi;

    public function __construct(Application $application)
    {
        parent::__construct($application);

        $this->_mPenilaianUjian = new PenilaianUjianModel();
        $this->_mRevisiUjianAkhir = new RevisiUjianAkhirModel();
        $this->_mAppUploadedFile = new AppUploadedFileModel();
    }

    public function index()
    {
        $this->accessControl()->inspect();

        $this->_retrieveParamData();

        $this->_retrieveData();

        $this->_configureForm();

        $this->_processSubmit();

        $this->_preFillForm();

        $this->_renderView();
    }

    private function _renderView()
    {
        $this->view->setContentTemplate('/common/data_entry_template.php');

        $this->view->appendData(['page_title' => 'Unggah Revisi']);

        $this->view->appendData(['page_description' => 'Isilah form di bawah ini untuk mengirimkan revisi yang telah diberikan oleh dosen penguji pada saat ujian. Revisi harus disetujui oleh kedua pembimbing sebelum waktu yang sudah ditentukan.<p><b><u>Ketentuan:</u></b></p><ul>
<li>File laporan final berjenis PDF, maksimum 5MB.</li>
<li>File draft publikasi final berjenis PDF (D4 - paper) atau PNG (D3 - poster), maksimum 2MB.</li>
<li>File draft publikasi final adalah draft versi terkini yang sudah Anda perbaiki, sesuai dengan revisi dari penguji jika ada. Tidak masalah jika belum dipublikasikan, karena SIAP belum dimulai.</li>
<li>Anda dapat mengubah judul laporan akhir bila diminta oleh penguji. Jika tidak, maka biarkan saja.</li>
</ul>']);

        $this->view->appendData(['form' => $this->_mainForm]);

        $this->view->appendData(['back_link' => $this->homeAddress("/ujian-akhir/revisi/detail?nomor_ujian={$this->_currentNomorUjian}")]);

        $this->view->render();
    }

    private function _retrieveParamData()
    {
        $this->_currentNomorUjian = isset($_GET['nomor_ujian']) ? $_GET['nomor_ujian'] : null;
    }

    private function _retrieveData()
    {
        $allKeputusan = $this->_mPenilaianUjian->findAllRekapKeputusanUjianByNomorUjian($this->_currentNomorUjian);

        if($allKeputusan == null)
            $this->renderErrorAndExit('Data ujian tidak ditemukan! Apakah Anda sudah melaksanakan ujian akhir?');

        $this->_rekapKeputusanUjian = $allKeputusan[0];

        $this->_existingRevisi = $this->_mRevisiUjianAkhir->findOneByIdUjian($this->_currentNomorUjian);
    }

    private function _configureForm()
    {
        $rku = $this->_rekapKeputusanUjian;

        $fields = $this->_mRevisiUjianAkhir->getColumnNames();

        $form = new Form($fields);

        // Has files
        $form->setEnctype('multipart/form-data');

        // Files
        $form->getInput('file_laporan_final')->setType('file');
        $form->getInput('file_draft_publikasi_final')->setType('file');

        // Readonly
        $form->getInput('id_ujian')->setReadOnly(true)->setValue($this->_currentNomorUjian)->setLabel('Nomor Ujian');
        $form->getInput('id_proposal')->setReadOnly(true)->setValue($rku['id_proposal']);

        // Status
        $status1 = $this->_existingRevisi == null ? 'diajukan' : $this->_existingRevisi['status_persetujuan_penguji_1'];
        $status2 = $this->_existingRevisi == null ? 'diajukan' : $this->_existingRevisi['status_persetujuan_penguji_2'];
        $form->getInput('status_persetujuan_penguji_1')->setReadOnly(true)->setValue($status1);
        $form->getInput('status_persetujuan_penguji_2')->setReadOnly(true)->setValue($status2);

        // Readonly hidden
        $form->getInput('id_dosen_penguji_1')->setReadOnly(true)->setValue($rku['id_dosen_penguji_1'])->setType('hidden');
        $form->getInput('id_dosen_penguji_2')->setReadOnly(true)->setValue($rku['id_dosen_penguji_2'])->setType('hidden');

        // Prefill
        $form->getInput('judul_final')->setValue($rku['judul_proposal'])->setType('textarea')->setExtras('style="min-height: 100px; width: 100%"');

        $this->_mainForm = $form;
    }

    private function _processSubmit()
    {
        $fields = $this->_mRevisiUjianAkhir->getColumnNames();

        $fv = new FormValidation($fields, true);

        if($fv->submitted())
        {
            if($this->_existingRevisi == null) // INSERT! User must upload all required files.
            {
                // All fields are required!
                $required = $fields;
            }
            else // UPDATE! User allowed to not upload any files
            {
                $required = $fields;

                Util::arrayAssocRemoveElementsByValue('file_laporan_final', $required);
                Util::arrayAssocRemoveElementsByValue('file_draft_publikasi_final', $required);
            }

            $fv->setUploadedFileModel(new AppUploadedFileModel());

            $fv->addRequiredInputs($required);

            $fv->getUploadedFile('file_laporan_final')->setAllowedTypes(['application/pdf'])->setAllowedMaximumSizeMegaBytes(5);
            $fv->getUploadedFile('file_draft_publikasi_final')->setAllowedTypes(['application/pdf', 'image/png'])->setAllowedMaximumSizeMegaBytes(2);

            $fv->processUploadedFiles();

            if ($fv->uploadedFilesError())
                $this->view->modifyData('error_message', $fv->getUploadedFilesErrorMessages());
            else
            {
                if ($fv->isValid())
                {
                    $entireData = $fv->getEntireData();

                    // Bagaimanapun, mahasiswa tidak diizinkan mengubah status persetujuan pembimbing
                    unset($entireData['status_persetujuan_penguji_1']);
                    unset($entireData['status_persetujuan_penguji_2']);

                    $this->_mRevisiUjianAkhir->addOrEdit($entireData);

                    $this->view->modifyData('error_message', 'Data berhasil disimpan!');
                }
                else
                    $this->view->modifyData('error_message', $fv->getInvalidMessage());
            }
        }
    }

    private function _preFillForm()
    {
        if($this->_existingRevisi != null)
        {
            $this->_mainForm->applyValues($this->_existingRevisi);
            $this->_mainForm->getInput('file_laporan_final')->setValue(
                $this->_mAppUploadedFile->createFileLink($this->_existingRevisi['file_laporan_final'], true));
            $this->_mainForm->getInput('file_draft_publikasi_final')->setValue(
                $this->_mAppUploadedFile->createFileLink($this->_existingRevisi['file_draft_publikasi_final'], true));
        }
    }
}