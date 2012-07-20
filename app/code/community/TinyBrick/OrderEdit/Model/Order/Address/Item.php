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
class TinyBrick_OrderEdit_Model_Order_Address_Item extends TinyBrick_OrderEdit_Model_Order_Item_Abstract
{
    /**
     * Quote address model object
     *
     * @var Mage_Sales_Model_Quote_Address
     */
    protected $_address;
    protected $_order;

    protected function _construct()
    {
        $this->_init('sales/order_address_item');
    }

    protected function _beforeSave()
    {
        parent::_beforeSave();
        if ($this->getAddress()) {
            $this->setOrderAddressId($this->getAddress()->getId());
        }
        return $this;
    }

    /**
     * Declare address model
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     * @return  Mage_Sales_Model_Quote_Address_Item
     */
    public function setAddress(TinyBrick_OrderEdit_Model_Order_Address $address)
    {
        $this->_address = $address;
        $this->_order   = $address->getOrder();
        return $this;
    }

    /**
     * Retrieve address model
     *
     * @return Mage_Sales_Model_Quote_Address
     */
    public function getAddress()
    {
        return $this->_address;
    }

    /**
     * Retrieve quote model instance
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getOrder()
    {
        return $this->_order;
    }


    public function importOrderItem(TinyBrick_OrderEdit_Model_Order_Item $orderItem)
    {
        $this->_order = $orderItem->getOrder();
        $this->setOrderItem($orderItem)
            ->setOrderItemId($orderItem->getId())
            ->setProductId($orderItem->getProductId())
            ->setProduct($orderItem->getProduct())
            ->setSku($orderItem->getSku())
            ->setName($orderItem->getName())
            ->setDescription($orderItem->getDescription())
            ->setWeight($orderItem->getWeight())
            ->setPrice($orderItem->getPrice())
            ->setCost($orderItem->getCost());

        if (!$this->hasQty()) {
            $this->setQty($orderItem->getQty());
        }
        $this->setOrderItemImported(true);
        return $this;
    }
    
    public function getOptionBycode($code)
    {
        if ($this->getOrderItem()) {
        	return $this->getOrderItem()->getOptionBycode($code);
        }
        return null;
    }
}