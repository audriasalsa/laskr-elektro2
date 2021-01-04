<?php


namespace controller\mahasiswa;


use controller\UsulanTopikController;
use lib\AppUtil;
use lib\CommonTemplateViewData;
use lib\DataViewerController;
use model\CredentialModel;
use model\TopikModel;

class PilihTopikController extends DataViewerController
{
    const SELECT_TYPE_OWN = 'own';
    const SELECT_TYPE_DOSEN = 'dosen';

    private $_selectType;
    private $_currentMahasiswa;

    protected function getIndexData($filterValues = null)
    {
        $this->_determineCurrentMahasiswa();

        if($this->_selectType == self::SELECT_TYPE_DOSEN)
            $data = (new TopikModel())->findAllJenisPengajuanDosen();
        else if($this->_selectType == self::SELECT_TYPE_OWN)
            $data = (new TopikModel())->findAllByIdMahasiswaPengusul($this->_currentMahasiswa['nim']);
        else
            $data = null;

        if($data != null)
            $data = $this->_addActionLinks($data);

        return $data;
    }

    private function _determineSelectType()
    {
        if(isset($_GET['type']))
        {
            $this->_selectType = $_GET['type'];
        }

        else $this->_selectType = null;
    }

    private function _determineCurrentMahasiswa()
    {
        $username = AppUtil::getCurrentUsername($this);

        $this->_currentMahasiswa = (new CredentialModel())->findMahasiswa($username);
    }

    private function _addActionLinks(array $data)
    {
        $pengajuanPemimbingPath = $this->getCurrentRoute()->findPathOf(PengajuanPembimbingController::class, 'index');

        $actionLink = $this->homeAddress($pengajuanPemimbingPath);

        $result = array();

        foreach ($data as $row)
        {
            $currentActionLink = "$actionLink?id_topik={$row['id']}";

            $aHref = "<a class='blue-action-button' href='$currentActionLink'>Pilih</a>";

            $row['aksi'] = $aHref;

            $result[] = $row;
        }

        return $result;
    }

    protected function getIndexViewData()
    {
        $this->_determineSelectType();

        $vd = new CommonTemplateViewData();

        if($this->_selectType == self::SELECT_TYPE_DOSEN)
        {
            $pageTitle = 'Pilih Topik Dosen';
            $description = <<< PHREDOC
Berikut ini adalah daftar topik yang diajukan oleh Dosen.
<p>
<b><u>Catatan</u></b>:
<ul>
    <li>Ketika Anda memilih topik di sini, maka pada saat membuat pengajuan pembimbing, dosen yang dapat dipilih hanyalah dosen pemilik topik yang Anda pilih tersebut.</li>
    <li>Ketika sebuah pengajuan pembimbing dengan topik dosen disetuji oleh dosen ybs., maka topik tersebut statusnya akan berubah dari 'bebas' menjadi 'diklaim'.</li>
    <li>Topik yang sudah diklaim, tidak akan muncul lagi pada halaman ini.</li>
</ul>
</p>
PHREDOC;
        }
        else
        {
            $pageTitle = 'Pilih Topik Anda Sendiri';
            $description = <<< PHREDOC
Berikut ini adalah daftar topik yang telah Anda buat sebelumnya.
<p>
<b><u>Catatan</u></b>:
<ul>
    <li>Anda dapat membuat lebih dari satu topik, tetapi hanya 1 topik saja yang dapat dijadikan proposal TA.</li>
    <li>Ketika sebuah pengajuan pembimbing dengan topik Anda sendiri disetuji oleh dosen yang Anda inginkan, maka topik Anda yang lain tidak dapat digunakan kembali.</li>
</ul>
</p>
PHREDOC;
        }

        $usulanTopikPath = $this->getCurrentRoute()->findPathOf(UsulanTopikController::class, 'index');

        $usulanTopikUrl = $this->homeAddress($usulanTopikPath);

        $description = "$description<p><a href='$usulanTopikUrl'>Buat Usulan Topik Baru</a></p>";

        $vd->setPageTitle($pageTitle);
        $vd->setPageDescription($description);

        return $vd;
    }
}