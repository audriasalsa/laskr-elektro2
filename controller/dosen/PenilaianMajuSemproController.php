<?php


namespace controller\dosen;


use m\extended\Form;
use model\PendaftaranSemproModel;
use model\PendaftaranUjianAkhirModel;

class PenilaianMajuSemproController extends PenilaianMajuUjianAkhirController
{
    protected function setupView()
    {
        $this->view->setContentTemplate('/common/data_entry_template.php');

        $this->view->appendData(['page_title' => 'Penilaian Maju Seminar Proposal']);
        $this->view->appendData(['page_description' => 'Silahkan mengentrikan nilai untuk mahasiswa bimbingan Anda pada form di bawah.<p><u>Catatan:</u></p>
<ul>
    <li>Jika D4, maka Anda hanya memberikan nilai ke satu mahasiswa.</li>
    <li>Jika D3, maka Anda bisa memberikan nilai ke lebih dari satu mahasiswa.</li>
    <li>Jika D3, nilai mahasiswa yang satu boleh berbeda dengan nilai mahasiswa yang lainnya.</li>
    <li>Rentang nilai adalah 0 s.d. 100.</li>
</ul>']);
    }

    protected function createBackLink()
    {
        return $this->homeAddress('/dosen/bimbingan/persetujuan-pendaftaran-sempro');
    }

    protected function executeApprove($idProposal, $idEvent, $currentIdDosen, $statusPembimbing)
    {
        $mPendaftaranSempro = new PendaftaranSemproModel();

        if($mPendaftaranSempro->accept($idProposal, $idEvent, $currentIdDosen) !== false)
        {
            $actionResult = "Proposal dengan id = {$idProposal} telah disetujui untuk mendaftar maju seminar proposal.";

                // Jadwalkan jika belum terjadwal
            if($this->mUjian->findByIdEventAndIdProposal($idEvent, $idProposal) == null)
            {
                $this->mUjian->addNew($idEvent, $idProposal);
                $statusEntry = 'Data baru ditambahkan ke tabel ujian.';
            }
            else
            {
                $statusEntry = 'Data sudah ada di tabel ujian.';

                $actionResult .= "<br/>Pendaftaran sudah disetujui oleh kedua pembimbing dan akan segera dijadwalkan. $statusEntry";
            }
        }
        else
            $actionResult = "Proposal gagal disetujui maju ujian akhir!";

        return $actionResult;
    }

    protected function getPenilaianLabels()
    {
        return array(
            '1. Kelengkapan komponen dan keutuhan isi Proposal',
            '2. Perumusan Masalah/Tujuan-Kejelasan/ketajaman/urgensi',
            '3. Dasar Teori - Kesesuaian/kejelasan/ketajaman',
            '4. Kesesuaian dengan permasalahan dan tujuan (bisnis proses)',
            '5. Kelengkapan Desain Sistem',
            '6. Detail penjadwalan aktifitas penelitian',
            '7. Kesiapan Mahasiswa',
            '8. Presentasi',
            self::NILAI_LABEL_SKIPPED,
            self::NILAI_LABEL_SKIPPED,
            self::NILAI_LABEL_SKIPPED,
            self::NILAI_LABEL_SKIPPED
        );
    }

    protected function configurePenilaianInput(Form &$form, $currentPenilaianInputId)
    {
        $form->getInput($currentPenilaianInputId)->setType('select')->setOptionsFromList([
            ''      => '-- Pilih Salah Satu --',
            '0.001' => 'Tidak Ada/Belum di Nilai', // TODO: Cannot be zero because in form validation class, 0 is treated as empty.
            '1'     => 'Kurang',
            '2'     => 'Cukup',
            '3'     => 'Baik'
        ]);
    }
}