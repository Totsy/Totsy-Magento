<?php
class Harapartners_Fulfillmentfactory_Block_Adminhtml_Itemqueue_Index extends Mage_Adminhtml_Block_Widget_Grid_Container{
	
    public function __construct(){
    	parent::__construct();
    	
		$this->_blockGroup = 'fulfillmentfactory';
		$this->_controller = 'adminhtml_itemqueue_index';
    	$this->_headerText = Mage::helper('fulfillmentfactory')->__('Dotcom Order Queue');
   		//$this->_addButtonLabel = Mage::helper('fulfillmentfactory')->__('New Item Queue');
   		$this->_removeButton('add');
   		
   		//TODO remove TEST button before launch
   		$this->_addButton('getInventory', array(
	            'label'     => 'TEST: Get Inventory API and update',
	            'onclick'   => 'setLocation(\'' . $this->getUrl('*/*/getInventory/') . '\')',
	    ));
	    
	    $this->_addButton('fulfillOrder', array(
	            'label'     => 'TEST: Update Order Status',
	            'onclick'   => 'setLocation(\'' . $this->getUrl('*/*/fulfillOrder/') . '\')',
	    ));
	    
	    $this->_addButton('submitOrder', array(
	            'label'     => 'TEST: Submit Orders API and Payment',
	            'onclick'   => 'setLocation(\'' . $this->getUrl('*/*/submitOrder/') . '\')',
	    ));
	    
	    $this->_addButton('shipmentUpdate', array(
	            'label'     => 'TEST: Get Shipment API',
	            'onclick'   => 'setLocation(\'' . $this->getUrl('*/*/shipmentUpdate/') . '\')',
	    ));
	    
	    $this->_addButton('submitProduct', array(
	            'label'     => 'TEST: Submit Products API',
	            'onclick'   => 'setLocation(\'' . $this->getUrl('*/*/submitProduct/') . '\')',
	    ));
	    
	    $this->_addButton('po', array(
	            'label'     => 'TEST: Submit Purchase Order API',
	            'onclick'   => 'setLocation(\'' . $this->getUrl('*/*/submitPO/') . '\')',
	    ));
	    
	    $this->_addButton('dotcom', array(
	            'label'     => 'TEST: DOTCOM',
	            'onclick'   => 'setLocation(\'' . $this->getUrl('*/*/dotcom/') . '\')',
	    ));
    }
 
}