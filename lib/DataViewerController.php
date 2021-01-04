<?php


namespace lib;


use m\Application;
use m\extended\FilterForm;
use m\Util;

abstract class DataViewerController extends AppController
{
    public function __construct(Application $application)
    {
        parent::__construct($application);
    }

    public function index()
    {
        $this->accessControl()->inspect();

        $this->setupIndexView();

        $filterValues = $_POST;
        unset($filterValues['submit']); // Submit button is not a filter field.

        $data = $this->getIndexData($filterValues);

        if($data != null) {

            $data = $this->addActionLink($data);

            $headers = AppUtil::toTableDisplayedHeaders($data);

            $filterFields = $this->indexFilterFields();

            if ($filterFields != null) {
                $filterForm = new FilterForm($filterFields);
                $filterForm->configure();
                $filterForm->preFill($filterValues);
                $this->view->appendData(array('filter_form' => $filterForm));
            }

            $this->view->appendData(['headers' => $headers]);
        }

        $this->view->appendData(['displayed_data' => $data]);

        $this->preRenderIndex();

        $this->view->render();
    }

    public function detail()
    {
        $this->accessControl()->inspect();

        $this->setupDetailView();

        $detailParamValue = $_GET[$this->getDetailActionParamName()];

        $data = $this->getDetailData($detailParamValue);

        if($data != null)
        {
            $headers = AppUtil::toTableDisplayedHeaders($data);

            $this->view->appendData(array(
                'headers'        => $headers,
                'displayed_data' => $data
            ));
        }

        $this->view->render();
    }

    protected function setupDetailView()
    {
        $this->view->setContentTemplate('/common/data_table_display_template.php');

        $viewData = $this->getDetailViewData();

        $detailUrl = $this->application()->getRoute()->toURL();

        $indexUrl = Util::strRemoveLastSegment('/', $detailUrl);
        $indexUrl = Util::strRemoveLastChars($indexUrl, 1);

        $viewData->setBackLink($indexUrl);

        $this->view->appendData($viewData->toAssoc());
    }

    protected function setupIndexView()
    {
        $this->view->setContentTemplate('/common/data_table_display_template.php');

        $viewData = $this->getIndexViewData();

        $this->view->appendData($viewData->toAssoc());
    }

    protected function addActionLink(array $tableLikeArray)
    {
        $paramName = $this->getDetailActionParamName();
        $actionLinkCaption = $this->getDetailActionLinkCaption();

        if($paramName == null)
            return $tableLikeArray;

        $copy = array();

        $baseUrl = $this->application()->getRoute()->toURL('/detail');

        foreach ($tableLikeArray as $row)
        {
            $actionLink = new ActionLink();
            $actionLink->setActionUrl($baseUrl);
            $actionLink->setCaption($actionLinkCaption == null ? 'Detail' : $actionLinkCaption);
            $actionLink->setParamValuePairs([
                $paramName => $row[$paramName]
            ]);

            $row['action'] = $actionLink->createHTMLLink();

            $copy[] = $row;
        }

        return $copy;
    }

    protected abstract function getIndexData($filterValues = null);

    protected function getDetailData($detailParamValue)
    {
        return null;
    }

    protected function getDetailActionParamName()
    {
        return null;
    }

    protected function getIndexViewData()
    {
        return new CommonTemplateViewData();
    }

    protected function getDetailViewData()
    {
        return new CommonTemplateViewData();
    }

    protected function indexFilterFields()
    {
        return null;
    }

    protected function preRenderIndex()
    {
    }

    // Caption untuk URL detail pada halaman index yang apabila diklik akan menuju halaman detail.
    protected function getDetailActionLinkCaption()
    {
        return null;
    }
}