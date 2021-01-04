<?php


namespace controller\panitia;


use lib\AppUtil;
use lib\CommonTemplateViewData;
use m\Util;
use model\LogBimbinganModel;

class RekapLogBimbinganDosenController extends \lib\DataViewerController
{
    protected function getIndexData($filterValues = null)
    {
        $m = new LogBimbinganModel();

        if($filterValues == null)
            return
                Util::arrayTableAddEmptyColumn($m->findRekapLogBimbinganDosen(), 'action');

        switch ($filterValues['log_pending'])
        {
            case '> 50%':
                $where = 'WHERE (log_pending / total_log) > 0.5';
                break;
            case '< 10%':
                $where = 'WHERE (log_pending / total_log) < 0.1';
                break;
            default:
                $where = '';
        }

        return Util::arrayTableAddEmptyColumn($m->findRekapLogBimbinganDosenWithWhereClause($where), 'action');
    }

    // Opsional, tetapi dianjurkan di-override untuk mengganti tulisan yang ada di halaman index
    protected function getIndexViewData()
    {
        return (new CommonTemplateViewData())
            ->setPageDescription(
                'Berikut ini adalah rekap jumlah keseluruhan log bimbingan yang diajukan oleh mahasiswa ke setiap dosen pembimbingnya'
            )
            ->setPageTitle('Rekap Log Bimbingan per Dosen');
    }

    // Opsional, untuk filter
    protected function indexFilterFields()
    {
        return array(
            array('log_pending' => '> 50%'),
            array('log_pending' => '< 10%')
        );
    }

    // Opsional, ada di parent class, tetapi boleh di override untuk mengganti template
    protected function setupIndexView()
    {
        $this->view->setContentTemplate('/common/data_table_display_wa_template.php');

        $viewData = $this->getIndexViewData();

        $this->view->appendData($viewData->toAssoc());

        /*
        $this->view->appendData(['wa_message' => 'Assalamualaikum Wr. Wb.

Yth. Bapak/Ibu *{{subjectName}}*, kami dari panitia Skripsi bermaksud mengingatkan bahwasannya saat ini *{{param}}* dari keseluruhan log bimbingan yang diajukan kepada Anda masih dalam status *diajukan*. Harap luangkan waktu untuk memeriksa log-log tersebut. 

Sebagai catatan, Anda dapat mengubah status log bimbingan menjadi \'ditolak\' atau \'disetujui\', tergantung dari kondisi masing-masing mahasiswa bimbingan Anda. Untuk melakukannya silahkan login ke sistem skripsi dan bukalah menu *Bimbingan* -> *Verifikasi Log Bimbingan*.

URL Sistem Skripsi JTI dapat diakses pada URL berikut: 
http://tugasakhir.jti.polinema.ac.id/v2/dosen/bimbingan/verifikasi-log-bimbingan

Terima kasih. Wassalamu\'alaikum Warrahmatullah.']);
        */

        $this->view->appendData(['wa_message' => 'Assalamualaikum Wr. Wb.

Yth. Bapak/Ibu *{{subjectName}}*, kami dari panitia Skripsi bermaksud mengingatkan bahwasannya saat ini *{{param}}* dari keseluruhan log bimbingan yang diajukan kepada Bapak/Ibu masih dalam status \'*diajukan*\'. Mohon berkenan meluangkan waktu untuk memeriksa log-log tersebut. 

Sebagai catatan, Bapak/Ibu dapat mengubah status log bimbingan menjadi \'ditolak\' atau \'disetujui\', tergantung dari kondisi masing-masing mahasiswa bimbingan. Untuk melakukannya silahkan login ke sistem skripsi, kemudian masuk ke menu *Bimbingan* -> *Verifikasi Log Bimbingan*.

URL Sistem Skripsi JTI dapat diakses pada URL berikut: 
http://tugasakhir.jti.polinema.ac.id/v2/dosen/bimbingan/verifikasi-log-bimbingan
Sedangkan untuk username dan password-nya menggunakan nama panggilan Bapak/Ibu.

Terima kasih, semoga selalu sehat dan dilancarkan segala urusannya. Wassalamu\'alaikum Warrahmatullah.']);

        $this->view->addScript('/script/WaSender.js?v=1.2');
        $this->view->addScript('/script/rekap_log_bimbingan_dosen.js');
    }
}