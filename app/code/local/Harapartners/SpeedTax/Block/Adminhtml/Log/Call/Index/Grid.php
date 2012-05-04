<?php
/*
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User Software Agreement (EULA).
 * It is also available through the world-wide-web at this URL:
 * http://www.harapartners.com/license [^]
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to eula@harapartners.com so we can send you a copy immediately.
 *
 */
class Harapartners_SpeedTax_Block_Adminhtml_Log_Call_Index_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct(){
        parent::__construct();
        $this->setId('SpeedTaxLogGrid');
    }

    protected function _prepareCollection(){
        $model = Mage::getModel('speedtax/log_call');
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
        $this->addColumn('log_id', array(
            'header'        => Mage::helper('speedtax')->__('Log ID'),
            'align'         => 'center',
            'width'         => '50px',
            'index'         => 'log_id'
        ));
        
        $this->addColumn('event', array(
            'header'        => Mage::helper('speedtax')->__('Event'),
            'align'         => 'center',
            'width'         => '50px',
            'index'         => 'event',
               'type'            => 'text'
        ));
     
        $this->addColumn('result_type', array(
            'header'        => Mage::helper('speedtax')->__('Result Type'),
            'align'         => 'center',
            'width'         => '50px',
            'index'         => 'result_type',
            'type'            => 'text'
        ));
        
        $this->addColumn('invoice_num', array(
            'header'        => Mage::helper('speedtax')->__('Invoice Number'),
            'align'         => 'center',
            'width'         => '50px',
            'index'         => 'invoice_num',
            'type'            => 'int'
        ));
        $this->addColumn('gross', array(
            'header'        => Mage::helper('speedtax')->__('Gross'),
            'align'         => 'center',
            'width'         => '50px',
            'index'         => 'gross',
            'type'            => 'int'
        ));
        $this->addColumn('exempt', array(
            'header'        => Mage::helper('speedtax')->__('Exempt'),
            'align'         => 'center',
            'width'         => '50px',
            'index'         => 'exempt',
            'type'            => 'int'
        ));
        $this->addColumn('tax', array(
            'header'        => Mage::helper('speedtax')->__('Tax'),
            'align'         => 'center',
            'width'         => '50px',
            'index'         => 'tax',
            'type'            => 'int'
        ));
        
       $this->addColumn('created_at', array(
            'header'        => Mage::helper('speedtax')->__('Created At'),
            'align'         => 'center',
            'width'         => '150px',
            'index'         => 'created_at',
            'type'          => 'datetime',
            'gmtoffset'     => true
        ));
        
        $this->addExportType('*/*/exportCsv', Mage::helper('speedtax')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('speedtax')->__('XML'));
        
        return parent::_prepareColumns();
    }

    public function getRowUrl($row){
        return $this->getUrl('*/*/edit', array(
                'store'=>$this->getRequest()->getParam('store'),
                'id'=>$row->getId()
        ));
    }
    
}