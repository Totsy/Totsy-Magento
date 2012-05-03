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

class Mage_Catalog_Model_Category_Attribute_Source_Departments extends Mage_Eav_Model_Entity_Attribute_Source_Abstract {
    
    public function getAllOptions(){
        if (!$this->_options) {
            $this->_options = array(
                array(
                    'value' => 'girls',
                    'label' => Mage::helper('catalog')->__('Girls Apparel')
                ),
                array(
                    'value' => 'boys',
                    'label' => Mage::helper('catalog')->__('Boys Apparel')
                ),
                array(
                    'value' => 'shoes',
                    'label' => Mage::helper('catalog')->__('Shoes')
                ),
                array(
                    'value' => 'accessories',
                    'label' => Mage::helper('catalog')->__('Accessories')
                ),
                array(
                    'value' => 'toys_and_books',
                    'label' => Mage::helper('catalog')->__('Toys & Books')
                ),
                array(
                    'value' => 'gear',
                    'label' => Mage::helper('catalog')->__('Gear')
                ), 
                array(
                    'value' => 'home',
                    'label' => Mage::helper('catalog')->__('Home')
                ),
                array(
                    'value' => 'moms_dads',
                    'label' => Mage::helper('catalog')->__('Moms & Dads')
                )                                                                             
            );
        }
        return $this->_options;
    }
    
}