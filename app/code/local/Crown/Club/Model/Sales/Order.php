<?php
/**
 *
 * @category 	Crown
 * @package 	Crown_Club
 * @since 		0.4.0
 */
class Crown_Club_Model_Sales_Order extends Mage_Sales_Model_Order
{
    /**
     * If the order is for a club member.
     * @since 0.4.0
     * @return bool
     */
    public function isClubMember() {
        if ($this->getCustomerIsGuest())
            return false;

        /* @var $clubHelper Crown_Club_Helper_Data */
        $clubHelper = Mage::helper('club/club');

        if ($this->getCustomer() && $clubHelper->isClubMember($this->getCustomer()))
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

        $currentSetting = true == $this->getData('customer_is_club') ? true: false;

        // Only update the value if it has changed
        if ($this->isClubMember() && $currentSetting != true) {
            $this->setData('customer_is_club', true);
        } elseif(!$this->isClubMember() && $currentSetting != false) {
            $this->setData('customer_is_club', false);
        }
        return $this;
    }
}
