<?php
class Harapartners_HpCheckout_CheckoutController extends Mage_Checkout_Controller_Action {
    
    protected $_stepControl = array( 
                                        array( 'billing' => false ), 
                                         array( 'shipping' => false ), 
                                         array( 'shipping_method' => false ), 
                                         array( 'payment' => false ), 
                                        array( 'review' => false ) 
                                    );
    
    protected $_order;
    
//    public function preDispatch(){
//        $this->getRequest()->setRoutingInfo(array(
//                'requested_route' => 'hpcheckout', 
//                'requested_controller' => 'checkout'
//        ));
//        parent::preDispatch();
//        return $this;
//    }
                                    
    public function indexAction() {
        $this->validateCart();
        $this->loadLayout();
        $this->_initLayoutMessages( 'checkout/session' );
        $this->renderLayout();                                
    }
    
    public function updateAction() {
        $jsonArray = array();
        $postData = $this->getRequest()->getPost();
        $jsonArray = $this->_getBlocksArray( $postData );
        $this->getResponse()->setBody( Mage::helper( 'core' )->jsonEncode( $jsonArray ) );
    }
    
    public function submitAction() {
        $this->validateCart();
        $result = array();
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $customerId = $customer->getId(); 
        try {
            $blocksSuccessFlag = true;
            $postData = $this->getRequest()->getPost();
            $jsonArray = $this->_getBlocksArray( $postData, true );
            $result[ 'blocks' ] = $jsonArray;
            foreach( $jsonArray as $block ) {
                if( $block[ 'status' ] ) {
                    $blocksSuccessFlag = false;
                    break;
                }
            }
            if( ! $blocksSuccessFlag ) {
                $result[ 'status' ] = 2;
            } else {
                if ($data = $this->getRequest()->getPost('payment', false)) {
                    $profile = Mage::getModel('paymentfactory/profile');
                    $profile->loadByCcNumberWithId($data['cc_number'].$customerId.$data[ 'cc_exp_year' ].$data[ 'cc_exp_month' ]);
                    if(!!$profile && !!$profile->getId()){
                        $cybersourceIdEncrypted = $profile->getEncryptedSubscriptionId();
                        if($cybersourceIdEncrypted) {
                            $data['cybersource_subid'] = $cybersourceIdEncrypted;
                        }
                    }
                    $this->_getHpCheckout()->getQuote()->getPayment()->importData($data);
                }
                $this->_getHpCheckout()->saveOrder();
    
                $storeId = Mage::app()->getStore()->getId();
                $paymentHelper = Mage::helper("payment");
                $zeroSubTotalPaymentAction = $paymentHelper->getZeroSubTotalPaymentAutomaticInvoice($storeId);
                if ($paymentHelper->isZeroSubTotal($storeId)
                        && $this->_getOrder()->getGrandTotal() == 0
                        && $zeroSubTotalPaymentAction == Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE_CAPTURE
                        && $paymentHelper->getZeroSubTotalOrderStatus($storeId) == 'pending') {
                    $invoice = $this->_initInvoice();
                    $invoice->getOrder()->setIsInProcess(true);
                    $invoice->save();
                }
            
//                $redirectUrl = $this->_getHpCheckout()->getCheckout()->getRedirectUrl();
                $result['status'] = 0;
            }
        } catch (Mage_Payment_Model_Info_Exception $e) {
            $message = $e->getMessage();
            $result['status'] = 1;
            if( !empty($message) ) {
                $result['message'] = $message;
            }
        } catch (Mage_Core_Exception $e) {
            $result['status'] = 1;
            $result['message'] = $e->getMessage();
        } catch (Exception $e) {
            $result['status'] = 1;
            $result['message'] = $this->__('There was an error processing your order. Please contact us or try again later.');
        }
        $this->_getHpCheckout()->getQuote()->save();
//        if (isset($redirectUrl)) {
//            $result['redirect'] = $redirectUrl;
//        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
    
    public function successAction() {
        $this->loadLayout();
        $this->_initLayoutMessages( 'checkout/session' );
        $this->renderLayout();
    }
    
    public function validateCart() {
        $quote = $this->_getHpCheckout()->getQuote();
        if (!$quote->hasItems() || $quote->getHasError()) {
            $this->_redirect('checkout/cart');
            return;
        }
        if (!$quote->validateMinimumAmount()) {
            $error = Mage::getStoreConfig('sales/minimum_order/error_message');
            Mage::getSingleton('checkout/session')->addError($error);
            $this->_redirect('checkout/cart');
            return;
        }
        Mage::getSingleton('checkout/session')->setCartWasUpdated(false);
        Mage::getSingleton('customer/session')->setBeforeAuthUrl(Mage::getUrl('*/*/*', array('_secure'=>true)));    
    }
    
    public function collectTotals() {
        $this->_getHpCheckout()->getQuote()->collectTotals()->save();
    }
    
    protected function _getBlocksArray( $postData, $isSubmit = false ) {
        $json = array();
        $currentStep = /*isset( $postData[ 'currentStep' ] ) ? $postData[ 'currentStep' ] : */'billing';
        
        $this->_updateBlocks( $postData );
        foreach( $this->_stepControl as $step ) {
            foreach( $step as $blockCode => $flag ) {
                if( $flag ) {
                    switch( $blockCode ) {
                        case 'billing':
                            $json[ 'billing' ] = $this->_updateBilling( isset( $postData[ 'billing' ] ) ? $postData[ 'billing' ] : array(), isset( $postData[ 'billing_address_id' ] ) ? $postData[ 'billing_address_id' ] : false );
                            break;
                        case 'shipping':
                            if( ! $this->_getHpCheckout()->getQuote()->isVirtual() ) {
                                $json[ 'shipping' ] = $this->_updateShipping( isset( $postData[ 'shipping' ] ) ? $postData[ 'shipping' ] : array() );
                            }
                            break;
                        case 'shipping_method':
                            if( ! $this->_getHpCheckout()->getQuote()->isVirtual() ) {
                                $json[ 'shipping_method' ] = $this->_updateShippingMethod( isset( $postData[ 'shipping_method' ] ) ? $postData[ 'shipping_method' ] : '' );
                            }
                            break;
                        case 'payment':
                            $json[ 'payment' ] = $this->_updatePayment( isset( $postData[ 'payment' ] ) ? $postData[ 'payment' ] : array(), true, $isSubmit );
                            break;
                        case 'review':
                            $json[ 'review' ] = $this->_updateReview();
                            break;
                    }
                }
            }
        }
        $this->collectTotals();
        $this->loadLayout( array( 'default', 'hpcheckout_checkout_index' ) );
        //$this->_initLayoutMessages( 'checkout/session' );
        $json[ 'billing' ][ 'html' ] = $this->_renderBilling();
        $json[ 'shipping' ][ 'html' ] = $this->_renderShipping();
        //$json[ 'shipping_method' ][ 'html' ] = $this->_renderShippingMethod();
        $json[ 'payment' ][ 'html' ] = $this->_renderPayment();
        $json[ 'review' ][ 'html' ] = $this->_renderReview();
        return $json;
    }
    
    protected function _getOrder()
    {
        if (is_null($this->_order)) {
            $this->_order = Mage::getModel('sales/order')->load($this->_getHpCheckout()->getQuote()->getId(), 'quote_id');
            if (!$this->_order->getId()) {
                throw new Mage_Payment_Model_Info_Exception(Mage::helper('core')->__("Can not create invoice. Order was not found."));
            }
        }
        return $this->_order;
    }
    
    protected function _updateBlocks( $postData ) {
        $currentStep = /*isset( $postData[ 'currentStep' ] ) ? $postData[ 'currentStep' ] : */'billing';
        $updatePayment = isset( $postData[ 'updatePayment' ] ) ? $postData[ 'updatePayment' ] : false;
        $updateFlag = false;
        foreach( $this->_stepControl as &$step ) {
            foreach( $step as $blockCode => &$flag ) {
                if( $blockCode == $currentStep ) {
                    $updateFlag = true;
                }
                $flag = $updateFlag ? true : false;
            }
        }
        $this->_stepControl[ 3 ][ 'payment' ] = $updatePayment ? true : false;
    }
    
    protected function _updateBilling( $billingData = array() )
    {
        $result = array();
        if ( isset( $billingData[ 'email' ] ) ) {
            $billingData[ 'email' ] = trim( $billingData[ 'email' ] );
        }
        if( ! empty( $billingData ) ) {
            $result = $this->_getHpCheckout()->saveBilling( $billingData );
        }
        return $result;
    }
    
    protected function _updateShipping( $shippingData = array() ) 
    {
        $result = array();
        if( ! empty( $shippingData ) ) {
            $result = $this->_getHpCheckout()->saveShipping( $shippingData );
        }
        return $result;
    }
    
    protected function _updateShippingMethod( $shippingMethodData = '' )
    {
        $result = array();
        $trueShippingMethodData = $this->_validateShippingMethod($shippingMethodData);
        if( $trueShippingMethodData ) {
            $result = $this->_getHpCheckout()->saveShippingMethod($trueShippingMethodData);
        }
        
        return $result;
    }
    
    protected function _validateShippingMethod($shippingMethodData){
        $allowedMethods = array();
        $shippingAddress = $this->_getHpCheckout()->getQuote()->getShippingAddress();
        if(!!$shippingAddress){
            $shippingRateGroups = $shippingAddress->collectShippingRates()->getGroupedAllShippingRates();
            foreach( $shippingRateGroups as $code => $rates ) {
                foreach( $rates as $rate ) {
                    if( ! $rate->getErrorMessage() ) {
                        $allowedMethods[] = $rate->getCode();
                    }
                }
            }
            if(in_array($shippingMethodData, $allowedMethods)){
                return $shippingMethodData;
            }elseif(count($allowedMethods) == 1){
                return $allowedMethods[0];
            }
        }
        
        return '';
    }
    
    protected function _updatePayment( $paymentData, $shouldCollectTotal = true, $withValidate = true ) {
        try {
            $result = array();
            if ( ! empty( $paymentData ) ) {
                $result = $this->_getHpCheckout()->savePayment( $paymentData, $shouldCollectTotal, $withValidate );
            }
        } catch (Mage_Core_Exception $e) {
            $result[ 'status' ] = 1;
            $result['message'] = $e->getMessage();
        }
        return $result;
    }
    
    protected function _updateReview() {
        return array( 'status' => 0, 'message' => '' );
    }
    
    protected function _renderBilling() {
        return $this->getLayout()->getBlock( 'hpcheckout.billing' )->toHtml();
    }
    
    protected function _renderShipping() {
        return $this->getLayout()->getBlock( 'hpcheckout.shipping' )->toHtml();
    }
    
    protected function _renderShippingMethod() {
        return $this->getLayout()->getBlock( 'hpcheckout.shipping_method' )->toHtml();
    }
    
    protected function _renderPayment() {
        return $this->getLayout()->getBlock( 'hpcheckout.payment' )->toHtml();
    }
    
    protected function _renderReview() {
        return $this->getLayout()->getBlock( 'hpcheckout.review' )->toHtml();
    }
    
    protected function _getHpCheckout() {
        return Mage::getSingleton( 'hpcheckout/checkout' );
    }
    
    protected function _getSession() {
        return Mage::getSingleton('customer/session');
    }
    
    /**
     * Action predispatch
     *
     * Check customer authentication for some actions
     */
//    public function preDispatch() {
//        // a brute-force protection here would be nice
//
//        parent::preDispatch();
//
//        if (!$this->getRequest()->isDispatched()) {
//            return;
//        }
//
//        $action = $this->getRequest()->getActionName();
//        $openActions = array(
//            'validationcheck',
//            'validationCheckPostAction'
//        );
//        $pattern = '/^(' . implode('|', $openActions) . ')/i';
//        
//        $session = $this->_getSession();
//        $session->setBeforeCartAuthUrl(Mage::helper('core/url')->getCurrentUrl());
//        
//        if (!preg_match($pattern, $action)) {
//            if (!$session->authenticate($this, null, true)) {
//                $this->setFlag('', 'no-dispatch', true);
//            }
//        } else {
//            $this->_getSession()->setNoReferer(true);
//        }
//    }
//    
//    public function postDispatch() {
//        parent::postDispatch();
//        $session = $this->_getSession();
//        //HP Yang
//        $session->setBeforeCartAuthUrl('');
//        //End
//        $session->unsNoReferer(false);
//    }
}