<?php
/**
 * @category    Totsy
 * @package     Totsy\Api\Block\Adminhtml\Client\Index
 * @author      Tharsan Bhuvanendran <tbhuvanendran@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

class Totsy_Api_Block_Adminhtml_Client_Index_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('ApiClient');
        $this->setDefaultSort('last_request');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('totsyapi/client')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('totsyapi_client_id', array(
            'header'    =>    Mage::helper('totsyapi')->__('ID'),
            'align'        =>    'right',
            'width'        =>    '50px',
            'index'        =>    'totsyapi_client_id',
        ));

        $this->addColumn('name', array(
            'header'    =>    Mage::helper('totsyapi')->__('Client Name'),
            'align'        =>    'right',
            'width'        =>    '50px',
            'index'        =>    'name',
        ));

        $this->addColumn('contact_info', array(
            'header'    =>    Mage::helper('totsyapi')->__('Primary Contact'),
            'align'        =>    'right',
            'width'        =>    '50px',
            'index'        =>    'contact_info',
        ));

        $this->addColumn('authorization', array(
            'header'    =>    Mage::helper('totsyapi')->__('Authorization Key'),
            'align'        =>    'right',
            'width'        =>    '50px',
            'index'        =>    'authorization',
        ));

        $this->addColumn('last_request', array(
            'header'    =>    Mage::helper('totsyapi')->__('Most Recent Request'),
            'align'        =>    'right',
            'width'        =>    '50px',
            'type'      =>  'datetime',
            'index'        =>    'last_request',
        ));

        $this->addColumn('active', array(
            'header'    =>    Mage::helper('totsyapi')->__('Active'),
            'align'        =>    'right',
            'width'        =>    '50px',
            'index'        =>    'active',
        ));

        return parent::_prepareColumns();
    }

    protected function _getStore()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 1);
        return Mage::app()->getStore($storeId);
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array(
            'store'    => $this->_getStore(),
            'id'     => $row->getId(),
        ));
    }
}