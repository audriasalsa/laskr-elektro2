<?php


namespace controller\dosen;


use lib\AppController;
use lib\AppUtil;
use m\Application;
use m\extended\Form;
use m\extended\FormValidation;
use m\extended\Input;
use m\Util;
use model\BeritaAcaraUjianModel;
use model\CredentialModel;
use model\DosenModel;
use model\MahasiswaModel;
use model\PenilaianUjianModel;
use model\ProposalModel;
use model\UjianModel;

class PenilaianUjianAkhirController extends AppController
{
    const JENIS_PENILAIAN_SEMINAR_PROPOSAL = 'seminar_proposal';
    const JENIS_PENILAIAN_UJIAN_AKHIR = 'ujian_akhir';
    const NILAI_LABEL_SKIPPED = 'skipped';

    private $_mPenilaianUjian;
    private $_mBeritaAcaraUjian;
    private $_mCredential;

    // Data
    private $_existingPenilaianData;
    private $_existingBeritaAcara;

    // Data
    private $_currentDosen;
    private $_ujian;

    protected $jenisPenilaian;
    protected $originalFormFields;
    protected $modifiedFormFields;
    protected $currentNimPengusul;
    protected $currentNomorUjian;
    protected $currentIdDosen;
    protected $mahasiswaPengusul;
    protected $mahasiswaAnggota;
    protected $allMahasiswa;
    protected $mainForm;

    public function __construct(Application $application)
    {
        parent::__construct($application);

        $this->_mPenilaianUjian = new PenilaianUjianModel();
        $this->_mBeritaAcaraUjian = new BeritaAcaraUjianModel();
        $this->_mCredential = new CredentialModel();

        //$this->_formFields = $this->_mPenilaianUjian->getColumnNames();
        $this->originalFormFields = [
            'id_ujian',
            'id_dosen',
            'nama_pengusul',
            'nim',
            'peran',
            'nilai_1',
            'nilai_2',
            'nilai_3',
            'revisi',
            'kesimpulan'
        ];

        $this->jenisPenilaian = self::JENIS_PENILAIAN_UJIAN_AKHIR;
    }

    public function index()
    {
        $this->accessControl()->inspect();

        $this->_initData();

        $this->_processSubmit();

        $this->_setupView();

        $this->_configureForm();

        $this->_prefillForm();

        $this->view->render();
    }

    private function _initData()
    {
        $this->currentNomorUjian  = isset($_GET['nomor_ujian']) ? $_GET['nomor_ujian'] : null;
        $this->currentNimPengusul = isset($_GET['nim']) ? $_GET['nim'] : null;

        // If there is no nomor ujian in URL param, exit immediately
        if($this->currentNomorUjian == null)
            $this->renderErrorAndExit('Nomor ujian tidak valid!');

        // Find ujian, if not found exit.
        $this->_ujian = $this->findUjian($this->currentNomorUjian);
        if($this->_ujian == null)
            $this->renderErrorAndExit('Data ujian tidak ditemukan! Pastikan Anda mengakses halaman ini dari menu Ujian Akhir.');

        // Find current dosen account, exit if not found
        $this->_retrieveCurrentDosen();

        if($this->_currentDosen == null)
            $this->renderErrorAndExit('Data dosen tidak ditemukan! Pastikan Anda sudah login, dan terdaftar di sistem.');
        $this->currentIdDosen = $this->_currentDosen['id'];

        // Find pengusul, exit if not found
        $mMahasiswa = new MahasiswaModel();
        $this->mahasiswaPengusul = $mMahasiswa->findByNim($this->currentNimPengusul);
        if($this->mahasiswaPengusul  == null)
            $this->renderErrorAndExit('Data mahasiswa tidak ditemukan!');

        // Try find anggota if exists
        $this->allMahasiswa = [$this->mahasiswaPengusul];

        $p = (new ProposalModel())->findByNimPengusul($this->currentNimPengusul);

        if($p != null)
        {
            $nimAnggota = $p['nim_anggota'];

            // Kalau null berarti kelompok ini hanya ada pengusul saja
            if($nimAnggota != null)
            {
                $this->mahasiswaAnggota = $mMahasiswa->findByNim($nimAnggota);

                $this->allMahasiswa[] = $this->mahasiswaAnggota;
            }
        }

        $this->_retrieveExistingPenilaianData();
    }

    private function _retrieveExistingPenilaianData()
    {
        $penilaianPengusul = $this->_mPenilaianUjian->findOneByNomorUjianIdDosenAndNim($this->currentNomorUjian, $this->currentIdDosen, $this->currentNimPengusul);

        $this->_existingPenilaianData = array($penilaianPengusul);

        if(count($this->allMahasiswa) > 1)
        {
            $penilaianAnggota = $this->_mPenilaianUjian->findOneByNomorUjianIdDosenAndNim($this->currentNomorUjian, $this->currentIdDosen, $this->mahasiswaAnggota['nim']);

            $this->_existingPenilaianData[] = $penilaianAnggota;
        }
    }

