<?php
class Harapartners_PromotionFactory_Block_Adminhtml_Groupcoupon_Export_Grid extends Mage_Adminhtml_Block_Widget_Grid {

	protected $_id = NULL;
    
	
	public function __construct(){
    	
    	
        parent::__construct();
        $this->setId('gridGroupCouponproductSold');
        $data =$this->getRequest()->getPost();
        $this->_id = $data['id'];
    }
	
    protected function _prepareCollection(){
        parent::_prepareCollection();
        $model = Mage::getModel('promotionfactory/groupcoupon');
        $collection = $model->getCollection()
        ->addFilter('rule_id',$this->_id)
        ; 
		$this->setCollection($collection);
       //	$this->getCollection()->initReport('promotionfactory/emailcoupon_collection');
        return $this;
    }
    
	protected function _prepareColumns(){
        $this->addColumn('sudo_code', array(
            'header'    =>Mage::helper('reports')->__('Real Coupon Code'),
            'index'     =>'code'
        ));

        $this->addColumn('code', array(
            'header'    =>Mage::helper('reports')->__('Code'),
            'width'     =>'120px',
            'align'     =>'right',
            'index'     =>'pseudo_code',
        ));

        $this->addExportType('*/*/exportCouponproductSoldCsv', Mage::helper('reports')->__('CSV'));
        $this->addExportType('*/*/exportCouponproductSoldExcel', Mage::helper('reports')->__('Excel'));

        return parent::_prepareColumns();
    }
}