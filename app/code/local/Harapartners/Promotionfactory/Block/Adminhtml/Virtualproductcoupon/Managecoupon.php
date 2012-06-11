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

class Harapartners_PromotionFactory_Block_Adminhtml_Virtualproductcoupon_Managecoupon extends Mage_Adminhtml_Block_Widget_Grid_Container{
    
    public function __construct(){
        parent::__construct();
        $this->_objectId = 'entity_id';
        $this->_blockGroup = 'promotionfactory';
        $this->_controller = 'adminhtml_virtualproductcoupon_managecoupon';
        $this->setTemplate('promotionfactory/virtualproductcoupon/managecoupon.phtml');
    }

    public function getHeaderText() {
    	return Mage::helper('promotionfactory')->__('Manage Coupon');
    }
    
    protected function _prepareLayout() {
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