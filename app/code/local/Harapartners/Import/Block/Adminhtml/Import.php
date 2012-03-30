<?php
class Harapartners_Import_Block_Adminhtml_Import extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_import';
    $this->_blockGroup = 'import';
    $this->_headerText = Mage::helper('import')->__('Import Manager');
    $this->_addButtonLabel = Mage::helper('import')->__('Add Import');
    parent::__construct();
  }
}