<?php
class Harapartners_Recurringmembership_Model_Ordercreator extends Mage_Core_Model_Abstract {
    
    protected $_quote = null;

    public function modifyQuoteByProfile($quote, $profile){
        
        //profile object contains the original order id when the profile is created/updated
        $originalOrder = Mage::getModel('sales/order')->load($profile->getOrderId());
        if(!$originalOrder || !$originalOrder->getId()){
            throw new Exception('Invalid order ID.');
        }

        $billingAddress = $quote->getBillingAddress();
        $billingAddress->addData($originalOrder->getBillingAddress()->getData());
        if (($validateRes = $billingAddress->validate())!==true) {
            throw new Exception('Invalide billing address.');
        }
        
        
        $payment = $quote->getPayment();
        $payment->importData($originalOrder->getPayment()->getData(), false);
                
        return $quote;
    }
    
    public function saveOrder($profile){
            if(!$profile || !$profile->getId()){
                throw new Exception('Invalid profile.');
            }
            //new quote
            $quote = Mage::getModel('sales/quote');
            $oldOrder = Mage::getModel('sales/order')->load($profile->getOrderId());
            $productId = $profile->getProductId();
            //Simulating cart
            $product = Mage::getModel('catalog/product')->load($productId);
            
            $buyInfo = array('qty' => '1');
             try{
               $quote->addProduct($product, new Varien_Object($buyInfo)); 
             }catch (Exception $e){
                 $a =1;
             }
            //simulating the login step
            $customer = Mage::getModel('customer/customer')->load($profile->getCustId());
            if(!!$customer->getId()){
                $quote->setCustomer($customer);
            }

            //the other steps are copied from the order
            $quote = $this->modifyQuoteByProfile($quote, $profile);

            $quote->collectTotals()->save();
    
            
            //PLACE ORDER
            $quote->reserveOrderId();
            $billing = $quote->getBillingAddress();
            $shipping = $quote->getShippingAddress();
            $convertQuote = Mage::getModel('sales/convert_quote');
            /* @var $convertQuote Mage_Sales_Model_Convert_Quote */
            //$order = Mage::getModel('sales/order');
            if ($quote->isVirtual()) {
                $order = $convertQuote->addressToOrder($billing);
            }
            else {
                $order = $convertQuote->addressToOrder($shipping);
            }
            /* @var $order Mage_Sales_Model_Order */
            $order->setBillingAddress($convertQuote->addressToOrderAddress($billing));
    
            if (!$quote->isVirtual()) {
                $order->setShippingAddress($convertQuote->addressToOrderAddress($shipping));
            }
            
            $order->setPayment($convertQuote->paymentToOrderPayment($quote->getPayment()));
    
            foreach ($quote->getAllItems() as $item) {
                $orderItem = $convertQuote->itemToOrderItem($item);
                if ($item->getParentItem()) {
                    $orderItem->setParentItem($order->getItemByQuoteItemId($item->getParentItem()->getId()));
                }
                $order->addItem($orderItem);
            }
            
            
            $order->place();
            $order->save();
            return $this;
            
            //TODO: error handling, email, etc should be done in the controller
            //$mailTemplate = Mage::getModel('core/email_template');
            //set template here
            //$mailTemplate->send($email, $name, $var);
    }
    
}