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
class Harapartners_Service_Block_Rewrite_Adminhtml_Catalog_Product_Edit extends Mage_Adminhtml_Block_Catalog_Product_Edit {
   

    protected function _prepareLayout() {
    	
    	//Only allowed for existing product, not while creating new product!
        if ($this->getProduct()->getId() &&  $this->getProduct()->isVirtual()) {
            $this->setChild('manage_coupon_button',
                $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setData(array(
                        'label'     => Mage::helper('catalog')->__('Manage Coupon'),
                        'onclick'   => 'setLocation(\''.$this->getUrl('promotionfactory/adminhtml_virtualproductcoupon/manageCouponByProduct', array('product_id'=>$this->getProduct()->getId())).'\')',
                    	'class'  	=> 'add'
                    ))
            );
        }
        return parent::_prepareLayout();
    }
    
    public function getManageCouponButtonHtml(){
    	return $this->getChildHtml('manage_coupon_button');
    }

}