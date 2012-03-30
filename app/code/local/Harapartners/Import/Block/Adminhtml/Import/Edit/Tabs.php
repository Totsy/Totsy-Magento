<?php

class Harapartners_Import_Block_Adminhtml_Import_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('import_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('import')->__('Import Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('import')->__('Import File'),
          'title'     => Mage::helper('import')->__('Import File'),
          'content'   => $this->getLayout()->createBlock('import/adminhtml_import_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}