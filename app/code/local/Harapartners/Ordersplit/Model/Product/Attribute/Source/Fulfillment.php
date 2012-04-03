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

class Harapartners_Ordersplit_Model_Product_Attribute_Source_Fulfillment extends Mage_Eav_Model_Entity_Attribute_Source_Abstract {

	
    public function getAllOptions(){
        if (!$this->_options) {
        	$helper = Mage::helper('ordersplit');
            $this->_options = array(
                array(
                    'value' => Harapartners_Ordersplit_Helper_Data::TYPE_VIRTUAL,
                    'label' => $helper->__('Virtual')
                ),
                array(
                    'value' => Harapartners_Ordersplit_Helper_Data::TYPE_DOTCOM,
                    'label' => $helper->__('DotCom')
                ),
                array(
                    'value' => Harapartners_Ordersplit_Helper_Data::TYPE_DROPSHIP,
                    'label' => $helper->__('Dropship')
                ),
                array(
                    'value' => Harapartners_Ordersplit_Helper_Data::TYPE_OTHER,
                    'label' => $helper->__('Other')
                )          
            );
        }
        return $this->_options;
    }
    
}