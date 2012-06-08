<?php
class Harapartners_PromotionFactory_Block_Adminhtml_Virtualproductcoupon_Index_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct(){
        parent::__construct();
        $this->setId('virtual-product-coupon-grid');
    }

    protected function _prepareCollection(){
        $coupons = Mage::getModel('promotionfactory/virtualproductcoupon')->getCollection();
        $this->setCollection($coupons);
        parent::_prepareCollection();
        return $this;
    }

    protected function _getStore(){
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    protected function _prepareColumns(){        
        $this->addColumn('entity_id', array(
            'header'        => Mage::helper('promotionfactory')->__('ID'),
            'index'         => 'entity_id'
        ));

        $this->addColumn('product_id', array(
            'header'        => Mage::helper('promotionfactory')->__('Product ID'),
            'index'         => 'product_id'
        ));
        
        $this->addColumn('code', array(
            'header'        => Mage::helper('promotionfactory')->__('Code'),
            'index'         => 'code'
        ));
        
       $this->addColumn('created_at', array(
            'header'        => Mage::helper('promotionfactory')->__('Created At'),
            'index'         => 'created_at',
        ));
        
        $this->addColumn('updated_at', array(
            'header'        => Mage::helper('promotionfactory')->__('Updated At'),
            'index'         => 'updated_at',
        ));
        
        $this->addColumn('status', array(
            'header'        => Mage::helper('promotionfactory')->__('Status'),
            'index'         => 'status',
        	'type'			=> 'options',
            'options' => Mage::getModel( 'promotionfactory/virtualproductcoupon' )->toArrayOption()
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row){
    	return "";
//        return $this->getUrl('*/*/edit', array(
//                'store'=>$this->getRequest()->getParam('store'),
//                'id'=>$row->getId()
//        ));
    }
    
}