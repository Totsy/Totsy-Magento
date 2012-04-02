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
class Harapartners_Fulfillmentfactory_Adminhtml_ItemqueueController extends Mage_Adminhtml_Controller_Action {
	
	/**
     * index page of fulfillment factory
     */
	public function indexAction() {        
        $this->loadLayout()
			->_setActiveMenu('fulfillmentfactory/itemqueue')
			->_addContent($this->getLayout()->createBlock('fulfillmentfactory/adminhtml_itemqueue_index'))
			->renderLayout();
    }
    
    /**
     * manually add new item queue object, deprecated for now
     */
	public function newAction() {
		//$this->_forward('edit');
    }
    
    /**
     * edit item queue object
     */
    public function editAction() {
    	$id = $this->getRequest()->getParam('id');
		//$data is used to pre-poluate form, by default load from session
    	$data = Mage::getSingleton('adminhtml/session')->getItemqueueFormData();
    	
    	//Do nothing for 'new'. With valid ID, load $data from DB
		if(!!$id){
			$model  = Mage::getModel('fulfillmentfactory/itemqueue')->load($id);
			if(!!$model && !!$model->getId()){
				$data = $model->getData();
			}else{
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('fulfillmentfactory')->__('Invalid ID'));
				$this->_redirect('*/*/');
				return;
			}
		}
		
    	if(!!$data){
			Mage::unregister('itemqueue_form_data');
			Mage::register('itemqueue_form_data', $data);
		}
		
