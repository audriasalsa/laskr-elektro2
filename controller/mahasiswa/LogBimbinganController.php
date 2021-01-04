<?php


namespace controller\mahasiswa;


use lib\AppController;
use lib\AppUtil;
use m\Application;
use m\extended\Form;
use m\extended\FormValidation;
use m\Util;
use model\BimbinganModel;
use model\LogBimbinganModel;
use model\RevisiSemproModel;

class LogBimbinganController extends AppController
{
    private $_mBimbingan;
    private $_mLogBimbingan;
    private $_mRevisiSempro;
    private $_formFields;

    public function __construct(Application $application)
    {
        parent::__construct($application);

        $this->_mBimbingan    = new BimbinganModel();
        $this->_mLogBimbingan = new LogBimbinganModel();
        $this->_mRevisiSempro = new RevisiSemproModel();

        $this->_formFields = array('id', 'jenis', 'nim_mahasiswa', 'id_dosen_pembimbing', 'tanggal', 'materi_bimbingan', 'status');
    }

    private function _currentNim()
    {
        // TODO: [WARNING] NIM by username!
        return AppUtil::getCurrentUsername($this);
    }

    public function logBimbingan($data)
    {
        $this->accessControl()->inspect();

        $this->_setupView();

        // Add eligibility check. Student who have not upload their revision cannot start bimbingan.
        // [@Notes] Disabled on 17 April atas order Pak Imam
        // $this->_eligibilityCheck();

        $fv = new FormValidation($this->_formFields, false);

        $actionType = $this->_determineAction($fv);

        $id = isset($_GET['id']) ? $_GET['id'] : null;

        $editedData = null;

        $actionResult = '';

        if($actionType != 'request_edit')
            $actionResult = $this->_commitAction($fv, $actionType, $id);
        else if($actionType == 'request_edit')
            $editedData = $this->_mLogBimbingan->findOneById($id);

        $form = new Form($this->_formFields);

        $form = $this->_configureForm($form);
        $form = $this->_prefillForm($form, $editedData);

        $existing = $this->_mLogBimbingan->findAllByNim($this->_currentNim());

        if($existing != null)
        {
            $existing = $this->_addActionLinks($existing);

            $headers = AppUtil::toTableDisplayedHeaders($existing);

            $headers = Util::arrayReplaceElementByValue('Materi bimbingan', 'Materi bimbingan dan/atau progres', $headers);

            $this->view->appendData(array(
                'headers'        => $headers,
                'displayed_data' => $existing
            ));
        }

        $this->view->appendData([
            'form'           => $form,
            'error_message'  => $actionResult
        ]);

        $this->view->appendData(['form' => $form]);

        $this->view->render();
    }

    private function _determineAction(FormValidation $fv)
    {
        if($fv->submitted()) // Commit INSERT OR UPDATE
        {
            return 'commit_add_or_edit';
        }
        else
        {
            $action = isset($_GET['action']) ? $_GET['action'] : null;

            if ($action == null)
                return 'default_action';
            else if($action == 'remove') // Commit DELETE
                return 'commit_remove';
            else if($action == 'edit') // Request UPDATE
                return 'request_edit';
            else
                return 'invalid_action';
        }
    }

    private function _commitAction(FormValidation $fv, $action = 'default_action', $id = null)
    {
        if($action == 'default_action')
            return '';

        $message = null;

        if($action == 'commit_add_or_edit') // Commit INSERT OR UPDATE
        {
            $error = $this->_saveEntry($fv);
            if ($error)
                $message = $error;
            else
                $message = 'Data log bimbingan berhasil disimpan.';
        }
        else
        {
            if($action == 'commit_remove') // Commit DELETE
            {
                $deleted = $this->_mLogBimbingan->removeById($id);

                $message = 'Data log bimbingan berhasil dihapus..';

                if (!$deleted)
                    $message = 'ID log yang akan dihapus tidak valid!';
            }
            else
                $message = 'Aksi tidak valid!';
        }

        return $message;
    }

    private function _addActionLinks($records)
    {
        $result = array();

        foreach ($records as $row)
        {
            $editUrl   = $this->application()->getRoute()->toURL("?action=edit&id={$row['id']}");
            $removeUrl = $this->application()->getRoute()->toURL("?action=remove&id={$row['id']}");

            $status = $row['status'];

            if($status == LogBimbinganModel::STATUS_DIAJUKAN)
            {
                $row['aksi'] = <<< PHPHREDOC
<a href="$editUrl">Edit</a>&nbsp;<a href="$removeUrl">Hapus</a>
PHPHREDOC;
            }
            elseif($status == LogBimbinganModel::STATUS_DITOLAK)
            {
                $row['aksi'] = <<< PHPHREDOC
<a href="$editUrl">Edit</a>
PHPHREDOC;
            }
            else
                $row['aksi'] = '';

            $result[] = $row;
        }

        return $result;
    }

