<?php
class Harapartners_Promotionfactory_Block_Adminhtml_Emailcoupon_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {    
    
    public function __construct(){
        parent::__construct();
        $this->_objectId = 'rule_id';
        $this->_blockGroup = 'promotionfactory';
        $this->_controller = 'adminhtml_emailcoupon';

          $this->setTemplate('promotionfactory/emailcoupon/edit.phtml');
    }

    protected function _prepareLayout()
    {
        $this->setChild('grid', $this->getLayout()->createBlock('promotionfactory/adminhtml_emailcoupon_edit_grid', 'adminhtml.emailcoupon.edit.grid'));
        return parent::_prepareLayout();
    }

    public function getBackButtonHtml() {
        return $this->getChildHtml('back_button');
    }
    
    public function getGridHtml(){
        
        return $this->getChildHtml('grid');
    }
    
}