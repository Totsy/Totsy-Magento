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

class Harapartners_MobileApi_AddressController extends Mage_Core_Controller_Front_Action{
    
    
    public function indexAction(){
        $httpMethod = $this->getRequest()->getMethod();
        
        if($httpMethod == 'DELETE'){
            $this->_forward('delete');
        }elseif($httpMethod == 'PUT'){
            $this->_forward('update');
        }elseif($httpMethod == 'GET'){
            try{
                $params = $this->getRequest()->getParams();
                $response = $this->getResponse();
                $response->setHeader('Content-type', 'application/json', true);
            
                if(isset($params['id']) && !!$params['id']){
                    $addressId = $params['id'];
                    $address = Mage::getModel('customer/address')->load($addressId);
                    if(! $address->getId()){
                        Mage::throwException($this->__("Address does not exist!"));
                    }
                        $result = Mage::helper('mobileapi')->getAddressInfo($address);
                    
                }else{
                    $response->setHttpResponseCode(400); 
                    Mage::throwException($this->__("Address does not exist!"));
                }
            }catch(Exception $e){
                $result = $e->getMessage();
            }    
            $response->setBody(json_encode($result));
        }
    }

    public function deleteAction(){
        $params = $this->getRequest()->getParams();
        $response = $this->getResponse();
        $response->setHeader('Content-type', 'application/json', true);
        try{
            if(isset($params['id']) && !!$params['id']){
                $addressId = $params['id'];
                $address = Mage::getModel('customer/address')->delete($addressId);
                $result = true;
            }else{
                $response->setHttpResponseCode(400);
                Mage::throwException($this->__('Invalid Request'));
            }
        }catch(Exception $e){
            $result = $e->getMessage();
        }
        $response->setBody(json_encode($result));
            
    }

    public function updateAction(){
        $params = $this->getRequest()->getParams();
        $response = $this->getResponse();
        $response->setHeader('Content-type', 'application/json', true);
        
        try{
            if(isset($params['id']) && !!$params['id']){
                $addressId = $params['id'];
                $addressData = $params;
                unset($addressData['id']);
                //$addressData = json_decode($params['json'], true);
                $address = Mage::getModel('customer/address')->load($addressId);
                if(!$address->getId()){
                    Mage::throwException($this->__('Address does not exist'));
                }
                foreach (Mage::getModel('mobileapi/address')->getAllowedAttributes($address) as $attributeCode=>$attribute) {
                    if (isset($addressData[$attributeCode])) {
                        $address->setData($attributeCode, $addressData[$attributeCode]);
                    }
                }
                
                $valid = $address->validate();
                if (is_array($valid)) {
                    Mage::throwException($this->__(implode("\n", $valid)));
                }

                    $address->save();
                    $result = Mage::helper('mobileapi')->getAddressInfo($address);
       
                
            }else{
                $response->setHttpResponseCode(400);
                Mage::throwException($this->__('Invalid Request'));
            }
        }catch(Exception $e){
            $result = $e->getMessage();
        }
        $response->setBody(json_encode($result));
    }

}    