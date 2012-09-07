<?php

class Totsy_Customer_ZipCodeInfoController
    extends Mage_Core_Controller_Front_Action
{
    public function lookupAction()
    {
        $fake = array(
            array('New York', 'NY'),
            array('Miami', 'FL'),
            array('Philadelphia', 'PA'),
            array('Crystal Springs', 'MI')
        );

        $this->getResponse()->setHeader('Content-Type', 'application/json')
            ->setBody(json_encode($fake));
    }
}
