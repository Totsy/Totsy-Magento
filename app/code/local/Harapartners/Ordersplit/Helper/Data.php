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


class Harapartners_Ordersplit_Helper_Data extends Mage_Core_Helper_Abstract {
	
	const TYPE_VIRTUAL = 'virtual';
	const TYPE_DOTCOM = 'dotcom';
	const TYPE_DROPSHIP = 'dropship';
	const TYPE_OTHER = 'other';
	
	public function orderSplit($oldOrder){		
		if(!($splitInfoArray = $this->_splitQuoteItems($oldOrder))){
			return false;
		}
		/**split order*/	
		return $this->createSplitOrder($oldOrder,$splitInfoArray);					
	}	
	
	protected function _splitQuoteItems($oldOrder){		
		$splitInfoArray = array();
		if(!$oldOrder || !$oldOrder->getId() || !$oldOrder->getQuoteId()){
			return null;
		}
		$oldQuote = Mage::getModel('sales/quote')->setStoreId($oldOrder->getStoreId())->load($oldOrder->getQuoteId());
		if(!$oldQuote || !$oldQuote->getId()){
				return null;
		}
		$virtualItems = array();
		$dropshipItems = array();
		$dotcomItems = array();
		$otherItems = array();	
		foreach ($oldQuote->getAllItems() as $item) {
			$product = Mage::getModel('catalog/product')->load($item->getProductId());
			//$product = Mage::getModel('catalog/product')->loadByAttribute('sku', $item->getSku());
			if($product->getIsVirtual()){
				array_push($virtualItems, $item);
			}elseif ($product->getFulfillmentType() == 'dotcom'){
				array_push($dotcomItems, $item);
			}elseif($product->getFulfillmentType() == 'dropship'){
				array_push($dropshipItems, $item);
			}else{
				array_push($otherItems, $item);	
			}							
		}
		
		if(!!count($dotcomItems) 
				+ !!count($virtualItems) 
				+ !!count($dropshipItems)
				+ !!count($otherItems) > 1){
			$splitInfoArray = array (
					array (
							'items' => $virtualItems,
					  		'state' => Mage_Sales_Model_Order::STATE_NEW,
					  		'type' => self::TYPE_VIRTUAL
					),
					array (
							'items' => $dropshipItems,
					  		'state' => Mage_Sales_Model_Order::STATE_NEW,
					  		'type' => self::TYPE_DROPSHIP
					),
					array (
							'items' => $dotcomItems,
					  		'state' => Mage_Sales_Model_Order::STATE_NEW,
					  		'type' => self::TYPE_DOTCOM
					),				  				
					array (
							'items' => $otherItems,
							'state' => Mage_Sales_Model_Order::STATE_NEW,
							'type' => self::TYPE_OTHER
					)
			);
		}else{
			if(count($dotcomItems)){
				Mage::helper('ordersplit')->processNonHybridOrder($oldOrder, 'dotcom');
			}elseif(count($virtualItems)){
				Mage::helper('ordersplit')->processNonHybridOrder($oldOrder, 'virtual');
			}elseif(count($dropshipItems)){
				Mage::helper('ordersplit')->processNonHybridOrder($oldOrder, 'dropship');
			}elseif(count($otherItems)){
				Mage::helper('ordersplit')->processNonHybridOrder($oldOrder, 'other');
			}
		}
			
		return $splitInfoArray;	
	}
	
	protected function _processPayment($oldQuote, $newQuote, $type){
		switch($type){
			case self::TYPE_VIRTUAL;
				break;
			case self::TYPE_DROPSHIP;
				break;
			case self::TYPE_DOTCOM;
				break;
			case self::TYPE_OTHER;
				break;
			default:
				//Error reporting
				break;
		}
	}	

