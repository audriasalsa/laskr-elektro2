<?php


namespace controller;


use m\Application;
use m\Controller;
use m\Util;

class JudulController extends Controller
{
    public function __construct(Application $application)
    {
        parent::__construct($application);
    }

    public function index()
    {
        $this->view->setContentTemplate('/judul/index_template.php');
        $this->view->render();
    }

    public function impor()
    {
        $this->accessControl()->inspect();

        if(isset($_POST['submit']))
        {
            $this->processUploadedFile($_FILES['file_impor']);
        }

        $this->view->setContentTemplate('judul/impor_template.php');
        $this->view->render();
    }

    private function processUploadedFile($fileData)
    {
        $this->accessControl()->inspect();

        $timestamp = date("YmdHis");

        $originalName = $fileData['name'];

        $newName = Util::sanitizeFileName($originalName);

        $newName = "{$timestamp}_{$newName}";

        $newName = $this->rootDir("static/upload/{$newName}");

        move_uploaded_file($fileData['tmp_name'], $newName);

        Util::prePrint($newName);
    }
}