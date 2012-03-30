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

class Harapartners_Affiliate_Model_Mysql4_record extends Mage_Core_Model_Mysql4_Abstract
{
   
    protected function _construct(){
        $this->_init('affiliate/record', 'affiliate_id');
    }

    public function loadByAffiliateCode($affiliateCode){
    	$readAdapter = $this->_getReadAdapter();
    	$select = $readAdapter->select()
            	->from($this->getMainTable())
            	->where('affiliate_code=:affiliate_code');
        $result = $readAdapter->fetchRow($select, array('affiliate_code' => $affiliateCode));
        if (!$result) {
           $result = array(); 
        }
        return $result;
    }
    
}
