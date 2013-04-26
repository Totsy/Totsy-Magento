<?php

class Totsy_Sailthru_Block_Adminhtml_Feedconfig_Index extends Mage_Adminhtml_Block_Widget_Grid_Container{
    
    public function __construct(){
        $this->_blockGroup = 'sailthru';
        $this->_controller = 'adminhtml_feedconfig_index';
        $this->_headerText = Mage::helper('sailthru')->__('Manage Feed Options & Parameters');
        parent::__construct();
    }
}

?>