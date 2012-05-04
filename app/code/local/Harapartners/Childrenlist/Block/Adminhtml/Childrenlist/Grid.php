<?php

class Harapartners_Childrenlist_Block_Adminhtml_Childrenlist_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('childlist');
        $this->setDefaultSort('created_at');
    }

    protected function _prepareCollection()
    {
        $model = Mage::getModel('childrenlist/child');
        $this->setCustomerId($this->getRequest()->getParam('id', false));
        $collection = $model->getCollection()->addFieldtoFilter('customer_id',$this->getCustomerId());
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {

        $this->addColumn('child_id', array(
            'header'        => Mage::helper('childrenlist')->__('ID'),
            'align'         => 'right',
            'width'         => '50px',
            'index'         => 'child_id',
        ));

        $this->addColumn('created_at', array(
            'header'        => Mage::helper('childrenlist')->__('Created On'),
            'align'         => 'left',
            'type'          => 'datetime',
            'width'         => '100px',
            'index'         => 'created_at',
        ));


        $this->addColumn('child_name', array(
            'header'        => Mage::helper('childrenlist')->__('Child Name'),
            'align'         => 'left',
            'width'         => '100px',
            'index'         => 'child_name',
            'type'          => 'text',
            'truncate'      => 50,
            'escape'        => true,
        ));

        $this->addColumn('child_gender', array(
            'header'        => Mage::helper('childrenlist')->__('Child Gender'),
            'align'         => 'left',
            'width'         => '50px',
            'index'         => 'child_gender',
            'type'          => 'options',
            'options'    => Harapartners_Childrenlist_Model_Child::getChildGenderLabels(),
            'truncate'      => 50,
            'escape'        => true,
        ));

        $this->addColumn('child_customer_relationship', array(
            'header'        => Mage::helper('childrenlist')->__('Relationship'),
            'align'         => 'left',
            'width'         => '50px',
            'index'         => 'child_customer_relationship',
            'type'          => 'options',
            'options'    => Harapartners_Childrenlist_Model_Child::getChildRelationshipLabels(),
            'truncate'      => 50,
            'escape'        => true,
        ));
        
        $this->addColumn('child_birthday', array(
            'header'        => Mage::helper('childrenlist')->__('Child Birthday'),
            'align'         => 'left',
            'type'          => 'date',
            'width'         => '100px',
            'index'         => 'child_birthday',
        ));
        
        $this->addColumn('additional_data', array(
            'header'        => Mage::helper('childrenlist')->__('Additional Data'),
            'align'         => 'left',
            'index'         => 'additional_data',
            'type'          => 'text',
            'truncate'      => 50,
            'nl2br'         => true,
            'escape'        => true,
        ));


        $this->addColumn('action',
            array(
                'header'    => Mage::helper('adminhtml')->__('Action'),
                'width'     => '50px',
                'type'      => 'action',
                'getter'     => 'getChildId',
                'actions'   => array(
                    array(
                        'caption' => Mage::helper('adminhtml')->__('Edit'),
                        /*'url'     => $this->getUrl('childrenlist/adminhtml_childedit/edit',array('id'=>1,'customerId' => 12)),
                        'field'   => 'child_id'*/
                        'url' =>array(
                                'base'=>'childrenlist/adminhtml_childedit/edit'
                             ),
                        'field'   => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false
        ));

        return parent::_prepareColumns();
    }
    
    public function getRowUrl($row)
    {
        return $this->getUrl('childrenlist/adminhtml_childedit/edit', array(
            'id' => $row->getChildId(),
            'customerId' => $this->getCustomerId(),
        ));
    }
}
