<?php 
class Harapartners_Paymentfactory_IndexController extends Mage_Core_Controller_Front_Action {
    
    public function indexAction() {
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->renderLayout();
    }
    
    public function deleteAction(){
        $id = $this->getRequest ()->getParam ( 'entity_id' );
        $customerSession = Mage::getSingleton('customer/session');
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $customerId = $customer->getId(); 
        try{
            $profile = Mage::getModel ( 'paymentfactory/profile' )->load( $id );
            if($profile->getId()) {
                $profile->setData('is_default',1)
                        ->setData('customer_id',$customerId)
                        ->save();
                $customerSession->addSuccess('Deleted Credit Card Successfully ');
            } else {
                $customerSession->addError('Credit Card Profile Not Found');
            }
        }catch(Exception $e){
            $customerSession->addError(Mage::helper('paymentfactory')->__($e->getMessage()));
        }
        $this->_redirect ( '*/*/' );
    }
    public function createAction(){
        $customerSession = Mage::getSingleton('customer/session');
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $customerId = $customer->getId(); 
        $data = $this->getRequest ()->getParams();
        
        $billing = new Varien_Object($data['billing']);
        $payment = new Varien_Object($data['payment']);
        #Create Address that will be link with Payment Profile
        $addressId = $this->createAddressFromForm($customer);
        #Check if there is already a cybersource profile if yes, dont create a new one
        $profile = Mage::getModel('paymentfactory/profile');
        $profile->loadByCcNumberWithId($payment->getData('cc_number').$customerId.$payment->getCcExpYear().$payment->getCcExpMonth());
        if($profile && $profile->getId()) {
            if(!$profile->getData('saved_by_customer')) {
                $profile->setData('saved_by_customer', 1);
                $profile->save();
            }
            $this->_redirect ( '*/*/' );
            return $this;
        }
        try{
            Mage::getModel ( 'paymentfactory/tokenize' )->createProfile($payment,$billing,$customerId,$addressId);
            $customerSession->addSuccess('Save Credit Card Successfully ');
        }catch(Exception $e){
            $customerSession->addError($e->getMessage());
        }
        $this->_redirect ( '*/*/' );
        
    }
    
        /**
     * Retrieve customer session object
     *
     * @return Mage_Customer_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('customer/session');
    }
    
    public function createAddressFromForm($customer) {
        $address = Mage::getModel('customer/address');
        $addressForm = Mage::getModel('customer/form');
        $addressForm->setFormCode('customer_address_edit')
                    ->setEntity($address);
        $addressData = $addressForm->extractData($this->getRequest(), 'billing', false);
        $addressErrors = $addressForm->validateData($addressData);
        if ($addressErrors === true) {
            $addressForm->compactData($addressData);
            $address->setCustomerId($customer->getId())
                ->setIsDefaultBilling(false)
                ->setIsDefaultShipping(false);
            $addressErrors = $address->validate();
            if (is_array($addressErrors)) {
                $errors = array_merge($errors, $addressErrors);
            } else {
                $address->save();
                return $address->getId();
            }
        } else {
            $errors = array_merge($errors, $addressErrors);
        }
    }


}