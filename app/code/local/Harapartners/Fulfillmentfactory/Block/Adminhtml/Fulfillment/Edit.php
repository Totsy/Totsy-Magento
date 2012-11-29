<?php
class Harapartners_Fulfillmentfactory_Block_Adminhtml_Fulfillment_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

    public function __construct(){
        parent::__construct();

        $this->_objectId = 'fulfillment_id';
        $this->_blockGroup = 'fulfillmentfactory';
        $this->_controller = 'adminhtml_fulfillment';

        $this->removeButton('delete');
        $this->removeButton('reset');
        $this->removeButton('back');

        $this->_updateButton('save', 'label', Mage::helper('fulfillmentfactory')->__('Update Category Products'));
    }

    public function getHeaderText() {
        return Mage::helper('fulfillmentfactory')->__('Edit Product Fulfillment Type');
    }

    public function getSaveUrl(){
        return $this->getUrl('*/*/save', array('_current'=>true));
    }

}