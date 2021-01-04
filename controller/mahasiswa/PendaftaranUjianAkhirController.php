<?php


namespace controller\mahasiswa;


use lib\AppController;
use lib\AppUtil;
use m\Application;
use m\extended\Form;
use m\extended\FormValidation;
use m\Util;
use model\AppUploadedFileModel;
use model\BeritaAcaraUjianModel;
use model\EventModel;
use model\LogBimbinganModel;
use model\MahasiswaModel;
use model\NilaiPklModel;
use model\PendaftaranUjianAkhirModel;
use model\ProposalModel;
use model\RevisiSemproModel;

class PendaftaranUjianAkhirController extends AppController
{
    //Const
    const MIN_BIMBINGAN_COUNT = 8;

    // Models
    private $_mPendaftaranUjianAkhir;
    private $_mProposal;
    private $_mEvent;
    private $_mMahasiswa;

    // Data
    private $_currentNim;
    private $_currentProposal;
    private $_currentEvent;
    private $_existingData;

    private $_indexUrl;
    private $_mRevisiSempro;
    private $_mNilaiPkl;
    private $_mLogBimbingan;

    public function __construct(Application $application)
    {
        parent::__construct($application);

        $this->_mPendaftaranUjianAkhir = new PendaftaranUjianAkhirModel();
        $this->_mProposal = new ProposalModel();
        $this->_mEvent = new EventModel();
        $this->_mMahasiswa = new MahasiswaModel();
        $this->_mRevisiSempro = new RevisiSemproModel();
        $this->_mNilaiPkl = new NilaiPklModel();
        $this->_mLogBimbingan = new LogBimbinganModel();

        $this->_currentNim = null;
        $this->_currentProposal = null;
        $this->_currentEvent = null;
        $this->_existingData = null;

        $this->_indexUrl = $this->homeAddress('/pendaftaran-ujian-akhir/index');
    }

    public function index()
    {
        $this->accessControl()->inspect();

        $viewData = array(
            'page_title'       => 'Pendaftaran Ujian Akhir',
            'page_description' => 'Klik pada tombol <i>Daftar</i> untuk melakukan pendaftaran. <p><u>Catatan:</u><ul>
<li>Ketika waktu yang tertera di kolom "Tanggal Selesai" sudah terlampaui, berarti Anda sudah tidak bisa melakukan pendaftaran lagi.</li>
</ul></p>',
        );

        $events = $this->_mEvent->findByKategori('ujian_akhir');

        if($events != null) {
            $events = $this->_addActionLinkToEvents($events);
            $events = $this->_addHourToEachEventTime($events); // Tambahkan jam di tanggal, kurangi tanggal selesai 1 hari
            $viewData['headers'] = AppUtil::toTableDisplayedHeaders($events);
            $viewData['displayed_data'] = $events;
        }

        $this->view->setData($viewData);
        $this->view->setContentTemplate('/common/data_table_display_template.php');
        $this->view->render();
    }

    private function _addActionLinkToEvents($semproEvents)
    {
        $updated = array();

        foreach ($semproEvents as $event)
        {
            $detailUrl = $this->view->homeAddress('/pendaftaran-ujian-akhir');

            $eventFinished = AppUtil::dateIsPassed($event['tanggal_selesai']);

            if($eventFinished)
            {
                $detailUrl .= "/lihat?id_event={$event['id']}";

                $event['action'] = AppUtil::createActionLink($detailUrl, 'Lihat Detail');
            }
            else
            {
                $detailUrl .= "/daftar?id_event={$event['id']}";

                $event['action'] = AppUtil::createActionLink($detailUrl, 'Daftar Sekarang!');
            }

            $updated[] = $event;
        }

        return $updated;
    }

    public function daftar()
    {
        $this->accessControl()->inspect();

        $this->_setupDaftarView();

        // Cannot be placed inside constructor!
        $this->_currentNim = AppUtil::getCurrentUsername($this);
        $this->_currentProposal = $this->_mProposal->findByNimPengusulOrAnggota($this->_currentNim);
        $this->_retrieveCurrentEvent();

        $this->_eligibilityCheck();

        $this->_existingData = $this->_mPendaftaranUjianAkhir->findOneByIdProposalAndIdEvent($this->_currentProposal['id'], $this->_currentEvent['id']);

        //pre_print($this->_existingData, true);

        // If date is passed, redirect to 'lihat'
        if(EventModel::isEnded($this->_currentEvent) && $this->_existingData == null)
            $this->redirect("/pendaftaran-ujian-akhir/lihat?id_event={$this->_currentEvent['id']}");

        // Form fields
        //$fields = array('id_proposal', 'id_event', 'file_laporan_akhir', 'file_presentasi', 'file_draft_publikasi', 'link_demo', 'status_persetujuan_pembimbing_1', 'status_persetujuan_pembimbing_2');
        $fields = $this->_mPendaftaranUjianAkhir->getColumnNames();

        // Process data if this is a submit event
        $fv = new FormValidation($fields, true);
        if($fv->submitted())
            $this->_processData($fv);

        // Draw form otherwise
        $form = new Form($fields);
        $form = $this->_configureForm($form);
        $form = $this->_preFillForm($form);

        // Render the view
        $this->view->appendData(['form' => $form]);
        $this->view->render();
    }

