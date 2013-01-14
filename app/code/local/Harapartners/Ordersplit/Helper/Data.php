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
    
    const TYPE_DOTCOM = 'dotcom';
    const TYPE_DOTCOM_STOCK = 'dotcom_stock';
    const TYPE_DROPSHIP = 'dropship';
    const TYPE_VIRTUAL = 'virtual';
    const TYPE_OTHER = 'other';
    
    public function getAllowedFulfillmentTypeArray(){
        return array(
                self::TYPE_DOTCOM, 
                self::TYPE_DROPSHIP, 
                self::TYPE_VIRTUAL,
                self::TYPE_DOTCOM_STOCK
        );
    }
    
    public function orderSplit($oldOrder){
        if(!!Mage::registry('disable_order_split')){
            return true;
        }
        
        if(!($splitInfoArray = $this->_splitQuoteItems($oldOrder))){
            return false;
        }
        /**split order*/    
        return $this->createSplitOrder($oldOrder,$splitInfoArray);                    
    }

    public function processOrder($order) {
        foreach($order->getAllItems() as $item) {
            if($item->getParentItemId()) {
                continue;
            }
            $product = Mage::getModel ( 'catalog/product' )->load ( $item->getProductId () );
            switch($product->getFulfillmentType()) {
                case self::TYPE_DOTCOM:
                    Mage::helper('ordersplit')->processNonHybridOrder($order, self::TYPE_DOTCOM);
                    break;
                case self::TYPE_DOTCOM_STOCK:
                    Mage::helper('ordersplit')->processNonHybridOrder($order, self::TYPE_DOTCOM_STOCK);
                    break;
                case self::TYPE_VIRTUAL:
                    Mage::helper('ordersplit')->processNonHybridOrder($order, self::TYPE_VIRTUAL);
                    break;
                case self::TYPE_DROPSHIP:
                    Mage::helper('ordersplit')->processNonHybridOrder($order, self::TYPE_DROPSHIP);
                    break;
                case self::TYPE_OTHER:
                default:
                    Mage::helper('ordersplit')->processNonHybridOrder($order, self::TYPE_OTHER);
            }
            break;
        }
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
            
            //Fulfillment type is determined by parent item only!
            if(!!$item->getParentItemId()){
                $productId = $item->getParentItem()->getProductId();
            }else{
                $productId = $item->getProductId();
            }
            $product = Mage::getModel('catalog/product')->load($productId);
            
            switch ($product->getFulfillmentType()){
                case self::TYPE_DOTCOM:
                    array_push($dotcomItems, $item);
                    break;
                case self::TYPE_DROPSHIP:
                    array_push($dropshipItems, $item);
                    break;
                case self::TYPE_VIRTUAL:
                    array_push($virtualItems, $item);
                    break;
                default:
                    array_push($otherItems, $item);
                    break;
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
                Mage::helper('ordersplit')->processNonHybridOrder($oldOrder, self::TYPE_DOTCOM);
            }elseif(count($virtualItems)){
                Mage::helper('ordersplit')->processNonHybridOrder($oldOrder, self::TYPE_VIRTUAL);
            }elseif(count($dropshipItems)){
                Mage::helper('ordersplit')->processNonHybridOrder($oldOrder, self::TYPE_DROPSHIP);
            }elseif(count($otherItems)){
                Mage::helper('ordersplit')->processNonHybridOrder($oldOrder, self::TYPE_OTHER);
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

    /**
     * @param $oldOrder
     * @param $itemsArray
     * @param bool $useOrderItems
     * @return bool|null
     * @throws Exception
     */
    public function createSplitOrder($oldOrder, $itemsArray, $useOrderItems = false){
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
            $oldQuote = false;
        }
        
        $newOrderCount = 0;
        $store = Mage::getModel('core/Store')->load($oldOrder->getStoreId());
        $customer = Mage::getModel('customer/customer')
                            ->setStore($store)
                            ->loadByEmail($oldOrder->getCustomerEmail());
                            
        //Harapartners, Jun, Cancel previous orders first, this will re-stock existing products
        //Critical for cart reservation logic!
        $oldOrder
            ->cancel()
            ->setStatus('splitted','splitted',$this->__('Order Canceled by Split Process'),false)
            ->save();
        
        //configurable products and related simple products must be configured the same fullfillment type
        
        //Gift Card, Reward Points and Customer Balance (Store credit)
        // 1)Gift Card logic is not effective in the current logic
        // 2)Reward Points are used during checkout, when order cancelled, it's automatically converted to customer balance
        //    The logic of applying reward points to new quotes is taken cared of during collectTotals
        // 3)Customer balance is not effective in the current logic
        
        foreach($itemsArray as $itemList) {
            if(count($itemList['items'])){
                try{
                    $newQuote = Mage::getModel('sales/quote');
                    $newQuote->setStore($store);
                    $newQuote->assignCustomer($customer);
                    $items = $itemList['items'];
                    $state = $itemList['state'];
                    $type = $itemList['type'];
                    
                    foreach($items as $oldItem) {
                        //Child item should have been cloned with parent item 
                        if($oldItem->getParentItemId()){
                            continue;
                        }
                        if($useOrderItems) {
                            $newItem = $this->_createQuoteItemFromOrderItem($newQuote, $oldItem);
                        } else {
                            $newItem = $this->_cloneQuoteItem($oldItem);                            
                            $newItem->setQuote($newQuote); //Fixed item 'is_nominal' check bug
                            $newQuote->addItem($newItem);
                            foreach($oldItem->getChildren() as $oldChildItem){
                                $newChildItem = $this->_cloneQuoteItem($oldChildItem);
                                $newChildItem->setParentItem($newItem);
                                $newChildItem->setQuote($newQuote);
                                $newQuote->addItem($newChildItem);                                
                            }
                        }
                    }

                    if($oldQuote) {
                    
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
                        ;

                        $oldPayment = $oldQuote->getPayment();


                        if(!$oldPayment->getCybersourceSubid() && !!$oldOrder->getPayment()->getCybersourceSubid())    {
                            $oldPayment->setCybersourceSubid($oldOrder->getPayment()->getCybersourceSubid());
                        }
                        $oldPayment->setPaymentId(null)
                            ->setQuoteId($newQuote->getId())
                            ->setQuote($newQuote);
                        $paymentData = $oldPayment->getData();
                        if($paymentData['method'] == 'free') {
                            $paymentData['use_reward_points'] = 1;
                        }
                        //test payment method "free"
                        $newQuote->getPayment()->importData($paymentData);
                    } else {
                        //2012-07-08 Using logic from Magento Admin Order Edit to handle the copying of the addresses when no quote exists
                        $newQuote->getBillingAddress()->setCustomerAddressId('');
                        Mage::helper('core')->copyFieldset(
                            'sales_copy_order_billing_address',
                            'to_order',
                            $oldOrder->getBillingAddress(),
                            $newQuote->getBillingAddress()
                        );

                        $newQuote->getBillingAddress()->setQuote($newQuote);

                        $newQuote->getShippingAddress()->setCustomerAddressId('');
                        Mage::helper('core')->copyFieldset(
                            'sales_copy_order_shipping_address',
                            'to_order',
                            $oldOrder->getShippingAddress(),
                            $newQuote->getShippingAddress()
                        );

                        if (!$newQuote->isVirtual() && $newQuote->getShippingAddress()->getSameAsBilling()) {
                            $newQuote->setShippingAsBilling(1);
                        }

                        $newQuote->getShippingAddress()->setShippingMethod($oldOrder->getShippingMethod());
                        $newQuote->getShippingAddress()->setShippingDescription($oldOrder->getShippingDescription());

                        $paymentData = $oldPayment->getData();
                        if($paymentData['method'] == 'free') {
                            $paymentData['use_reward_points'] = 1;
                        }
                        $newQuote->getPayment()->importData($paymentData);
                        if(!!$oldOrder->getPayment()->getCybersourceSubid())    {
                            $newQuote->getPayment()->setCybersourceSubid(base64_encode(Mage::getModel('core/encryption')->encrypt($oldOrder->getPayment()->getCybersourceSubid())));
                        }
                    }

                    $newQuote
                        ->getShippingAddress()
                        ->setCollectShippingRates(true)
                        ->collectShippingRates();
                    
                    //Harapartners, Jun, to be deleted
//                    $this->_revertGiftCard($oldOrder, $newQuote); //Gift Card logic is not effective in the current logic
//                    $this->_revertRewardPoints($oldOrder, $newQuote); //After moving reward point related "order_cancel_after" event to global, manual reversal is no longer needed
//                    $this->_revertCustomerBalance($oldOrder, $newQuote, $store); //Customer balance is not effective in the current logic
                    
                    if (!!$oldOrder->getCustomerId() && $oldOrder->getRewardPointsBalance()) {
                    	$newQuote->setUseRewardPoints(1);
                    }
                    
//                    $this->_processPayment($oldQuote, $newQuote, $type); //in case there should be additional logic for payment processing
                    
                    $newQuote->collectTotals();
                    $newQuote->save();

                    //The cloning of order items to quote items seems to neglect copying the product name for the child
                    //items. Let's copy those over if they are blank.
                    if($useOrderItems) {
                        foreach($newQuote->getItemsCollection() as $item) {
                            if($item->getParentItemId()) {
                                continue;
                            }
                            foreach($item->getChildren() as $child) {
                                if(!$child->getName()) {
                                    $child->setName($item->getName());
                                    $child->save();
                                }
                            }
                        }
                    }
                    
                    //set parent order as old order
                    $oldOrderIncrementId = $oldOrder->getOriginalIncrementId();
                    if (!$oldOrderIncrementId) {
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
                        $paymentFailed = false;
                        if($newOrder->getStatus() == 'payment_failed') {
                            $paymentFailed = true;
                        }
                       if(!!$newOrder && !!$newOrder->getId()) {
                           if(!empty($state)) {
                                $newOrder->setState($state, true, $this->__('Order Created by Split/Batch Cancel Process'))->save();
                            } else {
                                $newOrder->addStatusHistoryComment($this->__('Order Created by Split/Batch Cancel Process'))->save();
                            }
                            if($newOrder->getTotalDue() == 0 && $newOrder->isVirtual()) {
                                $newOrder->setData('state', 'complete')
                                    ->setStatus('complete')
                                    ->save();
                            }
                            if($paymentFailed) {
                                 $newOrder->setData('state', 'payment_failed')
                                    ->setStatus('payment_failed')
                                    ->save();
                            }
                        }else{
                            //order failed...
                            $newOrderCount--;
                            $isSuccess = false;
                        }

                        $newQuote->setIsActive(false)->save();

                   }catch(Exception $e){
                       //order failed...
                       Mage::logException($e);
                       $newOrderCount --;
                       $isSuccess = false;
                   }
                       
                }catch (Exception $exception){
                    Mage::logException($exception);
                    //order create exception add to log maybe
                    $isSuccess = false;
                }
            }
        }
        if($isSuccess) {
            if($newOrderCount <= 1){
                //throw new Exception('Split order should produce multiple orders.');
            }
            Mage::dispatchEvent('order_split_after', array('order' => $oldOrder));
        }else{
            //cancel previous orders
            if($newOrderCount > 0){
                throw new Exception('Split order failed for order: '.$oldOrder->getIncrementId().', but some of the splitted order(s) have been created, a manual check is required.');
            }else{
                throw new Exception('Split order failed for order: '.$oldOrder->getIncrementId().', no new order created.');
            }
        }
        return $isSuccess;        
    }
    
    public function processNonHybridOrder($order, $type){
        switch($type){
            case self::TYPE_VIRTUAL;
                    try{
                        $order->save();
                        $order->getPayment()->save();
                        $continue = true;
                        if($order->canInvoice() === false) {
                            $continue = false;
                        }

                        if($continue && (($invoice = $order->prepareInvoice()) == false)) {
                            $continue = false;
                        }

                        if($continue && (($invoice->register()) === false)) {
                            $continue = false;
                        }

                        if($continue && (!$invoice->getBaseGrandTotal())) {
                            $continue = false;
                        }

                        if($continue && $invoice->canCapture()) {
                            $invoice->capture();

                            $order->setStatus('processing');
                            $order->setState('processing');

                            $transactionSave = Mage::getModel('core/resource_transaction');
                            $transactionSave->addObject($invoice);
                            $transactionSave->addObject($invoice->getOrder());
                            $transactionSave->save();

                            $virtualproductcoupon = Mage::getModel('promotionfactory/virtualproductcoupon');
                            $virtualproductcoupon->openVirtualProductCouponInOrder($order);
                            $order->setData('state', 'complete')
                                ->setStatus('complete')
                                ->save();
//                        $invoiceId = Mage::getModel('sales/order_invoice_api')->create($order->getIncrementId(), array());
//                        $invoice = Mage::getModel('sales/order_invoice')->loadByIncrementId($invoiceId);
//                        $invoice->capture()->save();
//                       $order->addStatusToHistory($order->getStatus(), 'Auto Complete Virtual Order', false);
                        }
                    }
                    catch (Exception $exception){
                        Mage::logException($exception);
                        #Payment Failed
                        $virtualproductcoupon = Mage::getModel('promotionfactory/virtualproductcoupon');
                        $virtualproductcoupon->cancelVirtualProductCouponInOrder($order);
                        $order->setData('state', 'payment_failed')
                            ->setStatus('payment_failed')
                            ->save();
                        return null;
                    }
                break;
            case self::TYPE_DROPSHIP;
                break;
            case self::TYPE_DOTCOM:
                Mage::getModel('fulfillmentfactory/service_itemqueue')->saveFromOrder($order);
                break;
            case self::TYPE_DOTCOM_STOCK:
                Mage::getModel('fulfillmentfactory/service_itemqueue')->saveFromOrder($order);
                break;
            case self::TYPE_OTHER;
                break;
            default:
                //Error reporting
                break;
        }
    }
    
    //Harapartners, Jun, to be deleted
//    protected function _revertGiftCard($oldOrder, $newQuote){
//        $cards = Mage::helper('enterprise_giftcardaccount')->getCards($oldOrder);
//        if (is_array($cards) && count($cards)) {
//            foreach ($cards as $card) {
//                if (isset($card['authorized'])) {
//                      $giftCard = Mage::getModel('enterprise_giftcardaccount/giftcardaccount')->load($card['i']);                    
//                    if (!!$giftCard && !!$giftCard->getId() 
//                            && !Mage::registry('isGiftCardReverted')) {
//                        $giftCard->revert($card['authorized'])
//                                 ->setState(0)
//                                 ->setStateText('Available')
//                                    ->unsOrder()
//                                    ->save();
//                        Mage::unregister('isGiftCardReverted');
//                           Mage::register('isGiftCardReverted', true);
//                    }
//                    $newCards[] = array(
//                            'i'=>$giftCard->getId(),        // id
//                            'c'=>$giftCard->getCode(),      // code
//                            'a'=>$giftCard->getBalance(),   // amount
//                            'ba'=>$giftCard->getBalance(),  // base amount
//                    );                        
//                }
//            }
//            //apply on new quote                
//            Mage::helper('enterprise_giftcardaccount')->setCards($newQuote, $newCards);    
//        }
//    }
//    
//    protected function _revertRewardPoints($oldOrder, $newQuote){
//        if (!!$oldOrder->getCustomerId() && $oldOrder->getRewardPointsBalance() && !Mage::registry('isRewardPointsReverted')) {       
//            Mage::getModel('enterprise_reward/reward')
//                ->setCustomerId($oldOrder->getCustomerId())
//                ->setWebsiteId(Mage::app()->getStore($oldOrder->getStoreId())->getWebsiteId())
//                ->setPointsDelta($oldOrder->getRewardPointsBalance())
//                ->setAction(Enterprise_Reward_Model_Reward::REWARD_ACTION_REVERT)
//                ->setActionEntity($oldOrder)
//                ->updateRewardPoints();
//            Mage::unregister('isRewardPointsReverted');
//            Mage::register('isRewardPointsReverted', true);
//        }
//        if(Mage::registry('isRewardPointsReverted')){
//            $reward = Mage::getModel('enterprise_reward/reward')
//                ->setCustomerId($oldOrder->getCustomerId())
//                ->setWebsiteId(Mage::app()->getStore($oldOrder->getStoreId())->getWebsiteId())
//                ->loadByCustomer();
//           if($reward->getPointsBalance()>0){
//                   $newQuote->setUseRewardPoints(1);
//           }else{
//                   $newQuote->setUseRewardPoints(0);
//           }
//        }
//    }
//    
//    protected function _revertCustomerBalance($oldOrder, $newQuote, $store){
//        //revert store gredit. order is important must be after payment imported
//    	
//        if ($oldOrder->getCustomerId() && $oldOrder->getBaseCustomerBalanceAmount() && !Mage::registry('isStoreCreditReverted') ) {                
//              Mage::getModel('enterprise_customerbalance/balance')->setCustomerId($oldOrder->getCustomerId())
//                                                                    ->setWebsiteId(Mage::app()->getStore($oldOrder->getStoreId())->getWebsiteId())
//                                                                ->setAmountDelta($oldOrder->getBaseCustomerBalanceAmount())
//                                                                ->setHistoryAction(Enterprise_CustomerBalance_Model_Balance_History::ACTION_REVERTED)
//                                                                ->setOrder($oldOrder)
//                                                                ->save();    
//            Mage::unregister('isStoreCreditReverted');
//            Mage::register('isStoreCreditReverted', true);                                                                    
//        }
//
//        if(Mage::registry('isStoreCreditReverted')){
//            $balance = Mage::getModel('enterprise_customerbalance/balance')
//                    ->setCustomerId($oldOrder->getCustomerId())
//                       ->setWebsiteId($store->getWebsiteId())
//                        ->loadByCustomer();
//            if ($balance->getAmount()>0) {
//                $newQuote->setCustomerBalanceInstance($balance);
//                $newQuote->setUseCustomerBalance(1);
//            }else {
//                $newQuote->setUseCustomerBalance(0);
//            }                                
//        }
//    }
    
    protected function _cloneQuoteItem(Mage_Sales_Model_Quote_Item $oldItem){
        //Harapartners, Jun, create new item, important for maintaining the balance of cart reservation (i.e. empty origData)
        $newItem = Mage::getModel('sales/quote_item');
        $newItem->setData($oldItem->getData());
        $newItem->setId(null);
        
        //Deep copy of $oldItem object, including all options
        foreach($oldItem->getOptions() as $oldOption){
            $newOption = Mage::getModel('sales/quote_item_option');
            $newOption->setData($oldOption->getData());
            $newOption->setProduct($oldOption->getProduct());
            $newOption->setId(null);
            $newOption->setItemId(null);
            $newItem->addOption($newOption); //$newOption->setItem($newItem);
        }
        
        //Important logic to link the new quote object with the original quote object!
        if(!$oldItem->getOriginalQuoteItemId()){
            $newItem->setOriginalQuoteItemId($oldItem->getItemId());
        }
        
        return $newItem;
    }

    /**
     * @param Mage_Sales_Model_Quote $quote
     * @param Mage_Sales_Model_Order_Item $orderItem
     * @param null $qty
     * @return bool|Mage_Sales_Model_Quote_Item|string
     */
    protected function _createQuoteItemFromOrderItem(Mage_Sales_Model_Quote $quote, Mage_Sales_Model_Order_Item $orderItem, $qty = null)
{
    if (!$orderItem->getId()) {
        return false;
    }

    $product = Mage::getModel('catalog/product')
        ->setStoreId($orderItem->getOrder()->getStoreId())
        ->load($orderItem->getProductId());

    if ($product->getId()) {
        $product->setSkipCheckRequiredOption(true);
        $buyRequest = $orderItem->getBuyRequest();
        if (is_numeric($qty)) {
            $buyRequest->setQty($qty);
        }
        $item = $quote->addProduct($product, $buyRequest);
        if (is_string($item)) {
            return $item;
        }

        if ($additionalOptions = $orderItem->getProductOptionByCode('additional_options')) {
            $item->addOption(new Varien_Object(
                array(
                    'product' => $item->getProduct(),
                    'code' => 'additional_options',
                    'value' => serialize($additionalOptions)
                )
            ));
        }

        Mage::dispatchEvent('sales_convert_order_item_to_quote_item', array(
            'order_item' => $orderItem,
            'quote_item' => $item
        ));
        return $item;
    }

    return false;
}
    
}
