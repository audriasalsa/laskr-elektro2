<?php


namespace controller\dosen;


use lib\ActionLink;
use lib\AppController;
use lib\AppUtil;
use m\Application;
use m\Util;
use model\BeritaAcaraUjianModel;
use model\CredentialModel;
use model\MahasiswaModel;
use model\PenilaianUjianModel;
use model\UjianModel;

class BeritaAcaraUjianAkhirController extends AppController
{
    // Models
    private $_mPenilaianUjian;
    private $_mCredential;
    private $_mBeritaAcara;

    // Data
    private $_currentNomorUjian;
    private $_currentNim;
    private $_currentIdDosen;
    private $_rekapKeputusanCombined;
    private $_rekapKeputusanList;
    /**
     * @var array
     */
    private $_actionLinks;
    /**
     * @var string
     */
    private $_accessType;

    public function __construct(Application $application)
    {
        parent::__construct($application);

        $this->_mPenilaianUjian = new PenilaianUjianModel();
        $this->_mCredential = new CredentialModel();
        $this->_mBeritaAcara = new BeritaAcaraUjianModel();

        $this->_actionLinks = array();
        $this->_accessType = 'dosen';

        $this->_rekapKeputusanList = array();
    }

    public function index()
    {
        $this->accessControl()->inspect();

        $this->_setupView();

        $this->_retrieveDataFromUrlParam();

        $this->_retrieveRekapData();

        $this->view->appendData([
            'displayed_data' => AppUtil::toDisplayedData(
                $this->_toNicerRekapData($this->_rekapKeputusanCombined)
            )
        ]);

        $this->_retrieveActionLinks();

        $this->view->appendData(['action_links' => $this->_actionLinks]);

        // Backlink
        if($this->_accessType == 'dosen') //dosen/sidang/ujian-akhir/detail?
        {
            $backLink = $this->getBackLink($this->_currentNomorUjian);

            //pre_print($backLink, true);

            $this->view->appendData(['back_link' => $backLink]);
        }

        $this->view->render();
    }

    private function _setupView()
    {
        $this->view->setContentTemplate('/common/data_display_template.php');

        $this->view->appendData(['page_title' => $this->getPageTitle()]);

        $nowSegment = explode('-', date("Y-m-d"));

        $this->view->appendData(['page_description' => "Pada hari ini tanggal <b>$nowSegment[2]</b> bulan <b>$nowSegment[1]</b> tahun <b>$nowSegment[0]</b> di Jurusan Teknologi Informasi, telah dilaksanakan ujian akhir dengan detail sebagai berikut: "]);

        if(isset($_GET['message']))
            $this->view->appendData(['success_message' => base64_decode($_GET['message'])]);
    }

    private function _retrieveDataFromUrlParam()
    {
        // nomor_ujian=1&nim=1641720044
        $this->_currentNomorUjian = isset($_GET['nomor_ujian']) ? $_GET['nomor_ujian'] : null;
        $this->_currentNim        = isset($_GET['nim']) ? $_GET['nim'] : null;
        $this->_currentIdDosen    = $this->_currentIdDosen();

        if($this->_currentIdDosen == null) // Diakses oleh mahasiswa
            $this->_accessType = 'mahasiswa';
    }

    private function _retrieveRekapData()
    {
        if($this->_accessType == 'dosen') // Diakses oleh dosen
        {
            $this->_rekapKeputusanList = $this->_mPenilaianUjian->findAllRekapKeputusanUjianByNomorUjian(
                $this->_currentNomorUjian
            );

            $this->_rekapKeputusanCombined = $this->_combineRekapKeputusanList();
        }
        else
        {
            $this->_currentNim = $this->_retrieveNimFromCredential();

            $this->_rekapKeputusanCombined = $this->_mPenilaianUjian->findLatestRekapKeputusanUjianByNim($this->_currentNim);

            if($this->_rekapKeputusanCombined == null)
                $this->renderErrorAndExit('Tidak ada detail berita acara terbaru karena Anda belum pernah mendaftar ujian!');
        }
    }

