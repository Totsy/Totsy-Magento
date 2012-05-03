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
 
class Harapartners_Categoryevent_Block_Adminhtml_Browse_Index_Grid extends Mage_Adminhtml_Block_Widget_Grid {
    
    const CATEGOTYEVENT_LEVEL = 3; // Only this level is considered category event

    public function __construct(){
        parent::__construct();
        $this->setId('categoryeventBrowseGrid');
    }

    protected function _getStore(){
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    protected function _prepareCollection(){
        $store = $this->_getStore();
        
        //Store Select Filter  Yang
        $storeCatCollection = Mage::getModel('catalog/category')->getCollection();
        $subcatIds = array();
        if (!!$store->getId()) {
            $storeRootId = $store->getRootCategoryId();
            $storeCatCollection->addFieldToFilter('parent_id', $storeRootId)->load();
            $this->setStoreId($store);
        }
        foreach ($storeCatCollection as $subcat){
            array_push($subcatIds, $subcat->getId());        
        } 
        //Store Select Filter End Yang      
        $collection = Mage::getModel('catalog/category')->getCollection();
        $collection->addAttributeToSelect(array('name', 'description', 'thumbnail', 'event_start_date', 'event_end_date'));
        $collection->addFieldToFilter('level', self::CATEGOTYEVENT_LEVEL);
        $collection->addFieldToFilter('parent_id', $subcatIds[0]);
        $this->setCollection($collection);
        //$collection->load();
        parent::_prepareCollection();
        return $this;
    }

    protected function _prepareColumns(){        
        $this->addColumn('entity_id', array(
            'header'        => Mage::helper('categoryevent')->__('ID'),
            'align'         => 'right',
            'width'         => '50px',
            'index'         => 'entity_id',
            'type'          => 'number',
        ));
        
        $this->addColumn('name', array(
            'header'        => Mage::helper('categoryevent')->__('Name'),
            'align'         => 'right',
            'width'         => '50px',
            'index'         => 'name'
        ));
        
        $this->addColumn('thumbnail', array(
            'header'    => Mage::helper('categoryevent')->__('Event Image'),
            'index'     => 'thumbnail',
            'width'     => '120px',
            'renderer'    => 'Harapartners_Service_Block_Adminhtml_Widget_Grid_Column_Renderer_Category_Image'
        ));
        
        $this->addColumn('description', array(
            'header'        => Mage::helper('categoryevent')->__('Blurb'),
            'align'         => 'right',
            'width'         => '300px',
            'index'         => 'description'
        ));
        
        $this->addColumn('event_start_date', array(
            'header'        => Mage::helper('categoryevent')->__('Event Start Date'),
            'align'         => 'center',
            'width'         => '50px',
            'index'         => 'event_start_date',
            'type'          => 'datetime',
            'frame_callback' => array('Harapartners_Categoryevent_Block_Adminhtml_Browse_Index_Grid', 'timetransform')
        ));
        
        $this->addColumn('event_end_date', array(
            'header'        => Mage::helper('categoryevent')->__('Event End Date'),
            'align'         => 'center',
            'width'         => '50px',
            'index'         => 'event_end_date',
            'type'          => 'datetime',
            'gmtoffset'     => true,
        'frame_callback' => array('Harapartners_Categoryevent_Block_Adminhtml_Browse_Index_Grid', 'timetransform')
        ));
        
        return parent::_prepareColumns();
    }

    public function getRowUrl($row){
        return $this->getUrl('adminhtml/catalog_category/edit', array(
                'store'=>$this->getRequest()->getParam('store'),
                'id'=>$row->getId()
        ));
    }
    
    public static function timetransform($renderedValue, $row, $object, $bool){
        $beforeTime = strtotime($renderedValue);
        $offset = strtotime("+5 hours",0);
        $newTimeStamp = $beforeTime+$offset;
        $newdate = date("M d, Y h:i:s",$newTimeStamp);
        return $newdate;
    }
    
}