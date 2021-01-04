<?php


namespace lib;


class CommonTemplateViewData
{
    private $_pageTitle;
    private $_pageDescription;
    private $_errorMessage;
    private $_backLink;

    public function __construct()
    {
        $this->_pageTitle = 'Data Viewer';
        $this->_pageDescription = 'View your data here.';
        $this->_errorMessage = '';
        $this->_backLink = null;
    }

    public function toAssoc()
    {
        return [
            'page_title'       => $this->_pageTitle,
            'page_description' => $this->_pageDescription,
            'error_message'    => $this->_errorMessage,
            'back_link'        => $this->_backLink
        ];
    }

    /**
     * @return mixed
     */
    public function getPageTitle()
    {
        return $this->_pageTitle;
    }

    /**
     * @param mixed $pageTitle
     * @return CommonTemplateViewData
     */
    public function setPageTitle($pageTitle)
    {
        $this->_pageTitle = $pageTitle;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPageDescription()
    {
        return $this->_pageDescription;
    }

    /**
     * @param mixed $pageDescription
     * @return CommonTemplateViewData
     */
    public function setPageDescription($pageDescription)
    {
        $this->_pageDescription = $pageDescription;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getErrorMessage()
    {
        return $this->_errorMessage;
    }

    /**
     * @param mixed $errorMessage
     * @return CommonTemplateViewData
     */
    public function setErrorMessage($errorMessage)
    {
        $this->_errorMessage = $errorMessage;
        return $this;
    }

    /**
     * @return null
     */
    public function getBackLink()
    {
        return $this->_backLink;
    }

    /**
     * @param null $backLink
     * @return CommonTemplateViewData
     */
    public function setBackLink($backLink)
    {
        $this->_backLink = $backLink;
        return $this;
    }
}