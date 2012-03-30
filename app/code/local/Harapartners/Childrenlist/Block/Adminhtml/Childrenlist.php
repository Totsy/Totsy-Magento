<?php

class Harapartners_Childrenlist_Block_Adminhtml_Childrenlist extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct()
	{
	  $this->_controller = 'adminhtml_childrenlist';
	  $this->_blockGroup = 'childrenlist';
	  $this->_headerText = Mage::helper('childrenlist')->__('Children List');
	  $customerId = $this->getRequest()->getParam('id');
	  $param = array('add' => 1, 'customerId' => $customerId);
	  parent::__construct();
	}
	
	public function getCreateUrl(){
		$customerId = $this->getRequest()->getParam('id');
	    $param = array('add' => 1, 'customerId' => $customerId);
		return $this->getUrl('childrenlist/adminhtml_childedit/new',$param);
	}
}
