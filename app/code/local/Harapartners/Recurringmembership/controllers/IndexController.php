<?php 
class Harapartners_Recurringmembership_IndexController extends Mage_Core_Controller_Front_Action {
    
    public function indexAction() {
        $this->loadLayout();
        $this->renderLayout();    

    }
    
    public function deactiviateAction(){
        $id = Mage::getSingleton('customer/session')->getCustomer()->getId();
        $profile = Mage::getModel('recurringmembership/profile')->loadByCustomerId($id);
        $profile->setStatus(0);
        $profile->save();
        $this->_redirect ( '*/*/' );

    
    }
    
}