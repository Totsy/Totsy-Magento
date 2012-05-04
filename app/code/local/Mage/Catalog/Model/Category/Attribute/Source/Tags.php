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

class Mage_Catalog_Model_Category_Attribute_Source_Tags extends Mage_Eav_Model_Entity_Attribute_Source_Abstract {
    
    public function getAllOptions(){
        if (!$this->_options) {
            $this->_options = array(
                array(
                    'value' => 'holiday',
                    'label' => Mage::helper('catalog')->__('Holiday')
                ),
                array(
                    'value' => 'special',
                    'label' => Mage::helper('catalog')->__('Special')
                ),
                array(
                    'value' => 'toys',
                    'label' => Mage::helper('catalog')->__('Toys')
                )
            );
        }
        return $this->_options;
    }
    
}