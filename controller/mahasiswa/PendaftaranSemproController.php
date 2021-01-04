<?php


namespace controller\mahasiswa;


use lib\AppUtil;
use lib\CommonTemplateViewData;
use lib\DataViewerController;
use m\extended\Form;
use m\extended\FormValidation;
use m\Util;
use model\AppUploadedFileModel;
use model\CredentialModel;
use model\EventModel;
use model\GrupRisetModel;
use model\LogBimbinganModel;
use model\PendaftaranSemproModel;
use model\ProposalModel;
use model\RevisiSemproModel;
use model\VerifikasiProposalModel;

class PendaftaranSemproController extends DataViewerController
{
    const JUMLAH_MINIMAL_LOG_BIMBINGAN = 4;

    const ACTION_TYPE_EDIT = 'edit';
    const ACTION_TYPE_VIEW = 'view';

    // Model
    private $_mEvent;
    private $_mPendaftaranSempro;

    // Param
    private $_idEvent;
    private $_action;

    // Data
    private $_formFields;
    private $_currentUsername;
    private $_currentMahasiswa;
    private $_currentProposal;
    private $_currentEvent;
    private $_currentPendaftaranSempro;

    // View type
    private $_readOnly;

    protected function getIndexData($filterValues = null)
    {
        $this->_mEvent = new EventModel();

        $data = $this->_mEvent->findByKategori('seminar_proposal');

        if($data != null)
            $data = $this->_addActionLinkToSemproEvents($data);

        return $data;
    }

