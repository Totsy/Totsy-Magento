<?php
/**
 *
 * @category 	Crown
 * @package 	Crown_Club
 * @since 		0.4.0
 */
class Crown_Club_Model_Sales_Order extends Totsy_Sales_Model_Order
{
    /**
     * If the order is for a club member.
     * @since 0.4.0
     * @return bool
     */
    public function isClubMember() {
        // Guest members will never be part of the club :'(
        if ($this->getCustomerIsGuest())
            return false;

        /* @var $clubHelper Crown_Club_Helper_Data */
        $clubHelper = Mage::helper('crownclub');

        $customer = Mage::getModel('customer/customer')->load($this->getCustomerId());

        if ($customer->getId() && $clubHelper->isClubMember($customer))
            return true;

        return false;
    }

    /**
     *
     * @since 0.4.0
     * @return Crown_Club_Model_Sales_Order|Mage_Core_Model_Abstract
     */
    public function _beforeSave() {
        parent::_beforeSave();

        if (null != $this->getData('customer_is_club_member') )
            return $this;

        // Only update the value if it has changed
        if ($this->isClubMember()) {
            $this->setData('customer_is_club_member', 1);
        } elseif(!$this->isClubMember()) {
            $this->setData('customer_is_club_member', 0);
        }
        return $this;
    }
}
