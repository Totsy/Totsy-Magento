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

class TinyBrick_OrderEdit_Model_Order_Address_Rate extends Mage_Shipping_Model_Rate_Abstract
{
    protected $_address;

    protected function _construct()
    {
        $this->_init('orderedit/order_address_rate');
    }

    protected function _beforeSave()
    {
        parent::_beforeSave();
        if ($this->getAddress()) {
            $this->setAddressId($this->getAddress()->getId());
        }
        return $this;
    }

    public function setAddress(TinyBrick_OrderEdit_Model_Order_Address $address)
    {
        $this->_address = $address;
        return $this;
    }

    public function getAddress()
    {
        return $this->_address;
    }

    public function importShippingRate(Mage_Shipping_Model_Rate_Result_Abstract $rate,$orderId,$addressId)
    {
    	//Might need to be enabled if the rate quotes don't come back correctly
//        if ($rate instanceof Mage_Shipping_Model_Rate_Result_Error) {
//            $this
//                ->setCode($rate->getCarrier().'_error')
//                ->setCarrier($rate->getCarrier())
//                ->setCarrierTitle($rate->getCarrierTitle())
//                ->setErrorMessage($rate->getErrorMessage())
//            ;
//        } elseif ($rate instanceof Mage_Shipping_Model_Rate_Result_Method) {
            $this
            	->setCreatedAt(now())
            	->setUpdatedAt(now())
            	->setAddressId($addressId)
            	->setOrderId($orderId)
                ->setCode($rate->getCarrier().'_'.$rate->getMethod())
                ->setCarrier($rate->getCarrier())
                ->setCarrierTitle($rate->getCarrierTitle())
                ->setMethod($rate->getMethod())
                ->setMethodTitle($rate->getMethodTitle())
                ->setMethodDescription($rate->getMethodDescription())
                ->setPrice($rate->getPrice())
                ->save()
            ;
//        }
        return $this;
    }
}