<?php


namespace controller\panitia;


use lib\AppController;
use lib\CommonTemplateViewData;
use lib\DataViewerController;
use m\Application;
use m\Util;
use model\EventModel;
use model\UjianModel;

class RekapPendaftaranUjianAkhirController extends DataViewerController
{
    private $_mUjian;

    public function __construct(Application $application)
    {
        parent::__construct($application);

        $this->_mUjian = new UjianModel();
    }

    protected function getIndexData($filterValues = null)
    {
        $this->_mUjian->reloadFullyApprovedPendaftaran();

        $data = $this->_mUjian->findRekapPendaftaranUjianAkhir($filterValues);

        if($data != null)
        {
            $data = Util::arrayTableRemoveSomeColumns($data, ['id_event']);
            $data = Util::arrayTableAddNumbering($data);
        }

        return $data;
    }

    protected function getIndexViewData()
    {
        $vd = new CommonTemplateViewData();

        $vd->setPageTitle('Rekap Pendaftaran Ujian Akhir');
        $vd->setPageDescription('Berikut ini Mahasiswa yang telah mendaftar ujian akhir.');

        return $vd;
    }

    // Opsional, untuk filter
    protected function indexFilterFields()
    {
        $filterArray = array(
            array('kode_prodi_pengusul' => 'D3-MI', 'tahap' => null, 'status_persetujuan_pembimbing_1' => 'diajukan', 'status_persetujuan_pembimbing_2' => 'diajukan', 'judul_proposal' => null, 'nama_pengusul' => null), // null akan dianggap input filternya 'text'
            array('kode_prodi_pengusul' => 'D4-TI', 'tahap' => null, 'status_persetujuan_pembimbing_1' => 'disetujui', 'status_persetujuan_pembimbing_2' => 'disetujui', ),
        );

        $ujianEvents = (new EventModel())->findByKategori('ujian_akhir');

        $eventNames = Util::arrayTableRemoveSomeColumns($ujianEvents, ['id', 'deskripsi', 'kategori', 'tanggal_mulai', 'tanggal_selesai']);

        for($i = 0; $i < count($eventNames); $i++)
        {
            $filterArray[$i]['tahap'] = $eventNames[$i]['nama'];
        }

        //pre_print($filterArray, true);

        return $filterArray;
    }
}