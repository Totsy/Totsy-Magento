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

class Harapartners_Stockhistory_Block_Adminhtml_Transaction_Report_Grid extends Mage_Adminhtml_Block_Widget_Grid {
	
	//protected $options = array('0' => 'Pending', '1'=>'Processed', '2'=> 'Failed');
	protected $_reportCollection = null;
	
	public function __construct() {
		parent::__construct();
		$this->setId('ReportGrid');
		$this->setDefaultSort('product_id');
		$this->setDefaultDir('ASC');
		$this->setSaveParametersInSession(true);
		$this->setTemplate('widget/grid_po_report.phtml');
	}
	
	protected function _prepareCollection() {
		
		// Gather all products to be reported
		$poId = $this->getRequest()->getParam('po_id');
		$poObject = Mage::getModel('stockhistory/purchaseorder')->load($poId);
		if(!$poObject || !$poObject->getId()){
			Mage::throwException('Invalid PO.');
		}
		
		$rawCollection = Mage::getModel('stockhistory/transaction')->getCollection();
		$rawCollection->getSelect()->where('po_id = ?', $poId);
		
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
		
		//Add master pack product from category
		$categoryId = $poObject->getCategoryId();
		$category = Mage::getModel('catalog/category')->load($categoryId);
		$productCollection = Mage::getModel('catalog/product')->getCollection()
				->addCategoryFilter($category)
				->addAttributeToFilter('type_id', 'simple')
				->addAttributeToFilter('is_master_pack', 1);
		foreach($productCollection as $product){
			if(!array_key_exists($product->getId(), $uniqueProductList)){
				$uniqueProductList[$product->getId()] = array(
						'total' => 0,
						'qty'	=> 0,
				);
			}
		}
		
		//Building report collection
		$reportCollection = new Varien_Data_Collection();
		$productsSoldArray = Mage::helper('stockhistory')->getProductSoldInfoByCategory($category, $uniqueProductList);
		
		$sizeAttribute = Mage::getSingleton('eav/config')->getAttribute('catalog_product', 'size');
		$sizeOptions = $sizeAttribute->getSource()->getAllOptions(false);
		$colorAttribute = Mage::getSingleton('eav/config')->getAttribute('catalog_product', 'color');
		$colorOptions = $colorAttribute->getSource()->getAllOptions(false);
		
		foreach($uniqueProductList as $productId => $productInfo){
			$reportItem = new Varien_Object();
			$product = Mage::getModel('catalog/product')->load($productId);
			$soldNum = (isset($productsSoldArray[$productId]['qty'])) ? $productsSoldArray[$productId]['qty'] : 0;
			
			$tempSizeLabel = '';
			foreach($sizeOptions as $tempOption){
				if($tempOption['value'] == $product->getSize()){
					$tempSizeLabel = $tempOption['label'];
					break;
				}
			}
			
			$tempColorLabel = '';
			foreach($colorOptions as $tempOption){
				if($tempOption['value'] == $product->getColor()){
					$tempColorLabel = $tempOption['label'];
					break;
				}
			}
			
			//you may want to add some product info here, like SKU, Name, Vendor ... so the report is good looking
			$data = array(
				'po_id'					=>	$poId,
				'product_name'			=>	$product->getName(),
				'vendor_style'			=>	$product->getVendorStyle(),
				'sku'					=>  $product->getSku(),
				'color'					=>	$tempColorLabel,
				'size'					=>	$tempSizeLabel,
				'qty_sold'				=>	round($soldNum),
				'qty_stock'				=>	round($product->getStockItem()->getQty()),
				'qty_total'				=>	$productInfo['qty'],
				'total_cost'			=>	$productInfo['total'],
				'average_cost'			=>	round($productInfo['total']/$productInfo['qty'], 2),
				
			);
			$reportItem->addData($data);
			$reportCollection->addItem($reportItem);
		}
		$this->_reportCollection = $reportCollection;
		$this->setCollection($reportCollection);
		return parent::_prepareCollection();
	}
	
