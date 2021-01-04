<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Framework file. DO NOT DELETE!
require_once 'm/m.php';

// Third-party (composer) libraries
// TODO: Sort out this Composer usage!
//require_once 'thirdparty/vendor/autoload.php';

// Import your app's other classes if any
require_once 'lib/AppUtil.php';
require_once 'lib/ActionLink.php';
require_once 'lib/AppController.php';
require_once 'lib/DataViewerController.php';
require_once 'lib/CommonTemplateViewData.php';

// TODO: Finish this
//require_once 'lib/DynamicTemplate.php';

// Add your controllers here.
require_once 'controller/BerandaController.php';
require_once 'controller/JudulController.php';
require_once 'controller/mahasiswa/DraftProposalController.php';
require_once 'controller/ProposalController.php';
// TODO: This one is double import. Remove if there are no propblem exist after a some moments!
//require_once 'controller/PembimbingController.php';
require_once 'controller/PembimbingController.php';
require_once 'controller/RevisiSemproController.php';
require_once 'controller/mahasiswa/LogBimbinganController.php';
require_once 'controller/mahasiswa/PendaftaranUjianAkhirController.php';
require_once 'controller/mahasiswa/RevisiUjianAkhirController.php';
require_once 'controller/mahasiswa/UnggahRevisiUjianAkhirController.php';
require_once 'controller/mahasiswa/VerifikasiAbstrakController.php';
require_once 'controller/mahasiswa/PengajuanPembimbingController.php';
require_once 'controller/mahasiswa/PilihTopikController.php';
require_once 'controller/mahasiswa/PendaftaranSemproController.php';
require_once 'controller/mahasiswa/HasilVerifikasiProposalController.php';

// Panitia
require_once 'controller/PanitiaSemproController.php';
require_once 'controller/panitia/ImporDosenController.php';
require_once 'controller/panitia/ImporBimbinganController.php';
require_once 'controller/panitia/RekapLogBimbinganController.php';
require_once 'controller/panitia/RekapLogBimbinganDosenController.php';
require_once 'controller/panitia/RekapPendaftaranUjianAkhirController.php';
require_once 'controller/panitia/RekapYudisiumUjianAkhirController.php';

// TODO: Finish this
//require_once 'controller/panitia/GenerateDokumenController.php';

// Dosen
require_once 'controller/dosen/BimbinganController.php';
require_once 'controller/dosen/VerifikasiLogBimbinganController.php';
require_once 'controller/dosen/PersetujuanPendaftaranUjianAkhirController.php';
require_once 'controller/dosen/UjianAkhirController.php';
require_once 'controller/dosen/PenilaianUjianAkhirController.php';
require_once 'controller/dosen/BeritaAcaraUjianAkhirController.php';
require_once 'controller/dosen/PenilaianMajuUjianAkhirController.php';
require_once 'controller/dosen/VerifikasiRevisiUjianAkhirController.php';
require_once 'controller/dosen/VerifikasiAbstrakController.php';
require_once 'controller/dosen/PersetujuanPengajuanPembimbingController.php';
require_once 'controller/dosen/PersetujuanPendaftaranSemproController.php';
require_once 'controller/dosen/PenilaianMajuSemproController.php';
require_once 'controller/dosen/SeminarProposalController.php';
require_once 'controller/dosen/PenilaianSeminarProposalController.php';

// Mahasiswa & Dosen
require_once 'controller/UsulanTopikController.php';
require_once 'controller/dosen/BeritaAcaraSeminarProposalController.php'; // TODO: Refactor this. Move to controller root folder.

// Mahasiswa & Panitia
require_once 'controller/JumlahPembimbinganUtamaController.php';

// Public
require_once 'controller/ValidasiKelulusanController.php';

// Here is the place for your model scripts.
require_once 'model/AppAuthModel.php';
require_once 'model/AppUploadedFileModel.php';
require_once 'model/MahasiswaModel.php';
require_once 'model/ProposalModel.php';
require_once 'model/BimbinganModel.php';
require_once 'model/DosenModel.php';
require_once 'model/EventModel.php';
require_once 'model/HasilSemproModel.php';
require_once 'model/RevisiSemproModel.php';
require_once 'model/TempModel.php';
require_once 'model/LogBimbinganModel.php';
require_once 'model/CredentialModel.php';
require_once 'model/PendaftaranUjianAkhirModel.php';
require_once 'model/NimAktifModel.php';
require_once 'model/NilaiPklModel.php';
require_once 'model/UjianModel.php';
require_once 'model/PenilaianUjianModel.php';
require_once 'model/BeritaAcaraUjianModel.php';
require_once 'model/RevisiUjianAkhirModel.php';
require_once 'model/PenilaianPembimbingModel.php';
require_once 'model/VerifikasiAbstrakModel.php';
require_once 'model/TopikModel.php';
require_once 'model/ProdiModel.php';
require_once 'model/GrupRisetModel.php';
require_once 'model/PengajuanPembimbingModel.php';
require_once 'model/VerifikasiProposalModel.php';
require_once 'model/special/PengaturanModel.php';
require_once 'model/PendaftaranSemproModel.php';