    private function _setupView()
    {
        $this->view->setContentTemplate('/common/data_crud_template.php');
        $pageTitle = 'Log Bimbingan';
        $description = <<< PHREDOC
<p> 
    Catatlah selalu materi bimbingan maupun progress setiap kali Anda menemui dosen pembimbing atau ketika bimbingan secara daring.
</p>
<a>Catatan: </a>
<ul>
    <li>Jenis log bimbingan ada dua: <strong>pra_proposal</strong> dan <strong>pasca_proposal</strong></li>
    <li>Ketika Anda <strong>belum mengunggah revisi sempro</strong>, maka jenis log bimbingan akan secara otomatis disimpan sebagai <strong>pra_proposal</strong>.
    <li>Setelah revisi sempro dan seterusnya, jenis log bimbingan akan otomatis disimpan sebagai <strong>pasca_proposal</strong></li>
    <li>Untuk dapat maju <strong>seminar proposal</strong>, minimal Anda harus melakukan 3 (tiga) kali bimbingan <strong>pra_proposal</strong> terverifikasi ke dosen <strong>pembimbing utama</strong> Anda.</li>
    <li>Untuk dapat maju <strong>sidakng akhir</strong> tahap 1, minimal Anda harus melakukan 8 (delapan) kali bimbingan <strong>pasca_proposal</strong> terverifikasi ke masing-masing dosen pembimbing Anda.</li>
    <li>Log bimbingan yang masih dalam tahap 'diajukan' masih bisa diedit maupun dihapus.</li>
    <li>Log bimbingan yang ditolak masih bisa diedit tetapi tidak bisa dihapus.</li>
    <li>Log bimbingan yang sudah diverifikasi tidak dapat dihapus maupun diedit.</li>
</ul>
        
PHREDOC;

        $this->view->appendData([
            'page_title'       => $pageTitle,
            'page_description' => $description
        ]);
    }

    private function _configureForm(Form $form)
    {
        $nim = $this->_currentNim();

        $pembimbingOptions = $this->_mBimbingan->findPembimbingAsKeyValuePairs($nim);

        $form->getInput('id')->setReadOnly(true)->addAdditionalAttribute('placeholder', 'Dibangkitkan otomatis');
        $form->getInput('jenis')->setReadOnly(true);
        $form->getInput('nim_mahasiswa')->setReadonly(true);
        $form->getInput('id_dosen_pembimbing')->setType('select')->setOptionsFromList($pembimbingOptions)->setValue('');
        $form->getInput('tanggal')->setType('date')->addAdditionalAttribute('placeholder', 'Format: YYYY-MM-DD')->addAdditionalAttribute('class', 'datepicker')->setReadOnly(true);
        $form->getInput('materi_bimbingan')->setType('textarea')->addAdditionalAttribute('style', 'min-height: 200px; width: 100%;')->setLabel('Materi bimbingan dan/atau progres');
        $form->getInput('status')->setReadonly(true);
        $form->getSubmit()->setValue('Tambah Baru');

        return $form;
    }

    private function _prefillForm(Form $form, $editedData = null)
    {
        $form->getInput('nim_mahasiswa')->setValue($this->_currentNim());

        if($editedData == null) // Berarti INSERT
        {
            $form->getInput('jenis')->setValue($this->_mLogBimbingan->determineCurrentJenis($this->_currentNim()));
            $form->getInput('tanggal')->setValue(date('Y-m-d'));
            $form->getInput('status')->setValue('Diajukan');
        }
        else
        {
            /*
             *
             *  Array
             *  (
             *      [id] => 4
             *      [jenis] => pasca_proposal
             *      [nim_mahasiswa] => 1641720150
             *      [id_dosen_pembimbing] => 4
             *      [tanggal] => 2020-03-28
             *      [materi_bimbingan] => Bu Mentari cantik
             *      [status] => diajukan
             *     )
             */
            $form->getInput('id')->setValue($editedData['id']);
            $form->getInput('jenis')->setValue($editedData['jenis']);
            $form->getInput('nim_mahasiswa')->setValue($editedData['nim_mahasiswa']);
            $form->getInput('id_dosen_pembimbing')->setValue($editedData['id_dosen_pembimbing']);
            $form->getInput('tanggal')->setValue($editedData['tanggal']);
            $form->getInput('materi_bimbingan')->setValue($editedData['materi_bimbingan']);
            $form->getInput('status')->setValue($editedData['status']);
            $form->getSubmit()->setValue('Simpan Perubahan');
        }

        return $form;
    }

    private function _saveEntry(FormValidation $fv)
    {
        $required = Util::arrayDeleteElementByValue('id', $this->_formFields);

        $fv->addRequiredInputs($required);

        if($fv->isValid())
        {
            $data = $fv->getData();

            if($data['id'] == null || $data['id'] == '')
                $this->_mLogBimbingan->addNew($data);
            else
                $this->_mLogBimbingan->editExisting($data);
        }
        else
            return $fv->getInvalidMessage();
    }

    private function _eligibilityCheck()
    {
        // Cek sudah upload revisi atau belum
        $searchRevisi = $this->_mRevisiSempro->findOneByNim($this->_currentNim());

        $doneRevisi = $searchRevisi != null;

        // Apabila belum, STOP
        if(!$doneRevisi)
            $this->renderErrorAndExit('Anda tidak diperkenankan melakukan pencatatan bimbingan, karena belum mengunggah revisi proposal.');
    }
}