<?php


namespace controller\dosen;


use lib\AppController;
use lib\AppUtil;
use m\Application;
use m\extended\Form;
use m\extended\FormValidation;
use m\extended\Input;
use m\Util;
use model\BimbinganModel;
use model\CredentialModel;
use model\MahasiswaModel;
use model\PendaftaranUjianAkhirModel;
use model\PenilaianPembimbingModel;
use model\UjianModel;

class PenilaianMajuUjianAkhirController extends AppController
{
    const NILAI_LABEL_SKIPPED = 'skipped';

    // Models
    private $_mMahasiswa;
    private $_mPenilaianPembimbing;
    private $_mBimbingan;
    //private $_mPendaftaranUjianAkhir;

    // Param data
    private $_statusPembimbing;
    private $_idEvent;
    private $_idProposal;
    private $_action;

    // Data
    private $_currentIdDosen;
    private $_allMahasiswa;
    private $_skippedPenilaianInputIdList; // Label penilaian yang tidak dipakai

    // Form
    private $_mainForm;

    // Accessible from child classes
    protected $mUjian;

    public function __construct(Application $application)
    {
        parent::__construct($application);

        $this->_mMahasiswa = new MahasiswaModel();
        $this->_mPenilaianPembimbing = new PenilaianPembimbingModel();
        $this->_mBimbingan = new BimbinganModel();
        //$this->_mPendaftaranUjianAkhir = new PendaftaranUjianAkhirModel();
        $this->mUjian = new UjianModel();

        $this->_skippedPenilaianInputIdList = array();
    }

    public function index()
    {
        $this->accessControl()->inspect();

        $this->_retrieveParamData();

        $this->_initData();

        $this->_configureForms();

        $this->_processSubmit();

        $this->_prefillForm();

        $this->setupView();

        $this->view->render();
    }

    private function _retrieveParamData()
    {
        $this->_action     = isset($_GET['action']) ? $_GET['action'] : null;
        $this->_idProposal = isset($_GET['id_proposal']) ? $_GET['id_proposal'] : null;
        $this->_idEvent    = isset($_GET['id_event']) ? $_GET['id_event'] : null;

        // If there are no URL param for status_pembimbing then it must be the 'Persetujuan Maju Sempro' which can only be approved by Pembimbing-1 (Utama)
        $statusPembimbing = isset($_GET['status_pembimbing']) ? $_GET['status_pembimbing'] : 'Pembimbing-1';

        switch ($statusPembimbing)
        {
            case 'Pembimbing-1':
                $this->_statusPembimbing = 'pembimbing_1';
                break;
            case 'Pembimbing-2':
                $this->_statusPembimbing = 'pembimbing_2';
                break;
            default:
                $this->_statusPembimbing = null;
        }
    }

    private function _initData()
    {
        $dosen = (new CredentialModel())->findDosen(AppUtil::getCurrentUsername($this));

        $this->_currentIdDosen = $dosen['id'];

        $this->_allMahasiswa = $this->_mMahasiswa->findByIdProposal($this->_idProposal);
    }

