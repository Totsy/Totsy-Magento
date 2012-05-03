<?php
/*
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User Software Agreement (EULA).
 * It is also available through the world-wide-web at this URL:
 * http://www.harapartners.com/license [^]
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to eula@harapartners.com so we can send you a copy immediately.
 *
 */
class Harapartners_Service_Model_Rewrite_Sales_Quote_Address_Total_Grand extends Mage_Sales_Model_Quote_Address_Total_Grand
{
    public function collect(Mage_Sales_Model_Quote_Address $address) {
        $grandTotal     = $address->getGrandTotal();
        parent::collect($address);
        
        if($address->getGrandTotal() == $grandTotal) {
            $address->setGrandTotal($address->getGrandTotal() + $address->getTaxAmount());
            $address->setBaseGrandTotal($address->getBaseGrandTotal() + $address->getBaseTaxAmount());
        }
        
        return $this;
    }
    
}
