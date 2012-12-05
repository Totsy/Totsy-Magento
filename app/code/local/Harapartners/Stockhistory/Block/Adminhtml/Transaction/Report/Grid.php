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

class Harapartners_Stockhistory_Block_Adminhtml_Transaction_Report_Grid extends Harapartners_Stockhistory_Block_Adminhtml_Transaction_Report_Abstract {
    
    protected function _getUniqueProductList(){
        $uniqueProductList = parent::_getUniqueProductList();
        
        //Add master pack product from category
        //Only for Report, not to be submitted to DotCom or Printing
        $productCollection = Mage::getModel('catalog/product')->getCollection()
                ->addCategoryFilter($this->getCategory())
                ->addAttributeToFilter('type_id', 'simple')
                ->addAttributeToFilter(array(array('attribute'=>'is_master_pack', 'gt'=>0)))
                ->setOrder('vendor_style', 'asc')
                ->setOrder('color', 'asc')
	    		->setOrder('size', 'asc');

        $hasEmptyMasterPackItem = false;
        foreach($productCollection as $product){
            if(!array_key_exists($product->getId(), $uniqueProductList)){
                $uniqueProductList[$product->getId()] = array(
                        'total'    => 0,
                        'qty'    => 0
                );
                $hasEmptyMasterPackItem = true;
            }
            $uniqueProductList[$product->getId()]['is_master_pack']    = 'Yes';
        }
        
        if($hasEmptyMasterPackItem){
            Mage::register('has_empty_master_pack_item', 1);
        }
        
        return $uniqueProductList;
    }
    
    protected function _prepareColumns() {

        $this->addColumn('vendor_style', array(
                    'header'    =>    Mage::helper('stockhistory')->__('Vendor Style'),
                    'align'        =>    'right',
                    'width'        =>    '50px',
                    'index'        =>    'vendor_style',

        ));
        
        $this->addColumn('sku', array(
                    'header'    =>    Mage::helper('stockhistory')->__('SKU'),
                    'align'        =>    'right',
                    'width'        =>    '50px',
                    'index'        =>    'sku',

        ));
        
        $this->addColumn('product_name', array(
                    'header'    =>    Mage::helper('stockhistory')->__('Product Name'),
                    'align'        =>    'right',
                    'width'        =>    '50px',
                    'index'        => 'product_name'
        ));
        
        
        $this->addColumn('color', array(
                    'header'    =>    Mage::helper('stockhistory')->__('Product Color'),
                    'align'        =>    'right',
                    'width'        =>    '25px',
                    'index'        =>    'color',
        ));
        
        $this->addColumn('size', array(
                    'header'    =>    Mage::helper('stockhistory')->__('Size'),
                    'align'        =>    'right',
                    'width'        =>    '25px',
                    'index'        =>    'size',
        ));
        
        $this->addColumn('qty_sold', array(
                    'header'    =>    Mage::helper('stockhistory')->__('Qty Sold'),
                    'align'        =>    'right',
                    'width'        =>    '25px',
                    'index'        =>  'qty_sold',
        ));
        
        $this->addColumn('qty_stock', array(
                    'header'    =>    Mage::helper('stockhistory')->__('Qty Stock'),
                    'align'        =>    'right',
                    'width'        =>    '25px',
                    'index'        =>  'qty_stock',
        ));
        
        $this->addColumn('qty_total', array(
                    'header'    =>    Mage::helper('stockhistory')->__('Qty Total'),
                    'align'        =>    'right',
                    'width'        =>    '25px',
                    'index'        =>  'qty_total',
        ));
        
        $this->addColumn('qty_to_amend', array(
                    'header'    => Mage::helper('catalog')->__('Final Qty'),
                    'width'     => '1',
                    'type'      => 'number',
                    'renderer'  => 'stockhistory/adminhtml_widget_grid_column_renderer_input'
        ));
        
        $this->addColumn('is_master_pack', array(
                    'header'    =>    Mage::helper('stockhistory')->__('Master Pack'),
                    'align'        =>    'right',
                    'width'        =>    '25px',
                    'index'        =>  'is_master_pack'
        ));
        
        $this->addColumn('case_pack_qty', array(
                    'header'    =>    Mage::helper('stockhistory')->__('Case Pack Qty'),
                    'align'        =>    'right',
                    'width'        =>    '25px',
                    'index'        =>  'case_pack_qty',
        ));
        $this->addColumn('case_pack_grp_id', array(
                    'header'    =>    Mage::helper('stockhistory')->__('Case Pack Group Id'),
                    'align'        =>    'right',
                    'width'        =>    '25px',
                    'index'        =>  'case_pack_grp_id',
        ));
        
        $this->addColumn('unit_cost', array(
                    'header'    =>    Mage::helper('stockhistory')->__('Unit Cost'),
                    'align'        =>    'right',
                    'width'        =>    '30px',
                    'index'        =>    'unit_cost',
        ));
        
        $this->addColumn('total_cost', array(
                    'header'    =>    Mage::helper('stockhistory')->__('Total Cost'),
                    'align'        =>    'right',
                    'width'        =>    '30px',
                    'index'        =>    'total_cost',
        ));
        
        $this->addExportType('*/*/exportPoCsv', Mage::helper('stockhistory')->__('CSV'));
        
        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('product_id');
        $this->getMassactionBlock()->setFormFieldName('product_id');
        $this->getMassactionBlock()->setUseSelectAll(false);

        //change case pack status function
        $this->getMassactionBlock()->addItem('change_case_pack_no', array(
             'label'=> Mage::helper('stockhistory')->__('Set Case Pack to No'),
             'url'  => $this->getUrl('*/*/changeCasePack', array('change_to' => 0)),
             'confirm' => Mage::helper('stockhistory')->__('Are you sure you want to change it to "No"?')
        ));

        $this->getMassactionBlock()->addItem('change_case_pack_yes', array(
             'label'=> Mage::helper('stockhistory')->__('Set Case Pack to Yes'),
             'url'  => $this->getUrl('*/*/changeCasePack', array('change_to' => 1)),
             'confirm' => Mage::helper('stockhistory')->__('Are you sure you want to change it to "Yes"?')
        ));

        return $this;
    }
    
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

    public function getRowUrl($row) {
        return false;
    }
    
}