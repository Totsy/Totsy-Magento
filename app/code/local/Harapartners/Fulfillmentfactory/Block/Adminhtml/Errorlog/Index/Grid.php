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
class Harapartners_Fulfillmentfactory_Block_Adminhtml_Errorlog_Index_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct(){
        parent::__construct();
        $this->setId('fulfillmentErrorLogGrid');
        $this->setDefaultSort('created_at');	//sort by created_at desc
        $this->setDefaultDir('DESC');
    }

    protected function _prepareCollection(){
        $model = Mage::getModel('fulfillmentfactory/errorlog');
        
        $order_table = Mage::getSingleton('core/resource')->getTableName('sales_flat_order');
        
        $collection = $model->getCollection();
        $collection->getSelect()
        		   ->join($order_table, 'order_id=' . $order_table . '.entity_id', $order_table. '.increment_id');
        
		$this->setCollection($collection);
        parent::_prepareCollection();
        
        return $this;
    }

    protected function _getStore(){
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    protected function _prepareColumns(){
        $this->addColumn('entity_id', array(
            'header'        => Mage::helper('fulfillmentfactory')->__('ID'),
            'align'         => 'right',
            'width'         => '30px',
            'index'         => 'entity_id'
        ));
        
//        $this->addColumn('order_id', array(
//            'header'        => Mage::helper('fulfillmentfactory')->__('Order ID'),
//            'align'         => 'right',
//            'width'         => '30px',
//            'index'         => 'order_id'
//        ));
        
        $this->addColumn('increment_id', array(
            'header'        => Mage::helper('fulfillmentfactory')->__('Order #'),
            'align'         => 'right',
            'width'         => '30px',
            'index'         => 'increment_id'
        ));
        
//        $this->addColumn('store_id', array(
//            'header'        => Mage::helper('fulfillmentfactory')->__('Store ID'),
//            'align'         => 'right',
//            'width'         => '30px',
//            'index'         => 'store_id'
//        ));
        
        $this->addColumn('message', array(
            'header'        => Mage::helper('fulfillmentfactory')->__('Error Message'),
            'align'         => 'left',
            'width'         => '200px',
            'index'         => 'message'
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
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('entity_id');
        $this->getMassactionBlock()->setUseSelectAll(false);

        //batch cancel function
        $this->getMassactionBlock()->addItem('refulfill', array(
             'label'=> Mage::helper('fulfillmentfactory')->__('Fulfill Again'),
             'url'  => $this->getUrl('*/*/refulfill'),
             'confirm' => Mage::helper('fulfillmentfactory')->__('Are you sure?')
        ));

        return $this;
    }
}