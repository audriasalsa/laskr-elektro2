<?php


namespace controller\panitia;


use lib\CommonTemplateViewData;
use lib\DataViewerController;
use m\Application;
use m\Util;
use model\LogBimbinganModel;

class RekapLogBimbinganController extends DataViewerController
{
    public function __construct(Application $application)
    {
        parent::__construct($application);
    }

    /*
    protected function getIndexData_StandardFilteringDemo($filteredValues = null)
    {
        // Ignore empty filters
        Util::arrayAssocRemoveElementsByValue('', $filteredValues);

        return (new LogBimbinganModel())->findRekapPonselLogBimbingan($filteredValues);
    }
    */

    // Wajib
    protected function getIndexData($filterValues = null)
    {
        $m = new LogBimbinganModel();

        if($filterValues == null)
            return Util::arrayTableAddNumbering($m->findRekapPonselLogBimbingan());

        $filter = $filterValues['jumlah_bimbingan_diajukan'];

        if($filter == '')
            return Util::arrayTableAddNumbering($m->findRekapPonselLogBimbingan());

        $floor = 0;
        $ceil = 100;

        if($filter == '< 5')
            $ceil = 4;
        else
            $floor = 9;

        return Util::arrayTableAddNumbering($m->findRekapPonselLogBimbinganDiajukanWithCountBetween($floor, $ceil));
    }

    // Opsional, tetapi dianjurkan di-override untuk mengganti tulisan yang ada di halaman index
    protected function getIndexViewData()
    {
        return (new CommonTemplateViewData())
            ->setPageDescription(
                'Berikut ini adalah rekap jumlah keseluruhan kegiatan bimbingan yang diajukan oleh mahasiswa. Status <strong>[Diajukan]</strong> berarti belum disetujui oleh dosen pembimbingnya.'
            )
            ->setPageTitle('Rekap Log Bimbingan');
    }

    // Opsional, untuk filter
    protected function indexFilterFields()
    {
        return array(
            array('jumlah_bimbingan_diajukan' => '< 5'),
            array('jumlah_bimbingan_diajukan' => '>= 8')
        );
    }

    protected function preRenderIndex()
    {
        parent::preRenderIndex();

        $this->view->appendData(['script' => '/script/wa_sender.js']);
    }

    // Opsional
    protected function getDetailData($detailParamValue)
    {
        $nim = $detailParamValue;

        return (new LogBimbinganModel())->findAllByNim($nim);
    }

    // Opsional, bila tidak di-override, maka tidak akan dibuatkan link Detail
    protected function getDetailActionParamName()
    {
        return 'nim';
    }

    // Opsional, untuk menampilkan tulisan-tulisan yang ditampilkan di detail view bila perlu
    protected function getDetailViewData()
    {
        $vd = new CommonTemplateViewData();

        $vd->setPageTitle('Detail Log Bimbingan');
        $vd->setPageDescription('Berikut ini adalah keseluruhan riwayat bimbingan dari mahasiswa yang dipilih.');

        return $vd;
    }
}