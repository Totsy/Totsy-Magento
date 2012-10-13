<?php
/*
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User Software Agreement (EULA).
 * It is also available through the world-wide-web at this URL:
 * http://www.harapartners.com/license [^]
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to eula@harapartners.com so we can send you a copy immediately.
 *
 */
class Harapartners_HpCheckout_Model_Checkout
{
    const METHOD_GUEST = 'guest';
    const METHOD_CUSTOMER = 'customer';

    protected $_quote;
    protected $_checkoutSession;
    protected $_customerSession;
    protected $_helper;

    public function __construct() {
        $this->_checkoutSession = Mage::getSingleton('checkout/session');
        $this->_customerSession = Mage::getSingleton('customer/session');
        $this->_helper = Mage::helper( 'checkout' );
    }

    public function getCheckout() {
        return $this->_checkoutSession;
    }

    public function getCustomerSession() {
        return $this->_customerSession;
    }

    /**
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote() {
        return Mage::getSingleton('checkout/session')->getQuote();
    }

    public function saveBilling( $data )
    {
        if( empty( $data ) ) {
            return array( 'status' => -1, 'message' => $this->_helper->__('Invalid data.') );
        }

        $address = $this->getQuote()->getBillingAddress();
        $addressForm = Mage::getModel( 'customer/form' );
        $addressForm->setFormCode( 'customer_address_edit' )
        ->setEntityType( 'customer_address' )
        ->setIsAjaxRequest(Mage::app()->getRequest()->isAjax());

        $addressForm->setEntity($address);
        $addressData = $addressForm->extractData( $addressForm->prepareRequest( $data ) );

        $addressErrors = $addressForm->validateData( $addressData );
        if( $addressErrors !== true ) {
            return array( 'status' => 1, 'message' => $addressErrors );
        }
        $addressForm->compactData( $addressData );
        foreach( $addressForm->getAttributes() as $attribute ) {
            if (!isset($data[$attribute->getAttributeCode()])) {
                $address->setData($attribute->getAttributeCode(), NULL);
            }
        }

        $address->setData( 'email', $data[ 'email' ] );

        if (($validateRes = $address->validate()) !== true) {
            return array('status' => 1, 'message' => $validateRes);
        }

        $address->implodeStreetAddress();

        if (true !== ($result = $this->_validateCustomerData($data))) {
            return $result;
        }

        //        $this->getQuote()->collectTotals();
        //        $this->getQuote()->save();
        return array( 'status' => 0, 'message' => '' );
    }

    protected function _validateCustomerData( array $data )
    {
        $customerForm = Mage::getModel('customer/form');
        $customerForm->setFormCode('checkout_register')
        ->setIsAjaxRequest(Mage::app()->getRequest()->isAjax());

        $quote = $this->getQuote();
        if ( $quote->getCustomerId() ) {
            $customer = $quote->getCustomer();
            $customerForm->setEntity($customer);
            $customerData = $quote->getCustomer()->getData();
        } else {
            $customer = Mage::getModel('customer/customer');
            $customerForm->setEntity($customer);
            $customerRequest = $customerForm->prepareRequest( $data );
            $customerData = $customerForm->extractData( $customerRequest );
        }

        $customerErrors = $customerForm->validateData( $customerData );
        if ( $customerErrors !== true ) {
            return array(
                'status'     => -1,
                'message'   => implode(', ', $customerErrors)
            );
        }

        if ( $quote->getCustomerId() ) {
            return true;
        }

        $customerForm->compactData($customerData);

        $password = $customer->generatePassword();
        $customer->setPassword($password);
        $customer->setConfirmation($password);

        $result = $customer->validate();
        if (true !== $result && is_array($result)) {
            return array(
                'status'   => -1,
                'message' => implode(', ', $result)
            );
        }

        $quote->getBillingAddress()->setEmail($customer->getEmail());
        Mage::helper('core')->copyFieldset('customer_account', 'to_quote', $customer, $quote);
        return true;
    }

    public function saveShipping( $data )
    {
        if (empty($data)) {
            return array('status' => -1, 'message' => $this->_helper->__('Invalid data.'));
        }
        $address = $this->getQuote()->getShippingAddress();

        $addressForm    = Mage::getModel('customer/form');
        $addressForm->setFormCode('customer_address_edit')
        ->setEntityType('customer_address')
        ->setIsAjaxRequest(Mage::app()->getRequest()->isAjax());

        $addressForm->setEntity($address);
        $addressData    = $addressForm->extractData($addressForm->prepareRequest($data));
        $addressErrors  = $addressForm->validateData($addressData);
        if ($addressErrors !== true) {
            return array('status' => 1, 'message' => $addressErrors);
        }
        $addressForm->compactData($addressData);
        foreach ($addressForm->getAttributes() as $attribute) {
            if (!isset($data[$attribute->getAttributeCode()])) {
                $address->setData($attribute->getAttributeCode(), NULL);
            }
        }

        $address->setSameAsBilling(0);

        $address->implodeStreetAddress();
        $address->setCollectShippingRates(true);

        if (($validateRes = $address->validate())!==true) {
            return array('status' => 1, 'message' => $validateRes);
        }

        //        $this->getQuote()->collectTotals()->save();

        return array( 'status' => 0, 'message' => '' );
    }

    public function saveShippingMethod( $shippingMethod )
    {
        if (empty($shippingMethod)) {
            return array('status' => -1, 'message' => $this->_helper->__('Invalid shipping method.'));
        }
        $rate = $this->getQuote()->getShippingAddress()->getShippingRateByCode($shippingMethod);
        if (!$rate) {
            return array('status' => -1, 'message' => $this->_helper->__('Invalid shipping method.'));
        }
        $this->getQuote()->getShippingAddress()
        ->setShippingMethod($shippingMethod);
        //        $this->getQuote()->collectTotals()
        //            ->save();

        return array( 'status' => 0, 'message' => '' );
    }

    public function savePayment( $data, $shouldCollectTotal = true, $withValidate = true )
    {
        if (empty($data)) {
            return array('status' => -1, 'message' => $this->_helper->__('Invalid data.'));
        }
        $quote = $this->getQuote();
        if ($quote->isVirtual()) {
            $quote->getBillingAddress()->setPaymentMethod(isset($data['method']) ? $data['method'] : null);
        } else {
            $quote->getShippingAddress()->setPaymentMethod(isset($data['method']) ? $data['method'] : null);
        }

        if (!$quote->isVirtual() && $quote->getShippingAddress()) {
            $quote->getShippingAddress()->setCollectShippingRates(true);
        }


        $payment = $quote->getPayment();
        $payment->importData($data, $shouldCollectTotal, $withValidate);

        return array( 'status' => 0, 'message' => '' );
    }


    /**
     * @return Harapartners_HpCheckout_Model_Checkout
     */
    public function saveOrder()
    {
        Mage::log('here:'.__LINE__);
        $this->validate();

        switch ($this->getCheckoutMethod()) {
        case self::METHOD_GUEST:
            $this->_prepareGuestQuote();
            break;
        default:
            $this->_prepareCustomerQuote();
            break;
        }
        Mage::log('here:'.__LINE__);

        $shippingAddresses = $this->getQuote()->getAllShippingAddresses();
        
    	if ($this->getQuote()->hasVirtualItems()) {
            $shippingAddresses[] = $this->getQuote()->getBillingAddress();
        }
        Mage::log('here:'.__LINE__);

        $fulfillmentTypes = array();

        Mage::log('here:'.__LINE__);
        foreach ( $this->getQuote()->getAllItems() as $item) {
            if($item->getParentItemId()) {
                Mage::log('here:'.__LINE__);

                continue;
            }
            Mage::log('here:'.__LINE__);
            $product = Mage::getModel ( 'catalog/product' )->load ( $item->getProductId () );
            if($product->getIsVirtual()) {
                //continue;
            }
            $fulfillmentTypes [$product->getFulfillmentType ()] [] = $item->getId ();
		}
        Mage::log('here');
        Mage::log(print_r($fulfillmentTypes,true));

		if(count($fulfillmentTypes) > 1) {
            $this->_prepareMultiShip();
            Mage::log('here:'.__LINE__.':'.$this->getQuote()->getShippingAddress()->getId());
			$originalShippingAddress = Mage::getModel('sales/quote_address')
                            ->load($this->getQuote()->getShippingAddress()->getId());
            Mage::log('here:'.__LINE__.':'.$originalShippingAddress->getCustomerAddressId());

            $skipFirst = true;
			foreach($fulfillmentTypes as $_fulfillmentProducts) {
                //skipping the first fulfillment type
	        	if($skipFirst) {
	        		$skipFirst = false;
	        		continue;
	        	}
                $newAddress = clone $originalShippingAddress;
                foreach($newAddress->getItemsCollection() as $item) {
                    Mage::log('here:'.__LINE__);

                }
                $this->getQuote()->addShippingAddress($newAddress);
                $newAddress->save();
                Mage::log('here:'.__LINE__);

                //Loop through the default shipping address to remove the items from that shipping address.
                //We are then going to need to add the items to the new shipping address.
	        	foreach($_fulfillmentProducts as $_productId) {
                    Mage::log('here:'.__LINE__.':'.$_productId);
	        		foreach($originalShippingAddress->getItemsCollection() as $addressItem) {
                        Mage::log('here:'.__LINE__);
                        $quoteItem = $this->getQuote()->getItemById($addressItem->getQuoteItemId());
                        Mage::log('here:'.__LINE__.':'.gettype($quoteItem));
                        Mage::log('here:'.__LINE__.':'.$addressItem->getQuoteItemId());
                        Mage::log('here:'.__LINE__.':'.$quoteItem->getId());
//                        if($quoteItem->getProduct()->getIsVirtual()) {
//                            continue;
//                        }
                        $qty = $addressItem->getQty();
                        Mage::log('here:'.__LINE__.':'.$addressItem->getId());
                        if($quoteItem->getId() == $_productId) {
                            Mage::log('here:'.__LINE__.':'.$addressItem->getId());
                            if($addressItem->getHasChildren()) {
                                Mage::log('here:'.__LINE__);
                                foreach($addressItem->getChildren() as $child) {
                                    Mage::log('here:'.__LINE__);
                                    $originalShippingAddress->removeItem($child->getId());
                                    $child->delete();
                                }
                            }
                            $originalShippingAddress->removeItem($addressItem->getId());
                            $addressItem->delete();
                            Mage::log('here:'.__LINE__.': qty:'.$qty);
                            $newAddress->addItem($quoteItem,$qty);
                        }
	        		}
	        	}
                Mage::log('here:'.__LINE__);
                Mage::log('here:'.__LINE__.':'.$newAddress->getItemsCollection()->getSize());
                foreach($newAddress->getItemsCollection() as $item) {
                    Mage::log('here:'.__LINE__);
                }
                $newAddress->getItemsCollection()->save();
                Mage::log('here:'.__LINE__.':'.$newAddress->getItemsCollection()->getSize());
//                $newAddress->clearAllItems();
                $newAddress->setShippingMethod($originalShippingAddress->getShippingMethod());
                $newAddress->setShippingDescription($originalShippingAddress->getShippingDescription());
                $newAddress->setFreeShipping(true);
                $newAddress->setShippingAmount(0);
                $newAddress->setBaseShippingAmount(0);
                $newAddress->setCollectShippingRates(false);
                $newAddress->getItemsCollection()->save();
                Mage::log('here:'.__LINE__.':'.$newAddress->getItemsCollection()->getSize());
//                $newAddress->collectTotals();
                $newAddress->save();
                Mage::log('here:'.__LINE__);

	        }
            Mage::log('here:'.__LINE__);
//            $originalShippingAddress->save();
//            $originalShippingAddress->setCollectShippingRates(false);
//            $originalShippingAddress->collectTotals();
            $originalShippingAddress->save();

            $originalShippingAddress->clearAllItems();
            $originalShippingAddress->getItemsCollection()->clear();
            Mage::log('here:'.__LINE__.':'.$newAddress->getItemsCollection()->getSize());
            $this->getQuote()->setTotalsCollectedFlag(false);
            $this->getQuote()->collectTotals();
            $this->getQuote()->save();
            $originalShippingAddress->save();
            $this->getQuote()->save();
            $newAddress->save();
            $quote = Mage::getModel('sales/quote')->load($this->getQuote()->getId());
            $this->getCheckout()->replaceQuote($quote);
            $this->getQuote()->getPayment()->importData($this->getCheckout()->getData('payment_data'));
		}

        if(count($fulfillmentTypes) > 1) {
            Mage::log('here:'.__LINE__);
        	try {
                Mage::getSingleton('checkout/session')->setCheckoutState(true);
        		Mage::getModel('checkout/type_multishipping')->createOrders();
        	}
        	catch (Mage_Core_Exception $e) {
        		Mage::log($e->getMessage());
        		Mage::logException($e);
        	}
        }
        else {

            $service = Mage::getModel('sales/service_quote', $this->getQuote());
        	$service->submitAll();

            $this->_checkoutSession->setLastQuoteId($this->getQuote()->getId())
                ->setLastSuccessQuoteId($this->getQuote()->getId())
                ->clearHelperData();

            $order = $service->getOrder();

            if ( $order ) {

                //we need this in order to check if an order has virtual items
                //we want to know that for toggling some FAQ copy about virtual products
                if( $this->getQuote()->hasVirtualItems()==1 ) {
                    $order->hasVirtualItems = $this->getQuote()->hasVirtualItems();
                }

                Mage::dispatchEvent('hpcheckout_save_order_after',
                    array('order'=>$order, 'quote'=>$this->getQuote()));

                $redirectUrl = $this->getQuote()->getPayment()->getOrderPlaceRedirectUrl();

                if (!$redirectUrl && $order->getCanSendNewEmailFlag()) {
                    try {
                        $order->sendNewOrderEmail();
                    } catch (Exception $e) {
                        Mage::logException($e);
                    }
                }

                $this->_checkoutSession->setLastOrderId($order->getId())
                    ->setRedirectUrl($redirectUrl)
                    ->setLastRealOrderId($order->getIncrementId());
            }

            $profiles = $service->getRecurringPaymentProfiles();
            Mage::dispatchEvent(
                'checkout_submit_all_after',
                array('order' => $order, 'quote' => $this->getQuote(), 'recurring_profiles' => $profiles)
            );
        }

        return $this;
    }

