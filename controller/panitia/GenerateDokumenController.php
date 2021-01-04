<?php

namespace controller\panitia;

use lib\DynamicTemplate;
use m\Application;
use m\Controller;
use m\Util;

class GenerateDokumenController extends Controller
{
    const DOC_EXTENSION = '.html';
    const DOC_TEMPLATE_BERITA_ACARA_UJIAN_AKHIR = 'berita_acara_ujian_akhir';

    private $_templateDir;
    private $_outputDir;

    public function __construct(Application $application)
    {
        parent::__construct($application);

        $this->_templateDir = self::rootDir('/static/document_templates');
        $this->_outputDir = self::rootDir('/static/document_templates/output');
    }

    public function index()
    {
        $this->accessControl()->inspect();

        $templateFile = $this->_getTemplateFile();

        $templateFilePath = $this->_templateDir . '/' . $templateFile . self::DOC_EXTENSION;

        $dt = new DynamicTemplate($templateFilePath);
        $dt->synchronize();
        $html = $dt->getSynchronizedText();

        $this->_saveAsPdf(self::DOC_TEMPLATE_BERITA_ACARA_UJIAN_AKHIR, $html);

        $this->view->setContentTemplate('panitia/generate_dokumen_index_template.php');
        $this->view->render();
    }

    private function _getTemplateFile()
    {
        return self::DOC_TEMPLATE_BERITA_ACARA_UJIAN_AKHIR;
    }

    private function _saveAsPdf($docTemplateFile, $html)
    {
        $mpdf = new \Mpdf\Mpdf();
        $mpdf->WriteHTML($html);

        $outputFile = $this->_outputDir . "/$docTemplateFile.pdf";

        $mpdf->Output($outputFile, \Mpdf\Output\Destination::FILE);

        return $outputFile;
    }
}
