<?php

class Harapartners_Fulfillmentfactory_Block_Adminhtml_Itemqueue_Index
    extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct(){
        parent::__construct();

        $this->_blockGroup = 'fulfillmentfactory';
        $this->_controller = 'adminhtml_itemqueue_index';
        $this->_headerText = Mage::helper('fulfillmentfactory')->__('Item Queue');
        $this->_removeButton('add');
    }
}
