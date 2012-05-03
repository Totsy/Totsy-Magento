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

class Harapartners_Stockhistory_Block_Adminhtml_Transaction_Index_Grid extends Mage_Adminhtml_Block_Widget_Grid {
    
    
    public function __construct() {
        parent::__construct();
        $this->setId('TransactionGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }
    
    protected function _prepareCollection() {
        $collection = Mage::getModel('stockhistory/transaction')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    
    protected function _prepareColumns(){    
        $helper = Mage::helper('stockhistory');
        $this->addColumn('id', array(
                    'header'    =>    $helper->__('ID'),
                    'align'        =>    'right',
                    'width'        =>    '25px',
                    'index'        =>    'id',
        ));
        
        $this->addColumn('vendor_id', array(
                    'header'    =>    $helper->__('Vendor ID'),
                    'align'        =>    'right',
                    'width'        =>    '25px',
                    'index'        =>    'vendor_id',
        ));
        
        $this->addColumn('vendor_code', array(
                    'header'    =>    $helper->__('Vendor Code'),
                    'align'        =>    'right',
                    'width'        =>    '50px',
                    'index'        =>    'vendor_code',
        ));
        
        $this->addColumn('po_id', array(
                    'header'    =>    $helper->__('Purchase Order ID'),
                    'align'        =>    'right',
                    'width'        =>    '25px',
                    'index'        =>    'po_id',
        )); 
        
        $this->addColumn('product_id', array(
                    'header'    =>    $helper->__('Product ID'),
                    'align'        =>    'right',
                    'width'        =>    '25px',
                    'index'        =>    'product_id',
        ));
        
        $this->addColumn('category_id', array(
                    'header'    =>    $helper->__('Category ID'),
                    'align'        =>    'right',
                    'width'        =>    '25px',
                    'index'        =>    'category_id',
        ));
        
        $this->addColumn('product_sku', array(
                    'header'    =>    $helper->__('Product SKU'),
                    'align'        =>    'right',
                    'width'        =>    '50px',
                    'index'        =>    'product_sku',
        ));
        
        $this->addColumn('vendor_style', array(
                    'header'    =>    $helper->__('Vendor Style'),
                    'align'        =>    'right',
                    'width'        =>    '50px',
                    'index'        =>    'vendor_style',
        ));
        
        $this->addColumn('qty_delta', array(
                    'header'    =>    $helper->__('Qty Changed'),
                    'align'        =>    'right',
                    'width'        =>    '25px',
                    'index'        =>    'qty_delta',
        ));
        
        $this->addColumn('unit_cost', array(
                    'header'    =>    $helper->__('Unit Cost'),
                    'align'        =>    'right',
                    'width'        =>    '25px',
                    'index'        =>    'unit_cost',
        ));
        
        $this->addColumn('created_at', array(
                    'header'    =>    $helper->__('Created At'),
                    'align'        =>    'right',
                    'width'        =>    '50px',
                    'index'        =>    'created_at',
                    'type'        =>  'datetime',
                    'gmtoffset'    =>     true,
        ));
        
        $this->addColumn('updated_at', array(
                    'header'    =>    $helper->__('Updated At'),
                    'align'        =>    'right',
                    'width'        =>    '50px',
                    'index'        =>    'updated_at',
                    'type'        =>  'datetime',
                    'gmtoffset'    =>     true,
        ));
        
        $this->addColumn('action_type', array(
                    'header'    =>    $helper->__('Action'),
                    'align'        =>    'right',
                    'width'        =>    '50px',
                    'index'        =>    'action_type',
                    'type'        =>    'options',
                    'options'    =>  $helper->getGridTransactionTypeArray(),
        ));
    
        $this->addColumn('comment', array(
                    'header'    =>    $helper->__('Comment'),
                    'align'        =>    'right',
                    'width'        =>    '50px',
                    'index'        =>    'comment',        
        ));
        
        $this->addExportType('*/*/exportCsv', $helper->__('CSV'));
        
        return parent::_prepareColumns();
    }
    
    /**
     *    Custom csv export, sum the qty per product    
     * @return string $csv
     **/
//    public function getCsv()
//    {
//        $csv = '';
//        $ids = $this->getRequest()->getParam('transaction_id');
//        $this->_isExport = true; // Important! set to true can get all the records in all pages
//        $this->_prepareGrid();
//        if(!empty($ids)){            
//            $this->getCollection()->addFieldToFilter('transaction_id', array('in' => $ids));
//        }
//        $this->getCollection()->setPageSize(0);
//        $this->getCollection()->load();
//        $this->_afterLoadCollection();
//        
//        
//        $data = $helper->getCsvHeader();
//
//        
//        $csv.= implode(',', $data)."\n";
//        
//        try{
//            $Items = array();
//            foreach ($this->getCollection() as $item) {
//                
//                try{
//                    $item->setData('status', Harapartners_Stockhistory_Helper_Data::STATE_PROCESSED);
//                    $item->setData('updated_at', date('Y-m-d H:i:s'));
//                    $item->save();
//                    
//                    $itemId = $item->getData('entity_id');
//                    if( array_key_exists($itemId, $Items)){
//                        $Items[$itemId]['updated_at'] = $item->getData('updated_at');
//                        $Items[$itemId]['qty'] = $Items[$itemId]['qty'] + $item->getData('qty_delta');
//                    }else{
//                        $Items[$itemId] = array(
//                                            'entity_id'        =>    $item->getEntityId(),
//                                            'product_name'    =>    $item->getProductName(),
//                                            'product_sku'    =>    $item->getProductSku(),
//                                            'size'            =>    $item->getSize(),
//                                            'color'            =>     $item->getColor(),
//                                            'vendor_sku'    =>    $item->getVendorSku(),
//                                            'qty'            =>    $item->getQtyDelta(),
//                                            'created_at'    =>    $item->getCreatedAt(),
//                                            'updated_at'    =>    $item->getUpdatedAt(),
//                                            'status'        =>    'Processed'
//                        );
//                    }
//                    
//                    }catch(Exception $e){
//                    $this->_getSession()->addError($e->getMessage());
//                    $item->setData('status', Harapartners_Stockhistory_Helper_Data::STATE_FAILED);
//                    $item->save();
//                }
//
//            }
//            /*$entityIds = array_unique($entityIds);
//            foreach($entityIds as $entityId){
//                $data = array();
//                $transaction = Mage::getModel('stockhistory/transaction')->loadByEntityId($entityId);
//                $data[] = $transaction->getData('entity_id');
//                $data[] = $transaction->getData('sku');
//                $data[] = $transaction->getData('vendor');
//                $data[] = $transaction->getData('qty');
//                $data[] = $transaction->getData('created_at');
//                $data[] = $transaction->getData('updated_at');
//                $data[] = $transaction->getData('status');
//                $csv.= implode(',', $data)."\n";
//            }*/
//            foreach($Items as $product){
//                $data = array();
//                $data[] = $product['entity_id'];
//                $data[] = $product['product_name'];
//                $data[] = $product['product_sku'];
//                $data[] = $product['size'];
//                $data[] = $product['color'];
//                $data[] = $product['vendor_sku'];
//                $data[] = $product['qty'];
//                $data[] = $product['created_at'];
//                $data[] = $product['updated_at'];
//                $data[] = $product['status'];
//                $csv.= implode(',', $data)."\n";
//            }
//
//        }catch(Exception $e){
//            $this->_getSession()->addError($e->getMessage());
//        }
//        return $csv;
//    }
    
    protected function _getStore()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 1); // Future change needed
        return Mage::app()->getStore($storeId);
    }
    
//    public function getRowUrl($row)
//    {
//        return $this->getUrl('*/*/edit', array(
//                        'store'    => $this->_getStore(),    
//                        'id'     => $row->getId(),
//        ));
//    }
}