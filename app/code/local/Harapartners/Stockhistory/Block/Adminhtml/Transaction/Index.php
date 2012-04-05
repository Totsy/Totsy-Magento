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

class Harapartners_Stockhistory_Block_Adminhtml_Transaction_Index extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct()
	{	
		parent::__construct();
		$this->_controller = 'adminhtml_transaction_index';
		$this->_blockGroup = 'stockhistory';
		$this->_headerText = Mage::helper('stockhistory')->__('Transcations');
		
		$this->_removeButton('add');

     	$this->_addButton('import', array(
            'label'     => Mage::helper('stockhistory')->__('Import Transactions'),
            'onclick'   => 'setLocation(\'' . $this->getUrl('*/*/importCsv') .'\')',
      	));
	}

}