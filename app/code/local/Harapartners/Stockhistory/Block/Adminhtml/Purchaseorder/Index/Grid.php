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
    
    public function __construct()
    {
        parent::__construct();
        $this->setId('PurchaseOrderGrid');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }
    
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('stockhistory/purchaseorder')->getCollection();
        
        $eventEndDateAttrId = Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_category','event_end_date');
        $collection->getSelect()
                ->join(
                        array('cat_dt' => 'catalog_category_entity_datetime'),
                        'main_table.category_id = cat_dt.entity_id AND main_table.store_id = cat_dt.store_id',
                        array('category_event_end_date' => 'cat_dt.value')
                )
                ->where('cat_dt.attribute_id = ?', $eventEndDateAttrId);

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    
    protected function _prepareColumns() {
        $helper = Mage::helper('stockhistory');
        
		$this->addColumn('id', array(
                    'header'    =>    $helper->__('ID'),
                    'align'        =>    'right',
                    'width'        =>    '25px',
                    'index'        =>    'id',
        ));
        
        $this->addColumn('po_number', array(
                    'header'    =>    $helper->__('PO Number'),
                    'align'        =>    'right',
                    'width'        =>    '30px',
                    'index'        =>    'po_number',
                    //'renderer'    =>    new Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Checkbox(),
        ));
        
        $this->addColumn('name', array(
                    'header'    =>    $helper->__('PO Name'),
                    'align'        =>    'right',
                    'width'        =>    '100px',
                    'index'        =>    'name',
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
        
        $this->addColumn('category_id', array(
                    'header'    =>    $helper->__('Category ID'),
                    'align'        =>    'right',
                    'width'        =>    '25px',
                    'index'        =>    'category_id',
        ));
        
        $this->addColumn('category_event_end_date', array(
                    'header'        =>    $helper->__('Event End Date'),
                    'align'            =>    'right',
                    'width'            =>    '25px',
                    'index'            =>    'category_event_end_date', //For ORDER
                    'filter_index'    =>    'cat_dt.value',    //For WHERE
                    'type'            =>  'datetime',
                    'gmtoffset'        =>     true,
        ));
        
        $this->addColumn('comment', array(
                    'header'    =>    $helper->__('Note'),
                    'align'        =>    'right',
                    'width'        =>    '150px',
                    'index'        =>    'comment',
        ));
        
        $this->addColumn('status', array(
                    'header'    =>    $helper->__('Status'),
                    'align'        =>    'right',
                    'width'        =>    '25px',
                    'index'        =>    'status',
                    'type'        =>    'options',
                    'options'    =>    $helper->getGridPurchaseorderStatusArray(),
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
        
        //$this->addExportType('*/*/exportCsv', $helper->__('CSV'));
        
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
                        'store'    => $this->_getStore(),    
                        'id'     => $row->getId(),
        ));
    }
}