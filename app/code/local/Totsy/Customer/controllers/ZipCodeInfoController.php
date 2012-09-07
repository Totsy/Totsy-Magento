<?php

class Totsy_Customer_ZipCodeInfoController
    extends Mage_Core_Controller_Front_Action
{
    public function lookupAction()
    {
        $zip = $this->getRequest()->getParam('zip');

        $zipCodeInfo = Mage::getModel('totsycustomer/zipCodeInfo')->getCollection();
        $zipCodeInfo->addFieldToFilter('zip', $zip);

        $results = array();
        foreach ($zipCodeInfo as $zipInfo) {
            $results[] = array($zipInfo['city'], $zipInfo['state']);
        }

        $this->getResponse()->setHeader('Content-Type', 'application/json')
            ->setBody(json_encode($results));
    }
}
