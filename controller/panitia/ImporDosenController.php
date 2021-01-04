<?php


namespace controller\panitia;


use m\Application;
use m\Controller;
use m\extended\UploadedFile;
use m\Util;
use model\DosenModel;

class ImporDosenController extends Controller
{
    private $_mDosen;

    private $_failedImports;
    private $_successfulImports;

    public function __construct(Application $application)
    {
        parent::__construct($application);

        $this->_mDosen = new DosenModel();
    }

    public function impor()
    {
        $this->accessControl()->inspect();

        if(isset($_POST['submit']))
        {
            $filePath = UploadedFile::quickHandle('file_csv');

            $pembimbing1Col = 'Pembimbing';
            $pembimbing2Col = 'Pembimbing 2';

            if ($filePath != null)
            {
                $data = Util::csvRead($filePath);

                $pembimbing1 = Util::arrayTableSelectSomeKeys($data, [$pembimbing1Col]);
                $pembimbing2 = Util::arrayTableSelectSomeKeys($data, [$pembimbing2Col]);

                $merge = array_merge($pembimbing1, $pembimbing2);

                $flat = Util::arrayTableToFlat1Dimension($merge);

                // Hilangkan duplikat dosen
                $data = array_unique($flat);

                // Reset index-nya array
                $data = array_values($data);

                // Delete file after its data has been read
                unlink($filePath);

                $this->_tryImport($data);

                $this->view->appendData(['headers' => array('Nomor', 'Nama Dosen')]);
                $this->view->appendData(['failed_imports' => $this->_failedImports]);
                $this->view->appendData(['displayed_data' => $this->_successfulImports]);
            }
        }

        $this->view->appendData(['page_title' => 'Impor data Dosen']);
        $this->view->setContentTemplate('/common/data_import_adv_template.php');
        $this->view->render();
    }

    private function _tryImport($dosenNames)
    {
        $failed  = array();
        $success = array();

        foreach ($dosenNames as $dosenName)
        {
            $imported = $this->_mDosen->addNewDosenByName($dosenName);

            if(!$imported)
                $failed[] = $dosenName;
            else
                $success[] = $dosenName;
        }

        $this->_failedImports = Util::arrayFlat1DimensionToTableArray($failed, 'Nomor', 'Nama Dosen');
        $this->_successfulImports = Util::arrayFlat1DimensionToTableArray($success, 'Nomor', 'Nama Dosen');;
    }
}