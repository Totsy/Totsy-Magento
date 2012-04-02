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
class Harapartners_Fulfillmentfactory_Block_Adminhtml_Itemqueue_Index_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct(){
        parent::__construct();
        $this->setId('itemqueueGrid');
        $this->setDefaultSort('created_at');	//sort by created_at desc
        $this->setDefaultDir('DESC');
    }

    protected function _prepareCollection(){
        $model = Mage::getModel('fulfillmentfactory/itemqueue');
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
        $this->addColumn('itemqueue_id', array(
            'header'        => Mage::helper('fulfillmentfactory')->__('Item Queue ID'),
            'align'         => 'right',
            'width'         => '30px',
            'index'         => 'itemqueue_id'
        ));
        
        $this->addColumn('order_item_id', array(
            'header'        => Mage::helper('fulfillmentfactory')->__('Order Item ID'),
            'align'         => 'right',
            'width'         => '30px',
            'index'         => 'order_item_id'
        ));
        
        $this->addColumn('order_id', array(
            'header'        => Mage::helper('fulfillmentfactory')->__('Order ID'),
            'align'         => 'right',
            'width'         => '30px',
            'index'         => 'order_id'
        ));
        
        $this->addColumn('order_increment_id', array(
            'header'        => Mage::helper('fulfillmentfactory')->__('Order Increment ID'),
            'align'         => 'right',
            'width'         => '50px',
            'index'         => 'order_increment_id'
        ));
        
        $this->addColumn('store_id', array(
            'header'        => Mage::helper('fulfillmentfactory')->__('Store ID'),
            'align'         => 'right',
            'width'         => '30px',
            'index'         => 'store_id'
        ));
        
        $this->addColumn('product_id', array(
            'header'        => Mage::helper('fulfillmentfactory')->__('Product ID'),
            'align'         => 'right',
            'width'         => '30px',
            'index'         => 'product_id'
        ));
        
        $this->addColumn('name', array(
            'header'        => Mage::helper('fulfillmentfactory')->__('Name'),
            'align'         => 'right',
            'width'         => '150px',
            'index'         => 'name'
        ));
        
        $this->addColumn('sku', array(
            'header'        => Mage::helper('fulfillmentfactory')->__('SKU'),
            'align'         => 'right',
            'width'         => '100px',
            'index'         => 'sku'
        ));
        
        $this->addColumn('qty_ordered', array(
            'header'        => Mage::helper('fulfillmentfactory')->__('Order Item Quantity'),
            'align'         => 'right',
            'width'         => '30px',
            'index'         => 'qty_ordered'
        ));
        
        $this->addColumn('fulfill_count', array(
            'header'        => Mage::helper('fulfillmentfactory')->__('Fulfill Count'),
            'align'         => 'right',
            'width'         => '30px',
            'index'         => 'fulfill_count'
        ));
        
        $this->addColumn('status', array(
            'header'        => Mage::helper('fulfillmentfactory')->__('Status'),
            'align'         => 'right',
            'width'         => '100px',
            'index'         => 'status',
        	'type'			=> 'options',
        	'options'		=> Mage::helper('fulfillmentfactory')->getItemqueueStatusGridOptionList(),
            'renderer'		=> 'Harapartners_Fulfillmentfactory_Block_Adminhtml_Itemqueue_Index_Renderer_Status',
        ));
        
        $this->addColumn('created_at', array(
            'header'        => Mage::helper('fulfillmentfactory')->__('Created At'),
            'align'         => 'center',
            'width'         => '150px',
            'index'         => 'created_at',
        	'type'      	=> 'datetime',
            'gmtoffset' 	=> true
        ));
        
        $this->addColumn('updated_at', array(
            'header'        => Mage::helper('fulfillmentfactory')->__('Updated At'),
            'align'         => 'center',
            'width'         => '150px',
            'index'         => 'updated_at',
        	'type'      	=> 'datetime',
            'gmtoffset' 	=> true
        ));

        return parent::_prepareColumns();
    }
    
	protected function _prepareMassaction()
    {
        $this->setMassactionIdField('itemqueue_id');
        $this->getMassactionBlock()->setFormFieldName('itemqueue_id');
        $this->getMassactionBlock()->setUseSelectAll(false);

        //batch cancel function
        $this->getMassactionBlock()->addItem('batch_cancel', array(
             'label'=> Mage::helper('fulfillmentfactory')->__('Batch Cancel'),
             'url'  => $this->getUrl('*/*/batchCancel'),
             'confirm' => Mage::helper('fulfillmentfactory')->__('Are you sure?')
        ));

        return $this;
    }

    public function getRowUrl($row){
        return $this->getUrl('*/*/edit', array(
	            'store'=>$this->getRequest()->getParam('store'),
	            'id'=>$row->getId()
        ));
    }
}