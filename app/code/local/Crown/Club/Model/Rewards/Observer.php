<?php
/**
 *
 * @category 	Crown
 * @package 	Crown_Club
 * @since 		0.7.1
 */
class Crown_Club_Model_Rewards_Observer extends Enterprise_Reward_Model_Observer{

    /**
     * Removing the default order rewards businasssizzle
     *
     * @since 0.7.1
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Reward_Model_Observer
     */
    public function orderCompleted($observer)
    {
        return $this;
    }
}
