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

class Harapartners_MobileApi_AuthController extends Mage_Core_Controller_Front_Action
{
	public function indexAction()
	{
		$params = $this->getRequest()->getParams();
		$response = $this->getResponse();
		$response->setHeader('Content-type', 'application/json', true);
		$httpMethod = $this->getRequest()->getMethod();
		try{
			if($httpMethod == 'POST'){
				
					if(isset($params['json']) && !! $params['json']){
						$data = json_decode($params['json'], true);
						$email = isset($data['email']) ? $data['email'] : null;
						$password = isset($data['password']) ? $data['password'] : null;
						
						$auth = Mage::getModel('mobileapi/customer_session')->login($email, $password);
						if(!! $auth){
							$result = Mage::getSingleton('customer/session')->getSessionId();
						}else{
							Mage::throwException($this->__('Invalid username or password'));
						}
					}
			}
			elseif($httpMethod == 'GET'){
				$sessionId = $params['id'];
				$result = Mage::getModel('mobileapi/customer_session')->logout($sessionId);
			}
		}catch(Exception $e){
			$result = $e->getMessage();
		}
		$response->setBody(json_encode($result));
	}

}