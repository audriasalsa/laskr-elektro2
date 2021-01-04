<?php


namespace lib;


use m\Util;

class DynamicTemplate
{
    private $_templateFile;
    private $_tableArray;
    private $_templateText;
    private $_synchronizedText;

    public function __construct($templateFile, $tableArray = array())
    {
        $this->_templateFile = $templateFile;
        $this->_tableArray = $tableArray;
        $this->_templateText = '';
        $this->_synchronizedText = '';

        $this->_initialize();
    }

    /**
     * @param array $tableArray
     * @return DynamicTemplate
     */
    public function setTableArray($tableArray)
    {
        $this->_tableArray = $tableArray;
        return $this;
    }

    private function _initialize()
    {
        if(is_file($this->_templateFile))
            $this->_templateText = file_get_contents($this->_templateFile);
        else
            throw new \Exception('DynamicTemplate cannot find the specified template file: ' . $this->_templateFile);
    }

    public function synchronize()
    {
        foreach ($this->_tableArray as $row)
        {
            $search = array();
            $replace = array();

            foreach ($row as $column => $value)
            {
                $search[] = "[[$column]]";
                $replace[] = $value;
            }

            $rowResult = str_replace($search, $replace, $this->_templateText);

            $this->_synchronizedText .= "$rowResult\r\n";
        }
    }

    /**
     * @return string
     */
    public function getSynchronizedText()
    {
        if($this->_synchronizedText != '')
            return $this->_synchronizedText;

        return $this->_templateText;
    }
}