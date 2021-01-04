<?php


namespace controller\dosen;


use m\Application;
use model\AppUploadedFileModel;
use model\UjianModel;

class SeminarProposalController extends UjianAkhirController
{
    public function __construct(Application $application)
    {
        parent::__construct($application);

        $this->jenisUjian = UjianModel::JENIS_SEMINAR_PROPOSAL;
    }

    protected function getIndexViewData()
    {
        $vd = parent::getIndexViewData();

        $vd->setPageTitle('Seminar Proposal Terjadwal');
        $vd->setPageDescription('<p>Berikut ini adalah data sesi seminar proposal yang pendaftarannya telah disetujui oleh dosen pembimbing masing-masing judul.</p><p>Klik <b>Detail</b> untuk tindakan lebih lanjut pada setiap sesi.</p>');

        return $vd;
    }

    protected function getDetailUjianData($idUjian)
    {
        $data = (new UjianModel())->findRekapSeminarProposalByIdUjian($idUjian);

        $this->_convertFilesToLinks($data);

        //$data = $this->_addProposalToDisplayedData($data);

        return $data;
    }

    private function _convertFilesToLinks(&$data)
    {
        $ufm = new AppUploadedFileModel();

        // File presentasi	1c81f553bad57c2c1a7f9479f3995672.pdf
        $data['file_presentasi'] = $ufm->createFileLink($data['file_presentasi'], true);

        // File proposal 3df9df92e6b82019cc8cef5576a6b1ed.pdf
        $data['file_proposal'] = $ufm->createFileLink($data['file_proposal'], true);
    }

    protected function getPenilaianRoute()
    {
        return '/dosen/bimbingan/sempro/penilaian';
    }

    protected function getBeritaAcaraRoute()
    {
        return '/seminar-proposal/berita-acara';
    }

    protected function getDetailBackLink()
    {
        return $this->homeAddress('/dosen/bimbingan/sempro');
    }
}