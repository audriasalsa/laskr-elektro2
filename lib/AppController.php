<?php


namespace lib;


use m\Application;
use m\Controller;
use m\Util;

class AppController extends Controller
{
    const DEFAULT_ERROR_TEMPLATE = 'error_template.php';

    const BACKLINK_BEHAVIOR_BROWSER = 'browser';
    const BACKLINK_BEHAVIOR_DEFAULT_ROUTE = 'default_route';

    public function __construct(Application $application)
    {
        parent::__construct($application);
    }

    protected function renderErrorAndExit($errMessage, $backLinkBehavior = self::BACKLINK_BEHAVIOR_BROWSER, $contentTemplate = null)
    {
        $this->view->appendData(array('error_message' => $errMessage));

        if($contentTemplate != null)
            $this->view->setContentTemplate($contentTemplate);
        else // TODO: Check also all other renderXxxxAndExit() method
        {
            $this->view->setContentTemplate(self::DEFAULT_ERROR_TEMPLATE);

            if($backLinkBehavior == self::BACKLINK_BEHAVIOR_DEFAULT_ROUTE)
                $this->view->modifyData('back_link', $this->getCurrentRoute()->toURL());
            else
                $this->view->modifyData('back_link', 'javascript:history.back()');
        }

        $this->view->render();

        exit(0);
    }

    protected function renderSuccessAndExit($errMessage, $contentTemplate = null)
    {
        $this->view->appendData(array('success_message' => $errMessage));

        if($contentTemplate != null)
            $this->view->setContentTemplate($contentTemplate);

        $this->view->render();

        exit(0);
    }

    protected function renderAndExit(array $data = null, $contentTemplate = null)
    {
        if($data != null)
            $this->view->appendData($data);

        if($contentTemplate != null)
            $this->view->setContentTemplate($contentTemplate);

        $this->view->render();

        exit(0);
    }

    protected function downloadFile()
    {
        $file = isset($_GET['file']) ? $_GET['file'] : null;

        if($file == null)
            return;

        $file = base64_decode($file);

        $file = self::rootDir($file);

        //pre_print($file);

        Util::triggerDownload($file);
    }

    protected function toDownloadFileUrl($route, $fileName)
    {
        $file = base64_encode($fileName);

        return $this->homeAddress($route . '?file=' . $file);
    }
}