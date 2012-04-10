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

class Harapartners_Affiliate_Block_Adminhtml_Record_Index_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct(){
        parent::__construct();
        $this->setId('recordAffiliateGrid');
    }

    protected function _prepareCollection(){
        $model = Mage::getModel('affiliate/record');
        $collection = $model->getCollection();
//        foreach ($collection->getAllItems() as $item) {
//        	$trackingCode = json_decode($item->getTrackingCode(),true);
//        	if(isset($trackingCode['code']) && !!$trackingCode['code']){
//        		$item->setCode($trackingCode['code']);
//        	}       	
//        }        
		$this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    protected function _getStore(){
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    protected function _prepareColumns(){
    	$affilicateHelper = Mage::helper('affiliate');    
		$this->addColumn('affiliate_id', array(
            'header'        => $affilicateHelper->__('Affiliate ID'),
            'align'         => 'center',
            'width'         => '50px',
            'index'         => 'affiliate_id'
        ));
        $this->addColumn('affiliate_name', array(
            'header'        => $affilicateHelper->__('Affiliate Name'),
            'align'         => 'center',
            'width'         => '100px',
            'index'         => 'affiliate_name'
        )); 
        $this->addColumn('affiliate_code', array(
            'header'        => $affilicateHelper->__('Affiliate Code'),
            'align'         => 'center',
            'width'         => '100px',
            'index'         => 'affiliate_code'
        ));
        $this->addColumn('type', array(
            'header'        => $affilicateHelper->__('Type'),
            'align'         => 'right',
            'width'         => '50px',
            'index'         => 'type',
        	'type'			=> 'options',
            'options' => $affilicateHelper->getGridTypeArray()
        ));
        $this->addColumn('status', array(
            'header'        => $affilicateHelper->__('Status'),
            'align'         => 'right',
            'width'         => '50px',
            'index'         => 'status',
        	'type'			=> 'options',
            'options' => $affilicateHelper->getGridStatusArray()
        ));
		$this->addColumn('created_at', array(
            'header'        => $affilicateHelper->__('Created At'),
            'align'         => 'center',
            'width'         => '100px',
            'index'         => 'created_at',
        	'type'      	=> 'datetime',
            'gmtoffset' 	=> true
        ));   
		$this->addColumn('updated_at', array(
            'header'        => $affilicateHelper->__('Updated At'),
            'align'         => 'center',
            'width'         => '100px',
            'index'         => 'updated_at',
        	'type'      	=> 'datetime',
            'gmtoffset' 	=> true
        ));        
        
		$this->addExportType('*/*/exportCsv', $affilicateHelper->__('CSV'));
  		$this->addExportType('*/*/exportXml', $affilicateHelper->__('XML'));
      return parent::_prepareColumns();
    }

    public function getRowUrl($row){
        return $this->getUrl('*/*/edit', array(
	            'store'=>$this->getRequest()->getParam('store'),
	            'id'=>$row->getId()
        ));
    }
    
}