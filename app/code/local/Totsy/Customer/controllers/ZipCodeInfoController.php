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
            $country = Mage::getModel('directory/country')->loadByCode('US');
            $region = Mage::getModel('directory/region')->loadByCode($zipInfo['state'], $country->getId());

            $results[] = array(
                'city' => $zipInfo['city'],
                'state' => $zipInfo['state'],
                'region_id' => $region->getId()
            );
        }

        $this->getResponse()->setHeader('Content-Type', 'application/json')
            ->setBody(json_encode($results));
    }
}
