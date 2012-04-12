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

class Harapartners_Service_Model_Cart extends Mage_Checkout_Model_Cart{
	
	public function save(){
		parent::save();
        $quote = $this->getQuote();
        $itemArray = array();
	    $endDate = 0;
    	$items = $quote->getAllItems();
    	
    	if( count($items) ) {
    		foreach ( $items as $item){
    			$categoryIdsArray = $item->getProduct()->getCategoryIds();
    			foreach ( $categoryIdsArray as $id ){
    				$category = Mage::getModel('catalog/category')->load($id);
    				if (!!$category) {
	    				$categoryEndDate = strtotime($category->getData('event_end_date'));
	    				$endDate = ( $categoryEndDate > $endDate ) ? $categoryEndDate : $endDate;    					
    				}
    			}
    		}
    	}
        
        foreach($items as $item){
        	if(!$item->getId() || !!$item->getParentItemId()){
        		continue;
        	}
        	$attributeOption = $item->getOptionByCode('attributes');
        	$attributeInfo = array();
        	if(!!$attributeOption){
        		$attributeOptionValueArray = unserialize($item->getOptionByCode('attributes')->getValue());
        		foreach($attributeOptionValueArray as $attributeId => $attributeValue){
        			$attribute = Mage::getModel('catalog/resource_eav_attribute')->load($attributeId);
        			if(!!$attribute && !!$attribute->getId()){
        				$attributeInfo[$attribute->getData('frontend_label')] = $attribute->getSource()->getOptionText($attributeValue);
        			}
        		}
        	}
        	
        	$itemArray[$item->getItemId()] = array(
        		'item_id'				=>	$item->getItemId(),
				'item_name'				=>	$item->getName(),
        		'item_price'    		=>	$item->getBasePrice(),
        		'item_qty'				=>	$item->getQty(),
        		'item_type'  			=>	$item->getProductType(),
        		'item_thumbnail'		=>	Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'catalog/product' . $item->getProduct()->getThumbnail(), //Mage::app()->getHelper('catalog/image')->init($item->getProduct(), 'image')->resize(75), //
        		'item_url'				=>  Mage::getUrl($item->getProduct()->getUrlPath()),
        		'item_attribute_info'	=>	$attributeInfo
        	);
        }
        $quoteInfo = array(
        	'total_qty' 		=>	$quote->getData('items_qty'),
        	'subtotal'			=>	$quote->getData('subtotal'),
        	'original_qty' 		=>	Mage::getSingleton('checkout/session')->getData('top_cart_qty_temp'),
        	'header_count_timer'=>	Mage::getSingleton('checkout/session')->getCountDownTimer(),
        	'header_time_out'	=>	Mage::getModel('rushcheckout/session')->getQuoteItemExpireTime(),
        	'shipping_date'		=>	date('m-d-Y', $endDate + 15*24*3600 ),
        	'items'				=>	$itemArray,
        	'checkout_link'		=>	Mage::getUrl('checkout/onepage'),
			'cart_link'			=>  Mage::getUrl('checkout/cart')
        );
        $session = $this->getCheckoutSession()->setData('cart_info', $quoteInfo);
        return $this;
	}
}