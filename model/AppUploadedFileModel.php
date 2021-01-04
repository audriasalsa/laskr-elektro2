<?php


namespace model;


use m\UploadedFileModel;

class AppUploadedFileModel extends UploadedFileModel
{
    public function __construct()
    {
        parent::__construct('v2_uploaded_file');
    }
}