    private function _configureForms()
    {
        $tableColumns = $this->_mPenilaianPembimbing->getColumnNames();

        $mhsCount = count($this->_allMahasiswa);

        $formFields = array();

        // Setting form's ID, because if D3, the number of students usually is two.
        // Add it to table column names and edited via Form class later on.
        for($i = 0; $i < $mhsCount; $i++)
        {
            foreach ($tableColumns as $column)
                $formFields[] = "{$column}__$i";
        }

        // TODO: Make Form class' field can be added one by one. eg: $form->addField($fieldName)
        $form = new Form($formFields);

        // Setting form's Names, creating form as many as the students count
        for($i = 0; $i < $mhsCount; $i++)
        {
            foreach ($tableColumns as $column)
            {
                $id = "{$column}__$i";

                $name = "{$column}[$i]";

                $label = str_replace('_', ' ', ucfirst($column));

                $form->getInput($id)->setName($name)->setLabel($label);
            }

            // Pre-filled values
            $mhs = $this->_allMahasiswa[$i];

            //pre_print($form);

            $form->getInput("id_event__{$i}")->setValue($this->_idEvent)->setReadOnly(true)->setType('hidden');
            $form->getInput("id_proposal__{$i}")->setValue($this->_idProposal)->setReadOnly(true)->setType('hidden');
            $form->getInput("nim__{$i}")->setValue($mhs['nim'])->setReadOnly(true)->setLabel("Nim [{$mhs['nama']}]<br/>({$mhs['kode_prodi']})");
            $form->getInput("id_pembimbing__{$i}")->setValue($this->_currentIdDosen)->setReadOnly(true)->setType('hidden');
            $form->getInput("status_pembimbing__{$i}")->setValue($this->_statusPembimbing)->setReadOnly(true)->setType('hidden');

            // TODO: Delete this when there is no error for a while
            // Pelabelan nilai
            /*$nomor = ($i + 1);
            $form->getInput("nilai_1__{$i}")->setLabel("$nomor." . '1. Perangkat');
            $form->getInput("nilai_2__{$i}")->setLabel("$nomor." . '2. Tata Tulis');
            $form->getInput("nilai_3__{$i}")->setLabel("$nomor." . '3. Sikap');
            $form->getInput("nilai_4__{$i}")->setLabel("$nomor." . '4. Keaktifan');
            $form->getInput("nilai_5__{$i}")->setLabel("$nomor." . '5. Kreatifitas dan Daya Nalar');
            $form->getInput("nilai_6__{$i}")->setLabel("$nomor." . '6. Kemandirian');
            */

            // Pelabelan nilai
            $labels = $this->getPenilaianLabels();

            $nomorMhs = ($i + 1);

            for($j = 1; $j <= count($labels); $j++)
            {
                $lbl = $labels[($j - 1)];

                $currentPenilaianInputId = "nilai_{$j}__{$i}";

                if($lbl == self::NILAI_LABEL_SKIPPED) // TODO: Analyze this one: Better to be left with input type hidden or is it better to have no input at all?
                {
                    $form->getInput($currentPenilaianInputId)->setValue(0)->setReadOnly(true)->setType('hidden');

                    $this->_skippedPenilaianInputIdList[] = "nilai_{$j}";
                }
                else
                {
                    $form->getInput($currentPenilaianInputId)->setLabel("$nomorMhs." . $lbl);
                    $this->configurePenilaianInput($form, $currentPenilaianInputId);
                }
            }
        }

        $form->getSubmit()->setValue('Tetapkan nilai dan Izinkan Maju');

        $this->_mainForm = $form;

        $this->view->appendData(['form' => $this->_mainForm]);

        $backLink = $this->createBackLink();

        $this->view->appendData(['back_link' => $backLink]);
    }

    protected function getPenilaianLabels()
    {
        return array(
            '1. Perangkat',
            '2. Tata Tulis',
            '3. Sikap',
            '4. Keaktifan',
            '5. Kreatifitas dan Daya Nalar',
            '6. Kemandirian',
            self::NILAI_LABEL_SKIPPED,
            self::NILAI_LABEL_SKIPPED,
            self::NILAI_LABEL_SKIPPED,
            self::NILAI_LABEL_SKIPPED,
            self::NILAI_LABEL_SKIPPED,
            self::NILAI_LABEL_SKIPPED,
        );
    }

