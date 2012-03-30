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

class Harapartners_MobileApi_RewardController extends Mage_Core_Controller_Front_Action
{
	public function indexAction()
	{
		$params = $this->getRequest()->getParams();
		$response = $this->getResponse();
		$response->setHeader('Content-type', 'application/json', true);
		try{
			if(isset($params['id']) && !!$params['id']){
				$rewardId = $params['id'];
				$reward = Mage::getModel('enterprise_reward/reward')->load($rewardId);
				if(! $reward->getId()){
					Mage::throwException($this->__('Reward does not exist'));
				}
				$result = Mage::helper('mobileapi')->getRewardInfo($reward);
			
				
				
			
			}
		}catch(Exception $e){
			$result = $e->getMessage();
		}
		$response->setBody(json_encode($result));
	}
}