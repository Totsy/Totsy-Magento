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

class Harapartners_Promotionfactory_Block_Adminhtml_Virtualproductcoupon_Managecoupon_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct(){
        parent::__construct();
        $this->setId('virtualProductCouponGrid');
    }

    protected function _prepareCollection(){
    	$currentProduct = Mage::registry('manage_coupon_virtual_product');
        $couponCollection = Mage::getModel('promotionfactory/virtualproductcoupon')->getCollection()->loadByProductId( $currentProduct->getId() );
        $this->setCollection($couponCollection);
        parent::_prepareCollection();
        return $this;
    }

    protected function _getStore(){
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

	protected function _prepareColumns(){
		$helper = Mage::helper('promotionfactory');        
        $this->addColumn('entity_id', array(
            'header'        => $helper->__('ID'),
            'index'         => 'entity_id'
        ));

        $this->addColumn('product_id', array(
            'header'        => $helper->__('Product ID'),
            'index'         => 'product_id'
        ));
        
        $this->addColumn('code', array(
            'header'        => $helper->__('Code'),
            'index'         => 'code'
        ));
        
       $this->addColumn('created_at', array(
            'header'        => $helper->__('Created At'),
            'index'         => 'created_at',
        ));
        
        $this->addColumn('updated_at', array(
            'header'        => $helper->__('Updated At'),
            'index'         => 'updated_at',
        ));
        
        $this->addColumn('status', array(
            'header'        => $helper->__('Status'),
            'index'         => 'status',
        	'type'			=> 'options',
            'options' 		=> $helper->getGridStatusArray()
        ));

        $this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV'));
        $this->addExportType('*/*/exportExcel', Mage::helper('sales')->__('Excel XML'));
        
        return parent::_prepareColumns();
    }
    
	protected function _prepareMassaction() {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('coupon_ids');
	
        $this->getMassactionBlock()->addItem('deleteCoupons', array(
        	'label'		=> Mage::helper('promotionfactory')->__('Delete Coupons'),
        	'url'		=>$this->getUrl('*/*/massDeleteCoupons', array('_current'=>true)),
        	'confirm'	=>Mage::helper('promotionfactory')->__('Do you want to delete these coupons?')
        ));

        return $this;
    }

}