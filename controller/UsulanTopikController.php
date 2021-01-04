<?php


namespace controller;


use lib\AppController;
use lib\AppUtil;
use m\Application;
use m\extended\Form;
use m\extended\FormValidation;
use m\Util;
use model\CredentialModel;
use model\GrupRisetModel;
use model\MahasiswaModel;
use model\ProdiModel;
use model\TopikModel;

class UsulanTopikController extends AppController
{
    const ACCESS_TYPE_DOSEN = 'dosen';
    const ACCESS_TYPE_MAHASISWA = 'mahasiswa';
    const ACTION_TYPE_ENTRY = 'entry';
    const ACTION_TYPE_EDIT = 'edit';
    const ACTION_TYPE_REMOVE = 'remove';

    // Model
    private $_mTopik;

    // Data
    private $_formFields;
    private $_currentUsername;
    private $_accessType;
    private $_dosen;
    private $_mahasiswa;
    private $_actionType;
    private $_editedTopik;

    public function __construct(Application $application)
    {
        parent::__construct($application);

        $this->_mTopik = new TopikModel();

        $this->_formFields = $this->_mTopik->getColumnNames();
    }

    public function index()
    {
        $this->accessControl()->inspect();

        // Dosen atau mahasiswa yang mengakses?
        $this->_determineAccessType();

        // Tambah baru atau edit yang sudah ada?
        $this->_determineActionType();

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
        $pageTitle = 'Usulan Topik';

        if($this->_accessType == self::ACCESS_TYPE_DOSEN)
        {
            $description = <<< PHREDOC
<p> 
    Entrikan topik yang ingin Anda usulkan pada form di bawah ini lalu klik 'Simpan'.
</p>
<a>Catatan: </a>
<ul>
    <li>Satu orang dosen sebaiknya mengajukan topik dengan jumlah sesuai dengan kuota maksimal pembimbing utama.</li>
    <li>Status topik <strong>bebas</strong> artinya topik tersebut belum dipilih oleh mahasiswa sama sekali.</li>
    <li>Apabila topik yang Anda ajukan sudah dipilih oleh mahasiswa, lalu diajukan kepada Anda, dan Anda setujui, maka status topik akan berubah dari 'bebas' menjadi '<strong>diklaim</strong>.</li>
    <li>Topik yang statusnya 'diklaim', tidak akan dimunculkan di halaman mahasiswa lagi sehingga tidak dapat dilihat dan dipilih oleh mahasiswa lain.</li>
</ul>
        
PHREDOC;
        }
        else
        {
            $description = <<< PHREDOC
<p> 
    Buat topik yang ingin Anda usulkan untuk dijadikan Tugas Akhir Anda pada form di bawah ini lalu klik 'Simpan'.
</p>
<a>Catatan: </a>
<ul>
    <li>Anda dapat membuat banyak topik, namun <strong>hanya satu</strong> topik saja yang pada akhirnya dapat dilanjutkan menjadi TA.</li>
    <li>Anda dapat memilih topik yang Anda buat disini nanti di halaman Pengajuan Pembimbing.</li>
</ul>
        
PHREDOC;
        }

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

        $form->getInput('id')->setReadonly(true)->addAdditionalAttribute('placeholder', 'Dibangkitkan otomatis');
        $form->getInput('judul')->setType('textarea')->setExtras('style="min-height: 100px; width: 100%"')->setLabel('Topik/Judul');
        $form->getInput('deskripsi')->setType('textarea')->setExtras('style="min-height: 250px; width: 100%"');
        $form->getInput('kode_grup_riset')->setType('select')->setOptionsFromList((new GrupRisetModel())->getAsKeyValuePairs('kode', 'nama', 'nama', '', '-- Pilih salah satu --'));

        // TODO: The parameter 'defaultOptionKey' is useless. Because the one that will always be chosen at first is only option with empty ('') key
        $form->getInput('jenis')->setType('select')->setOptionsFromList(['penelitian' => 'Penelitian', 'pengembangan' => 'Pengembangan', '' => '-- Pilih salah satu --']);

        $prodiOptions = (new ProdiModel())->getAsKeyValuePairs('kode', 'nama', 'nama', '', '-- Pilih salah satu --');

        if($this->_accessType == self::ACCESS_TYPE_DOSEN)
        {
            $form->getInput('id_dosen_pengusul')->setValue($this->_dosen['id'])->setReadonly(true);
            $form->getInput('nim_mahasiswa_pengusul')->addAdditionalAttribute('placeholder', 'Hanya diisi otomatis apabila diusulkan mahasiswa')->setReadonly(true);

            // Jenis pengusul always 'dosen'
            $form->getInput('jenis_pengusul')->setType('hidden')->setValue('dosen');

            // Dosen can select any prodi
            $form->getInput('kode_prodi')->setType('select')->setOptionsFromList($prodiOptions);
        }
        else
        {
            $form->getInput('nim_mahasiswa_pengusul')->setValue($this->_mahasiswa['nim'])->setReadonly(true);
            $form->getInput('id_dosen_pengusul')->addAdditionalAttribute('placeholder', 'Hanya diisi otomatis apabila diusulkan dosen')->setReadonly(true);

            // Jenis pengusul can only be 'mahasiswa' or 'grup_riset
            $form->getInput('jenis_pengusul')->setType('select')->setOptionsFromList(array(
                ''                => '-- Pilih salah satu --',
                'mahasiswa'       => 'Mahasiswa (Topik ini ide Anda sendiri)',
                'grup_riset'      => 'Grup Riset',
                'magang_industri' => 'Magang Industri'
            ));

            // Match prodi combo box with current student's prodi
            Util::arrayAssocRemoveAllByKeyExcept($this->_mahasiswa['kode_prodi'], $prodiOptions);
            $form->getInput('kode_prodi')->setType('select')->setOptionsFromList($prodiOptions);

            if((new MahasiswaModel())->isD3($this->_mahasiswa['nim']))
            {
                // D3 can only do pengembangan
                $form->getInput('jenis')->setOptionsFromList(['pengembangan' => 'Pengembangan']);
            }
        }

        $form->getInput('status')->setValue('bebas')->setReadonly(true);
        $form->getSubmit()->setValue('Simpan');

        if($this->_actionType == self::ACTION_TYPE_EDIT)
            $form->applyValues($this->_editedTopik);

        $this->view->appendData(['form'=> $form]);
    }

