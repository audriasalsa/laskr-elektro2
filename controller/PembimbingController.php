<?php


namespace controller;


use lib\AppUtil;
use m\Application;
use m\Controller;
use m\extended\AuthPolicy;
use model\BimbinganModel;

class PembimbingController extends Controller
{
    private $_mBimbingan;

    public function __construct(Application $application)
    {
        parent::__construct($application);

        $this->_mBimbingan = new BimbinganModel();
    }

    public function infoPembimbing()
    {
        $this->accessControl()->inspect();

        $authModel = $this->accessControl()->findPolicy(AuthPolicy::class)->getModel();

        $currentUsername = $authModel->sessionUsername();

        $pembimbing = $this->_mBimbingan->findByNim($currentUsername);

        $viewData = array(
            'page_title'       => 'Informasi Pembimbing',
            'page_description' => $this->_pageDescription(),
        );

        if($pembimbing == null)
            $viewData['error_message'] = 'Tidak ada data! Hal ini disebabkan karena Anda belum memiliki pembimbing.';
        else
        {
            $display1 = AppUtil::toDisplayedData($pembimbing[0]);
            $viewData['data_pembimbing_1'] = $display1;

            if(count($pembimbing) > 1)
            {
                $display2 = AppUtil::toDisplayedData($pembimbing[1]);
                $viewData['data_pembimbing_2'] = $display2;
            }
        }

        $this->view->setData($viewData);
        $this->view->setContentTemplate('/mahasiswa/info_pembimbing_template.php');
        $this->view->render();
    }

    private function _pageDescription()
    {
        $res = AppUtil::createStaticResourceLink('form-bimbingan-skripsi.docx');

        //pre_print($res, true);

        $link = "<a href='http://$res'>sini</a>";

        $desc = <<<PHEREDOC
Berikut ini adalah dosen yang menjadi pembimbing-pembimbing Anda.
<br/>
Perhatian:
<ul>
    <li>Lakukanlah bimbingan setiap minggu!</li>
    <li>Untuk maju tahap 1, Anda harus melakukan minimal 8x bimbingan ke <strong>semua</strong> dosen pembimbing</li>
    <li>Unduh form bimbingan di $link!</li>
</ul>
PHEREDOC;

        return $desc;
    }
}