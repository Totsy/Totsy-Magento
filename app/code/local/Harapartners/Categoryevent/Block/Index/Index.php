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
	
	protected $_memcache = null;
	protected $_cdsLifeTime = 14400;
	
	public function getMemcache(){
    	if(!$this->_memcache){
    		$this->_memcache = Mage::getSingleton('memcachedb/resource_memcache');
    	}
    	return $this->_memcache;
    }
    
    public function getCdsDataObject(){
		$cdsData = array();
		$memcacheKey = 'DATA_' . Mage::app()->getStore()->getCode() . '_' . 'catalog_event';

		$cdsData = $this->getMemcache()->read($memcacheKey);
		if(!$this->_validateCdsData($cdsData)){
			$cdsData = $this->_getCdsDataFromDb();
			$this->getMemcache()->write($memcacheKey, $cdsData, $this->_cdsLifeTime);
		}
		
		return new Varien_Object($cdsData);
    }
    
    protected function _validateCdsData($cdsData){
    	if(!isset($cdsData['toplive'])){
			return false;
		}
		if(!isset($cdsData['live'])){
			return false;
		}
		return true;
    }
    
    protected function _getCdsDataFromDb(){
    	$cdsData = array();
		$defaultTimezone = date_default_timezone_get();
		$mageTimezone = Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE);
		date_default_timezone_set($mageTimezone);
		$sortDate = now("Y-m-d");
		date_default_timezone_set($defaultTimezone);
		$storeId = Mage::app()->getStore()->getId();
		$sortentry = Mage::getModel('categoryevent/sortentry')->loadByDate($sortDate, $storeId, false);
		
		$cdsData['toplive'] = json_decode($sortentry->getData('top_live_queue'), true);
		$cdsData['live'] = json_decode($sortentry->getData('live_queue'), true);
		$cdsData['upcoming'] = json_decode($sortentry->getData('upcoming_queue'), true);
		return $cdsData;
    }
    
	
//    public function renderSortCollection($sortDate, $storeId) {
//    	$sortedLive = Mage::getSingleton('customer/session')->getData('categoryevent_sort_live_queue');
//		$sortedUpcoming = Mage::getSingleton('customer/session')->getData('categoryevent_sort_upcoming_queue');
//		try {
//			Mage::getSingleton('customer/session')->setData('categoryevent_sort_update_skipcounter', 0);
//			Mage::getSingleton('customer/session')->setData('categoryevent_sort_update_skipcounter_upcoming', 0);
//			$pageNumber = $this->getPageNumber();				
//			$counter1 = $pageNumber; 
//			$counter2 = $counter1;
//			$liveArray = array();
//			$upcomingArray = array();
//	    	foreach ( $sortedLive as $value ){
//				if( ($counter1--)>0 ){
//					array_push( $liveArray, $value );
//				}else {
//					break;
//				}
//			}
//	    	foreach ( $sortedUpcoming as $value ){
//				if( ($counter2--)>0 ){
//					array_push( $upcomingArray, $value );
//				}else {
//					break;				
//				}
//			}
//			return array('live' => $liveArray, 'upcoming' => $upcomingArray);	
//		}catch (Exception $e){	
//			Mage::logException($e);
//			Mage::getSingleton('core/session')->addError('Cannot Load Events');
//		}
//    }
//
//    public function loadTopCollection($storeId, $sortDate){
//    	return Mage::getSingleton('customer/session')->getData('categoryevent_sort_top_live_queue');
//    }
//    
//    public function setCountDownTimer($counttime){
//    	if (!!$counttime){
//			$endcountRaw = strtotime($counttime);
//			$endcountFormat = date("F j, Y, G:i:s", $endcountRaw);
//			
//			if ( !Mage::getSingleton('customer/session')->hasData('countdown_timer') ) {
//				$timer = 0;
//				Mage::getSingleton('customer/session')->setData('countdown_timer', $timer++);
//				$timer = Mage::getSingleton('customer/session')->getData('countdown_timer');
//			} else {	
//				$timer = Mage::getSingleton('customer/session')->getData('countdown_timer');
//				Mage::getSingleton('customer/session')->setData('countdown_timer', ++$timer);
//			}
//    	}else {
//    		$endcountFormat = 0;
//    		$timer = 'no_time';
//    	}
//
//		return array('endcount' => $endcountFormat, 'timer' => $timer);
//    }
//    
//    public function getPageNumber(){
//    	$configKey = 'pagenumber_config';
//		return Mage::getStoreConfig('config/catalog_page_number/'.$configKey);
//    }
}