    private function _determineAccessType()
    {
        $this->_currentUsername = AppUtil::getCurrentUsername($this);

        $mCredential = new CredentialModel();

        $mahasiswa = $mCredential->findMahasiswa($this->_currentUsername);

        if($mahasiswa == null)
        {
            $dosen = $mCredential->findDosen($this->_currentUsername);

            $this->_accessType = self::ACCESS_TYPE_DOSEN;

            $this->_dosen = $dosen;
        }
        else
        {
            $this->_accessType = self::ACCESS_TYPE_MAHASISWA;

            $this->_mahasiswa = $mahasiswa;
        }
    }

    private function _determineActionType()
    {
        $this->_actionType = self::ACTION_TYPE_ENTRY;

        if(isset($_GET['action']))
        {
            if ($_GET['action'] == 'edit')
                $this->_actionType = self::ACTION_TYPE_EDIT;
            elseif ($_GET['action'] == 'remove')
                $this->_actionType = self::ACTION_TYPE_REMOVE;

            $selectedTopicId = $_GET['id'];

            $find = $this->_mTopik->findOneById($selectedTopicId);

            if ($find != null)
            {
                if ($find['id_dosen_pengusul'] == $this->_dosen['id']
                    || $find['nim_mahasiswa_pengusul'] == $this->_mahasiswa['nim'])
                {

                    if ($this->_actionType == self::ACTION_TYPE_EDIT)
                        $this->_editedTopik = $find; // Deleted later when the form is submitted;
                    elseif ($this->_actionType == self::ACTION_TYPE_REMOVE)
                        $this->_performRemove($find);
                }
                else
                    {
                    $this->renderErrorAndExit('Topik ini pengusulnya bukan Anda.');
                }
            }
        }
    }

    private function _performRemove($topik)
    {
        if($topik['status'] == TopikModel::STATUS_BEBAS)
        {
            $this->_mTopik->removeById($topik['id']);

            $message = "Topik dengan ID: {$topik['id']} telah dihapus.";

            $this->view->modifyData('error_message', $message);
        }
        else
            $this->renderErrorAndExit('Topik yang sudah diklaim tidak dapat dihapus.');
    }

    private function _handleAction()
    {
        $fv = new FormValidation($this->_formFields, false);

        // [Begin] To determine not required fields
        $notRequiredPengusul = $this->_accessType == self::ACCESS_TYPE_DOSEN ? 'nim_mahasiswa_pengusul' : 'id_dosen_pengusul';

        $notRequiredList = [$notRequiredPengusul];

        if($this->_actionType == self::ACTION_TYPE_ENTRY)
            $notRequiredList[] = 'id';
        // [End]

        $requiredInputs = Util::arrayDeleteElementsByValues($notRequiredList, $this->_formFields);

        $fv->addRequiredInputs($requiredInputs);

        if($fv->submitted())
        {
            if($fv->isValid())
            {
                $topicData = $fv->getData();

                if($topicData['id'] == null || $topicData['id'] == '')
                    $this->_mTopik->addNew($topicData, $this->_accessType);
                else
                    $this->_mTopik->edit($topicData);

                $this->view->modifyData('error_message', 'Data berhasil disimpan.');
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
        $idPengusul = $this->_accessType == self::ACCESS_TYPE_DOSEN ? $this->_dosen['id'] : $this->_mahasiswa['nim'];

        $existing = $this->_mTopik->findAllRekapByIdPengusul($idPengusul, $this->_accessType);

        if($existing != null)
        {
            $existing = $this->_addActionLinks($existing);

            $headers = AppUtil::toTableDisplayedHeaders($existing);

            //$headers = Util::arrayReplaceElementByValue('Materi bimbingan', 'Materi bimbingan dan/atau progres', $headers);

            $this->view->appendData(array(
                'headers'        => $headers,
                'displayed_data' => $existing
            ));
        }
    }

    private function _addActionLinks($records)
    {
        $result = array();

        foreach ($records as $row)
        {
            $editUrl   = $this->application()->getRoute()->toURL("?action=edit&id={$row['id']}");
            $removeUrl = $this->application()->getRoute()->toURL("?action=remove&id={$row['id']}");

            $status = $row['status'];

            if($status == TopikModel::STATUS_BEBAS)
            {
                $row['aksi'] = <<< PHPHREDOC
<a href="$editUrl">Edit</a>&nbsp;<a href="$removeUrl">Hapus</a>
PHPHREDOC;
            }
            else
                $row['aksi'] = '√';

            $result[] = $row;
        }

        return $result;
    }
}