    public function validate()
    {
        $helper = Mage::helper('checkout');
        $quote  = $this->getQuote();

        if ($quote->getCheckoutMethod() == self::METHOD_GUEST && !$quote->isAllowedGuestCheckout()) {
            Mage::throwException($this->_helper->__('Sorry, guest checkout is not enabled. Please try again or contact store owner.'));
        }
    }

    protected function _prepareGuestQuote()
    {
        $quote = $this->getQuote();
        $quote->setCustomerId(null)
        ->setCustomerEmail($quote->getBillingAddress()->getEmail())
        ->setCustomerIsGuest(true)
        ->setCustomerGroupId(Mage_Customer_Model_Group::NOT_LOGGED_IN_ID);
        return $this;
    }

    protected function _prepareCustomerQuote()
    {
        $quote      = $this->getQuote();
        $billing    = $quote->getBillingAddress();
        $shipping   = $quote->isVirtual() ? null : $quote->getShippingAddress();

        $customer = $this->getCustomerSession()->getCustomer();
        if (!$billing->getCustomerId() || $billing->getSaveInAddressBook()) {
            $customerBilling = $billing->exportCustomerAddress();
            $customer->addAddress($customerBilling);
            $billing->setCustomerAddress($customerBilling);
        }
        if ($shipping && !$shipping->getSameAsBilling() &&
            (!$shipping->getCustomerId() || $shipping->getSaveInAddressBook())) {
            $customerShipping = $shipping->exportCustomerAddress();
            $customer->addAddress($customerShipping);
            $shipping->setCustomerAddress($customerShipping);
        }

        if (isset($customerBilling) && !$customer->getDefaultBilling()) {
            $customerBilling->setIsDefaultBilling(true);
        }
        if ($shipping && isset($customerShipping) && !$customer->getDefaultShipping()) {
            $customerShipping->setIsDefaultShipping(true);
        } else if (isset($customerBilling) && !$customer->getDefaultShipping()) {
                $customerBilling->setIsDefaultShipping(true);
            }
        $quote->setCustomer($customer);
    }

