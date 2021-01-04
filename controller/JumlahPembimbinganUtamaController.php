<?php


namespace controller;


use lib\AppController;
use lib\CommonTemplateViewData;
use lib\DataViewerController;
use m\Application;
use model\PengajuanPembimbingModel;

class JumlahPembimbinganUtamaController extends DataViewerController
{
    private $_mPengajuanPembimbing;

    protected function getIndexData($filterValues = null)
    {
        $this->_mPengajuanPembimbing = new PengajuanPembimbingModel();

        $data = $this->_mPengajuanPembimbing->findRekapJumlahPembimbinganUtama();

        return $data;
    }

    protected function getIndexViewData()
    {
        $vd = new CommonTemplateViewData();
        $vd->setPageTitle('Jumlah Pembimbingan Utama');

        $desc = <<< PHREDOC
<a>Berikut ini adalah jumlah mahasiswa yang sudah disetujui oleh masing-masing dosen dengan ketentuan:</a>
<ul>
    <li>Kuota Dosen <strong>PNS</strong>: 6 untuk D4 & 4 untuk D3</li>
    <li>Kuota Dosen <strong>CPNS</strong>: 2 untuk D4 & 1 untuk D3</li>
    <li>Kuota Dosen <strong>Kontrak</strong>: 2 untuk D4 & 1 untuk D3</li>
</ul>
PHREDOC;

        $vd->setPageDescription($desc);

        return $vd;
    }
}