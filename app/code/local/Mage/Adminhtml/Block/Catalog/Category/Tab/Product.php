<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

/**
 * Product in category grid
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Catalog_Category_Tab_Product extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('catalog_category_products');
        $this->setDefaultSort('entity_id');
        $this->setUseAjax(true);
    }

    public function getCategory()
    {
        return Mage::registry('category');
    }

    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in category flag
        if ($column->getId() == 'in_category') {
            $productIds = $this->_getSelectedProducts();
            if (empty($productIds)) {
                $productIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', array('in'=>$productIds));
            }
            elseif(!empty($productIds)) {
                $this->getCollection()->addFieldToFilter('entity_id', array('nin'=>$productIds));
            }
        }
        else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    protected function _prepareCollection()
    {
        if ($this->getCategory()->getId()) {
            $this->setDefaultFilter(array('in_category'=>1));
        }
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('image') //Harapartners, Jun: include product base image in the grid
            ->addAttributeToSelect('vendor_style') //Totsy, Josh: include vendor style in the grid
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('price')
            ->addAttributeToSelect('type')
            ->addAttributeToSelect('size')
            ->addAttributeToSelect('color')
            ->addStoreFilter($this->getRequest()->getParam('store'))
            ->joinField('position',
                'catalog/category_product',
                'position',
                'product_id=entity_id',
                'category_id='.(int) $this->getRequest()->getParam('id', 0),
                'left');
        $this->setCollection($collection);

        if ($this->getCategory()->getProductsReadonly()) {
            $productIds = $this->_getSelectedProducts();
            if (empty($productIds)) {
                $productIds = 0;
            }
            $this->getCollection()->addFieldToFilter('entity_id', array('in'=>$productIds));
        }

        return parent::_prepareCollection();
    }

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
        
        if (!$this->getCategory()->getProductsReadonly()) {
            $this->addColumn('in_category', array(
                'header_css_class' => 'a-center',
                'type'      => 'checkbox',
                'name'      => 'in_category',
                'values'    => $this->_getSelectedProducts(),
                'align'     => 'center',
                'index'     => 'entity_id'
            ));
        }
        $this->addColumn('entity_id', array(
            'header'    => Mage::helper('catalog')->__('ID'),
            'sortable'  => true,
            'width'     => '60',
            'index'     => 'entity_id'
        ));
        $this->addColumn('name', array(
            'header'    => Mage::helper('catalog')->__('Name'),
            'index'     => 'name'
        ));
        $this->addColumn('type',
            array(
                'header'=> Mage::helper('catalog')->__('Type'),
                'width' => '60px',
                'index' => 'type_id',
                'type'  => 'options',
                'options' => Mage::getSingleton('catalog/product_type')->getOptionArray(),
        ));
        $this->addColumn('size', array(
            'header'    => Mage::helper('catalog')->__('Size'),
            'index'     => 'size',
            'type'        => 'options',
            'options'    => $sizeArray
        ));
        $this->addColumn('color', array(
            'header'    => Mage::helper('catalog')->__('Color'),
            'index'     => 'color',
            'type'        => 'options',
            'options'    => $colorArray
        ));
        //Harapartners, Yang: include product edit link in the grid
        $this->addColumn('edit', array(
            'header'    => Mage::helper('catalog')->__('Edit'),
            'width'     => '1',
            'sortable'  => false,
            'renderer'    => 'Harapartners_Service_Block_Adminhtml_Widget_Grid_Column_Renderer_Category_Edit'
        ));
        //Harapartners, Jun: include product base image in the grid
        $this->addColumn('image', array(
            'header'    => Mage::helper('catalog')->__('Image'),
            'sortable'  => false,
            'width'     => '120px',
            'renderer'    => 'Harapartners_Service_Block_Adminhtml_Widget_Grid_Column_Renderer_Product_Image'
        ));
        //Totsy, Josh: include vendor style in the grid
        $this->addColumn('vendor_style', array(
            'header'    => Mage::helper('catalog')->__('Vendor Style'),
            'index' => 'vendor_style',
        ));
        
        $this->addColumn('sku', array(
            'header'    => Mage::helper('catalog')->__('SKU'),
            'width'     => '80',
            'index'     => 'sku'
        ));
        $this->addColumn('price', array(
            'header'    => Mage::helper('catalog')->__('Price'),
            'type'  => 'currency',
            'width'     => '1',
            'currency_code' => (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE),
            'index'     => 'price'
        ));
        $this->addColumn('position', array(
            'header'    => Mage::helper('catalog')->__('Position'),
            'width'     => '1',
            'type'      => 'number',
            'editable'  => !$this->getCategory()->getProductsReadonly(),
            'index' => 'position'
            //'renderer'  => 'adminhtml/widget_grid_column_renderer_input'
        ));


        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

    protected function _getSelectedProducts()
    {
        $products = $this->getRequest()->getPost('selected_products');
        if (is_null($products)) {
            $products = $this->getCategory()->getProductsPosition();
            return array_keys($products);
        }
        return $products;
    }

    public function getAdditionalJavaScript ()
    {
        /**
         *  The row click event was used to select/deselect rows which was leading to products accidentally being removed
         *  from categories. The code below will disable the row click event on this particular grid.  See MGN-759.
         */
        return "\n\nthis.rows = $$('#catalog_category_products_table tbody tr');\n"
            . "for (var row=0; row<this.rows.length; row++) {\n"
            . "Event.stopObserving(this.rows[row],'mouseover');\n"
            . "Event.stopObserving(this.rows[row],'mouseout');\n"
            . "Event.stopObserving(this.rows[row],'click');\n"
            . "}\n";
    }
    
}