    public function getCheckoutMethod()
    {
        if ($this->getCustomerSession()->isLoggedIn()) {
            return self::METHOD_CUSTOMER;
        }
        if (!$this->getQuote()->getCheckoutMethod()) {
            $this->getQuote()->setCheckoutMethod(self::METHOD_GUEST);
        }
        return $this->getQuote()->getCheckoutMethod();
    }

    protected function _prepareMultiShip()
    {
        $billing = $this->getQuote()->getBillingAddress();
        $shipping = $this->getQuote()->getShippingAddress();
        foreach($shipping->getItemsCollection() as $item) {
            $shipping->removeItem($item->getId());
        }
        foreach($billing->getItemsCollection() as $item) {
            $billing->removeItem($item->getId());
        }
        foreach($this->getQuote()->getAllShippingAddresses() as $_ship) {
            if($_ship->getId() != $shipping->getId()) {
                $this->getQuote()->removeAddress($_ship->getId());
            }
        }

        foreach ($this->getQuote()->getAllItems() as $item) {
            if ($item->getParentItemId()) {
                continue;
            }
            if($item->getProduct()->getIsVirtual()) {
                $billing->addItem($item);
            } else {
                $shipping->addItem($item);
            }
        }
        $this->getQuote()->setIsMultiShipping(1);

        $shipping->save();
        $billing->save();
        $this->getQuote()->save();
    }

}