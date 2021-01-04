<?php


namespace controller\mahasiswa;


use lib\ActionLink;
use lib\AppController;
use lib\AppUtil;
use lib\CommonTemplateViewData;
use lib\DataViewerController;
use m\Application;
use m\extended\Form;
use m\Util;
use model\BeritaAcaraUjianModel;
use model\CredentialModel;
use model\PenilaianUjianModel;
use model\RevisiUjianAkhirModel;

class RevisiUjianAkhirController extends DataViewerController
{
    // Model
    private $_mBeritaAcaraUjian;
    private $_mRevisiUjianAkhir;
    private $_mPenilaianUjian;

    // Data
    private $_mainEntryFields;
    private $_currentNim;
    /**
     * @var bool
     */
    private $_nilaiUjianIsComplete;

    public function __construct(Application $application)
    {
        parent::__construct($application);

        $this->_mBeritaAcaraUjian = new BeritaAcaraUjianModel();
        $this->_mRevisiUjianAkhir = new RevisiUjianAkhirModel();
        $this->_mPenilaianUjian = new PenilaianUjianModel();

        $this->_mainEntryFields = $this->_mRevisiUjianAkhir->getColumnNames();
    }

    public function getIndexData($filterValues = null)
    {
        $this->_currentNim = $this->_retrieveCurrentNim();
        
        $ujianResults = $this->_mBeritaAcaraUjian->findRekapBeritaAcaraUjianByNim(
            $this->_currentNim,
            'nomor_ujian, nim, nama_mahasiswa AS nama, tahap, keputusan_penguji_1, keputusan_penguji_2',
            $filterValues);

        return $ujianResults;
    }

    // Opsional, tetapi dianjurkan di-override untuk mengganti tulisan yang ada di halaman index
    protected function getIndexViewData()
    {
        return (new CommonTemplateViewData())
            ->setPageDescription(
                "Berikut ini adalah riwayat hasil ujian akhir yang telah Anda lakukan. Klik <b>Entri Revisi</b> untuk melihat poin revisi dari masing-masing dosen penguji.'"
            )
            ->setPageTitle('Revisi Ujian Akhir');
    }

    // Opsional, bila tidak di-override, maka tidak akan dibuatkan link Detail
    protected function getDetailActionParamName()
    {
        return 'nomor_ujian';
    }

    /*
    public function detail()
    {
        $this->view->setContentTemplate('/common/data_entry_template.php');

        $form = new Form($this->_mainEntryFields);

        $this->view->appendData(['form' => $form]);

        $this->view->render();
    }
    */

    public function detail()
    {
        $this->accessControl()->inspect();

        $nomorUjian = isset($_GET['nomor_ujian']) ? $_GET['nomor_ujian'] : null;

        $this->view->setContentTemplate('/common/data_table_display_template.php');
        $this->view->appendData(['page_title' => 'Detail Revisi Ujian']);
        $this->view->appendData(['page_description' => 'Berikut ini adalah revisi yang diberikan penguji 1 dan 2 pada saat Anda ujian. Untuk dapat mengentrikan hasil revisi, nilai dari semua penguji harus sudah lengkap untuk semua mahasiswa yang terlibat pada LA/Skripsi ini.']);

        $data = $this->_mPenilaianUjian->findRekapRevisiUjianMahasiswaByNomorUjian($nomorUjian);

        $headers = AppUtil::toTableDisplayedHeaders($data);

        $this->view->appendData(['headers' => $headers]);
        $this->view->appendData(['displayed_data' => $data]);
        $this->view->appendData(['back_link' => $this->homeAddress('/ujian-akhir/revisi')]);

        // Error message
        $allNim = Util::arrayTableTake1ColumnAsArray1Dimension($data, 'nim');

        $checkMessage = '';

        foreach ($allNim as $nim)
        {
            $completionCheckResult = $this->_mPenilaianUjian->checkStatusNilaiUjianCompletion($nim, $nomorUjian);

            $checkMessage .= $completionCheckResult;

            if($checkMessage != '')
                $checkMessage .= '<br/>';
        }

        if($checkMessage == '')
        {
            $this->_nilaiUjianIsComplete = true;

            $this->view->appendData(['error_message' => 'Semua nilai sudah lengkap, Anda dapat mengentrikan revisi dengan mengklik tombol <b>Unggah Revisi</b> di bawah.']);

            $unggahAction = new ActionLink(
                $this->homeAddress('/ujian-akhir/revisi/unggah'),
                'Unggah Revisi',
                ['nomor_ujian' => $nomorUjian]
            );

            $unggahAction->setCssClass('form-submit-button');

            $this->view->appendData(['action_links' => [$unggahAction]]);
        }
        else
        {
            $this->_nilaiUjianIsComplete = false;

            $this->view->appendData(['error_message' => $checkMessage]);
        }

        $this->view->render();
    }

    private function _retrieveCurrentNim()
    {
        $username = AppUtil::getCurrentUsername($this);

        $mahasiswa = (new CredentialModel())->findMahasiswa($username);

        if($mahasiswa == null)
            return null;

        return $mahasiswa['nim'];
    }
}