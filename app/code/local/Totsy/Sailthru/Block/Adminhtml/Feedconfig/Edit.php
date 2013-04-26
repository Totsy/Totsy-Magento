<?php

class Totsy_Sailthru_Block_Adminhtml_Feedconfig_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {    
    
    public function __construct(){
        parent::__construct();
        //$this->_removeButton('delete');
        $this->_objectId = 'entity_id';
        $this->_blockGroup = 'sailthru';
        $this->_controller = 'adminhtml_feedconfig';
        $this->_headerText = Mage::helper('sailthru')->__('Create/Edit Feed Config');
    }
   
    protected function _prepareLayout() {
        $this->setChild('back_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('sailthru')->__('Back'),
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