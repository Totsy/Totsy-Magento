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

class Mage_Catalog_Model_Category_Attribute_Source_Ages extends Mage_Eav_Model_Entity_Attribute_Source_Abstract {
	
    public function getAllOptions(){
        if (!$this->_options) {
            $this->_options = array(
                array(
                    'value' => 'newborn',
                    'label' => Mage::helper('catalog')->__('Newborn 0-6M')
                ),
                array(
                    'value' => 'infant',
                    'label' => Mage::helper('catalog')->__('Infant 6-24M')
                ),
                array(
                    'value' => 'toddler',
                    'label' => Mage::helper('catalog')->__('Toddler 1-3 Y')
                ),
                array(
                    'value' => 'preschool',
                    'label' => Mage::helper('catalog')->__('Preschool 3-4Y')
                ),
                array(
                    'value' => 'school',
                    'label' => Mage::helper('catalog')->__('School Age 5+')
                ),
                array(
                    'value' => 'adult',
                    'label' => Mage::helper('catalog')->__('Adult')
                ),                                                
            );
        }
        return $this->_options;
    }
    
}