<?php


namespace controller\dosen;


use lib\ActionLink;
use lib\AppUtil;
use lib\CommonTemplateViewData;
use lib\DataViewerController;
use m\Application;
use m\Util;
use model\AppUploadedFileModel;
use model\EventModel;
use model\ProdiModel;
use model\ProposalModel;
use model\RevisiSemproModel;
use model\UjianModel;

class UjianAkhirController extends DataViewerController
{
    // Wheteher to show all tahap or the last one only.
    const SHOW_TYPE_LATEST_ONLY = 'latest_only';
    const SHOW_TYPE_ALL = 'all';

    // Models
    protected $mUjian;
    protected $mEvent;
    protected $mProposal;

    // Data
    protected $showType;
    protected $currentPeriodOnly;
    protected $jenisUjian;
    protected $latestUjianAkhir;

    public function __construct(Application $application)
    {
        parent::__construct($application);

        $this->showType = self::SHOW_TYPE_ALL;

        $this->mUjian = new UjianModel();
        $this->mEvent = new EventModel();
        $this->mProposal = new ProposalModel();

        // Data
        $this->currentPeriodOnly = true;
        $this->jenisUjian = UjianModel::JENIS_UJIAN_AKHIR;
    }

    // Tampilkan data sebelumnya apabila pendaftar tahap sekarang belum ada yang diacc dosennya.
    private function _getLatestUjianAkhir()
    {
        if($this->latestUjianAkhir == null)
        {
            $latest = $this->mEvent->findLatestByKategori($this->jenisUjian);

            $registrarCount = $this->mUjian->countUjianTerjadwalByIdEvent($latest['id']) < 1;

            if($registrarCount)
                $latest = $this->mEvent->findSecondLatestByKategori($this->jenisUjian);

            $this->latestUjianAkhir = $latest;
        }

        return $this->latestUjianAkhir;
    }

    protected function getIndexData($filterValues = null)
    {
        if(isset($_GET['current_period_only']))
        {
            if ($_GET['current_period_only'] == 'false')
            {
                pre_print('WARNING: [Cheat activated!] Showing data for all period..');

                $this->currentPeriodOnly = false;
            }
        }

        if($this->showType == self::SHOW_TYPE_LATEST_ONLY)
        {
            $lua = $this->_getLatestUjianAkhir();

            if ($filterValues == null)
                $filterValues = array();

            $filterValues['id_event'] = $lua['id'];
        }

        $ujianList =  $this->mUjian->findRekapUjianTerjadwal(
            $this->jenisUjian,
            'nomor_ujian, kode_prodi, tahap, id_proposal, judul_proposal, nama_pengusul, nama_dosen_moderator, nama_dosen_penguji_1, nama_dosen_penguji_2, waktu_mulai, waktu_selesai, kode_ruang, keterangan_ruang',
            $filterValues,
            $this->currentPeriodOnly
        );

        /* Seharusnya baris yang dikomen dibawah ini tidak perlu. Karena diatas sudah diperbaiki.
         * Jika dalam beberapa saat sistem tidak ada error terkait hal ini maka bisa dianggap stabil sehingga bisa dihapus.
         * /
        if($ujianList == null) // Belum ada yang daftar
        {
            // Ambil 1 ujian tepat 1 tahap sebelum tahap yang terbaru
            $secondLatest = $this->_mEvent->findSecondLatestUjianAkhir();

            // Ubah filternya
            $filterValues['id_event'] = $secondLatest['id'];

            // Coba lagi query-nya.
            $ujianList =  $this->_mUjian->findRekapUjianTerjadwal(
                'nomor_ujian, kode_prodi, tahap, id_proposal, judul_proposal, nama_pengusul, nama_dosen_moderator, nama_dosen_penguji_1, nama_dosen_penguji_2, waktu_mulai, waktu_selesai, kode_ruang, keterangan_ruang',
                $filterValues
            );

            // Update properti agar semua ikut berubah ke ujian yang sebelumnya
            $this->_latestUjianAkhir = $secondLatest;
        }
        */

        return $ujianList;
    }

    protected function getIndexViewData()
    {
        $viewData = new CommonTemplateViewData();

        $lua = $this->_getLatestUjianAkhir();

        if($this->showType != self::SHOW_TYPE_ALL)
            $namaTahap = $lua == null ? '' : "({$lua['nama']})";
        else
            $namaTahap = '(Semua Tahap)';

        // TODO: Yang tampil masih judul latest ujian akhir yang terbaru
        $viewData->setPageTitle("Ujian Terjadwal $namaTahap");
        $viewData->setPageDescription('Berikut ini adalah data sesi ujian pendaftarannya telah disetujui oleh semua dosen pembimbing. Klik <b>Detail</b> untuk tindakan lebih lanjut pada setiap sesi.');

        return $viewData;
    }

    public function detail()
    {
        $this->view->setContentTemplate('/common/data_display_template.php');
        $this->view->appendData(['back_link' => $this->getDetailBackLink()]);

        $idUjian = isset($_GET['nomor_ujian']) ? $_GET['nomor_ujian'] : null;

        $data = $this->getDetailUjianData($idUjian);

        if(ProdiModel::isD4($data['kode_prodi']))
        {
            unset($data['nim_anggota']);
            unset($data['nama_anggota']);
        }

        $actionLinks = $this->createActionLinks($idUjian, $data);

        $data = AppUtil::toDisplayedData($data);

        $pageTitle = $this->jenisUjian == UjianModel::JENIS_UJIAN_AKHIR ? 'Detail Sesi Ujian Akhir' : 'Detail Sesi Seminar Proposal';

        $this->view->appendData(['page_title' => $pageTitle]);
        $this->view->appendData(['page_description' => 'Klik button <b>Penilaian</b> atau <b>Berita Acara</b> di bagian bawah halaman ini untuk melakukan aksi terkait.']);
        $this->view->appendData(['displayed_data' => $data]);

        $this->view->appendData([
            'action_links' => $actionLinks
        ]);

        $this->view->render();
    }

