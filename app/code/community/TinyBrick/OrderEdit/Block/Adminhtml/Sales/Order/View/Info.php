<?php
class TinyBrick_OrderEdit_Block_Adminhtml_Sales_Order_View_Info extends Mage_Adminhtml_Block_Sales_Order_View_Info
{
	protected function _toHtml()
    {
    	$str = Mage::app()->getFrontController()->getRequest()->getPathInfo();
    	if(strpos($str, '/sales_order/view/')) {
    		$this->setTemplate('sales/order/view/edit.phtml');
    	}
        if (!$this->getTemplate()) {
            return '';
        }
        $html = $this->renderView();
        return $html;
    }
    
    public function getCountryList()
    {
		return Mage::getResourceModel('directory/country_collection')
				->addFieldToFilter('country_id', array('in' => explode(",", Mage::getStoreConfig('general/country/allow'))))
				->toOptionArray();
    }
    
    public function getStateList()
    {
    	$states = Mage::getResourceModel('directory/region_collection')
    			->addFieldToFilter('country_id', array('in' => explode(",", Mage::getStoreConfig('general/country/allow'))))
    			->setOrder('country_id', 'DESC')
    			->setOrder('default_name', 'ASC')
    			->load();
		$states = $states->getData();
		return $states;
    }

}