	protected function _prepareColumns() {

		$this->addColumn('vendor_style', array(
					'header'	=>	Mage::helper('stockhistory')->__('Vendor Style'),
					'align'		=>	'right',
					'width'		=>	'50px',
					'index'		=>	'vendor_style',

		));
		
		$this->addColumn('sku', array(
					'header'	=>	Mage::helper('stockhistory')->__('SKU'),
					'align'		=>	'right',
					'width'		=>	'50px',
					'index'		=>	'sku',

		));
		
		$this->addColumn('product_name', array(
					'header'	=>	Mage::helper('stockhistory')->__('Product Name'),
					'align'		=>	'right',
					'width'		=>	'50px',
					'index'		=> 'product_name'
		));
		
		
		$this->addColumn('color', array(
					'header'	=>	Mage::helper('stockhistory')->__('Product Color'),
					'align'		=>	'right',
					'width'		=>	'25px',
					'index'		=>	'color',
		));
		
		$this->addColumn('size', array(
					'header'	=>	Mage::helper('stockhistory')->__('Size'),
					'align'		=>	'right',
					'width'		=>	'25px',
					'index'		=>	'size',
		));
		
		$this->addColumn('qty_sold', array(
					'header'	=>	Mage::helper('stockhistory')->__('Quantity Sold'),
					'align'		=>	'right',
					'width'		=>	'25px',
					'index'		=>  'qty_sold',
		));
		
		$this->addColumn('qty_stock', array(
					'header'	=>	Mage::helper('stockhistory')->__('Stock Quantity'),
					'align'		=>	'right',
					'width'		=>	'25px',
					'index'		=>  'qty_stock',
		));
		
		$this->addColumn('qty_total', array(
					'header'	=>	Mage::helper('stockhistory')->__('PO Quantity'),
					'align'		=>	'right',
					'width'		=>	'25px',
					'index'		=>  'qty_total',
		));
		
		$this->addColumn('average_cost', array(
					'header'	=>	Mage::helper('stockhistory')->__('Average Cost'),
					'align'		=>	'right',
					'width'		=>	'30px',
					'index'		=>	'average_cost',
		));
		
		$this->addColumn('total_cost', array(
					'header'	=>	Mage::helper('stockhistory')->__('Total Cost'),
					'align'		=>	'right',
					'width'		=>	'30px',
					'index'		=>	'total_cost',
		));
		
		
		
		$this->addExportType('*/*/exportPoCsv', Mage::helper('stockhistory')->__('CSV'));
		
		return parent::_prepareColumns();
	}
	
	/**
	 *	Custom csv export, sum the qty per product	
	 * @return string $csv
	 **/
	public function getCsv() {
        $csv = '';
        $this->_isExport = true;
        $this->_prepareGrid();
		//HP Song -- Start	
		$collection = $this->_reportCollection;
		$this->_reportCollection = null;
		// HP -- End
        $this->_afterLoadCollection();

        $data = array();
        foreach ($this->_columns as $column) {
            if (!$column->getIsSystem()) {
                $data[] = '"'.$column->getExportHeader().'"';
            }
        }
        $csv.= implode(',', $data)."\n";
        //HP Song
        foreach ($collection->getItems() as $item) {
            $data = array();
            foreach ($this->_columns as $column) {
                if (!$column->getIsSystem()) {
                    $data[] = '"' . str_replace(array('"', '\\'), array('""', '\\\\'),
                        $column->getRowFieldExport($item)) . '"';
                }
            }
            $csv.= implode(',', $data)."\n";
        }

        if ($this->getCountTotals())
        {
            $data = array();
            foreach ($this->_columns as $column) {
                if (!$column->getIsSystem()) {
                    $data[] = '"' . str_replace(array('"', '\\'), array('""', '\\\\'),
                        $column->getRowFieldExport($this->getTotals())) . '"';
                }
            }
            $csv.= implode(',', $data)."\n";
        }

        return $csv;
    }
	
	
	public function getFilterVisibility(){
		return false;
	}
	
	protected function _getStore() {
		$storeId = (int) $this->getRequest()->getParam('store', 1); // Future change needed
		return Mage::app()->getStore($storeId);
	}
}