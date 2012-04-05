<?php

/*
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User Software Agreement (EULA).
 * It is also available through the world-wide-web at this URL:
 * http://www.harapartners.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to eula@harapartners.com so we can send you a copy immediately.
 * 
 */

class Harapartners_Service_Model_Rewrite_Reward_Observer extends Enterprise_Reward_Model_Observer
{
    /**
     * Update reward points for customer, send notification
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Reward_Model_Observer
     */
    public function saveRewardPoints($observer)
    {
        if (!Mage::helper('enterprise_reward')->isEnabled()) {
            return;
        }

        $request = $observer->getEvent()->getRequest();
        $customer = $observer->getEvent()->getCustomer();
        $data = $request->getPost('reward');
        $commentInput = $data['comment'];
		$user = Mage::getSingleton('admin/session');
		$userId = $user->getUser()->getUserId();
		$userFirstname = $user->getUser()->getFirstname();
		$commentInput = $commentInput.' by '.$userFirstname.' (id:'.$userId.')';
        $data['comment'] = $commentInput;
        if ($data) {
            if (!isset($data['store_id'])) {
                if ($customer->getStoreId() == 0) {
                    $data['store_id'] = Mage::app()->getDefaultStoreView()->getWebsiteId();
                } else {
                    $data['store_id'] = $customer->getStoreId();
                }
            }
            $reward = Mage::getModel('enterprise_reward/reward')
                ->setCustomer($customer)
                ->setWebsiteId(Mage::app()->getStore($data['store_id'])->getWebsiteId())
                ->loadByCustomer();
            if (!empty($data['points_delta'])) {
                $reward->addData($data)
                    ->setAction(Enterprise_Reward_Model_Reward::REWARD_ACTION_ADMIN)
                    ->setActionEntity($customer)
                    ->updateRewardPoints();
            } else {
                $reward->save();
            }
        }
        return $this;
    }
    
	public function checkRates(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('enterprise_reward')->isEnabledOnFront()) {
            return $this;
        }

        $groupId    = $observer->getEvent()->getCustomerSession()->getCustomerGroupId();
        $websiteId  = Mage::app()->getStore()->getWebsiteId();

        $rate = Mage::getModel('enterprise_reward/reward_rate');

        $hasRates = $rate->fetch(
            $groupId, $websiteId, Enterprise_Reward_Model_Reward_Rate::RATE_EXCHANGE_DIRECTION_TO_CURRENCY
        )->getId() /*&&
            $rate->reset()->fetch(
                $groupId,
                $websiteId,
                Enterprise_Reward_Model_Reward_Rate::RATE_EXCHANGE_DIRECTION_TO_POINTS
            )->getId()*/;

        Mage::helper('enterprise_reward')->setHasRates($hasRates);

        return $this;
    }

}
