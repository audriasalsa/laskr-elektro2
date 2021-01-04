<?php


namespace controller\dosen;


use lib\AppUtil;
use m\Application;
use m\Controller;
use m\Util;
use model\BimbinganModel;
use model\DosenModel;

class BimbinganController extends Controller
{
    // TODO: Make this dynamic as proper API access!
    const API_URL = 'http://tugasakhir.jti.polinema.ac.id/v2/extension/halaman-pengesahan/print.php';

    private $_mDosen;
    private $_mBimbingan;

    public function __construct(Application $application)
    {
        parent::__construct($application);

        $this->_mDosen = new DosenModel();
        $this->_mBimbingan = new BimbinganModel();
    }

    public function daftarMahasiswa()
    {
        // TODO: Add access control
        
        $currentUsername = AppUtil::getCurrentUsername($this);

        $dosen = $this->_mDosen->findByUsername($currentUsername);

        $bimbinganList = $this->_mBimbingan->findRekapBimbinganPerDosen($dosen['id']);
        $bimbinganList = Util::arrayTableAddNumbering($bimbinganList);
        $bimbinganList = Util::arrayTableRemoveSomeColumns($bimbinganList, ['id_dosen']);
        $bimbinganList = Util::arrayTableAddLinkToEmail($bimbinganList, 'email');

        $bimbinganList = $this->_addActionToBimbinganList($bimbinganList);

        $pageDescription = <<< PHREDOC
<p>Berikut ini adalah mahasiswa-mahasiswa yang menjadi bimbingan Anda pada periode saat ini.</p>
<p><u>Catatan:</u></p>
<ul>
    <li>Nama-nama yang ditampilkan di sini adalah mahasiswa yang aktif di tahun proposal berjalan.</li>
    <li>Apabila ada mahasiswa yang ID Proposalnya masih kosong, maka mahasiswa/kelompok tersebut masih belum mengunggah proposal.</li>
</ul>
PHREDOC;


        $this->view->appendData(['page_title' => 'Daftar Mahasiswa Bimbingan']);
        $this->view->appendData(['page_description' => $pageDescription]);
        $this->view->appendData(['headers' => AppUtil::toTableDisplayedHeaders($bimbinganList)]);
        $this->view->appendData(['displayed_data' => $bimbinganList]);

        $this->view->setContentTemplate('/common/data_table_display_template.php');
        $this->view->render();
    }

    private function _addActionToBimbinganList(array $bimbinganList)
    {
        $urlCaption = 'Lembar Pengesahan';

        $result = [];

        foreach ($bimbinganList as $row)
        {
            $nim = $row['nim_mahasiswa'];

            $actionUrl = $this->application()->getRoute()->toURL("/lembar-pengesahan?nim=$nim");

            $row['aksi'] = '<a href="' . $actionUrl . '">' . $urlCaption . '</a>';

            $result[] = $row;
        }

        return $result;
    }

    public function lembarPengesahan()
    {
        $nim = $_GET['nim'];

        $actionUrl = self::API_URL . "?nim=$nim";

        header("location: $actionUrl");
    }

    private static function _sendPostRequest($url, array $paramKeyValuePairs = [])
    {
        // use key 'http' even if you send the request to https://...
        $options = array(
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($paramKeyValuePairs)
            )
        );

        $context  = stream_context_create($options);

        $result = file_get_contents($url, false, $context);

        if ($result === FALSE)
        {
            // Error
            return false;
        }

        return true;
    }
}