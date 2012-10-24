<?php
/**
 *
 * @category 	Crown
 * @package 	Crown_Club
 * @since 		0.4.0
 */
class Crown_Club_Model_Rewards_Reward extends Enterprise_Reward_Model_Reward
{
    const REWARD_ACTION_CLUB    = 42;

    /**
     * Internal constructor
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('enterprise_reward/reward');
        self::$_actionModelClasses = self::$_actionModelClasses + array(
            self::REWARD_ACTION_CLUB                => 'crownclub/rewards_action_club',
        );
    }
}