    private function _currentIdDosen()
    {
        $username = AppUtil::getCurrentUsername($this);

        $dosen = $this->_mCredential->findDosen($username);

        if($dosen == null)
            return null;

        return $dosen['id'];
    }

    private function _retrieveNimFromCredential()
    {
        $username = AppUtil::getCurrentUsername($this);

        $mahasiswa = $this->_mCredential->findMahasiswa($username);

        return $mahasiswa['nim'];
    }

    private function _retrieveActionLinks()
    {
        if($this->_accessType == 'mahasiswa')
            $ttdType = $this->_accessType;
        else
        {
            if ($this->_currentIdDosen == $this->_rekapKeputusanCombined['id_dosen_penguji_1'])
                $ttdType = 'penguji_1';
            elseif ($this->_currentIdDosen == $this->_rekapKeputusanCombined['id_dosen_penguji_2'])
                $ttdType = 'penguji_2';
            else
                $ttdType = 'moderator';
        }

        $caption = 'Tanda tangani sebagai ' . (str_replace('_', ' ', $ttdType));

        $actionLinks = new ActionLink(
            $this->homeAddress($this->getCurrentRoute()->getPath(false)),
            $caption,
            ['nomor_ujian' => $this->_rekapKeputusanCombined['nomor_ujian'], 'ttd_type' => $ttdType]
        );

        $actionLinks->setCssClass('form-submit-button');

        $this->_actionLinks = [$actionLinks];
    }

    public function tandaTangan()
    {
        $this->accessControl()->inspect();

        $this->_retrieveDataFromUrlParam();

        $ttdType = isset($_GET['ttd_type']) ? $_GET['ttd_type'] : null;

        if($this->_accessType == 'mahasiswa') // Diakes oleh mahasiswa
        {
            $this->_currentNim = $this->_retrieveNimFromCredential();

            $rekapIndividual = $this->_mPenilaianUjian->findLatestRekapKeputusanUjianByNim($this->_currentNim);

            if($rekapIndividual == null)
                $this->renderErrorAndExit('Anda tidak berhak menandatangani berita acara ini!');

            if($rekapIndividual['keputusan_penguji_1'] == null || $rekapIndividual['keputusan_penguji_2'] == null)
                $this->renderErrorAndExit('Berita Acara belum boleh ditandatangani karena keputusan pembahas/penguji belum lengkap.');

            $this->_mBeritaAcara->ttd($this->_currentNomorUjian, $this->_currentNim);
        }
        else // Diakses oleh dosen
        {
            $allRekap = $this->_mPenilaianUjian->findAllRekapKeputusanUjianByNomorUjian($this->_currentNomorUjian);

            //pre_print($allRekap, true);

            foreach ($allRekap as $rekap)
            {
                if($rekap['keputusan_penguji_1'] == null || $rekap['keputusan_penguji_2'] == null)
                    $this->renderErrorAndExit('Berita Acara belum boleh ditandatangani karena keputusan pembahas/penguji belum lengkap.');

                $this->_mBeritaAcara->ttd($this->_currentNomorUjian, $rekap['nim'], $ttdType, $this->_currentIdDosen);
            }
        }

        /*
        $this->_rekapKeputusanCombined = $this->_mPenilaianUjian->findRekapKeputusanUjianByNomorUjianAndNim(
            $this->_currentNomorUjian,
            $this->_currentNim
        );

        if($this->_rekapKeputusanCombined == null)
            $this->renderErrorAndExit('Anda tidak berhak menandatangani berita acara ini!');

        if($this->_accessType == 'mahasiswa')
        {
            if ($this->_retrieveNimFromCredential() != $this->_rekapKeputusanCombined['nim'])
                $this->renderErrorAndExit('Berita acara ini bukan milik Anda!');
        }

        if($ttdType == 'mahasiswa') // Jika mahasiswa, tanda tangani miliknya sendiri saja
            $this->_mBeritaAcara->ttd($this->_currentNomorUjian, $this->_currentNim);
        else // Jika dosen, tanda tangani semuanya.
        {
            $mahasiswaTerkait = (new MahasiswaModel())->findTeamMembers($this->_currentNim);

            foreach ($mahasiswaTerkait as $mahasiswa)
                $this->_mBeritaAcara->ttd($this->_currentNomorUjian, $mahasiswa['nim'], $ttdType, $this->_currentIdDosen);
        }
        */

        $message = 'Berita acara berhasil ditanda tangani.';

        $currentPath = $this->getCurrentRoute()->getPath(false);

        $this->redirect("$currentPath?nomor_ujian={$this->_currentNomorUjian}&nim={$this->_currentNim}&message=" . base64_encode($message));
    }

