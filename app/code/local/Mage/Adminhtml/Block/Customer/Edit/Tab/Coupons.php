<?php
/**
 * Customer account form block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Tom Royer <troyer@totsy.com>
 */
class Mage_Adminhtml_Block_Customer_Edit_Tab_Coupons extends Mage_Adminhtml_Block_Template implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    public function __construct()
    {
        $this->setTemplate('customer/tab/coupons.phtml');
        parent::__construct();
    }
    public function getCustomer()
    {
        if (!$this->_customer) {
            $this->_customer = Mage::registry('current_customer');
        }
        return $this->_customer;
    }

    public function getTabLabel()
    {
        return Mage::helper('customer')->__('Coupons');
    }

    public function getTabTitle()
    {
        return Mage::helper('customer')->__('Coupons');
    }

    public function canShowTab()
    {
        if (Mage::registry('current_customer')->getId()) {
            return true;
        }
        return false;
    }

    public function isHidden()
    {
        if (Mage::registry('current_customer')->getId()) {
            return false;
        }
        return true;
    }

    public function getOrdersWithCouponsUsed() {
        $orders = Mage::getModel('sales/order')->getCollection()
            ->addAttributeToFilter('customer_id',  $this->getCustomer()->getId())
            ->addAttributeToFilter('coupon_code', array(
            'notnull' => true));
        if($orders->getData()) {
            return $orders->getData();
        } else {
            return null;
        }
    }
}
