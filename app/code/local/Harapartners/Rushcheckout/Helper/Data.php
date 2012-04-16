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
 */

class Harapartners_Rushcheckout_Helper_Data extends Mage_Core_Helper_Abstract {
	
	public function cleanExpiredQuotes($schedule) {
        $lifetimes = Mage::getConfig()->getStoresConfigByPath('config/rushcheckout_timer/limit_timer');
        foreach ($lifetimes as $storeId => $lifetime) {
            $quotes = Mage::getModel('sales/quote')->getCollection();
            $quotes->addFieldToFilter('store_id', $storeId);
            $quotes->addFieldToFilter('updated_at', array('to' => date("Y-m-d", time() - $lifetime)));
            $quotes->addFieldToFilter('is_active', 0);
            $quotes->walk('delete');
        }
        return $this;
    }
    
}