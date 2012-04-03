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

class Harapartners_Stockhistory_Block_Adminhtml_History_Report_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	//private $options = array('0' => 'Pending', '1'=>'Processed', '2'=> 'Failed');
	
	public function __construct()
	{
		parent::__construct();
		$this->setId('ReportGrid');
		$this->setDefaultSort('product_id');
		$this->setDefaultDir('ASC');
		$this->setSaveParametersInSession(true);
	}
	
	protected function _prepareCollection()
	{
		$poId = $this->getRequest()->getParam('po_id');
		$rawCollection = Mage::getModel('stockhistory/report')->getCollection();

		$rawCollection->getSelect()->where('po_id=?', $poId);
//					->columns(array('qty' => 'SUM(qty_delta)', 'total_all_cost' => 'SUM(total_cost)'))
//					->group('product_id');

		
		$uniqueProductList = array();
		foreach($rawCollection as $item){
			
			if(!array_key_exists($item->getProductId(), $uniqueProductList)){
				$uniqueProductList[$item->getProductId()] = array(
						'total' => 0,
						'qty'	=> 0,
				);
			}
			
			$uniqueProductList[$item->getProductId()]['total'] += $item->getQtyDelta() * $item->getUnitCost();
			$uniqueProductList[$item->getProductId()]['qty'] += $item->getQtyDelta();
			
		}
		
		$reportCollection = Mage::getModel('stockhistory/report')->getCollection();
//		$reportCollection->getSelect()->limit(0);
//		$reportCollection->load();
		
		foreach($uniqueProductList as $productId => $productInfo){
			$item = Mage::getModel('stockhistory/report');
			$product = Mage::getModel('catalog/product')->load($productId);
			//you may want to add some product info here, like SKU, Name, Vendor ... so the report is good looking
			$data = array(
				'product_id'	=>	$product->getId(),
				'vendor_id'		=>	$product->getVendor(),
				'qty'			=>	$productInfo['qty'],
				'total'			=>	$productInfo['total'],
				'average_cost'	=>	$productInfo['total']/$productInfo['qty'],
				
			);
			$item->addData($data);
			$reportCollection->addItem($item);
		}
		
		$this->setCollection($reportCollection);
		return parent::_prepareCollection();
	}
	
	protected function _prepareColumns()
	{
		$this->addColumn('product_id', array(
					'header'	=>	Mage::helper('stockhistory')->__('Product ID'),
					'align'		=>	'right',
					'width'		=>	'20px',
		));
		
		$this->addColumn('product_sku', array(
					'header'	=>	Mage::helper('stockhistory')->__('Product SKU'),
					'align'		=>	'right',
					'width'		=>	'50px',

		));
		
		$this->addColumn('qty', array(
					'header'	=>	Mage::helper('stockhistory')->__('Qty'),
					'align'		=>	'right',
					'width'		=>	'50px',
		));
		
		$this->addColumn('total_all_cost', array(
					'header'	=>	Mage::helper('stockhistory')->__('Total Cost'),
					'align'		=>	'right',
					'width'		=>	'30px',
		));
		

		
		//$this->addExportType('*/*/exportCsv', Mage::helper('stockhistory')->__('CSV'));
		
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