<?php

/*
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User Software Agreement (EULA).
 * It is also available through the world-wide-web at this URL:
 * http://www.harapartners.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to eula@harapartners.com so we can send you a copy immediately.
 * 
 */
 
class Harapartners_Categoryevent_Adminhtml_SortController
    extends Mage_Adminhtml_Controller_Action
{
    /**
     * Default action.
     */
    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Save sort action.
     * Update the live & upcoming event order in the sortentry for a specified
     * date.
     */
    public function sortSaveAction()
    {
        $liveSortedIdArray = (array) $this->getRequest()->getPost('recordsLiveArray');
        $upComingSortedIdArray = (array) $this->getRequest()->getPost('recordsUpArray');

        $sortDate = ($this->getRequest()->getPost('sortdate'))
            ? $this->getRequest()->getPost('sortdate')
            : $this->_getTodayWithTimeOffset();

        $storeId = ($this->getRequest()->getPost('storeid'))
            ? $this->getRequest()->getPost('storeid')
            : Mage_Core_Model_App::ADMIN_STORE_ID;

        try {
            if ($sortentry = Mage::getSingleton('adminhtml/session')->getData("sortentry_$sortDate")) {
                $sortentry->updateSortCollection($liveSortedIdArray, $upComingSortedIdArray)->save();
            } else {
                $sortentry = Mage::getModel('categoryevent/sortentry')
                    ->loadByDate($sortDate, $storeId)
                    ->updateSortCollection($liveSortedIdArray, $upComingSortedIdArray)
                    ->save();
                Mage::getSingleton('adminhtml/session')->setData("sortentry_$sortDate", $sortentry);
            }

            $jsonResponse['status'] = 1;
            $jsonResponse['error_message'] = '';
        } catch (Exception $e) {
            Mage::logException($e);

            $jsonResponse['status'] = 0;
            $jsonResponse['error_message'] = 'A system error prevented the sort order save operation.';
        }

        $this->getResponse()->setHeader('Content-Type', 'application/json')
            ->setBody(json_encode($jsonResponse));
    }

    /**
     * Rebuild sort action.
     * Reset the live & upcoming event order in the sortentry for a specified
     * date, to the default sort order.
     */
    public function sortRebuildAction()
    {
        $sortDate = ($this->getRequest()->getPost('sortdate'))
            ? $this->getRequest()->getPost('sortdate')
            : $this->_getTodayWithTimeOffset();

        $storeId = ($this->getRequest()->getPost('storeid'))
            ? $this->getRequest()->getPost('storeid')
            : Mage_Core_Model_App::ADMIN_STORE_ID;

        try {
            $sortentry = Mage::getModel('categoryevent/sortentry')->loadByDate($sortDate, $storeId);
// @todo: find a way to rebuild only when necessary here
//            if ($sortentry->getId()) {
                $sortentry->rebuild();
//            }
            Mage::getSingleton('adminhtml/session')->setData("sortentry_$sortDate", $sortentry);

            $jsonResponse['status'] = 1;
            $jsonResponse['error_message'] = '';     
        } catch (Exception $e){
            Mage::logException($e);

            $jsonResponse['status'] = 0;
            $jsonResponse['error_message'] = 'A system error prevented the sort order rebuild operation.';
        }

        $this->getResponse()->setHeader('Content-Type', 'application/json')
            ->setBody(json_encode($jsonResponse));
    }

    protected function _getTodayWithTimeOffset()
    {
        $defaultTimezone = date_default_timezone_get();
        $mageTimezone = Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE);
        date_default_timezone_set($mageTimezone);
        $sortDate = now("Y-m-d");
        date_default_timezone_set($defaultTimezone);
        return $sortDate;
    }
}
