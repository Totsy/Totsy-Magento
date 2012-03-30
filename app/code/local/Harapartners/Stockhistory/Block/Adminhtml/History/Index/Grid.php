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

class Harapartners_Stockhistory_Block_Adminhtml_History_Index_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	//private $options = array('0' => 'Pending', '1'=>'Processed', '2'=> 'Failed');
	
	public function __construct()
	{
		parent::__construct();
		$this->setId('StockGrid');
		$this->setDefaultSort('history_id');
		$this->setDefaultDir('ASC');
		$this->setSaveParametersInSession(true);
	}
	
	protected function _prepareCollection()
	{
		$collection = Mage::getModel('stockhistory/history')->getCollection();
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}
	
	protected function _prepareColumns()
	{
		$this->addColumn('history_id', array(
					'header'	=>	Mage::helper('stockhistory')->__('ID'),
					'align'		=>	'right',
					'width'		=>	'50px',
					'index'		=>	'history_id',
					//'renderer'	=>	new Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Checkbox(),
		));
		
		$this->addColumn('entity_id', array(
					'header'	=>	Mage::helper('stockhistory')->__('Product ID'),
					'align'		=>	'right',
					'width'		=>	'50px',
					'index'		=>	'entity_id',
		));
		
		$this->addColumn('product_name', array(
					'header'	=>	Mage::helper('stockhistory')->__('Product Name'),
					'align'		=>	'right',
					'width'		=>	'50px',
					'index'		=>	'product_name',
		)); 
		
		$this->addColumn('size', array(
					'header'	=>	Mage::helper('stockhistory')->__('Size'),
					'align'		=>	'right',
					'width'		=>	'20px',
					'index'		=>	'size',
		));
		
		$this->addColumn('color', array(
					'header'	=>	Mage::helper('stockhistory')->__('Color'),
					'align'		=>	'right',
					'width'		=>	'20px',
					'index'		=>	'color',
		));
		
		$this->addColumn('product_sku', array(
					'header'	=>	Mage::helper('stockhistory')->__('Product SKU'),
					'align'		=>	'right',
					'width'		=>	'50px',
					'index'		=>	'product_sku',
		));
		
		$this->addColumn('vendor_sku', array(
					'header'	=>	Mage::helper('stockhistory')->__('Vendor SKU'),
					'align'		=>	'right',
					'width'		=>	'50px',
					'index'		=>	'vendor_sku',
		));
		
		$this->addColumn('qty_delta', array(
					'header'	=>	Mage::helper('stockhistory')->__('Qty Changed'),
					'align'		=>	'right',
					'width'		=>	'50px',
					'index'		=>	'qty_delta',
		));
		
		$this->addColumn('unit_cost', array(
					'header'	=>	Mage::helper('stockhistory')->__('Unit Cost'),
					'align'		=>	'right',
					'width'		=>	'30px',
					'index'		=>	'unit_cost',
		));
		
		$this->addColumn('total_cost', array(
					'header'	=>	Mage::helper('stockhistory')->__('Total Cost'),
					'align'		=>	'right',
					'width'		=>	'50px',
					'index'		=>	'total_cost',
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
		
		$this->addColumn('status', array(
					'header'	=>	Mage::helper('stockhistory')->__('Status'),
					'align'		=>	'right',
					'width'		=>	'50px',
					'index'		=>	'status',
					'type'		=>	'options',
					'options'	=>  array('0' => 'Pending', '1'=>'Processed', '2'=> 'Failed')
		));
	
	    $this->addColumn('comment', array(
					'header'	=>	Mage::helper('stockhistory')->__('Comment'),
					'align'		=>	'right',
					'width'		=>	'50px',
					'index'		=>	'comment',
					
					
		));
		
		$this->addExportType('*/*/exportCsv', Mage::helper('stockhistory')->__('CSV'));
		
		return parent::_prepareColumns();
	}
	
	/**
	 *	Custom csv export, sum the qty per product	
	 * @return string $csv
	 **/
	public function getCsv()
	{
		$csv = '';
		$ids = $this->getRequest()->getParam('history_id');
        $this->_isExport = true; // Important! set to true can get all the records in all pages
        $this->_prepareGrid();
        if(!empty($ids)){        	
        	$this->getCollection()->addFieldToFilter('history_id', array('in' => $ids));
        }
        // Customize the collection to get the total qty for each product 
       
       	
//        $this->getCollection()->getSelect()
//        					->columns(array('qty' => 'SUM(qty_delta)'))
//        					->group('entity_id')
//        					->limit(0);
        $this->getCollection()->setPageSize(0);
        $this->getCollection()->load();
        $this->_afterLoadCollection();
		
        
        $data = Mage::helper('stockhistory')->getCsvHeader();

		
        $csv.= implode(',', $data)."\n";
        
		try{
			$Items = array();
        	foreach ($this->getCollection() as $item) {
            	
            	try{
            		$item->setData('status', Harapartners_Stockhistory_Helper_Data::STATE_PROCESSED);
            		$item->setData('updated_at', date('Y-m-d H:i:s'));
            		$item->save();
            		
            		$itemId = $item->getData('entity_id');
            		if( array_key_exists($itemId, $Items)){
            			$Items[$itemId]['updated_at'] = $item->getData('updated_at');
            			$Items[$itemId]['qty'] = $Items[$itemId]['qty'] + $item->getData('qty_delta');
            		}else{
            			$Items[$itemId] = array(
            								'entity_id'		=>	$item->getEntityId(),
            								'product_name'	=>	$item->getProductName(),
            								'product_sku'	=>	$item->getProductSku(),
            								'size'			=>	$item->getSize(),
            								'color'			=> 	$item->getColor(),
            								'vendor_sku'	=>	$item->getVendorSku(),
            								'qty'			=>	$item->getQtyDelta(),
            								'created_at'	=>	$item->getCreatedAt(),
            								'updated_at'	=>	$item->getUpdatedAt(),
            								'status'		=>	'Processed'
            			);
            		}
            		
           	 	}catch(Exception $e){
            		$this->_getSession()->addError($e->getMessage());
            		$item->setData('status', Harapartners_Stockhistory_Helper_Data::STATE_FAILED);
            		$item->save();
            	}

        	}
			/*$entityIds = array_unique($entityIds);
			foreach($entityIds as $entityId){
				$data = array();
				$report = Mage::getModel('stockhistory/report')->loadByEntityId($entityId);
				$data[] = $report->getData('entity_id');
				$data[] = $report->getData('sku');
				$data[] = $report->getData('vendor');
				$data[] = $report->getData('qty');
				$data[] = $report->getData('created_at');
				$data[] = $report->getData('updated_at');
				$data[] = $report->getData('status');
				$csv.= implode(',', $data)."\n";
			}*/
        	foreach($Items as $product){
        		$data = array();
        		$data[] = $product['entity_id'];
        		$data[] = $product['product_name'];
        		$data[] = $product['product_sku'];
        		$data[] = $product['size'];
        		$data[] = $product['color'];
        		$data[] = $product['vendor_sku'];
        		$data[] = $product['qty'];
        		$data[] = $product['created_at'];
        		$data[] = $product['updated_at'];
        		$data[] = $product['status'];
        		$csv.= implode(',', $data)."\n";
        	}
        	
        


		}catch(Exception $e){
			$this->_getSession()->addError($e->getMessage());
		}
        return $csv;
	}
	
//	protected function _prepareMassaction()
//	{
//		$this->setMassactionIdField('history_id');
//        $this->getMassactionBlock()->setFormFieldName('history_id');
//        $this->getMassactionBlock()->setUseSelectAll(false);
//        
//        $this->getMassactionBlock()->addItem('stock_export', array(
//             'label'	=> Mage::helper('stockhistory')->__('Export'),
//             'url' 		=> $this->getUrl('*/*/exportCsv'),
//        ));
//        return $this;
//		
//	}

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