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
class Harapartners_Dropshipfactory_Block_Adminhtml_Dropship_Index_Grid extends Mage_Adminhtml_Block_Widget_Grid {
	
	
    public function __construct(){
        parent::__construct();
        $this->setId('dropshipGrid');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
    }

    protected function _prepareCollection(){
        $collection = Mage::getModel('sales/order_item')->getCollection();
        
        $order_table = Mage::getSingleton('core/resource')->getTableName('sales_flat_order');
        $catalog_product_entity_int_table = Mage::getSingleton('core/resource')->getTableName('catalog_product_entity_int');
        $catalog_product_entity_varchar_table = Mage::getSingleton('core/resource')->getTableName('catalog_product_entity_varchar');
        
        $vendorAttributeId = Mage::helper('dropshipfactory')->getVendorAttributeId();
        $vendorStyleAttributeId = Mage::helper('dropshipfactory')->getVendorStyleAttributeId();
        $fulfillmentTypeAttributeId = Mage::helper('dropshipfactory')->getFulfillmentTypeAttributeId();
        
        $collection->getSelect()->join($order_table, 'order_id=' . $order_table . '.entity_id', $order_table. '.increment_id')
        						->join($catalog_product_entity_int_table, 'product_id=' . $catalog_product_entity_int_table . '.entity_id', $catalog_product_entity_int_table . '.value')
        						->join($catalog_product_entity_varchar_table, 'product_id=' . $catalog_product_entity_varchar_table . '.entity_id', null)
        						->where($order_table . '.state="' . Mage_Sales_Model_Order::STATE_NEW . '" AND ' .
        								$catalog_product_entity_int_table . '.attribute_id=' . $vendorAttributeId . ' AND ' .
        								$catalog_product_entity_varchar_table . '.attribute_id=' . $fulfillmentTypeAttributeId . ' AND ' .
        								$catalog_product_entity_varchar_table . '.value="' . Harapartners_Ordersplit_Helper_Data::TYPE_DROPSHIP . '"');
		       						
		$this->setCollection($collection);
		parent::_prepareCollection();
       
        return $this;
    }

    protected function _prepareColumns(){
		$item_table = 'main_table';
		$catalog_product_entity_int_table = Mage::getSingleton('core/resource')->getTableName('catalog_product_entity_int');
    	
        $this->addColumn('item_id', array(
            'header'        => Mage::helper('dropshipfactory')->__('Item ID'),
            'align'         => 'right',
            'width'         => '30px',
            'index'         => 'item_id'
        ));
        
        $this->addColumn('increment_id', array(
            'header'        => Mage::helper('dropshipfactory')->__('Order ID'),
            'align'         => 'right',
            'width'         => '30px',
            'index'         => 'increment_id'
        ));
        
        $this->addColumn('name', array(
            'header'        => Mage::helper('dropshipfactory')->__('Name'),
            'align'         => 'right',
            'width'         => '30px',
            'index'         => 'name'
        ));
        
        $this->addColumn('sku', array(
            'header'        => Mage::helper('dropshipfactory')->__('Sku'),
            'align'         => 'right',
            'width'         => '30px',
            'index'         => 'sku'
        ));
        
//        $this->addColumn('value', array(
//            'header'        => Mage::helper('dropshipfactory')->__('vendor'),
//            'align'         => 'right',
//            'width'         => '30px',
//        	'type'			=> 'options',
//        	'options'		=> Mage::helper('dropshipfactory')->getVendorList(),
//            'index'         => 'value',
//        	'filter_index'	=> $catalog_product_entity_int_table . '.value'
//        ));

        $this->addColumn('value', array(
            'header'        => Mage::helper('dropshipfactory')->__('vendor'),
            'align'         => 'right',
            'width'         => '30px',
        	'type'			=> 'text',
            'index'         => 'value',
        	'filter_index'	=> $catalog_product_entity_int_table . '.value'
        ));
        
        $this->addColumn('qty_ordered', array(
            'header'        => Mage::helper('dropshipfactory')->__('Quantity'),
            'align'         => 'right',
            'width'         => '30px',
            'index'         => 'qty_ordered'
        ));

        $this->addColumn('created_at', array(
            'header'        => Mage::helper('dropshipfactory')->__('Created At'),
            'align'         => 'center',
            'width'         => '150px',
            'index'         => 'created_at',
            'filter_index'  => $item_table . '.created_at',
        	'type'      	=> 'datetime',
            'gmtoffset' 	=> true
        ));
        
        $this->addColumn('updated_at', array(
            'header'        => Mage::helper('dropshipfactory')->__('Updated At'),
            'align'         => 'center',
            'width'         => '150px',
            'index'         => 'updated_at',
            'filter_index'  => $item_table . '.updated_at',
        	'type'      	=> 'datetime',
            'gmtoffset' 	=> true
        ));
        
        $this->addExportType('*/*/exportCSV', Mage::helper('dropshipfactory')->__('CSV'));

        return parent::_prepareColumns();
    }
    
    /**
     * @override
     *
     */
    public function getCsv() {
    	$this->_isExport = true;
    	$this->_prepareGrid();
    	$collection = $this->getCollection();
    	$collection->getSelect()->limit();
        $collection->setPageSize(0);
        $collection->load();
        $this->_afterLoadCollection();
        
        $header = Mage::getModel('dropshipfactory/service')->getCSVHeader();
        $csv = implode(',', $header) . "\n";
        
        foreach($collection as $row) {
        	$order = Mage::getModel('sales/order')->loadByIncrementId($row->getData('increment_id'));
        	
        	$data = array();
        	
        	$data[] = $row->getData('item_id');
        	$data[] = $row->getData('name');
        	$data[] = $row->getData('sku');
        	$data[] = $row->getData('increment_id');
        	$data[] = $order->getCreatedAt();
        	
       		$product = Mage::getModel('catalog/product')->load($row->getData('product_id'));
        	
        	$data[] = $product->getAttributeText('vendor_code');
        	
        	//customer information
        	$data[] = $order->getCustomerFirstname() . " " . $order->getCustomerLastname();
			
			$billingAddress = $order->getBillingAddress();
			$data[] = (isset($billingAddress)) ? $billingAddress->getStreet(1) . ' ' . $billingAddress->getStreet(2) : '';
			$data[] = (isset($billingAddress)) ? $billingAddress->getCity() : '';
			$data[] = (isset($billingAddress)) ? $billingAddress->getRegion() : '';
			$data[] = (isset($billingAddress)) ? $billingAddress->getPostcode() : '';
			$data[] = (isset($billingAddress)) ? $billingAddress->getTelephone() : '';
			$data[] = $order->getCustomerEmail();
			
			//shipment information
			$shipment = $order->getShippingAddress();
			
			$data[] = (isset($shipment)) ? $shipment->getFirstname() . ' ' . $shipment->getLastname() : '';
			$data[] = (isset($shipment)) ? $shipment->getStreet(1) . ' ' . $shipment->getStreet(2) : '';
			$data[] = (isset($shipment)) ? $shipment->getCity() : '';
			$data[] = (isset($shipment)) ? $shipment->getRegion() : '';
			$data[] = (isset($shipment)) ? $shipment->getPostcode() : '';
        	
        	$data[] = $row->getData('price');
        	$data[] = $row->getData('tax_amount');
        	$data[] = $row->getData('qty_ordered');
			$data[] = $order->getShippingMethod();
			$data[] = $order->getShippingAmount();
        	
        	$csv.= implode(',', $data) . "\n";
        }
        
        return $csv;
    }
}