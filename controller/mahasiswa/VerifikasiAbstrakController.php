<?php


namespace controller\mahasiswa;


use lib\AppController;
use lib\AppUtil;
use m\Application;
use m\Controller;
use m\extended\Form;
use m\extended\FormValidation;
use m\Util;
use model\AppUploadedFileModel;
use model\CredentialModel;
use model\DosenModel;
use model\ProposalModel;
use model\VerifikasiAbstrakModel;

class VerifikasiAbstrakController extends AppController
{
    // Model
    private $_mVerifikasiAbstrak;
    /**
     * @var ProposalModel
     */
    private $_mProposal;
    private $_mDosen;

    // Data
    /**
     * @var Form
     */
    private $_form;
    private $_formFields;
    private $_currentNim;
    private $_currentMahasiswa;
    private $_existingData;
    private $_currentProposal;

    public function __construct(Application $application)
    {
        parent::__construct($application);

        $this->_mVerifikasiAbstrak = new VerifikasiAbstrakModel();
        $this->_mProposal = new ProposalModel();
        $this->_mDosen = new DosenModel();

        $this->_formFields = $this->_mVerifikasiAbstrak->getColumnNames();
    }

    public function index()
    {
        $this->accessControl()->inspect();

        $this->_initData();

        $this->_setupView();

        $this->_setupForm();

        $this->_processSubmit();

        $this->_configureForm();

        $this->_preFillForm();

        $this->_renderView();
    }

    private function _setupView()
    {
        // Enable this code if must use TinyMCE
        //$this->view->addScript('/script/external/tinymce/tinymce.min.js');
        //$this->view->addScript('/script/page/verifikasi_abstrak_index.js');

        $this->view->setContentTemplate('/common/data_entry_template.php');

        $this->view->appendData(['page_title'       => 'Verifikasi Abstrak']);
        $this->view->appendData(['page_description' => 'Unggah file abstrak Anda lalu pilih dosen yang ditunjuk untuk memverifikasi berkas Anda, kemudian klik <b>Submit</b>']);
    }

    private function _setupForm()
    {
        $f = new Form($this->_formFields);

        $f->getInput('id_proposal')->setReadOnly(true);

        $f->setEnctype('multipart/form-data');

        $this->_form = $f;
    }

    private function _renderView()
    {
        $this->view->appendData(['form' => $this->_form]);

        $this->view->render();
    }

    private function _configureForm()
    {
        $f = $this->_form;

        $this->_existingData = $this->_mVerifikasiAbstrak->findOneByIdProposal($this->_currentProposal['id']);

        $dosenOptions = $this->_mDosen->getAsKeyValuePairs(
            'id_dosen_verifikator',
            'nama',
            'nama',
            '',
            '-- Pilih Salah Satu --',
            'v2_rekap_dosen_verifikator_abstrak');

        $f->getInput('id_proposal')->setValue($this->_currentProposal['id']);
        $f->getInput('id_dosen_verifikator')->setType('select')->setOptionsFromList($dosenOptions);
        $f->getInput('status_verifikasi')->setValue('diajukan')->setReadOnly(true);
        $f->getInput('file_abstrak')->setType('file');

        // Enable this code if must use TinyMCE
        // $f->getInput('abstrak')->setType('textarea')->setExtras('style="min-height: 400px; width: 100%"');
    }

    private function _currentNim()
    {
        if($this->_currentNim == null)
        {
            $username = AppUtil::getCurrentUsername($this);

            $this->_currentMahasiswa = (new CredentialModel())->findMahasiswa($username);

            $this->_currentNim = $this->_currentMahasiswa['nim'];
        }

        return $this->_currentNim;
    }

    private function _processSubmit()
    {
        $fv = new FormValidation($this->_formFields, true);

        if($fv->submitted())
        {
            $fv->setUploadedFileModel(new AppUploadedFileModel());

            $required = $this->_formFields;

            if($this->_existingData != null)
                Util::arrayAssocRemoveElementsByValue('file_abstrak', $required);

            $fv->addRequiredInputs($required);

            $fv->getUploadedFile('file_abstrak')->setAllowedTypes(['application/pdf'])->setAllowedMaximumSizeMegaBytes(0.5);

            $fv->processUploadedFiles();

            if ($fv->uploadedFilesError())
                $this->view->modifyData('error_message', $fv->getUploadedFilesErrorMessages());
            else {
                if ($fv->isValid())
                {
                    $submittedAbstract = $fv->getEntireData();

                    //pre_print($submittedAbstract);

                    $this->_mVerifikasiAbstrak->addNewOrUpdate($submittedAbstract);
                }
            }
        }
    }

    private function _preFillForm()
    {
        $this->_refreshExistingData();

        if($this->_existingData == null)
            return;

        $this->_existingData['file_abstrak'] = (new AppUploadedFileModel())->createFileLink(
            $this->_existingData['file_abstrak'], true);

        $this->_form->applyValues($this->_existingData);
    }

    private function _initData()
    {
        $this->_currentProposal = $this->_mProposal->findByNimPengusulOrAnggota($this->_currentNim());

        $this->_refreshExistingData();
    }

    private function _refreshExistingData()
    {
        $this->_existingData = $this->_mVerifikasiAbstrak->findOneByIdProposal(
            $this->_currentProposal['id']
        );
    }
}