    private function _eligibilityCheck()
    {
        // Kalau current proposalnya null, berarti belum mengentrikan data proposal
        if($this->_currentProposal == null)
            $this->renderErrorAndExit('Anda tidak bisa mendaftar ujian karena belum mengentrikan data proposal. Anda dapat mengakses form entri proposal pada menu [Proposal] >> [Entri Data Proposal].');

        // Harus sudah ada nim-nya di tabel nilai PKL.
        if(!$this->_mNilaiPkl->hasDonePkl($this->_currentProposal['nim_pengusul']))
            $this->renderErrorAndExit('Anda tidak bisa mendaftar ujian karena nilai PKL Anda belum Ada!');

        // Jika mahasiswanya bukan dari D3
        if(!$this->_mMahasiswa->isD3($this->_currentProposal['nim_pengusul']))
        {
            // Harus sudah mengentrikan revisi sempro.
            if(!$this->_mRevisiSempro->hasDoneRevisi($this->_currentProposal['id']))
                $this->renderErrorAndExit('Anda tidak bisa mendaftar ujian karena belum menyelesaikan revisi Sempro!');

            // Harus sudah bimbingan minimal 8 kali untuk pembimbing 1 dan 2 yang semuanya disetujui, untuk D4.
            $bimbinganCount = $this->_mLogBimbingan->findLogBimbinganCount($this->_currentNim);

            $b1 = $bimbinganCount[0];
            $b2 = $bimbinganCount[1];

            $minCount = self::MIN_BIMBINGAN_COUNT;

            // TODO: Jumlah minimum bimbingan berbeda pada tahap ujian yang berbeda.
            if($b1 < $minCount || $b2 < $minCount)
                $this->renderErrorAndExit("Anda tidak bisa mendaftar ujian karena jumlah bimbingan yang <u>disetujui</u> ke salah satu atau kedua dosen pembimbing kurang memadai (<strong>$b1</strong>/$minCount & <strong>$b2</strong>/$minCount).");
        }
    }

    public function lihat()
    {
        $this->accessControl()->inspect();
        $this->_setupLihatView();

        // Cannot be placed inside constructor!
        $this->_currentNim = AppUtil::getCurrentUsername($this);
        $this->_currentProposal = $this->_mProposal->findByNimPengusulOrAnggota($this->_currentNim);
        $this->_retrieveCurrentEvent();

        $this->_existingData = $this->_mPendaftaranUjianAkhir->findOneByIdProposalAndIdEvent($this->_currentProposal['id'], $this->_currentEvent['id']);

        $kelulusanData = (new BeritaAcaraUjianModel())->findOneRekapKelulusanUjianByIdProposalAndNim($this->_currentProposal['id'], $this->_currentNim);

        // Berikan kesempatan edit bagi yang sudah mendaftar tapi belum lulus ujian
        // TODO: Berarti bagi yang sudah ujian tapi BELUM lulus masih bisa edit dong?
        if($this->_existingData != null && $kelulusanData == null)
            $this->redirect("/pendaftaran-ujian-akhir/daftar?id_event={$this->_currentEvent['id']}");

        if($this->_existingData == null)
            $this->renderErrorAndExit('Anda tidak mendaftar pada tahap ini.');

        $this->view->appendData(['displayed_data' => AppUtil::toDisplayedData($this->_existingData)]);

        $this->view->render();
    }

    private function _setupLihatView()
    {
        // Set template
        $this->view->setContentTemplate('/common/data_display_template.php');
        $pageTitle = 'Data Pendaftaran Ujian Akhir';
        $description = 'Berikut ini adalah data pendaftaran ujian akhir yang telah Anda lakukan pada tahap ini.';

        $this->view->appendData([
            'page_title'       => $pageTitle,
            'page_description' => $description,
            'back_link'        => $this->_indexUrl
        ]);
    }