use m\Settings;
use m\Application;
use m\extended\AuthPolicy;

$settings = Settings::getInstance();

$settings->displayError();

// The app folder relative to your htdocs folder.
// If this function doesn't get called, the framework will try to detect it automatically.
// $settings->setAppFolder('/v2');

// Add your DB settings. This is not the best practice, but it is okay for now.
if($settings->getAppFolder() == '/v2') {
    $settings->setDbConnection(array(
        'server'   => 'tugasakhir.jti.polinema.ac.id',  // Remote MySQL server
        'database' => 'db_tugasakhir',                  // Remote DB name
        'username' => 'tugasakhir',                     // Username
        'password' => 'JchJnwjSCJ0w7oom'                // Password
    ));
}
else
{
    // Local database configuration. DO NOT FORGET to comment when the script is uploaded to remote host
    $settings->setDbConnection(array(
        'server'   => /*'tugasakhir.jti.polinema.ac.id', */ 'localhost',      //'192.168.1.249',  // Local MySQL server
        'database' => /*'db_tugasakhir',                 */ 'laskr',          //'laskr',          // Your DB name
        'username' => /*'tugasakhir',                    */ 'root',           //'root',           // Default username
        'password' => /*'JchJnwjSCJ0w7oom'               */ 'root',           //'bismillah'       // Local MySQL password
    ));
}

