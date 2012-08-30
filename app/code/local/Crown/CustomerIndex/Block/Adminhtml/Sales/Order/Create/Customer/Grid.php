<?php
class Crown_CustomerIndex_Block_Adminhtml_Sales_Order_Create_Customer_Grid extends Mage_Adminhtml_Block_Sales_Order_Create_Customer_Grid {
	/**
	 * (non-PHPdoc)
	 * @see Mage_Adminhtml_Block_Sales_Order_Create_Customer_Grid::_prepareCollection()
	 */
	protected function _prepareCollection() {
		$collection = Mage::getResourceModel ( 'CustomerIndex/CustomerIndex_Collection' );
		$this->setCollection ( $collection );
		return Mage_Adminhtml_Block_Widget_Grid::_prepareCollection ();
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Mage_Adminhtml_Block_Widget_Grid::_prepareColumns()
	 */
	protected function _prepareColumns()
    {
        $this->addColumn('entity_id', array(
            'header'    =>Mage::helper('sales')->__('ID'),
            'width'     =>'50px',
            'index'     =>'entity_id',
            'align'     => 'right',
        ));
        $this->addColumn('customer_name', array(
            'header'    =>Mage::helper('sales')->__('Name'),
            'index'     =>'customer_name'
        ));
        $this->addColumn('email', array(
            'header'    =>Mage::helper('sales')->__('Email'),
            'width'     =>'150px',
            'index'     =>'email'
        ));
        $this->addColumn('Telephone', array(
            'header'    =>Mage::helper('sales')->__('Telephone'),
            'width'     =>'100px',
            'index'     =>'billing_telephone'
        ));
        $this->addColumn('billing_postcode', array(
            'header'    =>Mage::helper('sales')->__('ZIP/Post Code'),
            'width'     =>'120px',
            'index'     =>'billing_postcode',
        ));
        $this->addColumn('billing_country_id', array(
            'header'    =>Mage::helper('sales')->__('Country'),
            'width'     =>'100px',
            'type'      =>'country',
            'index'     =>'billing_country_id',
        ));
        $this->addColumn('billing_region', array(
            'header'    =>Mage::helper('sales')->__('State/Province'),
            'width'     =>'100px',
            'index'     =>'billing_region',
        ));
        
    	if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('website_id', array(
                'header'    => Mage::helper('customer')->__('Website'),
                'align'     => 'center',
                'width'     => '80px',
                'type'      => 'options',
                'options'   => Mage::getSingleton('adminhtml/system_store')->getWebsiteOptionHash(true),
                'index'     => 'website_id',
            ));
        }

        return Mage_Adminhtml_Block_Widget_Grid::_prepareColumns();
    }
}