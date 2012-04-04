<?php
class Harapartners_Fulfillmentfactory_Block_Adminhtml_Errorlog_Index extends Mage_Adminhtml_Block_Widget_Grid_Container{
	
    public function __construct(){
    	parent::__construct();
    	
		$this->_blockGroup = 'fulfillmentfactory';
		$this->_controller = 'adminhtml_errorlog_index';
    	$this->_headerText = Mage::helper('fulfillmentfactory')->__('Fulfillment Error Log');
   		//$this->_addButtonLabel = Mage::helper('fulfillmentfactory')->__('New Item Queue');
   		$this->_removeButton('add');
    }
 
}