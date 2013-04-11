<?php

class Harapartners_SpeedTax_Model_Map extends Mage_Core_Model_Abstract {

	public function generateMappingReport($args){
		extract($args);
		
		$start = date('Y-m-d H:i:s',strtotime($start));
		$end = date('Y-m-d H:i:s',strtotime($end));

		$newEvents = Mage::getModel('catalog/category')->getCollection()
		            ->addAttributeToFilter('parent_id', 8)
		            ->addAttributeToFilter('level', 3)
		            ->addAttributeToFilter('is_active', '1')
		            ->addAttributeToFilter('event_start_date', array('from' => $start, 'date' => true ))
		            ->addAttributeToFilter('event_end_date', array('to' => $end, 'date' => true ))
		            ->addAttributeToSort('event_start_date', 'asc');

		if (!empty($ex_events)){
			$newEvents->addAttributeToFilter('entity_id', array('nin' => $ex_events));
		}

		if (empty($newEvents)) {
			throw new Exception('0 events found for specified date range');
		}

		$items = array();
		foreach($newEvents as $event) {
			$this->_prepareEvent( $event->getId(), $items, $ex_products);
		}

		$out = fopen('php://output','w');
		foreach ($items as $item) {
			fputcsv($out, $item);
		}
		fclose($out);
	}

	protected function _prepareEvent($categoryId, &$items, $ex_products) {
	    $stores = Mage::app()->getStores(false, true);
	    $defaultStore = $stores['default']->getId();

	    // fetch all products part of this category/event
	    $category = Mage::getModel('catalog/category')->load($categoryId);
	    $event    = $category->getData();
	    
	    $products = $category->getProductCollection()
	        ->addAttributeToSelect('tax_class')
	        ->addAttributeToSelect('name');
	    
		if (!empty($ex_products)){
			$newEvents->addAttributeToFilter('entity_id', array('nin' => $ex_products));
		}

	    // skip default tax class
	    $products->addAttributeToFilter('tax_class', array('nin' => '1100500'));

	    if (!$event['is_active'] || empty($products)) {
	        return false;
	    }

	    foreach ($products as $product) {
	    	$items[] = array(
	    		$product->getSku(),
	    		$product->getName(),
	    		$product->getTaxClass()
	    	);
	    }
	}
}

?>