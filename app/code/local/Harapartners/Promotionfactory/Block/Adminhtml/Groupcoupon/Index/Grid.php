<?php
class Harapartners_PromotionFactory_Block_Adminhtml_Groupcoupon_Index_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct(){
        parent::__construct();
        $this->setId('groupcouponPromotionGrid');
    }

    protected function _prepareCollection(){
        $model = Mage::getModel('salesrule/rule');
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
        $this->addColumn('rule_id', array(
            'header'        => Mage::helper('promotionfactory')->__('Rule ID'),
            'align'         => 'right',
            'width'         => '50px',
            'index'         => 'rule_id'
        ));

        $this->addColumn('name', array(
            'header'        => Mage::helper('promotionfactory')->__('Name'),
            'align'         => 'right',
            'width'         => '150px',
            'index'         => 'name'
        ));
        
        $this->addColumn('code', array(
            'header'        => Mage::helper('promotionfactory')->__('Code'),
            'align'         => 'right',
            'width'         => '150px',
            'index'         => 'code'
        ));
        
        $this->addColumn('is_active', array(
            'header'        => Mage::helper('promotionfactory')->__('Is Active'),
            'align'         => 'right',
            'width'         => '50px',
            'index'         => 'is_active',
        	'type'			=> 'options',
            'options' => array('1'=>'Yes','0'=>'No')
        ));
                
       $this->addColumn('from_date', array(
            'header'        => Mage::helper('promotionfactory')->__('Created At'),
            'align'         => 'center',
            'width'         => '150px',
            'index'         => 'from_date',
        	'type'      	=> 'date',
            'gmtoffset' 	=> true
        ));
        
        $this->addColumn('updated_at', array(
            'header'        => Mage::helper('promotionfactory')->__('Updated At'),
            'align'         => 'center',
            'width'         => '150px',
            'index'         => 'updated_at',
        	'type'      	=> 'date',
            'gmtoffset' 	=> true
        ));
        
        $this->addColumn('group_code_status', array(
            'header'        => Mage::helper('promotionfactory')->__('Group Code status'),
            'align'         => 'right',
            'width'         => '50px',
        	'index'         => 'rule_id',
        	'filter'    => false,
            'sortable'  => false,
            'renderer' 		=> 'Harapartners_Promotionfactory_Block_Adminhtml_Widget_Grid_Column_Renderer_Groupcoupon'
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