		$this->loadLayout()->_setActiveMenu('fulfillmentfactory/edit');
		$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
		$this->_addContent($this->getLayout()->createBlock('fulfillmentfactory/adminhtml_itemqueue_edit'));
		$this->renderLayout();
    }
    
    /**
     * save item queue object
     */
	public function saveAction() {
		$data = $this->getRequest()->getPost();
		//save data in session in case of failure
		Mage::getSingleton('adminhtml/session')->setItemqueueFormData($data);
		if(!$data){
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('fulfillmentfactory')->__('Nothing to save.'));
        	$this->_redirect('*/*/');
        	return;
		}
		
		try {
			$id = $this->getRequest()->getParam('id');
			$model = Mage::getModel('fulfillmentfactory/itemqueue');
			if(!!$id){
				$model->load($id);
				if(!$model || !$model->getId()){
					throw new Exception('Invalid ID');
				}
			}
			$model->importDataWithValidation($data)->save();
			
			Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('fulfillmentfactory')->__('Save success.'));
			Mage::getSingleton('adminhtml/session')->setAffiliateFormData(null); //clear form data from session
			if ($this->getRequest()->getParam('back')) {
				$this->_redirect('*/*/edit', array('id' => $model->getId()));
			}else{
				$this->_redirect('*/*/');
			}
			return;
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            Mage::getSingleton('adminhtml/session')->setAffiliateFormData($data);
            $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            return;
        }
    }
    
    /**
     * for batch cancel item
     */
    public function batchCancelAction() {
    	$ids = $this->getRequest()->getParam('itemqueue_id');
    	
    	try{
	    	$rsp = Mage::getModel('fulfillmentfactory/service_fulfillment')->batchCancel($ids);
	    	
	    	if($rsp) {
	    		//show success message
				Mage::getSingleton('adminhtml/session')->addSuccess(
						Mage::helper('fulfillmentfactory')->__('Selected items has been cancelled'));
	    	}
	    	else {
	    		//show fail message
				Mage::getSingleton('adminhtml/session')->addError(
						Mage::helper('fulfillmentfactory')->__('Batch Cancellation of items failed'));
	    	}
    	}catch (Exception $e){
    		Mage::getSingleton('adminhtml/session')->addError(
    				Mage::helper('fulfillmentfactory')->__($e->getMessage()));
    	}
    	
    	$this->_redirect('*/*/index');
    }
    
	/** TEST FUNCTION
     * for getting inventory from Dotcom test
     */
    public function getInventoryAction() {
    	$inventoryList = Mage::getModel('fulfillmentfactory/service_dotcom')->updateInventory();
    	Mage::getModel('fulfillmentfactory/service_fulfillment')->stockUpdate($inventoryList);
    	
    	//$this->_redirect('*/*/index');
    } 
    
    /** TEST FUNCTION
     * for stock update test
     */
    public function stockUpdateAction() {
    	$inventoryList = array (
			array (
				'sku' => 'newborntiered-0-3M',	//product sku
				'qty' => 5						//available number of products
			)
		);
    	
		Mage::getModel('fulfillmentfactory/service_fulfillment')->stockUpdate($inventoryList);
    	
    	$this->_redirect('*/*/index');
    }
    
	/** TEST FUNCTION
     * for fulfill orders test
     */
    public function fulfillOrderAction() {
    	Mage::getModel('fulfillmentfactory/service_fulfillment')->updateOrderFulfillStatus();
    	
    	$this->_redirect('*/*/index');
    }
    
	/** TEST FUNCTION
     * for shipment update test
     */
    public function shipmentUpdateAction() {
    	//Mage::getModel('fulfillmentfactory/service_dotcom')->updateShipment();
    	Mage::getModel('fulfillmentfactory/service_dotcom')->updateShipment('2012-03-15 00:00:00', '2012-12-01 00:00:00');
    	
    	//$this->_redirect('*/*/index');
    }
    
	/** TEST FUNCTION
     * for submit orders test
     */
    public function submitOrderAction() {
    	Mage::getModel('fulfillmentfactory/service_dotcom')->testSubmitOrdersToFulfill();
    	
    	//$this->_redirect('*/*/index');
    }
    
	/** TEST FUNCTION
     * for submit products test
     */
    public function submitProductAction() {
    	//Mage::getModel('fulfillmentfactory/service_dotcom')->submitProductsToDotcomByDate();
    	Mage::getModel('fulfillmentfactory/service_dotcom')->testSubmitProductsToDotcom();
    	
    	//$this->_redirect('*/*/index');
    }
    
	/** TEST FUNCTION
     * for submit products test
     */
    public function submitPOAction() {
    	Mage::getModel('fulfillmentfactory/service_dotcom')->runSubmitPurchaseOrders();
    	
    	//$this->_redirect('*/*/index');
    }
    
    /** TEST FUNCTION
     * for dotcom testing
     *
     */
    public function dotcomAction() {
  		$products = Mage::getModel('catalog/product')->getCollection()
  													 ->setPageSize(1);
    	
  		//Mage::getModel('fulfillmentfactory/service_dotcom')->runDotcomFulfillOrder();
  													 
  		//Mage::helper('fulfillmentfactory/dotcom')->submitProductItems($products);
		//Mage::helper('fulfillmentfactory/dotcom')->getShippingMethodListFromDotcom();
    	//Mage::helper('fulfillmentfactory/dotcom')->getInventory();
    	//Mage::helper('fulfillmentfactory/dotcom')->getShipment('2010-01-01 00:00:00', '2013-01-01 00:00:00');
    	//Mage::helper('fulfillmentfactory/dotcom')->getStock('3-12-2010 9:00:00', '3-12-2012 9:00:00');
    	//Mage::helper('fulfillmentfactory/dotcom')->getOrderStatus('3-01-2012 9:00:00', '12-12-2012 9:00:00');
    	//Mage::getModel('fulfillmentfactory/service_dotcom')->testSubmitPurchaseOrdersToDotcom();
    	//Mage::getModel('fulfillmentfactory/service_dotcom')->runSubmitPurchaseOrders();
    	Mage::getModel('fulfillmentfactory/service_dotcom')->runDotcomFulfillOrder();
    	
    	//Mage::helper('fulfillmentfactory/dotcom')->test();	
    }
}