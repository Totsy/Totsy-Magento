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

class Harapartners_ShippingFactory_Model_Shipping_Carrier_Flexible_Source_Unitofmeasure
{
    public function toOptionArray()
    {
        $ups = Mage::helper('shippingfactory');
        //$unitArr = Mage::getSingleton('shippingfactory/shipping_carrier_flexible')->getCode('unit_of_measure');
        $returnArr = array();
        foreach ($ups->getCode('unit_of_measure') as $key => $val){
            $returnArr[] = array('value'=>$key,'label'=>$key);
        }
        return $returnArr;
    }
}
