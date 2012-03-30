<?php
class Harapartners_PromotionFactory_Block_Adminhtml_Groupcoupon_Index extends Mage_Adminhtml_Block_Widget_Grid_Container{
	
    public function __construct(){
		$this->_blockGroup = 'promotionfactory';
		$this->_controller = 'adminhtml_groupcoupon_index';
    	$this->_headerText = Mage::helper('promotionfactory')->__('Create Group Coupons');
   		$this->_addButtonLabel = Mage::helper('promotionfactory')->__('New Rule');
        parent::__construct();
        $this->removeButton('add');
    }
 
}