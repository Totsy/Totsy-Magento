<?php
/**
 *
 * @category 	Crown
 * @package 	Crown_Club
 * @since 		0.4.0
 */
class Crown_Club_Model_Rewards_Action_Club extends Enterprise_Reward_Model_Action_Abstract
{
    /**
     * Return action message for history log
     * @since 		0.4.0
     * @param array $args Additional history data
     * @return string
     */
    public function getHistoryMessage($args = array())
    {
        $incrementId = isset($args['increment_id']) ? $args['increment_id'] : '';
        return Mage::helper('enterprise_reward')->__('Earned points for order #%s.', $incrementId);
    }

    /**
     * Setter for $_entity and add some extra data to history
     * @since 		0.4.0
     * @param Varien_Object $entity
     * @return Enterprise_Reward_Model_Action_Abstract
     */
    public function setEntity($entity)
    {
        parent::setEntity($entity);
        $this->getHistory()->addAdditionalData(array(
            'increment_id' => $this->getEntity()->getIncrementId()
        ));
        return $this;
    }

    /**
     * Retrieve points delta for action
     * @since 		0.4.0
     * @param int $websiteId
     * @return int
     */
    public function getPoints($websiteId)
    {
        if (!Mage::helper('enterprise_reward')->isOrderAllowed($this->getReward()->getWebsiteId())) {
            return 0;
        }

        $monetaryAmount = $this->getEntity()->getGrandTotal()
            - $this->getEntity()->getBaseShippingAmount()
            - $this->getEntity()->getBaseTaxAmount();

        $monetaryAmount = (float) ceil($monetaryAmount * .10);
        $pointsDelta = $this->getReward()->getRateToPoints()->calculateToPoints((float)$monetaryAmount);
        return $pointsDelta;
    }

    /**
     * Check whether rewards can be added for action
     * Checking for the history records is intentionaly omitted
     * @since 		0.4.0
     * @return bool
     */
    public function canAddRewardPoints()
    {
        return parent::canAddRewardPoints()
            && Mage::helper('enterprise_reward')->isOrderAllowed($this->getReward()->getWebsiteId());
    }
}