    private function _setupView()
    {
        $namaDosen = $this->_currentDosen['nama'];
        $namaMahasiswaPengusul = $this->mahasiswaPengusul['nama'];

        $this->view->setContentTemplate('/common/data_entry_template.php');
        $this->view->appendData(['back_link' => $this->getBackLink($this->currentNomorUjian)]);

        // TODO: This will give problem if the app is used by other jurusan
        $jenis = $this->_ujian['kode_prodi'] == 'D4-TI' ? 'Skripsi' : 'LA';
        $judul = $this->_ujian['judul_proposal'];

        $namaAllMahasiswa = (count($this->allMahasiswa) > 1) ? "$namaMahasiswaPengusul & {$this->mahasiswaAnggota['nama']}" : $namaMahasiswaPengusul;

        $description = <<< PHREDOC
<ul>
    <li>Dosen Penilai: <b>$namaDosen</b></li>
    <li>Jenis: <b>$jenis</b></li>
    <li>Judul: <b>$judul</b></li>
    <li>Mahasiswa: <b>$namaAllMahasiswa</b></li>
</ul>
<p>
<u style="color: #8b1014">Perhatian:</u>
<ul> 
    <li>Mohon untuk tidak mengentrikan nilai apabila Anda <b>bukan</b> penguji-1 maupun penguji-2.</li>
    <li>Jika Anda menguji mahasiswa Diploma III, biasanya akan ada 2 nilai mahasiswa dan Anda dapat memberikan nilai yang berbeda kepada masing-masing mahasiswa.</li>
    <li>Jika Anda ingin memberikan masukan yang sama kepada keduanya, klik button 'Samakan Nilai'.</li>
    <li>Jangan lupa klik button 'Simpan Penilaian' untuk menerapkan perubahan.</li>
</ul>
</p>
PHREDOC;

        $this->view->appendData(['page_title'       => 'Lembar Penilaian']);
        $this->view->appendData(['page_description' => $description]);

        $this->view->addScript('/script/page/penilaian_ujian_akhir_index.js');
    }

    private function _processSubmit()
    {
        $fv = new FormValidation($this->originalFormFields);

        if(!$fv->submitted())
            return false; // Form belum disubmit, lanjutkan

        // Nama Pengusul is not required because its type is input TYPE_NONE. So it doesn't have actual input.
        $required = Util::arrayDeleteElementByValue('nama_pengusul', $this->originalFormFields);
        $fv->addRequiredInputs($required);

        if(!$fv->isValid()) {
            $this->view->appendData(['error_message' => $fv->getInvalidMessage()]);
            return false;
        }

        $data = $fv->getData();

        //pre_print($data);

        $normalized = Util::arrayMultipleNamedInputFormTo2DArray($data);

        $this->_validateInput($normalized);

        foreach ($normalized as $nilaiMahasiswa)
        {
            // Antisipasi jika ada yang memasukkan penilaian dengan tanda koma (,) maka diganti titik (.)
            $nilaiMahasiswa = Util::arrayAssocChangeCommaToPoint(['nilai_1', 'nilai_2', 'nilai_3'], $nilaiMahasiswa);

            $this->_mPenilaianUjian->addNewOrEdit($nilaiMahasiswa);
        }

        // Refresh existing peniliaian due to form submit
        $this->_retrieveExistingPenilaianData();

        $this->view->appendData(['error_message' => 'Nilai berhasil diperbarui. Sekarang Anda dapat mengisikan berita acara.']);

        return true;
    }

    private function _retrieveCurrentDosen()
    {
        if($this->_currentDosen == null)
        {
            $username = AppUtil::getCurrentUsername($this);

            $this->_currentDosen = $this->_mCredential->findDosen($username);
        }
    }

    private function _configureForm()
    {
        // Modify to accomodate mulitple mahasiswa count in D3 prodis
        $this->modifiedFormFields = array();

        for($i = 0; $i < count($this->allMahasiswa); $i++)
        {
            foreach ($this->originalFormFields as $field)
                $this->modifiedFormFields[] = "{$field}__{$i}";
        }

        $form = new Form($this->modifiedFormFields);

        $this->configureFormDefaultState($form);

        $this->mainForm = $form;

        $this->view->appendData(['form' => $this->mainForm]);
    }

