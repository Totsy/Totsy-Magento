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

    public function customerHasAddresses()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($orderId);
        $customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
        return count($customer->getAddresses());
    }

    public function getAddressesHtmlSelect($type)
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($orderId);
        $customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
        $options = array();
        $options[] = array(
            'value' => '',
            'label' => Mage::helper('checkout')->__('New Address')
         );
        foreach ($customer->getAddresses() as $address) {
            $options[] = array(
                'value' => $address->getId(),
                'label' => substr($address->format('oneline'),0,80)
            );
        }

        $select = $this->getLayout()->createBlock('core/html_select')
            ->setName($type.'_address_id')
            ->setId($type.'-address-select')
            ->setExtraParams('onchange="changeAddress(' ."'". $type ."'". ')"')
            ->setClass('address-select')
            ->setOptions($options);
        return $select->getHtml();
    }

}