    protected function setupView()
    {
        $this->view->setContentTemplate('/common/data_entry_template.php');

        $this->view->appendData(['page_title' => 'Penilaian Maju Ujian Akhir']);
        $this->view->appendData(['page_description' => 'Silahkan mengentrikan nilai untuk mahasiswa bimbingan Anda pada form di bawah.<p><u>Catatan:</u></p>
<ul>
    <li>Jika D4, maka Anda hanya memberikan nilai ke satu mahasiswa.</li>
    <li>Jika D3, maka Anda bisa memberikan nilai ke lebih dari satu mahasiswa.</li>
    <li>Jika D3, nilai mahasiswa yang satu boleh berbeda dengan nilai mahasiswa yang lainnya.</li>
    <li>Rentang nilai adalah 0 s.d. 100.</li>
</ul>']);
    }

    protected function createBackLink()
    {
        return $this->homeAddress('/dosen/bimbingan/persetujuan-pendaftaran-ujian-akhir');
    }

    private function _processSubmit()
    {
        $fields = $this->_mPenilaianPembimbing->getColumnNames();

        $fv = new FormValidation($fields);

        if($fv->submitted())
        {
            //pre_print($_POST);

            $required = Util::arrayDeleteElementsByValues($this->_skippedPenilaianInputIdList, $fields);

            //pre_print($required);

            // [SudahTODO]: Ini perlu diperbaiki karena akan lolos bila isinya $_POST itu array (inputnya namanya lebih dari 1) <-- Ternyata bisa handle walaupun inputnya name-nya array. Gak tau kenapa. Harus dicek dan dipastikan nanti kalau ada waktu.
            $fv->addRequiredInputs($required);

            if($fv->isValid())
            {
                $data = $fv->getData();

                // Ubah jadi array 2d dengan nama input yang disatukan
                $normalized = Util::arrayMultipleNamedInputFormTo2DArray($data);

                //pre_print($normalized, true);

                // TODO: Rename this method because this method not only add but also update records
                $this->_mPenilaianPembimbing->addNewRecords($normalized);

                $resultMessage = $this->_approvePendaftaran();

                $this->view->appendData(['error_message' => "Nilai berhasil disimpan. $resultMessage"]);
            }
            else
                $this->view->appendData(['error_message' => /*$fv->getInvalidMessage()*/ /*'Aisyah CANTIKK!'*/'Penyimpanan nilai tidak dapat dilakukan, pastikan semua elemen penilaian telah terisi!']);
        }
    }

    private function _prefillForm()
    {
        $existing = $this->_mPenilaianPembimbing->findByIdEventIdProposalAndIdPembimbing(
            $this->_idEvent,
            $this->_idProposal,
            $this->_currentIdDosen,
            true
        );

        if($existing == null)
            return;

        $existing = Util::arrayTableSortBasedOnAnotherArrayColumn($existing, $this->_allMahasiswa, 'nim');
        //pre_print($existing);

        $i = 0;

        foreach ($existing as $nilai)
        {
            foreach ($nilai as $key => $value)
                $this->_mainForm->getInput("{$key}__{$i}")->setValue($value);

            $i++;
        }
    }

    private function _approvePendaftaran()
    {
        $this->_eligibilityCheck($this->_idProposal, $this->_statusPembimbing);

        // Disamakan dengan di PendaftaranUjianAkhirModel::STATUS_PEMBIMBING -> 'Pembimbing-1', padahal di param = 'pembimbing_1'
        $statusPembimbing = ucfirst(strtolower(str_replace('_', '-', $this->_statusPembimbing)));

        return $this->executeApprove($this->_idProposal, $this->_idEvent, $this->_currentIdDosen, $statusPembimbing);
    }

    protected function executeApprove($idProposal, $idEvent, $currentIdDosen, $statusPembimbing)
    {
        $mPendaftaranUjianAkhir = new PendaftaranUjianAkhirModel();

        if($mPendaftaranUjianAkhir->accept($idProposal, $idEvent, $currentIdDosen, $statusPembimbing) !== false)
        {
            $actionResult = "Proposal dengan id = {$idProposal} telah disetujui untuk mendaftar maju ujian akhir.";

            // Jika pendaftaran ini sudah diapprove oleh semua pembimbing, masukkan ke tabel ujian..
            if($mPendaftaranUjianAkhir->isFullyApproved($idProposal, $idEvent))
            {
                // Jadwalkan jika belum terjadwal
                if($this->mUjian->findByIdEventAndIdProposal($idEvent, $idProposal) == null) {
                    $this->mUjian->addNew($idEvent, $idProposal);
                    $statusEntry = 'Data baru ditambahkan ke tabel ujian.';
                }
                else
                    $statusEntry = 'Data sudah ada di tabel ujian.';

                $actionResult .= "<br/>Pendaftaran sudah disetujui oleh kedua pembimbing dan akan segera dijadwalkan. $statusEntry";
            }
            else
                $actionResult .= "<br/>Pendaftaran menunggu persetujuan dosen pembimbing lainnya untuk bisa dijadwalkan.";
        }
        else
            $actionResult = "Proposal gagal disetujui maju ujian akhir!";

        return $actionResult;
    }

    private function _eligibilityCheck($idProposal, $statusPembimbingCheck)
    {
        $statusPembimbingRiil = $this->_mBimbingan->findStatusDosenPembimbing($this->_currentIdDosen, $idProposal);

        if(!$statusPembimbingRiil == $statusPembimbingCheck)
            $this->renderErrorAndExit("Anda bukan pembimbing dari proposal dengan id: $idProposal.");
    }

    protected function configurePenilaianInput(Form &$form, $currentPenilaianInputId)
    {
        $form->getInput($currentPenilaianInputId)->setType('text');
    }
}