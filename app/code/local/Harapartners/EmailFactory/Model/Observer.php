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

class Harapartners_EmailFactory_Model_Observer extends Mage_Core_Model_Abstract {
    
    /*
     *  for event newsletter_subscriber_save_after,
     *  (which means after customer set their newsletter preference)
     */    
    
    public function newsletterupdateObserver($observer){
        //Sailthru status must follow Magento newletter status
        $subscriber = $observer->getEvent()->getSubscriber();
        
        //subscribe/unsubscribe newsletter: http://docs.sailthru.com/api/integration/lists?s[]=setemail
        $email = $subscriber->getSubscriberEmail();
        $newletterListName = Mage::getStoreConfig('sailthru_options/email/sailthru_news_list'); //Non-transactional, newletter related
        $sailthru = Mage::getSingleton('emailfactory/sailthruconfig')->getHandle();
        if(!! $subscriber 
                && $subscriber->getId()
                && $subscriber->getStatus() == Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED){
            $sailthru->setEmail($email, array(), array($newletterListName => 1));        
        }else{
            $sailthru->setEmail($email, array(), array($newletterListName => 0));
        }
        
        return $this;
    }
    
    
    /*
     *  for event customer_save_after, sync the new email to sailthru
     */    
    public function customerupdateObserver($observer){
        
        $sailthru = Mage::getSingleton('emailfactory/sailthruconfig')->getHandle();
        $defaultListName = Mage::getStoreConfig('sailthru_options/email/sailthru_def_list'); //Used for transactional emails
        $newletterListName = Mage::getStoreConfig('sailthru_options/email/sailthru_news_list'); //Non-transactional, newletter related
        $customer = $observer->getEvent()->getCustomer();
        
        //check if customer is update info or just register 
        if (!!$customer->getOrigData()){
            //update email address
              $email = $customer->getEmail();
               $oldEmail = $customer->getOrigData('email');
            if (isset($oldEmail) && strcmp($email, $oldEmail) != 0){
                //set default list(transectional list)
                //Note Sailthru 'change' API is not working, trying work around
                $sailthru->setEmail($oldEmail, array(), array($defaultListName => 0));
                $sailthru->setEmail($email, array(), array($defaultListName => 1));
                //set newsletter list
                $sailthru->setEmail($oldEmail, array(), array($newletterListName => 0));
                //Note that ALWAYS: $customer->save() >> $newsletter->save()
                //even the newletter info of the customer is not changed
                //e.g. customer change email
            }
        }else{
            //just register
            $sailthru->setEmail(
                    $customer->getEmail(),
                    array("name" => $customer->getName()), 
                    array($defaultListName => 1)
            );
            //Note $customer->save() >> $newsletter->save(), thus newsletterupdateObserver() will catch the update for the newsletter.     
        }
        return $this;
    }
    
    public function checkRegisterEmails(){
        //delete the record which is already successful to delivered
        $collectionToDelete = Mage::getModel('emailfactory/record')->getCollection();
        $collectionToDelete
                ->addFieldToFilter('sailthru_api_status',Harapartners_EmailFactory_Model_Record::SAILTHRU_API_STATUS_CHECK)
                ->addFieldToFilter('sailthru_email_deliver_status','delivered');
        $collectionToDelete->load();
        foreach ($collectionToDelete as $recordToDelete){
            $recordToDelete->delete();
        }
        
        //check the record which is not successful to delivered
        $collection = Mage::getModel('emailfactory/record')->getCollection();
        $collection->addFieldToFilter('sailthru_api_status',Harapartners_EmailFactory_Model_Record::SAILTHRU_API_STATUS_UNCHECK);
        $collection->load();
        foreach ($collection as $record){
            $sailthru = Mage::getSingleton('emailfactory/sailthruconfig')->getHandle();
            $result = $sailthru->getSend($record->getSendId());
            $record->setData('sailthru_email_deliver_status',$result['status']);
            
            $customerTrackingRecord = Mage::getModel('customertracking/record')->loadByCustomerEmail($record->getCustomerEmail());
            //Mage::dispatchEvent('customer_register_email_exception',$result);
            if(!!$customerTrackingRecord && $customerTrackingRecord->getId()){
                if (strcmp($result['status'], 'delivered')!=0){
                    $status = Harapartners_Customertracking_Model_Record::STATUS_EMAIL_OTHER_PROBLEMS;
                    if (strcmp($result['status'], 'softbounce')==0){
                        $status = Harapartners_Customertracking_Model_Record::STATUS_EMAIL_SOFTBRONCE;
                    }elseif (strcmp($result['status'], 'hardbounce')==0){
                        $status = Harapartners_Customertracking_Model_Record::STATUS_EMAIL_HARDBRONCE;
                    }
                }else{
                    $status = Harapartners_Customertracking_Model_Record::STATUS_EMAIL_CONFIRMED;
                }
                $customerTrackingRecord->setStatus($status);
                $customerTrackingRecord->save();
            }
            $record->setData('sailthru_api_status',Harapartners_EmailFactory_Model_Record::SAILTHRU_API_STATUS_CHECK);
            $record->save();
        }
    }
    
    public function sailthruSuccessPurchase($observer){
        $this->_sendPurchaseDataToSailThru();
        return $this;
    }
    
    public function sailthruPurchasing($observer){
        $purchaseIncompleteFlag = 1;
        $this->_sendPurchaseDataToSailThru($purchaseIncompleteFlag);
        return $this;
    }
    
    protected function _sendPurchaseDataToSailThru($status = 0){
    	try{
	          $scust = Mage::getSingleton('customer/session')->getCustomer();
	          $email = $scust->getEmail();
	          if($email != "") {
	              $sailthru = Mage::getSingleton('emailfactory/sailthruconfig')->getHandle();
	              $protoitems = Mage::getSingleton('checkout/session')->getQuote()->getAllItems();
	              $items = array();
	              $i = 0;
	              foreach($protoitems as $obi) {
	                    $name = $obi->getName();
	                    $sku = $obi->getSku();
	                    $obiName = isset($name)?$name:$sku;    
	                  $items[$i] = array("qty" => $obi->getQty(), "title" => $obiName, "price" => $obi["product"]->getFinalPrice()*100, "id" => $obi->getSku(), "url" => $obi["product"]->getProductUrl());
	                  $i++;
	              }
	              $data = array("email" => $email, "items" => $items, "incomplete" => $status);//0: complete ; 1: imcomplete
	              if (isset($_COOKIE['sailthru_bid'])) {
	                  $data['message_id'] = $_COOKIE['sailthru_bid'];
	              }                  
	              $success = $sailthru->apiPost("purchase", $data);
	              if(count($success) == 2) {
	                  Mage::throwException($this->__($success["errormsg"]));
	              }
	          }
	          return $this;
    	}catch(Exception $e){
    		//Harapartners, Jun, purchasing is a critical step, carry on even if sailthru connection failed
    		Mage::logException($e);
    	}
    }

}