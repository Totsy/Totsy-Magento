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
        $this->addColumn('affiliate_id', array(
            'header'        => Mage::helper('affiliate')->__('Affiliate ID'),
            'align'         => 'center',
            'width'         => '50px',
            'index'         => 'affiliate_id'
        ));    
       $this->addColumn('created_at', array(
            'header'        => Mage::helper('affiliate')->__('Created At'),
            'align'         => 'center',
            'width'         => '100px',
            'index'         => 'created_at',
        	'type'      	=> 'datetime',
            'gmtoffset' 	=> true
        ));   
       $this->addColumn('updated_at', array(
            'header'        => Mage::helper('affiliate')->__('Updated At'),
            'align'         => 'center',
            'width'         => '100px',
            'index'         => 'updated_at',
        	'type'      	=> 'datetime',
            'gmtoffset' 	=> true
        ));        
        $this->addColumn('status', array(
            'header'        => Mage::helper('affiliate')->__('Status'),
            'align'         => 'right',
            'width'         => '50px',
            'index'         => 'status',
        	'type'			=> 'options',
            'options' => array('1'=>'Enable','0'=>'Disable')
        ));       
        $this->addColumn('affiliate_code', array(
            'header'        => Mage::helper('affiliate')->__('Affiliate Code'),
            'align'         => 'center',
            'width'         => '100px',
            'index'         => 'affiliate_code'
        )); 
//        $this->addColumn('sub_affiliate_code', array(
//            'header'        => Mage::helper('affiliate')->__('Sub Affiliate Code'),
//            'align'         => 'center',
//            'width'         => '300px',
//            'index'         => 'sub_affiliate_code'
//        ));     
        $this->addColumn('type', array(
            'header'        => Mage::helper('affiliate')->__('Type'),
            'align'         => 'center',
            'width'         => '100px',
            'index'         => 'type'
        ));  
        $this->addColumn('tracking_code', array(
            'header'        => Mage::helper('affiliate')->__('Tracking Code'),
            'align'         => 'center',
            'width'         => '300px',
            'index'         => 'tracking_code'
        ));    
        $this->addColumn('referer_count', array(
            'header'        => Mage::helper('affiliate')->__('Total Bounces'),
            'align'         => 'center',
            'width'         => '200px',
            'index'         => 'total_bounces'
        ));               
		$this->addExportType('*/*/exportCsv', Mage::helper('affiliate')->__('CSV'));
  		$this->addExportType('*/*/exportXml', Mage::helper('affiliate')->__('XML'));
      return parent::_prepareColumns();
    }

    public function getRowUrl($row){
        return $this->getUrl('*/*/edit', array(
	            'store'=>$this->getRequest()->getParam('store'),
	            'id'=>$row->getId()
        ));
    }
    
}