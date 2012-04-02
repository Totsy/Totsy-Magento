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
class Harapartners_Fulfillmentfactory_Adminhtml_ErrorlogController extends Mage_Adminhtml_Controller_Action {
	
	/**
     * index page of fulfillment error log panel
     */
	public function indexAction() {        
        $this->loadLayout()
			->_setActiveMenu('fulfillmentfactory/errorlog')
			->_addContent($this->getLayout()->createBlock('fulfillmentfactory/adminhtml_errorlog_index'))
			->renderLayout();
    }
    
	/**
     * fulfill failed order again
     */
	public function refulfillAction() {
        $ids = $this->getRequest()->getParam('entity_id');
        $orderArray = array();
        
        foreach($ids as $errorLogId) {
        	$errorlogModel = Mage::getModel('fulfillmentfactory/errorlog')->load($errorLogId);
        	
        	if(!$errorlogModel->getOrderId()) {
        		continue;
        	}
        	
        	$order = Mage::getModel('sales/order')->load($errorlogModel->getOrderId());
        	
        	Mage::helper('fulfillmentfactory')->_pushUniqueOrderIntoArray($orderArray, $order);
        }
        
        Mage::getModel('fulfillmentfactory/service_dotcom')->submitOrdersToFulfill($orderArray, true);
    }
}