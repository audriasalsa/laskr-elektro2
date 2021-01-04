<?php


namespace controller\panitia;


use controller\dosen\PenilaianUjianAkhirController;
use lib\AppController;
use lib\AppUtil;
use lib\CommonTemplateViewData;
use lib\DataViewerController;
use m\Application;
use m\Settings;
use m\Util;
use model\EventModel;
use model\PenilaianUjianModel;

class RekapYudisiumUjianAkhirController extends DataViewerController
{
    const CSV_FILE_NAME = 'rekap_yudisium_ujian_akhir.csv';
    /**
     * @var PenilaianUjianModel
     */
    private $_mPenilaianUjian;


    public function __construct(Application $application)
    {
        parent::__construct($application);
    }

    private static function csvFileName()
    {
        return self::rootDir('/generated/' . self::CSV_FILE_NAME);
    }

    protected function getIndexData($filterValues = null)
    {
        $this->_mPenilaianUjian = new PenilaianUjianModel();

        $rekap = $this->_mPenilaianUjian->findRekapYudisiumNilaiAkhir($filterValues);

        if($rekap != null)
            $rekap = Util::arrayTableAddNumbering($rekap);


        Util::csvWrite($rekap, self::csvFileName(), ';');

        return $rekap;
    }

    public function downloadCsv()
    {
        $this->downloadFile();
    }

    protected function getIndexViewData()
    {
        $route = $this->getCurrentRoute()->getPath() . '/download-csv';
        $csvUrl = $this->toDownloadFileUrl($route, '/generated/' . self::CSV_FILE_NAME);

        $vd = new CommonTemplateViewData();

        $vd->setPageTitle('Yudisium Ujian Akhir');
        $vd->setPageDescription('
Berikut ini hasil rekapitulasi nilai mahasiswa yang telah melaksanakan ujian akhir.
<p>
<b><u>Catatan</u></b>:
<ul>
<li>Apabila ada nilai yang isinya <b>80.115</b> semua, maka itu adalah nilai yang digenerate default dari sistem karena penilai belum memberikan nilai asli.</li>
<li>Apabila ada <b>perubahan nilai</b>, maka pembimbing dan/atau penguji bersangkutan dapat mengubahnya melalui akun mereka masing kemudian <b>refresh</b>-lah halaman ini.</li>
<li>Apabila ada nama mahasiswa yang <b>rangkap</b>, hal tersebut disebabkan oleh dosen penguji yang bertindak sebagai sama-sama penguji 1, maupun sama-sama penguji 2.</li>
</ul>
</p>
<p><a href="' . $csvUrl . '">Unduh CSV</a></p>
');

        return $vd;
    }

    protected function indexFilterFields()
    {
        $filterArray =  array(
            array('kode_prodi' => 'D3-MI', 'tahap_ujian' => null, 'status_kelulusan' => 'BELUM_ADA_NILAI', 'nim' => null, 'nama' => null, 'judul_proposal' => null, 'nilai_huruf_akhir' => null), // null akan dianggap input filternya 'text'
            array('kode_prodi' => 'D4-TI', 'status_kelulusan' => 'LULUS_TANPA_REVISI'),
            array('status_kelulusan' => 'LULUS_DENGAN_REVISI'),
            array('status_kelulusan' => 'MENGULANG')
        );

        $filterArray = (new EventModel())->addEventNamesToFilterArray($filterArray, 'ujian_akhir');

        return $filterArray;
    }
}