    protected function configureFormDefaultState(Form $form)
    {
        $mhsCount = count($this->allMahasiswa);

        for($j = 0; $j < $mhsCount; $j++)
        {
            foreach ($this->originalFormFields as $field)
            {
                $id = "{$field}__$j";

                $name = "{$field}[$j]";

                $label = str_replace('_', ' ', ucfirst($field));

                $form->getInput($id)->setName($name)->setLabel($label);
            }

            $form->getInput("id_ujian__$j")->setReadOnly(true)->setValue($this->currentNomorUjian)->setType('hidden');
            $form->getInput("id_dosen__$j")->setReadOnly(true)->setValue($this->currentIdDosen)->setType('hidden');
            $form->getInput("nim__$j")->setReadOnly(true)->setValue($this->allMahasiswa[$j]['nim']);
            $form->getInput("peran__$j")->setType('select')->setOptionsFromList(['' => '-- Pilih Salah Satu --', 'penguji_1' => 'Penguji-1', 'penguji_2' => 'Penguji-2'])->setLabel('Anda sebagai');
            $form->getInput("nama_pengusul__$j")->setType(Input::TYPE_NONE)->setLabel('Penilaian Mahasiswa #' . ($j + 1) . ": {$this->allMahasiswa[$j]['nama']}");

            // Nilai
            $labels = $this->getPenilaianLabels();

            for ($k = 0; $k < count($labels); $k++)
            {
                $nilaiIndex = ($k + 1);

                $label = $labels[$k];

                $currentPenilaianInputId = "nilai_{$nilaiIndex}__$j";

                if($label != self::NILAI_LABEL_SKIPPED)
                {
                    $form->getInput($currentPenilaianInputId)->setLabel($label);

                    $this->configurePenilaianInput($form, $currentPenilaianInputId);
                }
            }

            $form->getInput("revisi__$j")->setType('textarea')->setExtras('style="min-height: 200px; width: 100%"');
            $form->getInput("kesimpulan__$j")->setType('select')->setOptionsFromList(
                $this->getKesimpulanOptions()
            );
        }

        if($mhsCount > 1)
            $this->_createButtonSamakanNilai($form, $mhsCount);

        $form->getSubmit()->setValue('Simpan Penilaian');
    }

    protected function getPenilaianLabels()
    {
        return array(
            'Nilai Perangkat (30%) <ul><li>Fungsi</li><li>Spesifikasi</li><li>Estetika</li></ul>',
            'Nilai Presentasi (25%) <ul><li>Sikap</li><li>Sistematika Penyampaian</li><li>Kemampuan Penyajian</li></ul>',
            'Nilai Kemampuan (45%) <ul><li>Penguasaan Materi</li><li>Kemampuan Menjawab</li></ul>',
            self::NILAI_LABEL_SKIPPED,
            self::NILAI_LABEL_SKIPPED,
            self::NILAI_LABEL_SKIPPED
        );
    }

    private function _createButtonSamakanNilai($form, $mhsCount)
    {
        $processedFormFields = Util::arrayDeleteElementsByValues(
            ['id_ujian', 'id_dosen', 'nama_pengusul', 'nim'],
            $this->originalFormFields
        );

        // Reset array index after some of its elements are removed
        // https://stackoverflow.com/questions/7536961/reset-php-array-index/7536963
        $processedFormFields = array_values($processedFormFields);

        $actionButtonParam = json_encode($processedFormFields);

        $actionButtonParam = str_replace('"', "'", $actionButtonParam);

        $actionButtonParam .= ", $mhsCount";

        $form->addActionButton('btn_samakan_nilai', 'Samakan Nilai', "PenilaianUjianAkhirIndex.btnSamakanNilai_onClick($actionButtonParam)");

        $form->getActionButton('btn_samakan_nilai')->setExtras('class="blue-action-button"');
    }

    protected function findUjian($nomorUjian)
    {
        return (new UjianModel())->findRekapUjianAkhirByIdUjian($nomorUjian);
    }

    private function _prefillForm()
    {
        if($this->_existingPenilaianData == null)
            return;

        $existing = Util::arrayTableSortBasedOnAnotherArrayColumn($this->_existingPenilaianData, $this->allMahasiswa, 'nim');
        //pre_print($existing);

        $i = 0;

        foreach ($existing as $nilai)
        {
            if($nilai == null)
                continue;

            foreach ($nilai as $key => $value)
            {
                $inputId = "{$key}__{$i}";

                //pre_print("Getting input --> $inputId");

                if($this->mainForm->inputIdExists($inputId))
                    $this->mainForm->getInput("{$key}__{$i}")->setValue($value);
            }

            $i++;
        }
    }

    private function _validateInput(array $normalizedInputData)
    {
        $checkedColumns = ['peran'];

        $samePeran = Util::arrayTableAllValuesAreTheSameForColums($checkedColumns, $normalizedInputData);

        if(!$samePeran)
            $this->renderErrorAndExit('Error! Peran penguji harus sama untuk semua mahasiswa. Tekan BACK <strong>dari browser</strong> agar data Anda tidak hilang.');
    }

    protected function getKesimpulanOptions()
    {
        return array(
            '' => '-- Pilih Salah Satu --',
            'lulus_tanpa_revisi' => 'Lulus Tanpa Revisi',
            'lulus_dengan_revisi' => 'Lulus Dengan Revisi',
            'mengulang' => 'Mengulang',
        );
    }

    protected function getBackLink($currentNomorUjian)
    {
        return $this->homeAddress("/dosen/sidang/ujian-akhir/detail?nomor_ujian={$currentNomorUjian}");
    }

    protected function configurePenilaianInput(Form &$form, $currentPenilaianInputId)
    {
        $form->getInput($currentPenilaianInputId)->setType('text');
    }
}