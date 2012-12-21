<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ryan.street
 * Date: 11/7/12
 * Time: 2:45 PM
 * To change this template use File | Settings | File Templates.
 */
class Harapartners_Ordersplit_Block_Sales_Order_Create_Search_Grid extends Mage_Adminhtml_Block_Sales_Order_Create_Search_Grid {

    /**
     * Prepare collection to be displayed in the grid
     *
     * @return Mage_Adminhtml_Block_Sales_Order_Create_Search_Grid
     */
    protected function _prepareCollection()
    {
        $attributes = Mage::getSingleton('catalog/config')->getProductAttributes();
        /* @var $collection Mage_Catalog_Model_Resource_Product_Collection */
        $collection = Mage::getModel('catalog/product')->getCollection();
        $collection
            ->setStore($this->getStore())
            ->addAttributeToSelect($attributes)
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('size')
            ->addAttributeToSelect('qty')
            ->addAttributeToSelect('color')
            ->addStoreFilter()
            ->addAttributeToFilter('type_id', array_keys(
            Mage::getConfig()->getNode('adminhtml/sales/order/create/available_product_types')->asArray()
        ))
            ->addAttributeToSelect('gift_message_available');

        $collection->joinField('qty_available',
            'cataloginventory/stock_item',
            'qty',
            'product_id=entity_id',
            '{{table}}.stock_id=1',
            'left');

        Mage::getSingleton('catalog/product_status')->addSaleableFilterToCollection($collection);

        $this->setCollection($collection);
        if ($this->getCollection()) {

            $this->_preparePage();

            $columnId = $this->getParam($this->getVarNameSort(), $this->_defaultSort);
            $dir      = $this->getParam($this->getVarNameDir(), $this->_defaultDir);
            $filter   = $this->getParam($this->getVarNameFilter(), null);

            if (is_null($filter)) {
                $filter = $this->_defaultFilter;
            }

            if (is_string($filter)) {
                $data = $this->helper('adminhtml')->prepareFilterString($filter);
                $this->_setFilterValues($data);
            }
            else if ($filter && is_array($filter)) {
                $this->_setFilterValues($filter);
            }
            else if(0 !== sizeof($this->_defaultFilter)) {
                $this->_setFilterValues($this->_defaultFilter);
            }

            if (isset($this->_columns[$columnId]) && $this->_columns[$columnId]->getIndex()) {
                $dir = (strtolower($dir)=='desc') ? 'desc' : 'asc';
                $this->_columns[$columnId]->setDir($dir);
                $this->_setCollectionOrder($this->_columns[$columnId]);
            }

            if (!$this->_isExport) {
                $this->getCollection()->load();
                $this->_afterLoadCollection();
            }
        }

        return $this;
    }

    /**
     * Prepare columns
     *
     * @return Mage_Adminhtml_Block_Sales_Order_Create_Search_Grid
     */
    protected function _prepareColumns()
    {
        $productSizeAttr = Mage::getModel('catalog/product')->getResource()->getAttribute('size');
        $productSizeOption = $productSizeAttr->getSource()->getAllOptions();
        $productColorAttr = Mage::getModel('catalog/product')->getResource()->getAttribute('color');
        $productColorOption = $productColorAttr->getSource()->getAllOptions();
        $sizeArray = array();
        $colorArray = array();
        foreach ( $productSizeOption as $option ){
            $sizeArray[$option['value']] = $option['label'];
        }
        foreach ( $productColorOption as $option ){
            $colorArray[$option['value']] = $option['label'];
        }
        $this->addColumn('entity_id', array(
            'header'    => Mage::helper('sales')->__('ID'),
            'sortable'  => true,
            'width'     => '60',
            'index'     => 'entity_id'
        ));
        $this->addColumn('name', array(
            'header'    => Mage::helper('sales')->__('Product Name'),
            'renderer'  => 'adminhtml/sales_order_create_search_grid_renderer_product',
            'index'     => 'name'
        ));
        $this->addColumn('sku', array(
            'header'    => Mage::helper('sales')->__('SKU'),
            'width'     => '80',
            'index'     => 'sku'
        ));

        $this->addColumn('qty_available',
            array(
                'header'=> Mage::helper('catalog')->__('Qty'),
                'width' => '100px',
                'type'  => 'number',
                'index' => 'qty_available',
        ));

        $this->addColumn('size',
            array(
                'header'=> Mage::helper('catalog')->__('Size'),
                'width' => '100px',
                'type'  => 'options',
                'index' => 'size',
                'options'    => $sizeArray
            ));

        $this->addColumn('color',
            array(
                'header'=> Mage::helper('catalog')->__('Color'),
                'width' => '100px',
                'type'  => 'options',
                'index' => 'color',
                'options'    => $colorArray
            ));

        $this->addColumn('fulfillment', array(
                'filter'    => false,
                'header' => Mage::helper('sales')->__('Fulfillment Type'),
                'renderer' => 'adminhtml/sales_order_create_search_grid_renderer_fulfillment',
                'index' => 'fulfillment',
                'width' => '80'
            )
        );

        $this->addColumn('price', array(
            'header'    => Mage::helper('sales')->__('Price'),
            'column_css_class' => 'price',
            'align'     => 'center',
            'type'      => 'currency',
            'currency_code' => $this->getStore()->getCurrentCurrencyCode(),
            'rate'      => $this->getStore()->getBaseCurrency()->getRate($this->getStore()->getCurrentCurrencyCode()),
            'index'     => 'price',
            'renderer'  => 'adminhtml/sales_order_create_search_grid_renderer_price',
        ));

        $this->addColumn('in_products', array(
            'header'    => Mage::helper('sales')->__('Select'),
            'header_css_class' => 'a-center',
            'type'      => 'checkbox',
            'name'      => 'in_products',
            'values'    => $this->_getSelectedProducts(),
            'align'     => 'center',
            'index'     => 'entity_id',
            'sortable'  => false,
        ));

        $this->addColumn('qty', array(
            'filter'    => false,
            'sortable'  => false,
            'header'    => Mage::helper('sales')->__('Qty To Add'),
            'renderer'  => 'adminhtml/sales_order_create_search_grid_renderer_qty',
            'name'      => 'qty',
            'inline_css'=> 'qty',
            'align'     => 'center',
            'type'      => 'input',
            'validate_class' => 'validate-number',
            'index'     => 'qty',
            'width'     => '1',
        ));

        return parent::_prepareColumns();
    }
}
