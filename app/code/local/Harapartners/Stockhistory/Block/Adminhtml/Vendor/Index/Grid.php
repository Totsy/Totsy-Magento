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

class Harapartners_Stockhistory_Block_Adminhtml_Vendor_Index_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	private $options = array('1' => 'Vendor', '2'=>'Sub Vendor', '3'=> 'Distributor');
	
	public function __construct()
	{
		parent::__construct();
		$this->setId('VendorGrid');
		$this->setDefaultSort('id');
		$this->setDefaultDir('ASC');
		$this->setSaveParametersInSession(true);
	}
	
	protected function _prepareCollection()
	{
		$collection = Mage::getModel('stockhistory/vendor')->getCollection();
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}
	
	protected function _prepareColumns()
	{
		$this->addColumn('id', array(
					'header'	=>	Mage::helper('stockhistory')->__('ID'),
					'align'		=>	'right',
					'width'		=>	'50px',
					'index'		=>	'id',
		));
		
		$this->addColumn('vendor_name', array(
					'header'	=>	Mage::helper('stockhistory')->__('Vendor Name'),
					'align'		=>	'right',
					'width'		=>	'50px',
					'index'		=>	'vendor_name',
		));
		
		$this->addColumn('vendor_code', array(
					'header'	=>	Mage::helper('stockhistory')->__('Vendor Code'),
					'align'		=>	'right',
					'width'		=>	'50px',
					'index'		=>	'vendor_code',
		)); 
		
		$this->addColumn('vendor_type', array(
					'header'	=>	Mage::helper('stockhistory')->__('Vendor Type'),
					'align'		=>	'right',
					'width'		=>	'50px',
					'index'		=>	'vendor_type',
					'type'		=>	'options',
					'options'	=>	$this->options,
		)); 
		
		$this->addColumn('contact_person', array(
					'header'	=>	Mage::helper('stockhistory')->__('Contact Person'),
					'align'		=>	'right',
					'width'		=>	'20px',
					'index'		=>	'contact_person',
		));
		
		$this->addColumn('email_list', array(
					'header'	=>	Mage::helper('stockhistory')->__('Email'),
					'align'		=>	'right',
					'width'		=>	'20px',
					'index'		=>	'email_list',
		));
		
		$this->addColumn('phone', array(
					'header'	=>	Mage::helper('stockhistory')->__('Phone'),
					'align'		=>	'right',
					'width'		=>	'30px',
					'index'		=>	'phone',
		));
		
		$this->addColumn('address', array(
					'header'	=>	Mage::helper('stockhistory')->__('Address'),
					'align'		=>	'right',
					'width'		=>	'50px',
					'index'		=>	'address',
		));
		
		$this->addColumn('parent_id', array(
					'header'	=>	Mage::helper('stockhistory')->__('Parent ID'),
					'align'		=>	'right',
					'width'		=>	'50px',
					'index'		=>	'parent_id',
		));
		
		$this->addColumn('comment', array(
					'header'	=>	Mage::helper('stockhistory')->__('Note'),
					'align'		=>	'right',
					'width'		=>	'50px',
					'index'		=>	'comment',
		));
		
		$this->addColumn('created_at', array(
					'header'	=>	Mage::helper('stockhistory')->__('Created At'),
					'align'		=>	'right',
					'width'		=>	'30px',
					'index'		=>	'created_at',
					'type'		=>  'datetime',
					'gmtoffset'	=> 	true,
		));
		
		$this->addColumn('updated_at', array(
					'header'	=>	Mage::helper('stockhistory')->__('Updated At'),
					'align'		=>	'right',
					'width'		=>	'30px',
					'index'		=>	'updated_at',
					'type'		=>  'datetime',
					'gmtoffset'	=> 	true,
		));
		
		
		
		$this->addExportType('*/*/exportCsv', Mage::helper('stockhistory')->__('CSV'));
		
		return parent::_prepareColumns();
	}
	

	protected function _getStore()
	{
		$storeId = (int) $this->getRequest()->getParam('store', 1); // Future change needed
		return Mage::app()->getStore($storeId);
	}
	
	public function getRowUrl($row)
	{
		return $this->getUrl('*/*/edit', array(
						'store'	=> $this->_getStore(),	
						'id' 	=> $row->getId(),
		));
	}
}