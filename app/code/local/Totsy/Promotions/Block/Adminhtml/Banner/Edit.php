<?php

class Totsy_Promotions_Block_Adminhtml_Banner_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {    
    
    public function __construct(){
        parent::__construct();
        $this->_removeButton('delete');
        $this->_objectId = 'entity_id';
        $this->_blockGroup = 'promotions';
        $this->_controller = 'adminhtml_banner';
        $this->_headerText = Mage::helper('promotions')->__('Create/Edit  Banner');
    }
   
    protected function _prepareLayout() {
        $this->setChild('back_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('promotions')->__('Back'),
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