	public function createSplitOrder($oldOrder, $itemsArray){
		$isSuccess = true;		
		Mage::dispatchEvent('order_split_before', array('order'=>$oldOrder));
		Mage::unregister('isSplitOrder');
		Mage::register('isSplitOrder', true);
		
		//force to load the root order
		$masterOrderId = $oldOrder->getIncrementId();
		$masterOrder = Mage::getModel('sales/order')->loadByIncrementId($masterOrderId);			
		while(!empty($masterOrderId) && (strpos($masterOrderId, '-') > 0)) {
			$masterOrderId = $masterOrder->getOriginalIncrementId();
			if(!empty($masterOrderId)) {
				$masterOrder = Mage::getModel('sales/order')->loadByIncrementId($masterOrderId);
			}
		}
		
		// setStoreId is important for admin generated orders
		$oldQuote = Mage::getModel('sales/quote')->setStoreId($masterOrder->getStoreId())->load($masterOrder->getQuoteId());	
		if(!$oldQuote || !$oldQuote->getId()){
				return null;
		}
		
		$newOrderCount = 0;
		$store = Mage::getModel('core/Store')->load($oldQuote->getStoreId());
		$customer = Mage::getModel('customer/customer')
							->setStore($store)
							->loadByEmail($oldQuote->getCustomerEmail());
		//configurable products and related simple products must be configured the same fullfillment type
		foreach($itemsArray as $itemList) {
			if(count($itemList['items'])){
				try{
					$newQuote = Mage::getModel('sales/quote');
					$newQuote->setStore($store);
					$newQuote->assignCustomer($customer);
					//$newQuote->save();		
					$items = $itemList['items'];
					$state = $itemList['state'];
					$type = $itemList['type'];
					foreach($items as $item) {
						//Important logic to link the new quote object with the original quote object!
						if(!$item->getOriginalQuoteItemId()){
							$item->setOriginalQuoteItemId($item->getItemId());
						}
						
						$item->setItemId(null);
						$item->setQuote($newQuote);
		   				//$item->setQuoteId($newQuote->getId());
						$newQuote->addItem($item);
					}
					
					$billingAddress = $oldQuote->getBillingAddress()
												->setQuote($newQuote)
												//->setQuoteId($newQuote->getId())
												->setAddressId(null)
												->setEntityId(null)
												->getData();
											
					$shippingAddress = $oldQuote->getShippingAddress()
												 ->setQuote($newQuote)
												 //->setQuoteId($newQuote->getId())
												 ->setAddressId(null)
												 ->setEntityId(null)
												 ->setShippingMethod($masterOrder->getShippingMethod())
												 ->getData();
												 
					$newQuote->getBillingAddress()
							->setData($billingAddress);
					$newQuote->getShippingAddress()
							->setData($shippingAddress)
							->setCollectShippingRates(true)
							->collectShippingRates();	
					$newQuote->save();		
					$oldPayment = $oldQuote->getPayment();
					if(!$oldPayment->getCybersourceSubid() && !!$oldOrder->getPayment()->getCybersourceSubid())	{
						$oldPayment->setCybersourceSubid($oldOrder->getPayment()->getCybersourceSubid());
					}				
					$oldPayment->setPaymentId(null)
							->setQuoteId($newQuote->getId())
							->setQuote($newQuote);
					
					//test payment method "free"
					$newQuote->getPayment()->importData($oldPayment->getData(), false);
					$newQuote->save();
					$this->_revertGiftCard($oldOrder, $newQuote);
					$this->_revertCustomerBalance($oldOrder, $newQuote, $store);
					//$this->_processPayment($oldQuote, $newQuote, $type); //in case there should be additional logic for payment processing
					$newQuote->collectTotals();
					
					//set parest order as old order
					$oldOrderIncrementId = $oldOrder->getOriginalIncrementId();
					//check if orginalId exist or if it is root master order
			       	if (!$oldOrderIncrementId || (strpos($oldOrderIncrementId, '-') <= 0)) {
			           	$oldOrderIncrementId = $oldOrder->getIncrementId();
			       	}
			       	
			       	//Try to place the order
			       	try{
			       		$newOrderCount ++;
				       	$orderData = array(
					        'original_increment_id'     => $oldOrderIncrementId,
					        'relation_parent_id'        => $oldOrder->getId(),
					        'relation_parent_real_id'   => $oldOrder->getIncrementId(),
					        'edit_increment'            => $oldOrder->getEditIncrement() + $newOrderCount,
					        'increment_id'              => $oldOrderIncrementId.'-'.($oldOrder->getEditIncrement() + $newOrderCount)
					    );
					    $newQuote->setReservedOrderId($orderData['increment_id']);
					    $service = Mage::getModel('sales/service_quote', $newQuote);
					    $service->setOrderData($orderData);
					    $service->submitAll();			    
					    $newOrder = $service->getOrder();			    
					    
				       	if(!!$newOrder && !!$newOrder->getId()) {
				       		if(!empty($state)) {
								$newOrder->setState($state, true)->save();
							}
						}else{
							//order failed...
							$newOrderCount--;
							$isSuccess = false;
						}
						
						$newQuote->setIsActive(false)->save();
						
			       	}catch(Exception $e){
			       		//order failed...
			       		$newOrderCount --;
			       		$isSuccess = false;
			       	}
			       	
				}catch (Exception $exception){
					//order create exception add to log maybe
					$isSuccess = false;
				}
			}
		}
		if($isSuccess) {
			//cancel previous orders
			if($newOrderCount > 1){
				$oldOrder->setState(Mage_Sales_Model_Order::STATE_CANCELED, true)->save();
				Mage::dispatchEvent('order_split_after', array('order' => $oldOrder));
			}else{
				//This is suppressed for now.
				//throw new Exception('Split order should produce multiple orders.');
			}
		}else{
			//cancel previous orders
			if($newOrderCount > 0){
				throw new Exception('Split order failed, but some of the splitted order(s) have been created, a manual check is required.');
			}else{
				throw new Exception('Split order failed, no new order created.');
			}
		}
		return $isSuccess;		
	}
	
