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
			Mage::getModel ( 'paymentfactory/profile' )->deleteById( $id );
			$customerSession = Mage::getSingleton('customer/session');
			$customerSession->addSuccess('Deleted Credit Card Successfully ');
		}catch(Exception $e){
			Mage::throwException(
                Mage::helper('paymentfactory')->__($e->getMessage()));
		}
		$this->_redirect ( '*/*/' );
	}
	
}