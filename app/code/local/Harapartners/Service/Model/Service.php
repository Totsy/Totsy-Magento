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

class Harapartners_Service_Model_Service extends Mage_Core_Model_Abstract{
	
	// ===== Cronjob related ===== //
	public function cleanCacheAfterSortRebuild($schedule) {
		$types = Mage::app()->getCacheInstance()->getTypes();
		foreach ($types as $type) {
		    $tags = Mage::app()->getCacheInstance()->cleanType($type);
		    Mage::dispatchEvent('adminhtml_cache_refresh_type', array('type' => $type));
		    $updatedTypes++;
		}
        return $this;
    }
 
}