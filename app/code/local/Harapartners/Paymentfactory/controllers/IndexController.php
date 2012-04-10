<?php 
class Harapartners_Paymentfactory_IndexController extends Mage_Core_Controller_Front_Action {
	
	public function indexAction() {
		$this->loadLayout();
		$this->_initLayoutMessages('customer/session');
		$this->renderLayout();	

	}
	
	public function deleteAction(){
		$id = $this->getRequest ()->getParam ( 'entity_id' );
		try{
			//Mage::getModel ( 'paymentfactory/profile' )->deleteById( $id );			
			$profile = Mage::getModel ( 'paymentfactory/profile' )->load( $id );
			$profile->setData('is_default',1);
			$profile->save();
			
			$customerSession = Mage::getSingleton('customer/session');
			$customerSession->addSuccess('Deleted Credit Card Successfully ');
		}catch(Exception $e){
			Mage::throwException(
                Mage::helper('paymentfactory')->__($e->getMessage()));
		}
		$this->_redirect ( '*/*/' );
	}
	public function createAction(){
		
		$customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
		
		$data = $this->getRequest ()->getParams();
		
		$billing = new Varien_Object($data['billing']);
		$payment = new Varien_Object($data['payment']);
		
		try{
			//Mage::getModel ( 'paymentfactory/profile' )->deleteById( $id );			
			Mage::getModel ( 'paymentfactory/tokenize' )->createProfile($payment,$billing,$customerId);
			$customerSession = Mage::getSingleton('customer/session');
			$customerSession->addSuccess('Save Credit Card Successfully ');
		}catch(Exception $e){
			Mage::throwException(
                Mage::helper('paymentfactory')->__($e->getMessage()));
		}
		
		$this->_redirect ( '*/*/' );
		
	}
	
}