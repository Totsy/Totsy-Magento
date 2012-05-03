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
class Harapartners_Fulfillmentfactory_Block_Adminhtml_Orderqueue_Index_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct(){
        parent::__construct();
        $this->setId('OrderQueueGrid');
        $this->setDefaultSort('updated_at');    //sort by created_at desc
        $this->setDefaultDir('DESC');
    }

    protected function _prepareCollection(){
        $status = $this->getRequest()->getParam('custom_status');
        
        $collection = Mage::getResourceModel('sales/order_grid_collection');
        $collection->addAttributeToFilter('status', $status);
        $this->setCollection($collection);
        
        parent::_prepareCollection();
        
        return $this;
    }

    protected function _getStore(){
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    protected function _prepareColumns(){
        $this->addColumn('increment_id', array(
            'header'        => Mage::helper('fulfillmentfactory')->__('Order #'),
            'align'         => 'right',
            'width'         => '100px',
            'type'          => 'text',
            'index'         => 'increment_id',
        ));
        
        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header'    => Mage::helper('sales')->__('Purchased From (Store)'),
                'index'     => 'store_id',
                'type'      => 'store',
                'width'        => '30px',
                'store_view'=> true,
                'display_deleted' => true,
            ));
        }
        
        $this->addColumn('created_at', array(
            'header' => Mage::helper('sales')->__('Purchased On'),
            'align'  => 'right',
            'index' => 'created_at',
            'type' => 'datetime',
            'width' => '200px',
        ));
        
        $this->addColumn('billing_name', array(
            'header' => Mage::helper('sales')->__('Bill to Name'),
            'align'    => 'right',
            'index' => 'billing_name',
        ));

        $this->addColumn('shipping_name', array(
            'header' => Mage::helper('sales')->__('Ship to Name'),
            'align'         => 'right',
            'index' => 'shipping_name',
        ));

        $this->addColumn('base_grand_total', array(
            'header' => Mage::helper('sales')->__('G.T. (Base)'),
            'align'    => 'right',
            'index' => 'base_grand_total',
            'type'  => 'currency',
            'currency' => 'base_currency_code',
        ));

        $this->addColumn('grand_total', array(
            'header' => Mage::helper('sales')->__('G.T. (Purchased)'),
            'align'    => 'right',
            'index' => 'grand_total',
            'type'  => 'currency',
            'currency' => 'order_currency_code',
        ));
        
        $this->addColumn('status', array(
            'header'        => Mage::helper('fulfillmentfactory')->__('Status'),
            'align'         => 'right',
            'width'         => '50px',
            'index'         => 'status'
        ));
        
        $this->addColumn('updated_at', array(
            'header'        => Mage::helper('fulfillmentfactory')->__('Updated At'),
            'align'         => 'right',
            'width'         => '200px',
            'index'         => 'updated_at',
            'type'          => 'datetime',
            'gmtoffset'     => true
        ));

        return parent::_prepareColumns();
    }
    
    public function getRowUrl($row) {
        //Mage::getModel('adminhtml/url')->getUrl('adminhtml/sales_order/view')
        return $this->getUrl('adminhtml/sales_order/view', array(
                'order_id'=>$row->getId()
        ));
    }
}