// This is how to set up your route
// Normally consists of:
/*
    array('URL/typed/in/the/browser', 'ControllerName', 'MethodName');
*/
$settings->setRoute(array(
    // Experimental
    array('/judul', 'Judul', 'index'),
    array('/judul/index.php', 'Judul', 'index'),
    array('/judul/impor', 'Judul', 'impor'),
    // Semua
    array('/', 'Beranda', 'index'),
    array('/index.php', 'Beranda', 'index'),
    array('/index/login', 'Beranda', 'login'),
    array('/index/logout', 'Beranda', 'logout'),

    // Mahasiswa dan dosen
    array('/ujian-akhir/berita-acara', 'dosen\BeritaAcaraUjianAkhir', 'index'),
    array('/ujian-akhir/berita-acara/ttd', 'dosen\BeritaAcaraUjianAkhir', 'tandaTangan'),
    array('/pra-proposal/usulan-topik', 'UsulanTopik', 'index'),
    array('/seminar-proposal/berita-acara', 'dosen\BeritaAcaraSeminarProposal', 'index'),
    array('/seminar-proposal/berita-acara/ttd', 'dosen\BeritaAcaraSeminarProposal', 'tandaTangan'),

    // Mahasiswa dan panitia
    array('/pra-proposal/jumlah-pembimbingan-utama', 'JumlahPembimbinganUtama', 'index'),

    // Mahasiswa
    array('/index/data-diri', 'Beranda', 'dataDiri'),
    array('/index/pendaftaran', 'Beranda', 'pendaftaran'),
    array('/proposal/entri-data-proposal', 'mahasiswa\DraftProposal', 'index'),
    //array('/proposal/hasil-verifikasi', 'Proposal', 'hasilVerifikasi'),
    array('/proposal/hasil-verifikasi', 'mahasiswa\HasilVerifikasiProposal', 'index'),
    //array('/proposal/pendaftaran-sempro', 'Proposal', 'pendaftaranSempro'),
    array('/proposal/pendaftaran-sempro', 'mahasiswa\PendaftaranSempro', 'index'),
    //array('/proposal/detail-pendaftaran-sempro', 'Proposal', 'detailPendaftaranSempro'),
    array('/proposal/detail-pendaftaran-sempro', 'mahasiswa\PendaftaranSempro', 'detail'),
    //array('/proposal/ganti-proposal-sempro', 'Proposal', 'gantiProposalSempro'),
    //array('/proposal/cetak-bukti-pendaftaran-sempro', 'Proposal', 'cetakBuktiPendaftaranSempro'),
    array('/proposal/hasil-revisi-sempro', 'RevisiSempro', 'hasilRevisi'),
    array('/proposal/unggah-hasil-revisi-sempro', 'RevisiSempro', 'unggahHasilRevisi'),
    array('/pembimbing/info-pembimbing', 'Pembimbing', 'infoPembimbing'),
    array('/pembimbing/log-bimbingan', 'mahasiswa\LogBimbingan', 'logBimbingan'),
    array('/pendaftaran-ujian-akhir/index', 'mahasiswa\PendaftaranUjianAkhir', 'index'),
    array('/pendaftaran-ujian-akhir/lihat', 'mahasiswa\PendaftaranUjianAkhir', 'lihat'),
    array('/pendaftaran-ujian-akhir/daftar', 'mahasiswa\PendaftaranUjianAkhir', 'daftar'),
    array('/ujian-akhir/revisi', 'mahasiswa\RevisiUjianAkhir', 'index'),
    array('/ujian-akhir/revisi/detail', 'mahasiswa\RevisiUjianAkhir', 'detail'),
    array('/ujian-akhir/revisi/unggah', 'mahasiswa\UnggahRevisiUjianAkhir', 'index'),
    array('/ujian-akhir/verifikasi-abstrak', 'mahasiswa\VerifikasiAbstrak', 'index'),
    array('/pra-proposal/pengajuan-pembimbing', 'mahasiswa\PengajuanPembimbing', 'index'),
    array('/pra-proposal/pilih-topik', 'mahasiswa\PilihTopik', 'index'),

    // Panitia
    array('/panitia/sempro/impor-jadwal', 'PanitiaSempro', 'imporJadwal'),
    array('/panitia/sempro/impor-hasil', 'PanitiaSempro', 'imporHasil'),
    array('/panitia/impor/impor-dosen', 'panitia\ImporDosen', 'impor'),
    array('/panitia/impor/impor-bimbingan', 'panitia\ImporBimbingan', 'impor'),
    array('/panitia/rekap/log-bimbingan', 'panitia\RekapLogBimbingan', 'index'),
    array('/panitia/rekap/log-bimbingan/detail', 'panitia\RekapLogBimbingan', 'detail'),
    array('/panitia/rekap/log-bimbingan-dosen', 'panitia\RekapLogBimbinganDosen', 'index'),
    array('/panitia/rekap/pendaftaran-ujian-akhir', 'panitia\RekapPendaftaranUjianAkhir', 'index'),
    array('/panitia/rekap/yudisium-ujian-akhir', 'panitia\RekapYudisiumUjianAkhir', 'index'),
    array('/panitia/rekap/yudisium-ujian-akhir/download-csv', 'panitia\RekapYudisiumUjianAkhir', 'downloadCsv'),
    array('/panitia/dokumen/generate', 'panitia\GenerateDokumen', 'index'),

    // Dosen
    array('/dosen/bimbingan/daftar-mahasiswa-bimbingan', 'dosen\Bimbingan', 'daftarMahasiswa'),
    array('/dosen/bimbingan/daftar-mahasiswa-bimbingan/lembar-pengesahan', 'dosen\Bimbingan', 'lembarPengesahan'),
    array('/dosen/bimbingan/verifikasi-log-bimbingan', 'dosen\VerifikasiLogBimbingan', 'index'),
    array('/dosen/bimbingan/persetujuan-pendaftaran-ujian-akhir', 'dosen\PersetujuanPendaftaranUjianAkhir', 'index'),
    array('/dosen/sidang/ujian-akhir', 'dosen\UjianAkhir', 'index'),
    array('/dosen/sidang/ujian-akhir/detail', 'dosen\UjianAkhir', 'detail'),
    array('/dosen/sidang/ujian-akhir/penilaian', 'dosen\PenilaianUjianAkhir', 'index'),
    //array('/dosen/sidang/ujian-akhir/berita-acara', 'dosen\BeritaAcaraUjianAkhir', 'index'),
    array('/dosen/bimbingan/persetujuan-pendaftaran-ujian-akhir/penilaian-maju-ujian', 'dosen\PenilaianMajuUjianAkhir', 'index'),
    array('/dosen/sidang/verifikasi-revisi-ujian-akhir', 'dosen\VerifikasiRevisiUjianAkhir', 'index'),
    array('/dosen/laporan-akhir/verifikasi-abstrak', 'dosen\VerifikasiAbstrak', 'index'),
    array('/dosen/laporan-akhir/verifikasi-abstrak/accept', 'dosen\VerifikasiAbstrak', 'accept'),
    array('/dosen/bimbingan/persetujuan-pengajuan-pembimbing', 'dosen\PersetujuanPengajuanPembimbing', 'index'),
    array('/dosen/bimbingan/persetujuan-pendaftaran-sempro', 'dosen\PersetujuanPendaftaranSempro', 'index'),
    array('/dosen/bimbingan/persetujuan-pendaftaran-sempro/penilaian-maju-sempro', 'dosen\PenilaianMajuSempro', 'index'),
    array('/dosen/bimbingan/sempro', 'dosen\SeminarProposal', 'index'),
    array('/dosen/bimbingan/sempro/detail', 'dosen\SeminarProposal', 'detail'),
    array('/dosen/bimbingan/sempro/penilaian', 'dosen\PenilaianSeminarProposal', 'index'),


    // Public
    array('/public/validasi-kelulusan', 'ValidasiKelulusan', 'index')
    // Add your other routes here...


    // Below are some examples of other valid routing
    /*
    array('/url/*', 'Home', 'url'),
    array('/search', 'Home', 'search'),
    array('/vision', 'Vision', 'index'),
    array('/publikasi-jti', 'PublikasiJTI', 'index'),
    array('/api/contacts', 'Api', 'contacts')
    */
));


$app = new Application($settings);

// Enabling access control, if you need your controller method can only be accessed with credential
$authPolicy = new AuthPolicy(new \model\AppAuthModel());
$app->enableAccessControl([$authPolicy]);

// Execute the application!
$app->run();