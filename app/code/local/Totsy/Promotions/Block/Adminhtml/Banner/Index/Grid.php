<?php

class Totsy_Promotions_Block_Adminhtml_Banner_Index_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct(){
        parent::__construct();
        $this->setId('bannerPromotionsGrid');
    }

    protected function _prepareCollection(){
        $collection = Mage::getModel('promotions/banner')->getCollection();
        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    protected function _prepareColumns(){

        $this->addColumn('entity_id', array(
            'header'        => Mage::helper('promotions')->__('ID'),
            'align'         => 'center',
            'width'         => '50px',
            'index'         => 'entity_id'
        ));
        $this->addColumn('is_active', array(
            'header'        => Mage::helper('promotions')->__('Active'),
            'align'         => 'center',
            'width'         => '50px',
            'index'         => 'is_active',
            'type'          => 'options',
            'options'       => array( 0 => 'NO', 1 => 'YES')
        ));
        $this->addColumn('name', array(
            'header'        => Mage::helper('promotions')->__('Name'),
            'align'         => 'center',
            'width'         => '100px',
            'index'         => 'name'
        )); 
        $this->addColumn('image', array(
            'header'        => Mage::helper('promotions')->__('Image'),
            'align'         => 'center',
            'width'         => '100px',
            'index'         => 'image',
            'renderer'      => 'promotions/adminhtml_widget_grid_column_render_image'
        ));
        $this->addColumn('link', array(
            'header'        => Mage::helper('promotions')->__('Link'),
            'align'         => 'right',
            'width'         => '50px',
            'index'         => 'link'
        ));
        $this->addColumn('at_home', array(
            'header'        => Mage::helper('promotions')->__('At Home Page'),
            'align'         => 'right',
            'width'         => '50px',
            'index'         => 'at_home',
            'type'          => 'options',
            'options'       => array( 0 => 'NO', 1 => 'YES')
        ));
        $this->addColumn('at_events', array(
            'header'        => Mage::helper('promotions')->__('At Events Pages'),
            'align'         => 'right',
            'width'         => '50px',
            'index'         => 'at_events',
        ));
        $this->addColumn('at_products', array(
            'header'        => Mage::helper('promotions')->__('At Events Pages'),
            'align'         => 'right',
            'width'         => '50px',
            'index'         => 'at_products',
        ));
        $this->addColumn('start_at', array(
            'header'        => Mage::helper('promotions')->__('Start Date'),
            'align'         => 'center',
            'width'         => '100px',
            'index'         => 'start_at',
            'type'          => 'datetime',
            'gmtoffset'     => true
        ));   
        $this->addColumn('end_at', array(
            'header'        => Mage::helper('promotions')->__('End Date'),
            'align'         => 'center',
            'width'         => '100px',
            'index'         => 'end_at',
            'type'          => 'datetime',
            'gmtoffset'     => true
        ));      
        
      return parent::_prepareColumns();
    }

    public function getRowUrl($row){
        return $this->getUrl('*/*/edit', array(
                'id'=>$row->getId()
        ));
    }
    
}