    private function _addActionLinkToSemproEvents($semproEvents)
    {
        $updated = array();

        foreach ($semproEvents as $event)
        {
            $detailUrl = $this->view->homeAddress('/proposal/detail-pendaftaran-sempro');

            $eventIsActive = EventModel::isActive($event);

            if(!$eventIsActive)
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

    protected function getIndexViewData()
    {
        $vd = new CommonTemplateViewData();

        $vd->setPageTitle('Pendaftaran Seminar Proposal');
        $vd->setPageDescription('Klik pada tombol <i>Daftar</i> untuk melakukan pendaftaran');

        return $vd;
    }

    public function detail()
    {
        // Check login
        $this->accessControl()->inspect();

        // Siapkan data yang dibutuhkan di awal
        $this->_initData();

        // Apakah sudah lulus/selesai sempro? Kalau sudah, berarti data hanya ditampilkan saja tidak bisa diedit
        $this->_determineReadOnly();

        // Eksekusi simpan
        $this->_handleAction();

        // View ketika action sudah selesai
        $this->_setupView();

        // Tampilkan form
        $this->_setupForm();

        $this->view->render();
    }

    private function _initData()
    {
        if($this->_mEvent == null)
            $this->_mEvent = new EventModel();

        if($this->_mPendaftaranSempro == null)
            $this->_mPendaftaranSempro = new PendaftaranSemproModel();

        $this->_formFields = array(
            'id_event',
            'tanggal_daftar',
            'id_proposal',
            'judul',
            'kode_grup_riset',
            'file_presentasi',
            'file_proposal_revisi',
            'status_persetujuan_pembimbing',
            'informasi_tambahan'
        );

        // Param
        $this->_idEvent = isset($_GET['id_event']) ? $_GET['id_event'] : null;

        $this->_action = isset($_GET['action']) ? $_GET['action'] : null;

        // Data
        $this->_currentEvent = $this->_mEvent->findById($this->_idEvent);
        $this->_currentUsername = AppUtil::getCurrentUsername($this);
        $this->_currentMahasiswa = (new CredentialModel())->findMahasiswa($this->_currentUsername);
        $this->_currentProposal = (new ProposalModel())->findByNimPengusulOrAnggota($this->_currentMahasiswa['nim']);

        if($this->_currentProposal != null)
            $this->_currentPendaftaranSempro = $this->_mPendaftaranSempro->findOneByIdProposalAndIdEvent($this->_currentProposal['id'], $this->_idEvent);
    }

    private function _determineReadOnly()
    {
        $hasDoneRevisi = (new RevisiSemproModel())->hasDoneRevisi($this->_currentProposal['id']);

        if($this->_action == self::ACTION_TYPE_VIEW || $hasDoneRevisi)
        {
            $this->_readOnly = true;
        }
        else
        {
            $this->_readOnly = false;
        }
    }

    private function _handleAction()
    {
        if($this->_readOnly)
            $this->_showReadOnlyData();

        $fv = new FormValidation($this->_formFields, true);

        if($fv->submitted())
        {
            $fv->setUploadedFileModel(new AppUploadedFileModel());

            // Field informasi_tambahan is not mandatory
            $required = Util::arrayDeleteElementByValue('informasi_tambahan', $this->_formFields);

            if($this->_currentPendaftaranSempro != null)
            {
                // Files can be emptied if user do not want to update them
                $required = Util::arrayDeleteElementByValue('file_presentasi', $required);
                $required = Util::arrayDeleteElementByValue('file_proposal_revisi', $required);
            }

            $fv->addRequiredInputs($required);

            // All files must be in PDF file format
            $fv->getUploadedFile('file_presentasi')->setAllowedTypes(['application/pdf']);
            $fv->getUploadedFile('file_proposal_revisi')->setAllowedTypes(['application/pdf']);

            $fv->processUploadedFiles();

            if ($fv->uploadedFilesError())
                $this->view->modifyData('error_message', $fv->getUploadedFilesErrorMessages());
            else
            {
                if ($fv->isValid())
                {
                    $entireData = $fv->getEntireData();

                    // Insert if not exist, otherwise update
                    if($this->_currentPendaftaranSempro == null)
                        $this->_mPendaftaranSempro->addNew($entireData);
                    else
                        $this->_mPendaftaranSempro->edit($entireData);

                    $this->view->modifyData('error_message', 'Data berhasil disimpan!');

                    // Reload form
                    $this->_currentPendaftaranSempro = $this->_mPendaftaranSempro->findOneByIdProposalAndIdEvent(
                        $entireData['id_proposal'],
                        $entireData['id_event']
                    );
                }
                else
                {
                    $this->view->modifyData('error_message', $fv->getInvalidMessage());
                }
            }
        }
    }

    private function _setupForm()
    {
        if($this->_readOnly)
            return;

        $form = new Form($this->_formFields);
        $form->setEnctype('multipart/form-data');

        $form->getInput('id_event')->setReadOnly(true)->setValue($this->_currentEvent['id']);
        $form->getInput('tanggal_daftar')->setReadOnly(true)->setValue(date("Y-m-d H:i:s"));
        $form->getInput('id_proposal')->setReadOnly(true)->setValue($this->_currentProposal['id']);
        $form->getInput('judul')->setValue($this->_currentProposal['judul_proposal'])->setType('textarea')->setExtras('style="min-height: 100px; width: 100%"');
        $form->getInput('kode_grup_riset')->setType('select')->setOptionsFromList((new GrupRisetModel())->getAsKeyValuePairs('kode', 'nama', 'nama'))->setValue($this->_currentProposal['nama_grup_riset']);
        $form->getInput('file_presentasi')->setType('file');
        $form->getInput('file_proposal_revisi')->setType('file');
        $form->getInput('status_persetujuan_pembimbing')->setType('text')->setReadonly('true')->setValue('diajukan');
        $form->getInput('informasi_tambahan')->setType('textarea')->addAdditionalAttribute('placeholder', 'Tuliskan hal lain yang mungkin ingin Anda sampaikan kepada penguji, jika ada.')->setExtras('style="min-height: 100px; width: 100%"');;

        if($this->_currentPendaftaranSempro != null)
        {
            $appliedData = $this->_currentPendaftaranSempro;

            $appliedData['file_presentasi'] = (new AppUploadedFileModel())->createFileLink($appliedData['file_presentasi'], true);
            $appliedData['file_proposal_revisi'] = (new AppUploadedFileModel())->createFileLink($appliedData['file_proposal_revisi'], true);

            $form->applyValues($appliedData);
        }

        $this->view->modifyData('form', $form);
    }

    private function _setupView()
    {
        // Set template
        $this->view->setContentTemplate('/common/data_entry_template.php');

        $pageTitle = 'Pendaftaran Seminar Proposal';

        $jumlahMinimalLogBimbingan = self::JUMLAH_MINIMAL_LOG_BIMBINGAN;

        $description = <<< PHREDOC
<a>Lengkapi formulir pendaftaran di bawah ini dengan ketentuan sebagai berikut:</a>
<ul> 
    <li>Untuk dapat melakukan pendaftaran Anda harus sudah memiliki <strong>log bimbingan yang disetujui sedikitnya sejumlah $jumlahMinimalLogBimbingan</strong>.</li>
    <li>Pastikan Anda mengupload draft proposal Anda yang <strong>terbaru</strong>, yang telah direvisi sesuai dengan masukan dari grup riset dan saran pembimbing.</li>
    <li>Jika revisi proposal Anda belum selesai, upload dulu seadanya agar form ini bisa disubmit.</li>
    <li>Anda masih <b>DAPAT MENGUBAH PROPOSAL</b> nantinya, sampai dengan sesaat sebelum Anda memulai sempro pada hari-H.</li>
    <li>Semua field isian wajib diisi kecuali 'Informasi Tambahan'.</li>
    <li>Jenis berkas yang diizinkan baik file presentasi maupun proposal adalah dalam format <strong>PDF</strong>.</li>
    <li>Ukuran maksimal untuk semua file yang diupload adalah 5mb.</li>
    <li>Pastikan koneksi internet Anda stabil karena jika berkas Anda berukuran besar, proses upload akan memakan waktu relatif lama.</li>
</ul>
PHREDOC;

        $this->view->appendData([
            'page_title'       => $pageTitle,
            'page_description' => $description
        ]);

        $this->_eligibilityCheck();
    }

    private function _eligibilityCheck()
    {
        // Exit if dont have any proposal
        if($this->_currentProposal == null)
            AppUtil::forceRenderErrorMessage($this->view, 'Anda tidak bisa melakukan pendaftaran seminar proposal karena Anda belum memiliki proposal.');

        // Exit if current proposal does not verified yet
        $isVerified = (new VerifikasiProposalModel())->proposalIsVerified($this->_currentProposal['id']);
        if(!$isVerified)
            AppUtil::forceRenderErrorMessage($this->view, 'Anda tidak bisa melakukan pendaftaran seminar proposal karena proposal Anda belum diverifikasi grup riset.');
        
        $jumlahBimbinganProposal = (new LogBimbinganModel())->getCount($this->_currentProposal['id'], 'pra_proposal', 'true');
        // Exit if bimbingan count less than regulation
        if($jumlahBimbinganProposal < self::JUMLAH_MINIMAL_LOG_BIMBINGAN)
            AppUtil::forceRenderErrorMessage($this->view, "Anda tidak bisa melakukan pendaftaran seminar proposal karena jumlah log bimbingan proposal Anda kurang (minimal 4x). Jumlah bimbingan pasca sempro Anda yang disetujui masih $jumlahBimbinganProposal.");

        // Exit if event code is not valid
        $valid = $this->_mEvent->isValid($this->_idEvent, EventModel::KATEGORI_SEMINAR_PROPOSAL);
        if(!$valid)
            AppUtil::forceRenderErrorMessage($this->view, 'Kode event tidak valid. Jangan sembarangan mengubah-ubah parameter URL di aplikasi ini. Apabila data Anda hilang/terganggu jangan salahkan panitia!');
    }

    private function _showReadOnlyData()
    {
        $viewData = array(
            'back_link'        => $this->homeAddress('/proposal/pendaftaran-sempro'),
            'page_title'       => 'Detail Pendaftaran Seminar Proposal',
            'page_description' => 'Berikut adalah detail pendaftaran seminar proposal yang telah Anda lakukan di tahap ini.',
        );

        if($this->_currentPendaftaranSempro != null)
        {
            $displayedData = AppUtil::toDisplayedData($this->_currentPendaftaranSempro);

            $viewData['displayed_data'] = $displayedData;
        }
        else
            $viewData['error_message'] = 'Anda tidak mendaftar pada tahap ini.';

        $this->renderAndExit($viewData, '/common/data_display_template.php');
    }
}