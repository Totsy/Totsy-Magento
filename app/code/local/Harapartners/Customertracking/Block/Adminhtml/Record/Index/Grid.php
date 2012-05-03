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

class Harapartners_Customertracking_Block_Adminhtml_Record_Index_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct(){
        parent::__construct();
        $this->setId('customertrackingRecordGrid');
    }

    protected function _prepareCollection(){
        $model = Mage::getModel('customertracking/record');
        $collection = $model->getCollection();
        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    protected function _getStore(){
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    protected function _prepareColumns(){
        
        $helper = Mage::helper('customertracking');
        
        $this->addColumn('customertracking_id', array(
            'header'        => $helper->__('Customertracking ID'),
            'align'         => 'center',
            'width'         => '25px',
            'index'         => 'customertracking_id'
        ));
        
        $this->addColumn('customer_id', array(
            'header'        => $helper->__('customer ID'),
            'align'         => 'center',
            'width'         => '25px',
            'index'         => 'customer_id'
        ));
        
        $this->addColumn('customer_email', array(
            'header'        => $helper->__('customer Email'),
            'align'         => 'center',
            'width'         => '50px',
            'index'         => 'customer_email'
        ));
        
        $this->addColumn('affiliate_id', array(
            'header'        => $helper->__('Affiliate ID'),
            'align'         => 'center',
            'width'         => '25px',
            'index'         => 'affiliate_id'
        ));
        
        $this->addColumn('affiliate_code', array(
            'header'        => $helper->__('Affiliate Code'),
            'align'         => 'center',
            'width'         => '50px',
            'index'         => 'affiliate_code'
        ));
        
        $this->addColumn('sub_affiliate_code', array(
            'header'        => $helper->__('Sub Affiliate Code'),
            'align'         => 'center',
            'width'         => '50px',
            'index'         => 'sub_affiliate_code'
        ));
        
        $this->addColumn('status', array(
            'header'        => $helper->__('Status'),
            'align'         => 'right',
            'width'         => '50px',
            'index'         => 'status',
            'type'            => 'options',
            'options' => $helper->getGridStatusArray()
        ));
        $this->addColumn('registration_param', array(
            'header'        => $helper->__('Registration Param'),
            'align'         => 'center',
            'width'         => '300px',
            'index'         => 'registration_param'
        ));
        
        $this->addColumn('created_at', array(
            'header'        => $helper->__('Created At'),
            'align'         => 'center',
            'width'         => '50px',
            'index'         => 'created_at',
            'type'          => 'datetime',
            'gmtoffset'     => true
        ));  
        $this->addColumn('updated_at', array(
            'header'        => $helper->__('Last Activity At'),
            'align'         => 'center',
            'width'         => '50px',
            'index'         => 'updated_at',
            'type'          => 'datetime',
            'gmtoffset'     => true
        ));    
 
        $this->addExportType('*/*/exportCsv', $helper->__('CSV'));
          $this->addExportType('*/*/exportXml', $helper->__('XML'));
      return parent::_prepareColumns();
    }

    public function getRowUrl($row){
        return $this->getUrl('*/*/index', array(
                'store'=>$this->getRequest()->getParam('store'),
                'id'=>$row->getId()
        ));
    }
    
}