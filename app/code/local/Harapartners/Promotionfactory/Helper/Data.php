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

class Harapartners_Promotionfactory_Helper_Data extends Mage_Core_Helper_Abstract {
    
    public function getGridStatusArray(){
        return array(
                Harapartners_Promotionfactory_Model_Virtualproductcoupon::COUPON_STATUS_AVAILABLE => 'Available',
                Harapartners_Promotionfactory_Model_Virtualproductcoupon::COUPON_STATUS_RESERVED => 'Reserved',
                Harapartners_Promotionfactory_Model_Virtualproductcoupon::COUPON_STATUS_USED => 'Used'
        );
    }
    
    public function getFormStatusArray(){
        return array(
                   array('label' => 'Available', 'value' => Harapartners_Promotionfactory_Model_Virtualproductcoupon::COUPON_STATUS_AVAILABLE),
                   array('label' => 'Reserved', 'value' => Harapartners_Promotionfactory_Model_Virtualproductcoupon::COUPON_STATUS_RESERVED),
                   array('label' => 'Used', 'value' => Harapartners_Promotionfactory_Model_Virtualproductcoupon::COUPON_STATUS_USED)
           );
    }
    
}