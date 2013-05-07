<?php

class Totsy_Promotions_Block_Adminhtml_Banner_Index extends Mage_Adminhtml_Block_Widget_Grid_Container{
    
    public function __construct(){
        $this->_blockGroup = 'promotions';
        $this->_controller = 'adminhtml_banner_index';
        $this->_headerText = Mage::helper('promotions')->__('Manage Banners ');
        parent::__construct();
    }
 
}