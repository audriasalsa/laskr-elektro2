<?php


namespace lib;


use m\Controller;
use m\extended\AuthPolicy;
use m\Settings;
use m\Util;
use m\View;

class AppUtil
{
    public static function toDisplayedData(array $keyValueArray)
    {
        $displayed = array();

        foreach ($keyValueArray as $key => $value)
        {
            $newRow = array();
            $newRow['caption'] = Util::strFormatTableColumnName($key);
            $newRow['content'] = $value;

            $displayed[] = $newRow;
        }

        return $displayed;
    }

    public static function toTableDisplayedHeaders(array $tableLikeArray)
    {
        $headers = array();

        if(count($tableLikeArray) < 1)
            return null;

        foreach ($tableLikeArray[0] as $column => $value)
        {
            $header = Util::strFormatTableColumnName($column);

            $headers[] = $header;
        }

        return $headers;
    }

    public static function tableLikeArrayAddActionLink($tableLikeArray, $actionRootUrl, array $paramSourceColumns = null, $actionCaption = 'Go')
    {
        $updated = array();

        foreach ($tableLikeArray as $row)
        {
            $actionParams = '?';

            foreach ($paramSourceColumns as $column)
            {
                $paramValue = $row[$column];

                $actionParams .= "$column=$paramValue&";
            }

            $actionParams = Util::strRemoveLastChars($actionParams, 1);

            $fullUrl = $actionRootUrl . $actionParams;

            $row['action']  = '<a href="' . $fullUrl . '">' . $actionCaption . '</a>';

            $updated[] = $row;
        }

        return $updated;
    }

    public static function getCurrentUsername(Controller $c)
    {
        $authModel = $c->accessControl()->findPolicy(AuthPolicy::class)->getModel();

        return $authModel->sessionUsername();
    }

    public static function dateIsPassed($date)
    {
        $date = new \DateTime($date);
        $now = new \DateTime();

        $nowTimeStamp  = $now->getTimestamp();
        $dateTimeStamp = $date->getTimestamp();

        if($dateTimeStamp < $nowTimeStamp)
            return true;

        return false;
    }

    public static function dateIsStartedOrPassed($date)
    {
        $date = new \DateTime($date);
        $now = new \DateTime();

        $nowTimeStamp  = $now->getTimestamp();
        $dateTimeStamp = $date->getTimestamp();

        if($nowTimeStamp >= $dateTimeStamp)
            return true;

        return false;
    }

    public static function dateIsNotYetPassed($date, $theEndIsAt24PM = false)
    {
        $date = new \DateTime($date);
        $now = new \DateTime();

        if($theEndIsAt24PM)
            $date->add(new \DateInterval('P1D'));

        $nowTimeStamp  = $now->getTimestamp();
        $dateTimeStamp = $date->getTimestamp();

        if($nowTimeStamp <= $dateTimeStamp)
            return true;

        return false;
    }

    public static function createActionLink($actionUrl, $caption = null)
    {
        if($caption == null)
            $caption = $actionUrl;

        return '<a href="' . $actionUrl . '">' . $caption . '</a>';
    }

    public static function createStaticResourceLink($filename)
    {
        return Settings::getInstance()->rootURL() . "/static/resources/$filename";
    }

    public static function removeQuotes($string)
    {
        $search = ["'", '"'];

        return str_replace($search, '', $string);
    }

    public static function forceRenderErrorMessage(View &$view, $errorMessage, $template = '/common/data_display_template.php')
    {
        $view->modifyData('error_message', $errorMessage);
        $view->setContentTemplate($template);
        $view->render();

        exit(0);
    }
}