<?php


namespace controller\dosen;


use m\Application;
use m\extended\Form;
use model\UjianModel;

class PenilaianSeminarProposalController extends PenilaianUjianAkhirController
{
    public function __construct(Application $application)
    {
        parent::__construct($application);

        $this->jenisPenilaian = self::JENIS_PENILAIAN_SEMINAR_PROPOSAL;
    }

    protected function findUjian($nomorUjian)
    {
        return (new UjianModel())->findRekapSeminarProposalByIdUjian($nomorUjian);
    }

    protected function getKesimpulanOptions()
    {
        return array(
            '' => '-- Pilih Salah Satu --',
            'lulus_tanpa_revisi' => 'Diterima Tanpa Revisi',
            'lulus_dengan_revisi' => 'Diterima Dengan Revisi',
            'mengulang' => 'Ditolak',
        );
    }

    protected function getBackLink($currentNomorUjian)
    {
        return $this->homeAddress("/dosen/bimbingan/sempro/detail?nomor_ujian={$currentNomorUjian}");
    }

    protected function getPenilaianLabels()
    {
        return array(
            'Kesiapan mahasiswa <ul><li>Kemampuan dan pemahaman mahasiswa pada topik/judul yang diajukan</li></ul>',
            'Kesiapan sumberdaya <ul><li>Kesiapan data</li><li>Hardware & Software</li><li>Waktu</li></ul>',
            'Kelayakan topik <ul><li>Keterbaruan</li><li>Urgensi topik/judul</li></ul>',
            self::NILAI_LABEL_SKIPPED,
            self::NILAI_LABEL_SKIPPED,
            self::NILAI_LABEL_SKIPPED
        );
    }

    protected function configurePenilaianInput(Form &$form, $currentPenilaianInputId)
    {
        $form->getInput($currentPenilaianInputId)->setType('select')->setOptionsFromList([
            ''  => '-- Pilih Salah Satu --',
            '1' => 'Sangat Kurang',
            '2' => 'Kurang',
            '3' => 'Cukup',
            '4' => 'Baik',
            '5' => 'Sangat Baik'
        ]);
    }
}