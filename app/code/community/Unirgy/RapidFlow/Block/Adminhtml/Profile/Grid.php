<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    Unirgy_RapidFlow
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

class Unirgy_RapidFlow_Block_Adminhtml_Profile_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('profileGrid');
        $this->setDefaultSort('profile_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInProfile(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('urapidflow/profile')->getCollection();
        /*
        $collection->getSelect()->columns(array(
            'rows_status'=>new Zend_Db_Expr("concat(rows_processed,' / ',rows_errors)", 'main_table')
        ));
        */
        $this->setCollection($collection);
        if (Mage::helper('urapidflow')->hasEeGwsFilter()) {
            $collection->addFieldToFilter('store_id', array('in'=>Mage::helper('urapidflow')->getEeGwsStoreIds()));
        }
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $hlp = Mage::helper('urapidflow');
        $source = Mage::getSingleton('urapidflow/source');

        $this->addColumn('profile_id', array(
            'header'    => $this->__('ID'),
            'align'     =>'right',
            'width'     => '50px',
            'index'     => 'profile_id',
            'type'      => 'number',
        ));

        $this->addColumn('title', array(
            'header'    => $this->__('Title'),
            'align'     =>'left',
            'index'     => 'title',
        ));

        /*
        $this->addColumn('content', array(
            'header'    => Mage::helper('urapidflow')->__('Item Content'),
            'width'     => '150px',
            'index'     => 'content',
        ));
        */


        $this->addColumn('started_at', array(
            'header'    => $this->__('Last Run'),
            'align'     => 'left',
            'width'     => '130px',
            'index'     => 'started_at',
            'type'      => 'datetime',
        ));

        $this->addColumn('rows_processed', array(
            'header'    => $this->__('Rows'),
            'align'     => 'left',
            'width'     => '60px',
            'filter'    => false,
            'index'     => 'rows_processed',
        ));

        $this->addColumn('rows_errors', array(
            'header'    => $this->__('Errors'),
            'align'     => 'left',
            'width'     => '60px',
            'filter'    => false,
            'index'     => 'rows_errors',
        ));
/*
        $this->addColumn('scheduled_at', array(
            'header'    => $this->__('Next Schedule'),
            'align'     => 'left',
            'width'     => '130px',
            'index'     => 'scheduled_at',
            'type'      => 'datetime',
        ));
*/
        $this->addColumn('profile_status', array(
            'header'    => $this->__('Status'),
            'align'     => 'left',
            'width'     => '80px',
            'index'     => 'profile_status',
            'type'      => 'options',
            'options'   => $source->setPath('profile_status')->toOptionHash(),
            'renderer'  => 'urapidflow/adminhtml_profile_grid_status',
        ));

        $this->addColumn('run_status', array(
            'header'    => $this->__('Activity'),
            'align'     => 'left',
            'width'     => '80px',
            'index'     => 'run_status',
            'type'      => 'options',
            'options'   => $source->setPath('run_status')->toOptionHash(),
            'renderer'  => 'urapidflow/adminhtml_profile_grid_status',
        ));
/*
        $this->addColumn('invoke_status', array(
            'header'    => $this->__('Invoke Status'),
            'align'     => 'left',
            'width'     => '80px',
            'index'     => 'invoke_status',
            'type'      => 'options',
            'options'   => $source->setPath('invoke_status')->toOptionHash(),
            'renderer'  => 'urapidflow/adminhtml_profile_grid_status',
        ));
*/
        $this->addColumn('data_type', array(
            'header'    => $this->__('Data Type'),
            'align'     => 'left',
            'index'     => 'data_type',
            'type'      => 'options',
            'options'   => $source->setPath('data_type')->toOptionHash(),
        ));

        $this->addColumn('profile_type', array(
            'header'    => $this->__('Profile Type'),
            'align'     => 'left',
            'index'     => 'profile_type',
            'type'      => 'options',
            'options'   => $source->setPath('profile_type')->toOptionHash(),
        ));

/*
        $this->addColumn('action', array(
            'header'    =>  $this->__('Action'),
            'width'     => '100',
            'type'      => 'action',
            'getter'    => 'getId',
            'actions'   => array(
                array(
                    'caption'   => $this->__('Edit'),
                    'url'       => array('base'=> '* /* /edit'),
                    'field'     => 'id'
                )
            ),
            'filter'    => false,
            'sortable'  => false,
            'index'     => 'stores',
            'is_system' => true,
        ));
*/
        //$this->addExportType('*/*/exportCsv', Mage::helper('urapidflow')->__('CSV'));
        //$this->addExportType('*/*/exportXml', Mage::helper('urapidflow')->__('XML'));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        if (Mage::getStoreConfig('urapidflow/advanced/disable_changes')) {
            return;
        }

        $hlp = Mage::helper('urapidflow');

        $this->setMassactionIdField('profile_id');
        $this->getMassactionBlock()->setFormFieldName('profiles');

        $this->getMassactionBlock()->addItem('delete', array(
            'label'    => $this->__('Delete'),
            'url'      => $this->getUrl('*/*/massDelete'),
            'confirm'  => $this->__('Are you sure?')
        ));

        $this->getMassactionBlock()->addItem('profile_status', array(
            'label'=> $this->__('Change status'),
            'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
            'additional' => array(
                'status' => array(
                    'name' => 'status',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => $this->__('Status'),
                    'values' => Mage::getSingleton('urapidflow/source')->setPath('profile_status')->toOptionHash()
                )
            )
        ));
        return $this;
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

}