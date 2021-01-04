<?php


namespace model;


use m\Controller;
use m\extended\AuthModel;
use m\Util;

class AppAuthModel extends AuthModel
{
    const SESSION_KEY_AUTH_ACCESS_TYPE = 'auth_access_type';

    private $_quarantinedRoutes;
    private $_routeAccessTypes;

    public function __construct()
    {
        parent::__construct('v2_credential');

        // Any routes that needs to be disabled (inaccessible) must be put here..
        $this->_quarantinedRoutes = array(
            '/judul',
            '/judul/index.php',
            '/judul/impor'
        );

        $this->_initRouteAccessTypes();
    }

    private function _initRouteAccessTypes()
    {
        $this->_routeAccessTypes = array(
            'mahasiswa' => array(
                '/',
                '/index.php',
                '/index/login',
                '/index/logout',
                '/index/data-diri',
                '/index/pendaftaran',
                '/proposal/entri-data-proposal',
                '/proposal/hasil-verifikasi',
                '/proposal/pendaftaran-sempro',
                '/proposal/ganti-proposal-sempro',
                '/proposal/cetak-bukti-pendaftaran-sempro',
                '/proposal/detail-pendaftaran-sempro',
                '/proposal/hasil-revisi-sempro',
                '/proposal/unggah-hasil-revisi-sempro',
                '/pembimbing/info-pembimbing',
                '/pembimbing/log-bimbingan',
                '/pendaftaran-ujian-akhir/index',
                '/pendaftaran-ujian-akhir/lihat',
                '/pendaftaran-ujian-akhir/daftar',
                '/ujian-akhir/berita-acara',
                '/ujian-akhir/berita-acara/ttd',
                '/ujian-akhir/revisi',
                '/ujian-akhir/revisi/detail',
                '/ujian-akhir/revisi/unggah',
                '/ujian-akhir/verifikasi-abstrak',
                '/pra-proposal/usulan-topik',
                '/pra-proposal/pengajuan-pembimbing',
                '/pra-proposal/pilih-topik',
                '/pra-proposal/jumlah-pembimbingan-utama',
                '/seminar-proposal/berita-acara',
                '/seminar-proposal/berita-acara/ttd',
            ),
            'dosen'     => array(
                '/',
                '/dosen/bimbingan/verifikasi-log-bimbingan',
                '/dosen/bimbingan/persetujuan-pendaftaran-ujian-akhir',
                '/dosen/sidang/ujian-akhir',
                '/dosen/sidang/ujian-akhir/detail',
                '/dosen/sidang/ujian-akhir/penilaian',
                //'/dosen/sidang/ujian-akhir/berita-acara',
                '/ujian-akhir/berita-acara',
                '/ujian-akhir/berita-acara/ttd',
                '/dosen/bimbingan/persetujuan-pendaftaran-ujian-akhir/penilaian-maju-ujian',
                '/dosen/sidang/verifikasi-revisi-ujian-akhir',
                '/dosen/laporan-akhir/verifikasi-abstrak/accept',
                '/pra-proposal/usulan-topik',
                '/dosen/bimbingan/persetujuan-pengajuan-pembimbing',
                '/dosen/bimbingan/persetujuan-pendaftaran-sempro',
                '/dosen/bimbingan/persetujuan-pendaftaran-sempro/penilaian-maju-sempro',
                '/dosen/bimbingan/sempro',
                '/dosen/bimbingan/sempro/detail',
                '/dosen/bimbingan/sempro/penilaian',
                '/seminar-proposal/berita-acara',
                '/seminar-proposal/berita-acara/ttd',
            ),
            'panitia'   => array(
                '/',
                '/index.php',
                '/index/login',
                '/index/logout',
                '/panitia/sempro/impor-jadwal',
                '/panitia/sempro/impor-hasil',
                '/panitia/impor/impor-dosen',
                '/panitia/impor/impor-bimbingan',
                '/panitia/rekap/log-bimbingan',
                '/panitia/rekap/log-bimbingan/detail',
                '/panitia/rekap/log-bimbingan-dosen',
                '/panitia/rekap/pendaftaran-ujian-akhir',
                '/panitia/rekap/yudisium-ujian-akhir',
                '/panitia/dokumen/generate',
                '/pra-proposal/jumlah-pembimbingan-utama'
            )
        );
    }

    public function getUser($username, $password)
    {
        $username = Util::sanitizeSqlInjection($username);
        $password = Util::sanitizeSqlInjection($password);

        $sql = "SELECT * FROM {$this->tableName} WHERE username = '$username' AND password = '$password' LIMIT 1";

        $result = $this->executeReadSQL($sql);

        if(count($result) > 0)
            return $result[0];

        return null;
    }

    public function getRedirectRoute()
    {
        return '/index/login';
    }

    public function getUserType($username, $password)
    {
        $user = $this->getUser($username, $password);

        if($user != null) {
            if (isset($user['type']))
                return $user['type'];
        }

        return null;
    }

    public function sessionStoreAccessType($accessType)
    {
        $this->getSession()->write(self::SESSION_KEY_AUTH_ACCESS_TYPE, $accessType);
    }

    public function grantAccess(Controller $controller)
    {
        $sessionOk = $this->sessionUsername() !== null;

        if($sessionOk)
        {
            $route = $controller->getCurrentRoute()->getPath(false);
            //pre_print($route, true);
            return $this->_checkRoute($route);
        }

        return false;
    }

    private function _checkRoute($route)
    {
        $quarantined = array_search($route, $this->_quarantinedRoutes) === false ? false : true;

        if($quarantined)
            return false;

        $type = $this->getSession()->read(self::SESSION_KEY_AUTH_ACCESS_TYPE);

        $permittedRoutes = $this->_routeAccessTypes[$type];

        $search = array_search($route, $permittedRoutes);

        if($search !== false)
            return true;

        return false;
    }
}