	public function processNonHybridOrder($order, $type){
		switch($type){
			case self::TYPE_VIRTUAL;
				if($order->canInvoice()) {				
					try{
						$action = Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE_CAPTURE;
						$orderPayment = $order->getPayment();
						$orderPayment->getMethodInstance()->setData('forced_payment_action', $action);
						$orderPayment->place();//somehow we must force this one to be capture!
//						$invoiceId = Mage::getModel('sales/order_invoice_api')
//								->create($order->getIncrementId(), array());				
//						$invoice = Mage::getModel('sales/order_invoice')
//								->loadByIncrementId($invoiceId);				
//						$invoice->capture()->save();							
//						$order->setStatus('complete');
//       				$order->addStatusToHistory($order->getStatus(), 'Auto Complete Virtual Order', false);
					}
					catch (Exception $exception){
						// order create exception add to log maybe
						//invoice failed
						return null;
					}
				}			
				break;
			case self::TYPE_DROPSHIP;
				break;
			case self::TYPE_DOTCOM;
				Mage::getModel('fulfillmentfactory/service_itemqueue')->saveFromOrder($order);
				break;
			case self::TYPE_OTHER;
				break;
			default:
				//Error reporting
				break;
		}
	}
	
	protected function _revertGiftCard($oldOrder, $newQuote){
		$cards = Mage::helper('enterprise_giftcardaccount')->getCards($oldOrder);
        if (is_array($cards) && count($cards)) {
            foreach ($cards as $card) {
                if (isset($card['authorized'])) {
                  	$giftCard = Mage::getModel('enterprise_giftcardaccount/giftcardaccount')->load($card['i']);					
			        if (!!$giftCard && !!$giftCard->getId() 
			        		&& !Mage::registry('isGiftCardReverted')) {
			            $giftCard->revert($card['authorized'])
			            		 ->setState(0)
			            		 ->setStateText('Available')
			               		 ->unsOrder()
			               		 ->save();
			            Mage::unregister('isGiftCardReverted');
			       		Mage::register('isGiftCardReverted', true);
			        }
					$newCards[] = array(
			                'i'=>$giftCard->getId(),        // id
			                'c'=>$giftCard->getCode(),      // code
			                'a'=>$giftCard->getBalance(),   // amount
			                'ba'=>$giftCard->getBalance(),  // base amount
			        );						
                }
            }
			//apply on new quote				
			Mage::helper('enterprise_giftcardaccount')->setCards($newQuote, $newCards);	
        }
	}
	
	protected function _revertCustomerBalance($oldOrder, $newQuote, $store){
		//revert store gredit. order is important must be after payment imported
		if ($oldOrder->getCustomerId() && $oldOrder->getBaseCustomerBalanceAmount() && !Mage::registry('isStoreCreditReverted') ) {				
		  	Mage::getModel('enterprise_customerbalance/balance')->setCustomerId($oldOrder->getCustomerId())
								           					 	->setWebsiteId(Mage::app()->getStore($oldOrder->getStoreId())->getWebsiteId())
													            ->setAmountDelta($oldOrder->getBaseCustomerBalanceAmount())
													            ->setHistoryAction(Enterprise_CustomerBalance_Model_Balance_History::ACTION_REVERTED)
													            ->setOrder($oldOrder)
													            ->save();	
			Mage::unregister('isStoreCreditReverted');
			Mage::register('isStoreCreditReverted', true);														            
		}
		if(Mage::registry('isStoreCreditReverted')){
		    $balance = Mage::getModel('enterprise_customerbalance/balance')
		        	->setCustomerId($oldOrder->getCustomerId())
		       		->setWebsiteId($store->getWebsiteId())
		       	 	->loadByCustomer();
			if ($balance->getAmount()>0) {
            	$newQuote->setCustomerBalanceInstance($balance);
            	$newQuote->setUseCustomerBalance(1);
            }else {
            	$newQuote->setUseCustomerBalance(0);
            }		               	 	
		}
	}
	
}