    // Opsional, bila tidak di-override, maka tidak akan dibuatkan link Detail
    protected function getDetailActionParamName()
    {
        return 'nomor_ujian'; // Langsung ambil kolom bernama nomor_ujian dari data yang ditampilkan
    }

    private function _convertFilesToLinks(&$data)
    {
        $ufm = new AppUploadedFileModel();

        // File presentasi	1c81f553bad57c2c1a7f9479f3995672.pdf
        $data['file_presentasi'] = $ufm->createFileLink($data['file_presentasi'], true);

        // File laporan akhir	042a301513da66900b0b4d177a87fa9e.pdf
        $data['file_laporan_akhir'] = $ufm->createFileLink($data['file_laporan_akhir'], true);

        // File draft publikasi	e1534e9ab6d2106c4efaf02088b9f403.png
        $data['file_draft_publikasi'] = $ufm->createFileLink($data['file_draft_publikasi'], true);

        // Link video demo	https://www.youtube.com
        $data['link_video_demo'] = AppUtil::createActionLink($data['link_video_demo']);

        // Link instalasi aplikasi
        $data['link_instalasi_aplikasi'] = AppUtil::createActionLink($data['link_instalasi_aplikasi']);
    }

    // Opsional, untuk filter
    protected function indexFilterFields()
    {
        $filterArray =  array(
            array('kode_prodi' => 'D3-MI', 'tahap' => null, 'id_proposal' => null, 'judul_proposal' => null, 'nama_pengusul' => null), // null akan dianggap input filternya 'text'
            array('kode_prodi' => 'D4-TI'),
        );

        $filterArray = (new EventModel())->addEventNamesToFilterArray($filterArray, $this->jenisUjian, 'tahap', $this->currentPeriodOnly);

        return $filterArray;
    }

    private function _addProposalToDisplayedData(array $data)
    {
        $revisiData = (new RevisiSemproModel())->findOneRekapByIdProposal($data['id_proposal']);

        if($revisiData != null)
            $fileProposal = $revisiData['file_proposal_final'];
        else // Jika data revisi tidak ditemukan, fallback ke proposal awal (biasanya untuk D3)
        {
            $proposalOrig = $this->mProposal->findOneById($data['id_proposal']);

            $fileProposal = $proposalOrig['draft'];
        }

        $data['file_proposal'] = (new AppUploadedFileModel())->createFileLink($fileProposal, true);

        return $data;
    }

    protected function getDetailBackLink()
    {
        return $this->homeAddress('/dosen/sidang/ujian-akhir');
    }

    protected function getDetailUjianData($idUjian)
    {
        $data = $this->mUjian->findRekapUjianAkhirByIdUjian($idUjian);

        $this->_convertFilesToLinks($data);

        $data = Util::arrayAssocChangeKey('informasi_tambahan', 'Informasi Tambahan <br/>(Username/password aplikasi, dlsb.)', $data);

        $data = $this->_addProposalToDisplayedData($data);

        return $data;
    }

    protected function createActionLinks($idUjian, $ujianData)
    {
        $nimPengusul = $ujianData['nim_pengusul'];
        $nimAnggota = $ujianData['nim_anggota'];

        // No need these lines becasue penilaian page now has been combined into single page.
        // TODO: Delete this one after some moments if there are no problems.
        /*
        $actionLinks = [
            (new ActionLink($this->homeAddress($this->getPenilaianRoute()), 'Penilaian Pengusul', ['nomor_ujian' => $idUjian, 'nim' => $nimPengusul]))->setCssClass('default-action-button'),
            (new ActionLink($this->homeAddress($this->getBeritaAcaraRoute()), 'Berita Acara Pengusul', ['nomor_ujian' => $idUjian, 'nim' => $nimPengusul]))->setCssClass('default-action-button'),
        ];

        if(ProdiModel::isD3($ujianData['kode_prodi']))
        {
            $actionLinks[] = (new ActionLink($this->homeAddress($this->getPenilaianRoute()), 'Penilaian Anggota', ['nomor_ujian' => $idUjian, 'nim' => $nimAnggota]))->setCssClass('form-submit-button');
            $actionLinks[] = (new ActionLink($this->homeAddress($this->getBeritaAcaraRoute()), 'Berita Acara Anggota', ['nomor_ujian' => $idUjian, 'nim' => $nimAnggota]))->setCssClass('form-submit-button');
        }
        */

        $actionLinks = [
            (new ActionLink($this->homeAddress($this->getPenilaianRoute()), 'Penilaian', ['nomor_ujian' => $idUjian, 'nim' => $nimPengusul]))->setCssClass('blue-action-button'),
            (new ActionLink($this->homeAddress($this->getBeritaAcaraRoute()), 'Berita Acara', ['nomor_ujian' => $idUjian, 'nim' => $nimPengusul]))->setCssClass('green-action-button'),
        ];

        return $actionLinks;
    }

    protected function getPenilaianRoute()
    {
        return '/dosen/sidang/ujian-akhir/penilaian';
    }

    protected function getBeritaAcaraRoute()
    {
        return '/ujian-akhir/berita-acara';
    }
}