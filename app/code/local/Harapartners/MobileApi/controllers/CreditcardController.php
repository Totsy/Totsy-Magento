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

class Harapartners_MobileApi_CreditcardController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {   
        $httpMethod = $this->getRequest()->getMethod();
        
        if($httpMethod == 'DELETE'){
            $this->_forward('delete');
        }elseif($httpMethod == 'PUT'){
            $this->_forward('update');
        }elseif($httpMethod == 'GET'){
            try{
                $params = $this->getRequest()->getParams();
                $response = Mage::app()->getResponse();
                $response->setHeader('Content-type', 'application/json', true);
            
                if(isset($params['id']) && !! $params['id']){
                    $ccId = $params['id'];
                    $ccInfo = Mage::getModel('paymentfactory/profile')->load($ccId);
                    if(!$ccInfo->getId()){
                        throw new Exception('Credit Card does not exist!');
                    }
                    $result = Mage::helper('mobileapi')->getCcInfo($ccInfo);
                    
                }else{
                    $response->setHttpResponseCode(400);
                    Mage::throwException($this->__('Invalid Request!'));
                }
                
            }catch(Exception $e){
                $result = $e->getMessage();
            }
            $response->setBody(json_encode($result));
        }
    }    
    
    public function deleteAction()
    {
        $params = $this->getRequest()->getParams();
        $response = $this->getResponse();
        $response->setHeader('Content-type', 'application/json', true);
        
        try{
            if(isset($params['id']) && !! $params['id']){
                $ccId = $params['id'];
                $ccInfo = Mage::getModel('paymentfactory/profile')->load($ccId);
                if(!$ccInfo->getId()){
                    throw new Exception('Credit Card does not exist!');
                }
                $ccInfo->delete();
                $result = 'true';
            }else{
                $response->setHttpResponseCode(400);
                Mage::throwException($this->__('Invalid Request!'));
            }
        }catch(Exception $e){
            $result = $e->getMessage();
        }
        $response->setBody(json_encode($result));
    }
    
    public function updateAction()
    {
        
    }
}