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

class Harapartners_Stockhistory_Block_Adminhtml_Purchaseorder_Index_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	//private $options = array('0' => 'Pending', '1'=>'Processed', '2'=> 'Failed');
	
	public function __construct()
	{
		parent::__construct();
		$this->setId('PurchaseOrderGrid');
		$this->setDefaultSort('id');
		$this->setDefaultDir('ASC');
		$this->setSaveParametersInSession(true);
	}
	
	protected function _prepareCollection()
	{
		$collection = Mage::getModel('stockhistory/purchaseorder')->getCollection();
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
					//'renderer'	=>	new Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Checkbox(),
		));
		
		$this->addColumn('vendor_id', array(
					'header'	=>	Mage::helper('stockhistory')->__('Vendor ID'),
					'align'		=>	'right',
					'width'		=>	'50px',
					'index'		=>	'vendor_id',
		));
		
		
		
		$this->addColumn('name', array(
					'header'	=>	Mage::helper('stockhistory')->__('Name'),
					'align'		=>	'right',
					'width'		=>	'20px',
					'index'		=>	'name',
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
					'width'		=>	'50px',
					'index'		=>	'created_at',
					'type'		=>  'datetime',
					'gmtoffset'	=> 	true,
		));
		
		$this->addColumn('updated_at', array(
					'header'	=>	Mage::helper('stockhistory')->__('Updated At'),
					'align'		=>	'right',
					'width'		=>	'50px',
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