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
     * @var 0.7.3
     */
    private $_quote = null;

    /**
     * Return action message for history log
     * @since 		0.4.0
     * @param array $args Additional history data
     * @return string
     */
    public function getHistoryMessage($args = array())
    {
        $incrementId = isset($args['increment_id']) ? $args['increment_id'] : '';
        return Mage::helper('enterprise_reward')->__('TotsyPLUS Credits for order #%s.', $incrementId);
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
     * Quote setter
     *
     * @since 0.7.3
     * @param Mage_Sales_Model_Quote $quote
     * @return Crown_Club_Model_Rewards_Action_Club
     */
    public function setQuote(Mage_Sales_Model_Quote $quote) {
        $this->_quote = $quote;
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
        if ($this->_quote) {
            $quote = $this->_quote;
            $monetaryAmount = $quote->getBaseGrandTotal();
            $monetaryAmount = $monetaryAmount < 0 ? 0 : $monetaryAmount;
        } else {
            $monetaryAmount = $this->getEntity()->getGrandTotal();
        }

        $monetaryAmount = (float) $monetaryAmount * .10;
        $pointsDelta = $this->getReward()->getRateToPoints()->calculateToPoints((float)$monetaryAmount);
        return $pointsDelta;
    }
}
