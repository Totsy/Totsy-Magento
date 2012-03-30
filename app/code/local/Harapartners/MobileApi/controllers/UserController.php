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

class Harapartners_MobileApi_UserController extends Mage_Core_Controller_Front_Action
{
	
	protected $_mapAttributes = array('customer_id' => 'entity_id');
	
 	protected function _prepareData($data)
    {
       foreach ($this->_mapAttributes as $attributeAlias=>$attributeCode) {
            if(isset($data[$attributeAlias]))
            {
                $data[$attributeCode] = $data[$attributeAlias];
                unset($data[$attributeAlias]);
            }
        }
        return $data;
    }
	
	public function indexAction()
	{
		$params = $this->getRequest()->getParams();
		$httpMethod = $this->getRequest()->getMethod();
		$response = Mage::app()->getResponse();
		$response->setHeader('Content-type', 'application/json');
		
		try{
			if($httpMethod == 'POST' || $httpMethod == 'PUT'){
				
					$data = $params;
					//unset($data['id']);
					if(isset($data['email']) && !! $data['email']){
						$email = $data['email'];
						$customer = Mage::getModel('customer/customer');
						$customer->setWebsiteId(Mage::app()->getWebsite()->getId());
						$customer->loadByEmail($email);
						if((!$customer->getId() && $httpMethod == 'POST') || (!! $customer->getId() && $httpMethod == 'PUT')){
							$customerData = $this->_prepareData($data);
							$customer->setData($customerData)
									->save();
							//Mage::getSingleton('customer/session')->loginById($customer->getId());
							$result = Mage::helper('mobileapi')->getUserInfo($customer);
								
						}else{
							$response->setHttpResponseCode(400);
							Mage::throwException($this->__('Invalid Request!'));
						}
					}
				
			}elseif($httpMethod == 'GET'){
				if(count($params)==1 && isset($params['id']) && !!$params['id']){
					$userId= $params['id'];
					$user = Mage::getModel('customer/customer')->load($userId);
					if(! $user->getId()){
						Mage::throwException($this->__('User does not exist!'));
					}
					$result = Mage::helper('mobileapi')->getUserInfo($user);						
				}
			}else{
				$response->setHttpResponseCode(400);
				Mage::throwException($this->__('Invalid Request!'));
			}
		}catch(Exception $e){
			$result = $e->getMessage();
		}
		$response->setBody(json_encode($result));
		
	}
	
	public function addressAction()
	{
		$params = $this->getRequest()->getParams();
		$httpMethod = $this->getRequest()->getMethod();
		$response = Mage::app()->getResponse();
		$response->setHeader('Content-type', 'application/json', true);
		
		try{
			if($httpMethod == 'GET'){
				if(count($params)==2 && isset($params['id']) && !! $params['id']){
					$userId = $params['id'];
					$customer = Mage::getModel('customer/customer')->load($userId);
					if(! $customer->getId()){
						Mage::throwException($this->__('User does not exist!'));
					}
					$result= Mage::helper('mobileapi')->getUserAddressesInfo($customer);
				}
			}elseif($httpMethod == 'POST'){
				
					//$data = json_decode($params['json'], true);
					$addressData = $params;
					unset($addressData['id']);
					$customerId = $params['id'];
					$address = Mage::getModel('customer/address');
					foreach (Mage::getModel('mobileapi/address')->getAllowedAttributes($address) as $attributeCode=>$attribute){
            			if (isset($addressData[$attributeCode])){
                			$address->setData($attributeCode, $addressData[$attributeCode]);
            			}
        			}	
        			$address->setCustomerId($customerId);
        			$valid = $address->validate();
        			if (is_array($valid)) {
            			Mage::throwException($this->__(implode("\n", $valid)));
        			}

            		$address->save();
					$result = Mage::helper('mobileapi')->getAddressInfo($address);
				
			}
		}catch(Exception $e){
			$result = $e->getMessage();
		}
		$response->setBody(json_encode($result));
	}
	
	public function creditcardAction()
	{
		$params = $this->getRequest()->getParams();
		$httpMethod = $this->getRequest()->getMethod();
		$response = Mage::app()->getResponse();
		$response->setHeader('Content-type', 'application/json', true);
		
		try{
			if($httpMethod == 'GET'){
				if(isset($params['id']) && !! $params['id']){
					$userId = $params['id'];
					$user = Mage::getModel('customer/customer')->load($userId);
					if(!$user->getId()){
						Mage::throwException($this->__('User does not exist!'));
					}
					$collection = $this->_getCreditCardCollection($userId);		
					$result = array();
					foreach($collection as $cc){
						$result[] = Mage::helper('mobileapi')->getCCInfo($cc);
					}
				}else{
						$response->setHttpResponseCode(400); 
						Mage::throwException($this->__("Invalid Request!"));
				}
			}elseif($httpMethod == 'POST'){
					$creditCard = Mage::getModel('paymentfactory/profile');
					$ccData = $params;
					unset($ccData['id']);
					//To Do-- the prepareCcInfo function
					$ccData = Mage::getModel('mobileapi/creditcard')->prepareCcInfo($ccData);
					$creditCard->setData($ccData)
								->save();
					$result = Mage::helper('mobileapi')->getCcInfo($creditCard);	
			}else{
					$response->setHttpResponseCode(400); 
					Mage::throwException($this->__("Invalid Request!"));
			}
		}catch(Exception $e){
			$result = $e->getMessage();
		}
		$response->setBody(json_encode($result));
		
	}
	
	protected function _getCreditCardCollection($userId)
	{
		$collection = Mage::getModel('paymentfactory/profile')->getCollection()
			->loadByCustomerId($userId);
		
		return $collection;
	}
}