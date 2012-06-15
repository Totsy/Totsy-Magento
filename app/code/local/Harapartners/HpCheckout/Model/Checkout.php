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
        $this->_quote = $this->_checkoutSession->getQuote();
        $this->_helper = Mage::helper( 'checkout' );
    }

    public function getCheckout() {
        return $this->_checkoutSession;
    }

    public function getCustomerSession() {
        return $this->_customerSession;
    }

    public function getQuote() {
        return $this->_quote;
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

        //        $quote->save();

        return array( 'status' => 0, 'message' => '' );
    }

    public function saveOrder()
    {
        $this->validate();
        switch ($this->getCheckoutMethod()) {
        case self::METHOD_GUEST:
            $this->_prepareGuestQuote();
            break;
        default:
            $this->_prepareCustomerQuote();
            break;
        }

        $service = Mage::getModel('sales/service_quote', $this->getQuote());
        $service->submitAll();

        $this->_checkoutSession->setLastQuoteId($this->getQuote()->getId())
        ->setLastSuccessQuoteId($this->getQuote()->getId())
        ->clearHelperData();

        $order = $service->getOrder();

        if ($order) {
            Mage::dispatchEvent('hpcheckout_save_order_after',
                array('order'=>$order, 'quote'=>$this->getQuote()));

            $redirectUrl = $this->getQuote()->getPayment()->getOrderPlaceRedirectUrl();

            if (!$redirectUrl && $order->getCanSendNewEmailFlag()) {
                try {    
                
                    foreach($order->getAllItems() as $orderItem){
                        
                        if($orderItem->getProductType() == 'virtual'){

                            $virtualProductCode = $orderItem->getProductOptionByCode('reservation_code');
                            
                            print $virtualProductCode;
                            
                            /*
                            
                            print $virtualProductCode;
                            exit();
                            
                            $templateId = 15;

                            $mailer = Mage::getModel('core/email_template_mailer');
                            $emailInfo = Mage::getModel('core/email_info');
                            $customer = Mage::getModel('customer/customer');

                            $emailInfo->addTo($customer->getCustomerEmail(), $customer->getCustomerName());

                            $mailer->addEmailInfo($emailInfo);
                            //$mailer->setSender(Mage::getStoreConfig(XML_PATH_EMAIL_IDENTITY, $customer['store_id']));
                            $mailer->setStoreId($customer['store_id']);
                            $mailer->setTemplateId($templateId);
                            $mailer->setTemplateParams( array(
                                    'order'        => $order,
                                    'store'        => Mage::app()->getStore(),
                                    'store_view'=>$store = Mage::app()->getStore()->getStoreView(),
                                    'virtual_product_code'=> $virtualProductCode
                                )
                            );
                            $mailer->send();
                            */
                        } 
                    }

                    $order->sendNewOrderEmail();
                    exit();
                    
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

}