<?php


namespace lib;


use m\Util;

class ActionLink
{
    private $_caption;
    private $_actionUrl;
    private $_paramValuePairs;
    private $_cssClass;
    // TODO: tag type so can be chosen between <a></a> or any other possible tags

    public function __construct($actionUrl = null, $caption = null, $paramValuePairs = null)
    {
        $this->_actionUrl = $actionUrl;
        $this->_caption = $caption == null ? $this->_actionUrl : $caption;
        $this->_paramValuePairs = $paramValuePairs == null ? array() : $paramValuePairs;
        $this->_cssClass = '';
    }

    /**
     * @return string
     */
    public function getCssClass()
    {
        return $this->_cssClass;
    }

    /**
     * @param string $cssClass
     * @return ActionLink
     */
    public function setCssClass($cssClass)
    {
        $this->_cssClass = $cssClass;
        return $this;
    }

    /**
     * @return string
     */
    public function getCaption()
    {
        return $this->_caption;
    }

    /**
     * @param string $caption
     * @return ActionLink
     */
    public function setCaption($caption)
    {
        $this->_caption = $caption;
        return $this;
    }

    /**
     * @return string
     */
    public function getActionUrl()
    {
        return $this->_actionUrl;
    }

    /**
     * @param string $actionUrl
     * @return ActionLink
     */
    public function setActionUrl($actionUrl)
    {
        $this->_actionUrl = $actionUrl;
        return $this;
    }

    /**
     * @return array
     */
    public function getParamValuePairs()
    {
        return $this->_paramValuePairs;
    }

    /**
     * @param array $paramValuePairs
     * @return ActionLink
     */
    public function setParamValuePairs($paramValuePairs)
    {
        $this->_paramValuePairs = $paramValuePairs;
        return $this;
    }

    public function createHTMLLink()
    {
        $html = <<< PHREDOC
<a href="{$this->fullActionUrl()}">{$this->_caption}</a>
PHREDOC;

        return $html;
    }

    public function fullActionUrl()
    {
        $paramString = "";

        foreach ($this->_paramValuePairs as $key => $value)
        {
            $paramString .= "$key=$value&";
        }

        if($paramString != '')
        {
            $paramString = Util::strRemoveLastChars($paramString);

            return $this->_actionUrl . '?' . $paramString;
        }

        return $this->_actionUrl;
    }
}