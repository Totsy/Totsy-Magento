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
class Harapartners_Fulfillmentfactory_Block_Adminhtml_Itemqueue_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {    
    
    public function __construct(){
        parent::__construct();

        $this->_objectId = 'itemqueue_id';
        $this->_blockGroup = 'fulfillmentfactory';
        $this->_controller = 'adminhtml_itemqueue';

        $this->removeButton('delete');
        
        $this->_updateButton('save', 'label', Mage::helper('fulfillmentfactory')->__('Save Item Queue'));
        //$this->_updateButton('delete', 'label', Mage::helper('fulfillmentfactory')->__('Delete Item Queue'));
    }

    public function getHeaderText() {
        if( Mage::registry('itemqueue_form_data')) {
            return Mage::helper('fulfillmentfactory')->__('Edit Item Queue');
        } else {
            return Mage::helper('fulfillmentfactory')->__('Add Item Queue');
        }
    }
    
    protected function _prepareLayout() {
        $this->setChild('back_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('fulfillmentfactory')->__('Back'),
                    'onclick'   => "setLocation('".$this->getUrl('*/*/index')."')",
                    'class'   => 'back'
                ))
        );
        
        return parent::_prepareLayout();
    }

    public function getBackButtonHtml() {
        return $this->getChildHtml('back_button');
    }

    public function getSaveUrl(){
        return $this->getUrl('*/*/save', array('_current'=>true));
    }
    
}