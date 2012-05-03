<?php
/**
 * Harapartners
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Harapartners License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.Harapartners.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@Harapartners.com so we can send you a copy immediately.
 *
 * 
 */

class Harapartners_Categoryevent_Block_Index_Index extends Mage_Core_Block_Template {

    public function getIndexDataObject(){
        $helper = Mage::helper('categoryevent/memcache');
        return $helper->getIndexDataObject();
    }
    
    public function hasProduct($categoryId){
        return Mage::getResourceModel('categoryevent/sortentry')->checkEventProduct($categoryId);
    }
}