    private function _retrieveCurrentEvent()
    {
        // Check event Id-nya valid apa tidak
        // Bisa ada di URL (GET) ketika dibuka dari URL, bisa POST ketika submit form
        $idEvent = (isset($_GET['id_event'])) ? $_GET['id_event'] : null;

        // Ambil info event sempro saat ini.
        $this->_currentEvent = $this->_mEvent->findById($idEvent);

        // Jika tidak valid langsung STOP!
        if($this->_currentEvent == null) $this->renderErrorAndExit(
            'Kode event tidak valid! Jangan mengedit-edit parameter URL. Jika data Anda corrupt, jangan salahkan panitia!'
        );

        // Jika bukan event ujian akhir langsung STOP!
        if($this->_currentEvent['kategori'] != 'ujian_akhir') $this->renderErrorAndExit(
            'Kode event tidak valid: Bukan kode event ujian akhir!'
        );

        // Jika tanggal belum buka langsung STOP!
        if(!EventModel::isStarted($this->_currentEvent)) $this->renderErrorAndExit(
            'Pendaftaran event ini belum dibuka. Cobalah lagi ketika sudah tiba tanggal pembukaannya.'
        );
    }

    private function _setupDaftarView()
    {
        // Set template
        $this->view->setContentTemplate('/common/data_entry_template.php');
        $pageTitle = 'Pendaftaran Ujian Akhir';
        $description = <<< PHREDOC
<p> 
    Lengkapilah isian berikut untuk mendaftar maju ujian akhir
</p>
<a>Catatan: </a>
<ul>
    <li>Anda tidak akan bisa mendaftar apabila belum melakukan <strong>Revisi Sempro</strong>.</li>
    <li>Anda tidak akan bisa mendaftar apabila belum memiliki minimal 8 entri <strong>Log Bimbingan</strong> yang disetujui oleh kedua dosen pembimbing.</li>
    <li>File draft publikasi diisi draft makalah/paper untuk D4 & poster untuk D3.</li>
    <li>Anda masih dapat mengedit isi form ini sampai dengat sesaat sebelum hari-H ujian.</li>
</ul>
<p/>
<a>Keterangan form: </a>
<ul>
    <li>File SKLA harus berjenis PDF, dan wajib diisi.</li>
    <li>File laporan akhir harus berjenis PDF, dan wajib diisi.</li>
    <li>File presentasi harus berjenis PDF, dan wajib diisi.</li>
    <li>File draft publikasi harus berjenis PDF atau PNG, dan wajib diisi. Bila belum selesai, silahkan upload seadanya.</li>
    <li>Link video demo berupa URL ke video Anda di YouTube.</li>
    <li>Link instalasi aplikasi berupa URL App Store, hosting atau tautan ke file instalasi aplikasi Anda.</li>
    <li>Jika aplikasi yang Anda buat membutuhkan username/password untuk mencobanya, isikan username/password tersebut di kolom isian <b>Informasi Tambahan</b>.</li>
</ul>
<a><u style="color: #bb534d;">Penting</u> untuk diperhatikan bila prodi Anda  <b>D3</b></a>:
<ul>
    <li>Pastikan hanya <b>salah 1 mahasiswa saja</b> yang mendaftar ujian. Mahasiswa lainnya akan otomatis terdaftar juga.</li>
    <li>Tidak ada perbedaan nilai dalam 1 tim antara yang mendaftar atau tidak. Hal ini hanya untuk mempermudah pengolahan data pada sistem saja. Selain itu semua dianggap sama.</li>
</ul>
PHREDOC;

        $this->view->appendData([
            'page_title'       => $pageTitle,
            'page_description' => $description,
            'back_link'        => $this->_indexUrl
        ]);
    }

    private function _configureForm(Form $form)
    {
        $idLabel = $this->_mMahasiswa->isD3($this->_currentProposal['nim_pengusul']) ? 'Nomor LA' : 'Nomor Skripsi';

        $form->setEnctype('multipart/form-data');
        $form->getInput('id_proposal')->setReadOnly(true)->setLabel($idLabel);
        $form->getInput('id_event')->setReadOnly(true);
        $form->getInput('file_skla')->setType('file');
        $form->getInput('file_laporan_akhir')->setType('file');
        $form->getInput('file_presentasi')->setType('file');
        $form->getInput('file_draft_publikasi')->setType('file')->setLabel('File draft publikasi<br/>(D3: Poster/D4: Paper)');
        $form->getInput('status_persetujuan_pembimbing_1')->setReadonly(true);
        $form->getInput('status_persetujuan_pembimbing_2')->setReadonly(true);
        $form->getInput('informasi_tambahan')->setType('textarea')->setExtras('style="min-height: 200px; width: 100%"')->setLabel('Informasi Tambahan<br/>(username/password aplikasi, jika ada)<br/>Atau informasi lain yang perlu Anda sampaikan ke penguji');

        return $form;
    }

