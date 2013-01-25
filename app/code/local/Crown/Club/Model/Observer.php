<?php
class Crown_Club_Model_Observer {

	/**
	 * Moves expired club members back to the non club members group.
	 * @param Mage_Cron_Model_Schedule $schedule
	 * @since 0.1.0
	 * @return void
	 */
	public function removeExpiredClubMembers($schedule) {
		$helper = Mage::helper('crownclub');
		if (!$helper->moduleSetupComplete()) return;

		$clubModel = Mage::getModel('crownclub/club');

		$expiredMembers = $clubModel->getExpiredMembersOutOfGracePeriod();

		foreach ($expiredMembers as $expiredMember) {
			$customer = Mage::getModel('customer/customer')->load($expiredMember->getId());
			$clubModel->removeClubMember($customer)->sendClubMembershipCancelledEmail($customer);
		}
	}

	/**
	 * Warns expired club members that their payment has failed and their subscription account
	 * will be cancelled once it exits the grace period.
	 * @param Mage_Cron_Model_Schedule $schedule
	 * @since 0.1.0
	 * @return void
	 */
	public function warnExpiredClubMembers($schedule) {
		$helper = Mage::helper('crownclub');
		if (!$helper->moduleSetupComplete()) return;

		$clubModel = Mage::getModel('crownclub/club');

		$expiredMembers = $clubModel->getExpiredMembersInGracePeriod();

		foreach ($expiredMembers as $expiredMember) {
			$customer = Mage::getModel('customer/customer')->load($expiredMember->getId());
			$clubModel->sendClubMembershipPaymentFailedEmail($customer);
		}
	}

    /**
     * Update points balance after order becomes completed
     *
     * @param Varien_Event_Observer $observer
     * @since 0.4.0
     * @return Crown_Club_Model_Observer
     */
    public function orderShipped($observer)
    {
        /* @var $order Mage_Sales_Model_Order */
        $order = $observer->getEvent()->getOrder();

        // Check if order is club membership
        if ($order->isNominal()) {
            foreach ($order->getAllVisibleItems() as $item) {
                $productModel = Mage::getModel('catalog/product')->load($item->getProductId());
                if ($productModel->getIsClubSubscription()) {
                    return $this;
                }
            }
        }

        if ($order->getCustomerIsGuest()
            || !Mage::helper('enterprise_reward')->isEnabledOnFront($order->getStore()->getWebsiteId())
            || !$order->getData('customer_is_club_member')
        ){
            return $this;
        }

        // Give credit where credit is due
        if ($order->getCustomerId() && $this->_isOrderPaidNow($order)) {
            /* @var $reward Enterprise_Reward_Model_Reward */
            $reward = Mage::getModel('enterprise_reward/reward')
                ->setActionEntity($order)
                ->setCustomerId($order->getCustomerId())
                ->setWebsiteId($order->getStore()->getWebsiteId())
                ->setAction(Crown_Club_Model_Rewards_Reward::REWARD_ACTION_CLUB)
                ->updateRewardPoints();
            if ($reward->getRewardPointsUpdated() && $reward->getPointsDelta()) {
                $order->addStatusHistoryComment(
                    Mage::helper('enterprise_reward')->__('Club customer earned %s for the order.', Mage::helper('enterprise_reward')->formatReward($reward->getPointsDelta()))
                )->save();
            }
        }
        return $this;
    }

    /**
     * Redirect to plus if they access the dashboard page and aren't a member
     *
     * @since 0.7.0
     * @return void
     */
    public function checkPlusMemberDashboardUrl() {
        $customer = Mage::helper('customer')->getCustomer();

        // Reroute non customers to Plus Sign Up Page
        if ( ( preg_match( '#plus/dashboard#', Mage::app ()->getRequest ()->getPathInfo () ) ) && ( !Mage::helper('crownclub')->isClubMember($customer) ) ) {
            Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getUrl('plus'));
        }

        return;
    }

    /**
     * Check if order is paid exactly now
     * If order was paid before Rewards were enabled, reward points should not be added
     *
     * @since 0.7.3
     * @param Mage_Sales_Model_Order $order
     * @return bool
     */
    protected function _isOrderPaidNow($order)
    {
        $isOrderPaid = (float)$order->getBaseTotalPaid() > 0
            && ($order->getBaseGrandTotal() - $order->getBaseSubtotalCanceled() - $order->getBaseTotalPaid()) < 0.0001;

        if (!$order->getOrigData('base_grand_total')) {//New order with "Sale" payment action
            return $isOrderPaid;
        }

        return $isOrderPaid && ($order->getOrigData('base_grand_total') - $order->getOrigData('base_subtotal_canceled')
            - $order->getOrigData('base_total_paid')) >= 0.0001;
    }
}