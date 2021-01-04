<?php


namespace controller;


use lib\AppController;
use m\Application;
use m\extended\UploadedFile;
use m\Util;
use model\EventModel;
use model\HasilSemproModel;
use model\TempModel;


class PanitiaSemproController extends AppController
{
    private $_mTemp;
    private $_mEvent;
    private $_mHasilSempro;

    public function __construct(Application $application)
    {
        parent::__construct($application);

        $this->_mTemp = new TempModel();
        $this->_mEvent = new EventModel();
        $this->_mHasilSempro = new HasilSemproModel();
    }

    public function imporJadwal()
    {
        $this->accessControl()->inspect();

        $this->view->appendData(['page_title' => 'Impor Jadwal Sempro']);

        if(isset($_POST['submit']))
        {
            $filePath = UploadedFile::quickHandle('file_csv');

            if($filePath != null)
            {
                $data = Util::csvRead($filePath);

                // Delete file after its data has been read
                unlink($filePath);

                $this->_mTemp->loadAllToTempImportJadwalSempro($data);

                $this->view->appendData([
                    'error_message' => count($data) . ' baris data berhasil diimpor.',
                    'headers' => Util::arrayTableGetHeaders($data),
                    'displayed_data' => $data
                ]);
            }
            else
                $this->view->appendData(['error_message' => 'File yang diimpor tidak boleh kosong!']);
        }

        $this->view->setContentTemplate('/common/data_import_template.php');
        $this->view->render();
    }

    public function imporHasil()
    {
        $this->accessControl()->inspect();

        $this->view->appendData([
            'page_title'    => 'Impor Hasil Sempro',
            'event_options' => $this->_mEvent->findIdNamaPairs()
        ]);

        //pre_print($this->view->data('event_options'), true);

        if(isset($_POST['submit']))
        {
            if($_POST['id_event'] != null)
            {
                $filePath = UploadedFile::quickHandle('file_csv');

                if ($filePath != null)
                {
                    $data = Util::csvRead($filePath);
                    $data = Util::arrayTableSelectSomeKeys($data, ['nim', 'hasil_seminar']);
                    $data = Util::arrayTableAddKeyToAll($data, 'id_event', $_POST['id_event']);

                    // Delete file after its data has been read
                    unlink($filePath);

                    $failedImports = array();
                    $successImports = array();

                    foreach ($data as $row)
                    {
                        $success = $this->_mHasilSempro->addNew($row['id_event'], $row['nim'], $row['hasil_seminar']);

                        if(!$success)
                            $failedImports[] = $row;
                        else
                            $successImports[] = $row;
                    }

                    $failedCount  = count($failedImports);
                    $successCount = count($successImports);

                    $this->view->appendData([
                        'error_message'  => "$successCount baris data berhasil diimpor. Sisanya $failedCount gagal. Baris-baris yang gagal bisa dilihat di bagian paling bawah halaman.",
                        'headers'        => Util::arrayTableGetHeaders($data),
                        'displayed_data' => $successImports,
                        'failed_imports' => $failedImports
                    ]);
                } else
                    $this->view->appendData(['error_message' => 'File yang diimpor tidak boleh kosong!']);
            }
            else
                $this->view->appendData(['error_message' => 'Tahap Sempro harus dipilih!']);
        }

        $this->view->setContentTemplate('/panitia/sempro/impor_hasil_template.php');
        $this->view->render();
    }
}