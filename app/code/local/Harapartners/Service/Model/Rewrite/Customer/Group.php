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

class Harapartners_Service_Model_Rewrite_Customer_Group extends Mage_Customer_Model_Group {
    
    protected function _beforeSave(){
        $this->_prepareData();
        if(!!$this->getId()){
        	throw new Mage_Core_Exception('Modifying existing customer group is forbidden.');
        }
        return parent::_beforeSave();
    }
    
}