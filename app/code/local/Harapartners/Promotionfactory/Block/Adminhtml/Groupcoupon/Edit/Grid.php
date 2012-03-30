<?php
class Harapartners_Promotionfactory_Block_Adminhtml_Groupcoupon_Edit_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    protected $_id = NULL;
	
	public function __construct(){
        parent::__construct();
        $this->setId('groupcouponPromotionEditGrid');
        $this->_id = $this->getRequest ()->getParam ( 'id' );
    }

    protected function _prepareCollection(){
        $model = Mage::getModel('promotionfactory/groupcoupon');
        $collection = $model->getCollection()->addFilter('rule_id', $this->_id);
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
            'header'        => Mage::helper('promotionfactory')->__('ID'),
            'align'         => 'right',
            'width'         => '50px',
            'index'         => 'entity_id'
        ));
        
        $this->addColumn('rule_id', array(
            'header'        => Mage::helper('promotionfactory')->__('Rule ID'),
            'align'         => 'right',
            'width'         => '100px',
            'index'         => 'rule_id'
        ));
        
        $this->addColumn('sudo_code', array(
            'header'        => Mage::helper('promotionfactory')->__('Real Code'),
            'align'         => 'right',
            'width'         => '100px',
            'index'         => 'code'
        )); 

        $this->addColumn('code', array(
            'header'        => Mage::helper('promotionfactory')->__('Code'),
            'align'         => 'right',
            'width'         => '100px',
            'index'         => 'pseudo_code'
        ));       

        $this->addColumn('created_at', array(
            'header'        => Mage::helper('promotionfactory')->__('Created At'),
            'align'         => 'center',
            'width'         => '150px',
            'index'         => 'created_at',
        	'type'      	=> 'datetime',
            'gmtoffset' 	=> true
        ));
        
        $this->addColumn('updated_at', array(
            'header'        => Mage::helper('promotionfactory')->__('Updated At'),
            'align'         => 'center',
            'width'         => '150px',
            'index'         => 'updated_at',
        	'type'      	=> 'datetime',
            'gmtoffset' 	=> true
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row){
        return $this->getUrl('*/*/edit', array(
	            'store'=>$this->getRequest()->getParam('store'),
	            'id'=>$row->getId()
        ));
    }
    
}