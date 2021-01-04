<?php


namespace controller\dosen;


use model\PendaftaranSemproModel;

class PersetujuanPendaftaranSemproController extends PersetujuanPendaftaranUjianAkhirController
{
    protected function retrievePendaftaranData()
    {
        return (new PendaftaranSemproModel())->findRekapPendaftaranSemproByIdDosen($this->_currentDosenId());
    }

    protected function setupView()
    {
        parent::setupView();

        $pageTitle = 'Persetujuan Pendaftaran Sempro';
        $description = 'Berikut ini adalah daftar mahasiswa yang akan mendaftar Seminar Proposal. Apabila tidak Anda setujui, maka mahasiswa tersebut tidak akan dijadwalkan untuk maju seminar proposal.';

        $this->view->appendData([
            'page_title'       => $pageTitle,
            'page_description' => $description
        ]);
    }

    protected function createActionUrl($actionType, $row)
    {
        $actionParam = "action=$actionType&id_proposal={$row['id_proposal']}&id_event={$row['id_event']}";

        $penilaianUrl = $this->application()->getRoute()->toURL("/penilaian-maju-sempro?{$actionParam}");

        return $penilaianUrl;
    }
}