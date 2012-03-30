<?php
class Harapartners_Childrenlist_Block_Collection extends Mage_Core_Block_Template
{
 
    public function __construct()
    {
        parent::__construct();
        
        $CustomerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
        $collection = Mage::getModel('childrenlist/child')->getCollection();
        $collection->addFieldToFilter('customer_id', $CustomerId);
        $collection->load();
        $this->setCollection($collection);
    }
 
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $pager = $this->getLayout()->createBlock('page/html_pager', 'custom.pager');
        $pager->setAvailableLimit(array(5=>5, 'all'=>'all'));
        $pager->setCollection($this->getCollection());
        $this->setChild('pager', $pager);
        $this->getCollection()->load();
        return $this;
    }
 
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
}