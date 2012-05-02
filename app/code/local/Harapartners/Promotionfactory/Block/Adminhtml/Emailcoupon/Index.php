<?php
class Harapartners_PromotionFactory_Block_Adminhtml_Emailcoupon_Index extends Mage_Adminhtml_Block_Widget_Grid_Container{
	
    public function __construct(){
		$this->_blockGroup = 'promotionfactory';
		$this->_controller = 'adminhtml_emailcoupon_index';
		$this->_headerText = Mage::helper('promotionfactory')->__('Existing Original Coupons');
		parent::__construct();
		$this->removeButton('add');
    }
 
}