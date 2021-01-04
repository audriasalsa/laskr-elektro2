<?php


namespace controller;


use lib\AppController;
use lib\AppUtil;
use m\Application;
use m\extended\Form;
use m\extended\FormValidation;
use m\Util;
use model\RevisiSemproModel;

class ValidasiKelulusanController extends AppController
{
    /**
     * @var Form
     */
    private $_mainform;
    /**
     * @var RevisiSemproModel
     */
    private $_mRevisiSempro;
    private $_currentNim;

    public function __construct(Application $application)
    {
        parent::__construct($application);

        $this->_mRevisiSempro = new RevisiSemproModel();

        $this->_currentNim = null;
    }

    public function index()
    {
        $this->view->setContentTemplate('/common/data_crud_template.php');

        $this->_mainform = new Form(['nim']);

        $this->view->appendData(['no_data_caption' => "Masukkan NIM yang benar dan klik 'Cek!'"]);

        $this->_configureForm();
        
        $this->_processSubmit();

        $this->_preFillForm();

        $this->view->appendData(['form' => $this->_mainform]);

        $this->view->appendData(['page_title' => 'Validasi Kelulusan Mahasiswa']);
        $this->view->appendData(['page_description' => '
<p>Masukkan NIM mahasiswa yang akan dicek kelulusannya pada isian dibawah lalu klik tombol <b>Cek</b>.</p>
<b><u>Catatan</u></b>: Mahasiswa yang berhak diterbitkan <b>SKL</b> adalah yang:
<ul>
<li>Kolom Keputusan Penguji 1 statusya: <b>LULUS_DENGAN_REVISI</b> atau <b>LULUS_TANPA_REVISI</b></li>
<li>Kolom Keputusan Penguji 2 statusya: <b>LULUS_DENGAN_REVISI</b> atau <b>LULUS_TANPA_REVISI</b></li>
<li>Status Revisi Penguji 1 isinya: <b>disetujui</b></li>
<li>Status Revisi Penguji 2 isinya: <b>disetujui</b></li>
</ul>
']);

        $this->view->render();
    }

    private function _configureForm()
    {
        $f = $this->_mainform;

        $f->getSubmit()->setValue('Cek!');
    }

    private function _processSubmit()
    {
        $fv = new FormValidation($this->_mainform->getFields(), false);

        if(!$fv->submitted())
            return;

        $data = $fv->getData();

        $this->_currentNim = $data['nim'];

        $existing = $this->_mRevisiSempro->findRekapKelulusanUjianByNim($data['nim'], 'nim, nama, judul_final AS `Judul LA/Skripsi`, keputusan_penguji_1, keputusan_penguji_2, status_revisi_penguji_1, status_revisi_penguji_2');

        if($existing != null)
        {
            $headers = AppUtil::toTableDisplayedHeaders($existing);

            $headers = Util::arrayReplaceElementByValue('Materi bimbingan', 'Materi bimbingan dan/atau progres', $headers);

            $this->view->appendData(array(
                'headers'        => $headers,
                'displayed_data' => $existing
            ));
        }
        else
        {
            $this->view->appendData(array(
                'headers'        => ['nim', 'keterangan'],
                'displayed_data' => [
                    [
                        'nim' => $data['nim'],
                        'keterangan' => 'Data tidak ditemukan!'
                    ]
                ]
            ));
        }
    }

    private function _preFillForm()
    {
        $this->_mainform->getInput('nim')->setValue($this->_currentNim);
    }
}