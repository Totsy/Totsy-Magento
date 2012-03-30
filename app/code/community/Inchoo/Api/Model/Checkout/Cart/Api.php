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

class Inchoo_Api_Model_Checkout_Cart_Api extends Mage_Checkout_Model_Cart_Api
{
	public function info($quoteId, $store = null)
	{
		$quote = $this->_getQuote($quoteId, $store);

        if ($quote->getGiftMessageId() > 0) {
            $quote->setGiftMessage(
                Mage::getSingleton('giftmessage/message')->load($quote->getGiftMessageId())->getMessage()
            );
        }
        $user_link = array(
        		'rel'	=>	'self',
				'call'	=>	'customer.info',
				'params'=>	$quote->getCustomerId(),
		);
		
		$user = array(
				'link'	=>	$user_link,
		);
		
		$link = array(
				'rel'	=>	'self',
				'call'	=>	'cart.info',
				'params'=>	$quoteId,
		);
		$items= array();
		foreach($quote->getAllItems() as $item){
			$itemInfo = array(
				'item_id'	=>	$item->getId(),
				'name'		=>	$item->getName(),
				'quantity'	=>	$item->getQty(),
				'link'		=>	array(
									'rel'	=>	'self',
									'call'	=>	'catalog_product.info',
									'params'=>	$item->getProductId(),
								),
			);
			$items[] = $itemInfo;
		}
		
		$result = array(
				'user'	=>	$user,
				'items'	=>	$items,
				'link'	=>  $link,
		);
		return $result;
	}
	
	public function update($quoteId, $itemsInfo, $store = null)
	{	//need QA
		$quote = $this->_getQuote($quoteId, $store);
	
		foreach($itemsInfo as $itemInfo){
			$itemId = $itemInfo['item_id'];
			$qty = $itemInfo['quantity'];
			$cart = $this->_getCart();
			$result = $cart->getQuote()->updateItem($itemId, $qty);
			return $result;
		}
	}
	
	/**
	 *  Create a new quote and add items 
	 *  @param array $productData
	 *  @return bool
	 */
	public function create($customerId, $productData, $store = null)
	{   
		//return $productData;
		$storeId = $this->_getStoreId($store);
		$customer = Mage::getModel('customer/customer')->load($customerId);
		$quote = Mage::getModel('sales/quote');
		$quote->setStoreId($storeId)
					->setCustomer($customer)
					->setIsActive(true)
					->setIsMultiShipping(false)
					->save();
		
		if(empty($productData)){
			$this->_fault('invalid_product_data');
		}
		$errors = array();
		foreach($productData as $productItem){
			
			try{
				$productId = $productItem['product_id'];
				$qty = $productItem['quantity'];	
				$product = Mage::getModel('catalog/product')->load($productId);	
				$result = $quote->addProduct($product, $qty);
				
				if(is_string($result)){
					Mage::throwException($result);
				}
			}catch(Mage_Core_Exception $e){
				$errors[] = $e->getMessage();
				
			}
			if(!empty($errors)){
				$this->_fault("add_product_fault", implode(PHP_EOL, $errors));
			}
		}
		
		try{
			$quote->collectTotals()->save();
			
		}catch(Exception $e){
			$this->_fault("add_product_quote_save_fault", $e->getMessage());
		}
		
		return (int) $quote->getId();
	}
	
	public function delete()
	{
		
		$cart = $this->_getCart();
		$cart->getQuote()->truncate();
		
		return true;
	}
	
	protected function _getCart()
	{
		return Mage::getSingleton('checkout/cart');
	}
	
}