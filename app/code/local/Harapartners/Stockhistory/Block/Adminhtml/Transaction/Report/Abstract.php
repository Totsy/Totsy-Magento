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

class Harapartners_Stockhistory_Block_Adminhtml_Transaction_Report_Abstract extends Mage_Adminhtml_Block_Widget_Grid {
    
    protected $_reportCollection = null;
    protected $_vendorObj = null;
    protected $_poObj = null;
    protected $_category = null;
    
    public function __construct() {
        parent::__construct();
        $this->setId('ReportGrid');
        $this->setDefaultSort('product_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setTemplate('widget/grid_po_report.phtml');
    }
    
    public function getPreparedCollection() {
        return $this->_reportCollection;
    }
    
    public function getVendorObj() {
        if(!$this->_vendorObj){
            $this->_vendorObj = Mage::getModel('stockhistory/vendor')->load($this->getPoObject()->getData('vendor_id'));
        }
        return $this->_vendorObj;
    }
    
    public function getPoObject() {
        if(!$this->_poObj){
            $poObject = Mage::getModel('stockhistory/purchaseorder')->load($this->getRequest()->getParam('po_id'));
            if(!$poObject || !$poObject->getId()){
                Mage::throwException('Invalid PO.');
            }
            $this->_poObj = $poObject;
        }
        return $this->_poObj;
    }
    
    public function getCategory(){
        if(!$this->_category){
            $this->_category = Mage::getModel('catalog/category')->load($this->getPoObject()->getCategoryId());
        }
        return $this->_category;
    }
    
    protected function _prepareCollection() {
        $uniqueProductList = $this->_getUniqueProductList();
        
        //Building report collection
        $reportCollection = new Varien_Data_Collection();
        $productsSoldArray = Mage::helper('stockhistory')->getProductSoldInfoByCategory($this->getCategory(), $uniqueProductList);
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
                'product_id'            => $product->getId(),
                'po_id'                    =>    $this->getPoObject()->getId(),
                'vendor_style'            =>    $product->getVendorStyle(),
                'product_name'            =>    $product->getName(),
                'sku'                    =>  $product->getSku(),
                'color'                    =>    $tempColorLabel,
                'size'                    =>    $tempSizeLabel,
                'qty_sold'                =>    round($soldNum),
                'qty_stock'                =>    round($product->getStockItem()->getQty()),
                'qty_total'                =>    $productInfo['qty'],
                'is_master_pack'        =>    $productInfo['is_master_pack'],
                'case_pack_qty'            =>    round($product->getData('case_pack_qty')),
                'case_pack_grp_id'      =>  $product->getCasePackGrpId(),
                'unit_cost'                =>    $productInfo['qty'] ?
                                    round($productInfo['total']/$productInfo['qty'], 2) : $productInfo['total'],
                'total_cost'            =>    $productInfo['total'],
                'category_id'       =>  $this->getPoObject()->getCategoryId()
            );
            $reportItem->addData($data);
            $reportCollection->addItem($reportItem);
        }
        
        //set collection in session
        $reportData = $reportCollection->toArray();
        if(!empty($reportData['items'])){
            Mage::getSingleton('adminhtml/session')->setPOReportGridData($reportData['items']);
        }
        
        $this->_reportCollection = $reportCollection;
        $this->setCollection($reportCollection);
        //return parent::_prepareCollection(); //We don't need to prepare Filter/Sort/etc...
        return $this;
    }
    
    protected function _getUniqueProductList(){
        // Gather all products to be reported
        $rawCollection = Mage::getModel('stockhistory/transaction')->getCollection();
        $rawCollection->getSelect()->where('po_id = ?', $this->getPoObject()->getId());
        
        $removeProducts = array();
        
        $uniqueProductList = array();
        foreach($rawCollection as $item){
        	$newTransactionId = $item->getId(); // Hara Song, Save current trasaction ID for certain item
        	$product = Mage::getModel('catalog/product')->getCollection()
                ->addAttributeToSelect(array('sale_wholesale', 'is_master_pack'))
                ->addAttributeToFilter('entity_id', $item->getProductId());
        	$product = $product->getFirstItem();
            
        	$productId = $product->getId();
           
        	//ignore empty rows, some products may have been removed
        	if(empty($productId)) {
        		continue;
        	}
        	
            if(!!$item->getProductId() && !array_key_exists($item->getProductId(), $uniqueProductList)){
                $uniqueProductList[$item->getProductId()] = array(
                        'total'             		=> 0,
                        'qty'               	 	=> 0,
                        'is_master_pack'    		=> 'No',
                		'latest_transaction_id'    	=> $item->getId(),
               			'amendment_total'			=> 0,
                		'amendment_qty'				=> 0,
                );
            }
            // Hara Song only update the qty and total if the transaction is the latest
            if($item->getActionType() == Harapartners_Stockhistory_Model_Transaction::ACTION_TYPE_EVENT_IMPORT 
            		|| $item->getActionType() == Harapartners_Stockhistory_Model_Transaction::ACTION_TYPE_DIRECT_IMPORT){
            
	            if($newTransactionId >= $uniqueProductList[$item->getProductId()]['latest_transaction_id']){
		            $uniqueProductList[$item->getProductId()]['total'] = $item->getQtyDelta() * $product->getData('sale_wholesale');
		            $uniqueProductList[$item->getProductId()]['qty'] = $item->getQtyDelta();
		            $uniqueProductList[$item->getProductId()]['latest_transaction_id'] = $newTransactionId;
	            }
            }elseif(Harapartners_Stockhistory_Model_Transaction::ACTION_TYPE_AMENDMENT){
            		$uniqueProductList[$item->getProductId()]['amendment_total'] += $item->getQtyDelta() * $product->getData('sale_wholesale');
            		$uniqueProductList[$item->getProductId()]['amendment_qty'] += $item->getQtyDelta();
            }
	        if((int)$product->getData('is_master_pack')){
            	$uniqueProductList[$item->getProductId()]['is_master_pack'] = 'Yes';
            }

                $uniqueProductList[$item->getProductId()]['is_master_pack_value'] = $product->getData('is_master_pack');
            
            //add items which should be removed
            if($item->getActionType() == Harapartners_Stockhistory_Model_Transaction::ACTION_TYPE_REMOVE) {
            	$removeProducts[$item->getProductId()] = 1;
            }
        }
        //Hara Song Add two types import together
        foreach($uniqueProductList as &$value){
        	$value['total'] = $value['total'] + $value['amendment_total'];
        	$value['qty'] = $value['qty'] + $value['amendment_qty'];
        }
        
        $newUniqueProductList = array();
        
        //use an new array to save non-removed items
        foreach($uniqueProductList as $productId => $data) {
        	if(!isset($removeProducts[$productId])) {
        		$newUniqueProductList[$productId] = $data;
        	}
        }
        
        return $newUniqueProductList;
        //return $uniqueProductList;
    }
    
    //No pager for this report!
    public function getPagerVisibility(){
        return false;
    }

    //No filters for this report!
    public function getFilterVisibility(){
        return false;
    }
    
}
