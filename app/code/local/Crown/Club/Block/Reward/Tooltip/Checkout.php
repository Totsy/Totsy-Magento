<?php
/**
 * Crown Partners LLC
 *
 * @category    {category}
 * @package     {package}
 * @author: chris.davidowski
 */

class Crown_Club_Block_Reward_Tooltip_Checkout extends Enterprise_Reward_Block_Tooltip_Checkout
{
    /**
     * Set the reward to calculate like the customer is a plus member if they have a plus membership in their cart.
     *
     * @param int|string $action
     */
    public function initRewardType($action)
    {
        parent::initRewardType($action);
        if ($this->_actionInstance) {
            if(!Mage::helper('crownclub')->isClubMember(Mage::getSingleton('customer/session')->getCustomer())) {
                foreach(Mage::getSingleton('checkout/session')->getQuote()->getAllItems() as $item) {
                    if($item->getProductId() == Mage::getStoreConfig('Crown_Club/clubgeneral/club_product_id')) {
                        $this->_rewardInstance->setData('customer_group_id',Mage::getStoreConfig('Crown_Club/clubgeneral/club_customer_group'));
                    }
                }
            }

        }
    }
}