<?php


namespace controller\panitia;


use m\Application;
use m\Controller;
use m\extended\UploadedFile;
use m\Util;
use model\BimbinganModel;

class ImporBimbinganController extends Controller
{
    private $_mBimbingan;
    private $_successfulImports;
    private $_failedImports;

    public function __construct(Application $application)
    {
        parent::__construct($application);

        $this->_mBimbingan = new BimbinganModel();

        $this->_successfulImports = array();
        $this->_failedImports = array();
    }

    public function impor()
    {
        $this->accessControl()->inspect();

        if(isset($_POST['submit']))
        {
            $filePath = UploadedFile::quickHandle('file_csv');

            if ($filePath != null)
            {
                $data = Util::csvRead($filePath);

                $data = Util::arrayTableSelectSomeKeys($data, ['NIM', 'Nama', 'Pembimbing', 'Pembimbing 2']);

                // Delete file after its data has been read
                unlink($filePath);

                $this->_tryImport($data);

                $sc = count($this->_successfulImports);
                $fc = count($this->_failedImports);
                $tc = count($data);

                $this->view->appendData(['error_message' => "Proses impor selesai memproses $tc baris data. Sebanyak $sc baris berhasil diimpor dan $fc sisanya gagal."]);
                $this->view->appendData(['headers' => array('Nim', 'Nama Mahasiswa', 'Pembimbing-1', 'Pembimbing-2')]);
                $this->view->appendData(['failed_imports' => $this->_failedImports]);
                $this->view->appendData(['displayed_data' => $this->_successfulImports]);
            }
        }

        $this->view->appendData(['page_title' => 'Impor data Bimbingan']);
        $this->view->setContentTemplate('/common/data_import_adv_template.php');
        $this->view->render();
    }

    private function _tryImport($data)
    {
        foreach ($data as $row)
        {
            $nim = $row['NIM'];
            $p1  = $row['Pembimbing'];
            $p2  = $row['Pembimbing 2'];

            // $this->_mBimbingan->editOrAdd('1641720208', 'Indra Dharma Wijaya, ST., M.MT.', 'Yoppy Yunhasnawa, S.ST., M.Sc.');
            $success = $this->_mBimbingan->editOrAdd($nim, $p1, $p2);

            // pre_print("Sukses? $success");
            // pre_print($this->_mBimbingan->getLastErrorMessage());

            if(!$success)
            {
                $row['Exception'] = $this->_mBimbingan->getLastWriteErrorMessage();
                $this->_failedImports[] = $row;
            }
            else
                $this->_successfulImports[] = $row;
        }

        //pre_print($this->_failedImports, true);
    }
}