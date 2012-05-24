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
        try{
            //Mage::getModel ( 'paymentfactory/profile' )->deleteById( $id );            
            $profile = Mage::getModel ( 'paymentfactory/profile' )->load( $id );
            $profile->setData('is_default',1);
            $profile->save();
            $customerSession->addSuccess('Deleted Credit Card Successfully ');
        }catch(Exception $e){
            $customerSession->addError(Mage::helper('paymentfactory')->__($e->getMessage()));
        }
        $this->_redirect ( '*/*/' );
    }
    public function createAction(){
        $customerSession = Mage::getSingleton('customer/session');
        $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
        
        $data = $this->getRequest ()->getParams();
        
        $billing = new Varien_Object($data['billing']);
        $payment = new Varien_Object($data['payment']);
        
        #Check if there is already a cybersource profile if yes, dont create a new one
        $profile = Mage::getModel('paymentfactory/profile');
        $profile->loadByCcNumberWithId($payment->getData('cc_number').$customerId.$payment->getCcExpYear().$payment->getCcExpMonth());
        if($profile && $profile->getId()) {
            $this->_redirect ( '*/*/' );
            return $this;
        }
        try{
            //Mage::getModel ( 'paymentfactory/profile' )->deleteById( $id );            
            Mage::getModel ( 'paymentfactory/tokenize' )->createProfile($payment,$billing,$customerId);
            $customerSession->addSuccess('Save Credit Card Successfully ');
        }catch(Exception $e){
            $customerSession->addError($e->getMessage());
        }
        
        $this->_redirect ( '*/*/' );
        
    }
    
}