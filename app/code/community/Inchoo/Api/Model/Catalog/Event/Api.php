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

class Inchoo_Api_Model_Catalog_Event_Api extends Mage_Catalog_Model_Category_Api
{
	/**
	 *	Overwrite Magento default info function 
	 */
	public function info($eventId, $store = null, $attributes = null)
	{   
		$result = $this->_getEventInfo($eventId);
		return $result;
	}
	
	/**
	 *  Add function to get current/active event
	 */
	public function currentevent($store = null)
	{
        try{
			$collection = Mage::getModel('catalog/category')->getCollection()
	            ->addAttributeToFilter('children_count', array('eq' => 0))
	            ->addFieldToFilter('is_active', '1')
				->addFieldToFilter('event_end_date', array( "gt" => time()));
	         
			$tree = array();
	        foreach($collection->getItems() as $event){
	        	$tree[] = $this->_getEventInfo($event->getId());
	        }
			return $tree;
        }catch(Exception $e){
        	$e->getMessage();
        }
	}
	
	/**
	 *  Get Event by ID
	 *  @param $eventId
	 */
	protected function _getEventInfo($eventId)
	{
		$event = $this->_initCategory($eventId, $store);
		$collection = $event->getProductCollection();
		$links = array();
		$items = array();
		$baseUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB); 
		
		foreach($collection as $product){
			$items[] = array(
					'rel'		=>	'related',
					'call'		=>	$baseUrl . 'product/id/' . $product->getId(),
					//'params'	=>	$product->getId(),
			);
		}
		$link = array(
				'rel'		=>	'self',
				'call'		=>	$baseUrl . 'event/id/' . $eventId,
				//'params'	=>	$eventId,
		);
		
		$result = array(
				'name'		=>	$event->getName(),
				'blurb'		=>	$event->getDescription(),
				'start'		=>	$event->getEventStartDate(),
				'end'		=>	$event->getEventEndDate(),
				'items'		=>	$items,
				'link'		=>	$link,
				'enabled'	=>	$event->getIsActive(),
		);
		
		return $result;
	}
}