<?php

class Totsy_Sailthru_Block_Adminhtml_Feedconfig_Index_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct(){
        parent::__construct();
        $this->setId('feedconfigSailthruGrid');
    }

    protected function _prepareCollection(){
        $collection = Mage::getModel('sailthru/feedconfig')->getCollection();
        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    protected function _prepareColumns(){
    	$helper = Mage::helper('sailthru');

        $this->addColumn('entity_id', array(
            'header'        => $helper->__('ID'),
            'align'         => 'center',
            'width'         => '50px',
            'index'         => 'entity_id'
        ));
        $this->addColumn('type', array(
            'header'        => $helper->__('Type'),
            'align'         => 'center',
            'width'         => '50px',
            'index'         => 'type',
            'type'          => 'options',
            'options'       => Totsy_Sailthru_Helper_Feedconfig::mapTypes()
        ));
        $this->addColumn('order', array(
            'header'        => $helper->__('Order'),
            'align'         => 'center',
            'width'         => '50px',
            'index'         => 'order',
            'type'          => 'options',
            'options'       => Totsy_Sailthru_Helper_Feedconfig::mapOrders()
        ));
        $this->addColumn('start_at_day', array(
            'header'        => $helper->__('Satrt At Day'),
            'align'         => 'right',
            'width'         => '50px',
            'index'         => 'start_at_day'
        ));
        $this->addColumn('start_at_time', array(
            'header'        => $helper->__('Start At Time'),
            'align'         => 'right',
            'width'         => '50px',
            'index'         => 'start_at_time',
        ));
        $this->addColumn('include', array(
            'header'        => $helper->__('Include'),
            'align'         => 'right',
            'width'         => '100px',
            'index'         => 'iclude',
            'renderer'      => 'sailthru/adminhtml_widget_grid_column_render_list'
        ));
        $this->addColumn('exclude', array(
            'header'        => $helper->__('Exclude'),
            'align'         => 'right',
            'width'         => '100px',
            'index'         => 'exclude',
            'renderer'      => 'sailthru/adminhtml_widget_grid_column_render_list'
        )); 
        $this->addColumn('filter', array(
            'header'        => $helper->__('Filter'),
            'align'         => 'right',
            'width'         => '100px',
            'index'         => 'filter',
            'renderer'      => 'sailthru/adminhtml_widget_grid_column_render_list'
        )); 
        $this->addColumn('hash', array(
            'header'        => $helper->__('Link'),
            'align'         => 'center',
            'width'         => '100px',
            'index'         => 'hash',
            'renderer'      => 'sailthru/adminhtml_widget_grid_column_render_hash'
        ));

      return parent::_prepareColumns();
    }

    public function getRowUrl($row){
        return $this->getUrl('*/*/edit', array(
                'id'=>$row->getId()
        ));
    }
    
}
?>