    private function _preFillForm(Form $form)
    {
        $form->getInput('id_proposal')->setValue($this->_currentProposal['id']);
        $form->getInput('id_event')->setValue($this->_currentEvent['id']);

        $form->getInput('status_persetujuan_pembimbing_1')->setValue('diajukan');
        $form->getInput('status_persetujuan_pembimbing_2')->setValue('diajukan');

        if($this->_existingData != null)
        {
            $ufm = new AppUploadedFileModel();

            //$e = $this->_existingData;

            $this->_existingData['file_skla'] = $ufm->createFileLink($this->_existingData['file_skla'], true);
            $this->_existingData['file_laporan_akhir'] = $ufm->createFileLink($this->_existingData['file_laporan_akhir'], true);
            $this->_existingData['file_presentasi'] = $ufm->createFileLink($this->_existingData['file_presentasi'], true);
            $this->_existingData['file_draft_publikasi'] = $ufm->createFileLink($this->_existingData['file_draft_publikasi'], true);

            $form->applyValues($this->_existingData);

            /*
            $form->getInput('file_laporan_akhir')->setValue($e['file_laporan_akhir']);
            $form->getInput('file_presentasi')->setValue($e['file_presentasi']);
            $form->getInput('file_draft_publikasi')->setValue($e['file_draft_publikasi']);

            $form->getInput('link_video_demo')->setValue($e['link_demo']);

            $form->getInput('status_persetujuan_pembimbing_1')->setValue($e['status_persetujuan_pembimbing_1']);
            $form->getInput('status_persetujuan_pembimbing_2')->setValue($e['status_persetujuan_pembimbing_2']);
            */
        }

        return $form;
    }

    private function _processData(FormValidation $fv)
    {
        if($this->_existingData == null) // INSERT! User must upload all required files.
        {
            /*
            $required = array(
                'id_proposal',
                'id_event',
                'file_laporan_akhir',
                'file_presentasi',
                'file_draft_publikasi',
                'status_persetujuan_pembimbing_1',
                'status_persetujuan_pembimbing_2'
            );
            */
            $required = $this->_mPendaftaranUjianAkhir->getColumnNames();

            Util::arrayAssocRemoveElementsByValue('informasi_tambahan', $required);
        }
        else // UPDATE! User allowed to not uploading any files
        {
            $required = array(
                'id_proposal',
                'id_event',
                'link_video_demo',
                'link_instalasi_aplikasi',
                'status_persetujuan_pembimbing_1',
                'status_persetujuan_pembimbing_2',
            );
        }

        $fv->setUploadedFileModel(new AppUploadedFileModel());

        $fv->addRequiredInputs($required);

        $fv->getUploadedFile('file_skla')->setAllowedTypes(['application/pdf']);
        $fv->getUploadedFile('file_laporan_akhir')->setAllowedTypes(['application/pdf']);
        $fv->getUploadedFile('file_presentasi')->setAllowedTypes(['application/pdf']);
        $fv->getUploadedFile('file_draft_publikasi')->setAllowedTypes(['application/pdf', 'image/png']);

        $fv->processUploadedFiles();

        if ($fv->uploadedFilesError())
            $this->view->modifyData('error_message', $fv->getUploadedFilesErrorMessages());
        else
        {
            if ($fv->isValid())
            {
                $entireData = $fv->getEntireData();

                // Bagaimanapun, mahasiswa tidak diizinkan mengubah status persetujuan pembimbing
                unset($entireData['status_persetujuan_pembimbing_1']);
                unset($entireData['status_persetujuan_pembimbing_2']);

                $this->_mPendaftaranUjianAkhir->addOrEdit($entireData);

                $this->view->modifyData('error_message', 'Data berhasil disimpan!');
            }
            else
                $this->view->modifyData('error_message', $fv->getInvalidMessage());
        }
    }

    // Tambahkan waktu diakhir jam supaya mhs tidak bingung antara jam 00:00 dan 24:00
    private function _addHourToEachEventTime(array $events)
    {
        $modified = array();

        foreach ($events as $row)
        {
            $row['waktu_mulai'] = $row['tanggal_mulai'] . " 00:00:00";
            unset($row['tanggal_mulai']);

            $row['waktu_selesai'] = date('Y-m-d', strtotime('-1 day', strtotime($row['tanggal_selesai']))) . " 23:59:59";
            unset($row['tanggal_selesai']);

            $modified[] = array(
                'id'            => $row['id'],
                'nama'          => $row['nama'],
                'deskripsi'     => $row['deskripsi'],
                'kategori'      => $row['kategori'],
                'waktu_mulai'   => $row['waktu_mulai'],
                'waktu_selesai' => $row['waktu_selesai'],
                'action'        => $row['action']
            );
        }

        return $modified;
    }
}