    private function _toNicerRekapData($rekapKeputusan)
    {
        $copy = $rekapKeputusan;

        //pre_print($rekapKeputusan);

        unset($copy['id_event']);
        unset($copy['id_dosen_penguji_1']);
        unset($copy['id_dosen_penguji_2']);
        unset($copy['id_dosen_moderator']);

        $copy['keputusan_penguji_1'] = "<b><u>{$copy['keputusan_penguji_1']}</u></b>";
        $copy['keputusan_penguji_2'] = "<b><u>{$copy['keputusan_penguji_2']}</u></b>";

        /*
         (
             [id_ujian] => 15
             [nim] => 1731710181
             [id_penguji_1_riil] => 320
             [id_penguji_2_riil] =>
             [id_moderator_riil] =>
             [waktu_ttd_penguji_1] => 2020-06-18 17:18:28
             [waktu_ttd_penguji_2] =>
             [waktu_ttd_moderator] =>
             [waktu_ttd_mahasiswa] =>
        )
        */
        /*
        if($this->_accessType == 'dosen') // Jika diakses oleh dosen, maka berita acaranya diambil salah satu dari haril combine.
            $ba = $this->_mBeritaAcara->findOneByNomorUjianAndNim($copy['nomor_ujian'], $copy['nim_mahasiswa_1']);
        else // Kalau yang mengakses mahasiswa, nama kolomnya masih asli karena tidak dicombine
            $ba = $this->_mBeritaAcara->findOneByNomorUjianAndNim($copy['nomor_ujian'], $copy['nim']);
        */

        $allBA = $this->_mBeritaAcara->findAllByNomorUjian($copy['nomor_ujian']);

        if($allBA != null)
        {
            $copy['ditandatangani_penguji_1_pada'] = "<a style='color: #bb534d;'>{$allBA[0]['waktu_ttd_penguji_1']}</a>";
            $copy['ditandatangani_penguji_2_pada'] = "<a style='color: #bb534d;'>{$allBA[0]['waktu_ttd_penguji_2']}</a>";
            $copy['ditandatangani_moderator_pada'] = "<a style='color: #bb534d;'>{$allBA[0]['waktu_ttd_moderator']}</a>";

            for ($i = 0; $i < count($allBA); $i++)
            {
                $mhsNum = ($i + 1);

                $copy["ditandatangani_mahasiswa_{$mhsNum}_pada"] = "<a style='color: #bb534d;'>{$allBA[$i]['waktu_ttd_mahasiswa']}</a>";
            }
        }

        return $copy;
    }

    private function _combineRekapKeputusanList()
    {
        $dataCount = count($this->_rekapKeputusanList);

        if($dataCount < 2)
            return $this->_rekapKeputusanList[0];

        $combine = $this->_rekapKeputusanList[0];

        $combine = Util::arrayAssocChangeKey('nim', 'nim_mahasiswa_1', $combine);
        $combine = Util::arrayAssocChangeKey('nama', 'nama_mahasiswa_1', $combine);

        for($i = 1; $i < $dataCount; $i++)
        {
            $mhsNum = ($i + 1);

            $combine["nim_mahasiswa_{$mhsNum}"] = $this->_rekapKeputusanList[$i]['nim'];
            $combine["nama_mahasiswa_{$mhsNum}"] = $this->_rekapKeputusanList[$i]['nama'];
        }

        return $combine;
    }

    protected function getBackLink($nomorUjian)
    {
        return $this->homeAddress("/dosen/sidang/ujian-akhir/detail?nomor_ujian={$nomorUjian}");
    }

    protected function getPageTitle()
    {
        return 'Berita Acara Ujian';
    }
}