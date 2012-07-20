<?php
/**
 * TinyBrick Commercial Extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the TinyBrick Commercial Extension License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://store.delorumcommerce.com/license/commercial-extension
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@tinybrick.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this package to newer
 * versions in the future. 
 *
 * @category   TinyBrick
 * @package    TinyBrick_OrderEdit
 * @copyright  Copyright (c) 2010 TinyBrick Inc. LLC
 * @license    http://store.delorumcommerce.com/license/commercial-extension
 */
class TinyBrick_OrderEdit_Block_Adminhtml_Sales_Order_Shipping_Update extends Mage_Adminhtml_Block_Sales_Order_Create_Shipping_Method_Form
{
	public function getOrder()
	{
		$orderId = $this->getRequest()->getParam('order_id');
		$order = Mage::getModel('sales/order')->load($orderId);
		return $order;
	}
	
	public function getOrderStatus(){
		$order = $this->getOrder();
		$status = $order->getStatus();
		return $status;
	}
	
	public function getShippingRateCollection()
	{
		$rateCollection = Mage::getModel('orderedit/order_address_rate')->getCollection()->addFieldToFilter('order_id',$this->getOrder()->getId());
		$sortedRates = array();
		foreach($rateCollection as $rate){
			$sortedRates[$rate->getCarrierTitle()][] = array('rate_id' => $rate->getRateId(),'carrier' => $rate->getCarrier(), 'carrier_title' => $rate->getCarrierTitle(), 'code' => $rate->getCode(), 'method' => $rate->getMethod(), 'method_title' => $rate->getMethodTitle(), 'price' => $rate->getPrice());
		}
		return $sortedRates;
	}
	
	public function getStores()
	{
		return Mage::getModel('storelocator/storeLocator')->getCollection()->addFieldToFilter('status',1)->setOrder('title','asc');
	}
	
	public function getShippingRates()
	{
		$shippingRates = $this->getShippingRateCollection();
		if(count($shippingRates)==0){
			Mage::getModel('orderedit/order_address')->recalculateShippingRates($this->getOrder());
			$shippingRates = $this->getShippingRateCollection();
		}		
		return $shippingRates;
	}
	
	public function getShippingAddressRates($params)
	{
		//Remove any old rates that exist
		$oldRates = Mage::getModel('orderedit/order_address_rate')->getCollection()->addFieldToFilter('order_id',$this->getOrder()->getId());
		foreach($oldRates as $oldRate){$oldRate->delete();}
		
		//Get new rates
		Mage::getModel('orderedit/order_address')->recalculateShippingRates($this->getOrder(),$params);	
		$shippingRates = $this->getShippingRateCollection();

		return $shippingRates;
	}
	
	public function getFormattedPrice($price)
	{
		return Mage::helper('core')->formatCurrency($price);
	}
	
}