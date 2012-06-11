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

class Harapartners_PromotionFactory_Block_Adminhtml_Groupcoupon_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {    
    
    public function __construct(){
        parent::__construct();

        $this->_objectId = 'rule_id';
        $this->_blockGroup = 'promotionfactory';
        $this->_controller = 'adminhtml_groupcoupon';
        $this->setTemplate('promotionfactory/groupcoupon/edit.phtml');
    }

    public function getHeaderText() {
        if( Mage::registry('emailcoupon_data') && Mage::registry('emailcoupon_data')->getId() ) {
            return Mage::helper('promotionfactory')->__('Create Group Coupons');
        } else {
            return Mage::helper('promotionfactory')->__('Add Rule');
        }
    }
    
    protected function _prepareLayout() {
        $this->setChild('grid', $this->getLayout()->createBlock('promotionfactory/adminhtml_groupcoupon_edit_grid', 'adminhtml.groupcoupon.edit.grid'));
        return parent::_prepareLayout();
    }

    public function getBackButtonHtml() {
        return $this->getChildHtml('back_button');
    }
    
    public function getGridHtml(){
        return $this->getChildHtml('grid');
    }

    public function getSaveUrl(){
        return $this->getUrl('*/*/save', array('_current'=>true));
    }
    
}