<?php


namespace controller\dosen;


class BeritaAcaraSeminarProposalController extends BeritaAcaraUjianAkhirController
{
    protected function getBackLink($nomorUjian)
    {
        return $this->homeAddress("/dosen/bimbingan/sempro/detail?nomor_ujian={$nomorUjian}");
    }

    protected function getPageTitle()
    {
        return 'Berita Acara Seminar Proposal';
    }
}