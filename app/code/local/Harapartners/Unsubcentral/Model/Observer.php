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

class Harapartners_Unsubcentral_Model_Observer extends Mage_Core_Model_Abstract {
    
    
    
    public function unsubscribeCustomerObserver($observer){
        
        $subscriber = $observer->getEvent()->getSubscriber();
        $customerEmail = $subscriber->getSubscriberEmail();
        $unsubcentralItem = Mage::getModel('unsubcentral/item')->loadByEmail($customerEmail);
        
        
        //If customer already has previous subscription, and in the current save the user unsubscribe
        if(!! $subscriber 
                && $subscriber->getId()
                && $subscriber->getStatus() == Mage_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED){ 
            $unsubcentralItem->setData('subscriber_email', $customerEmail);
            $unsubcentralItem->setData('unsubcentral_api_status', Harapartners_Unsubcentral_Model_Item::API_PROCESSING_UNSUBSCRIBE_STATUS);
            $now = Mage::getModel('core/date')->timestamp(time());
            $unsubcentralItem->setData('update_at', date('Y-m-d H:i:s', $now));
            $unsubcentralItem->save();
        }
        
        
        
        if(!! $subscriber 
                && $subscriber->getId()
                && $subscriber->getStatus() == Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED
                && (!$unsubcentralItem || !$unsubcentralItem->getId()) ){ //And if there is no exist $unsubcentralItem record
            $unsubcentralItem->setData('subscriber_email', $customerEmail);
            $unsubcentralItem->setData('unsubcentral_api_status', Harapartners_Unsubcentral_Model_Item::API_PROCESSING_REGISTER_STATUS);
            $now = Mage::getModel('core/date')->timestamp(time());
            $unsubcentralItem->setData('update_at', date('Y-m-d H:i:s', $now));
            $unsubcentralItem->save();
        }
        
        

        return $this;
    }
    
    
    public function runUpdateUnscribeScript(){
        /*initial setting*/
        $login = Mage::getStoreConfig('unsubcentral_options/login/unsubcentral_login_name');
        $password = Mage::getStoreConfig('unsubcentral_options/login/unsubcentral_login_password');
        $optOuptList = Mage::getStoreConfig('unsubcentral_options/listid/opt_out_list');
        $registerList = Mage::getStoreConfig('unsubcentral_options/listid/registered_users_list');
        $date_today = date("Y-m-d");
        
        
        /*Register API requests*/
        
        echo "<h3>The following is the Register results</h3><br/>";
        
        $registerCollection=Mage::getModel('unsubcentral/item')->getCollection()
                ->addFieldToFilter('unsubcentral_api_status', Harapartners_Unsubcentral_Model_Item::API_PROCESSING_REGISTER_STATUS);
        foreach($registerCollection as $registerItem){
            
            $email = $registerItem->getData('subscriber_email');    
            $url = 'https://login8.unsubcentral.com/uc/address_upload.pl?';
            $fields = array(
                    'login' => $login,
                    'password' => urlencode($password),
                    'listID' => $registerList,
                    'md5' => 'false',
                    'suppressed_text' => urlencode($email)
            );
            
            foreach($fields as $key => $value) { 
                    $fieldsStringArray[] = $key.'='.$value; 
            }
            
            $ch = curl_init();
            curl_setopt($ch,CURLOPT_URL, $url);
            curl_setopt($ch,CURLOPT_POST, count($fields));
            curl_setopt($ch,CURLOPT_POSTFIELDS, implode('&', $fieldsStringArray));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            curl_close($ch);
            echo $result.'<br/>';
            /*$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            echo  '<br/>'.$httpCode;*/
            
            if (!(strcasecmp(substr(trim($result), 0, 7), 'WORKING') == 0)){
                if (strcasecmp(substr(trim($result), 0, 5), 'ERROR') == 0){
                    $registerItem->setErrorMessage($result);
                } else {
                    $registerItem->setErrorMessage('unknown error');
                }
                $registerItem->setUnsubcentralApiStatus(Harapartners_Unsubcentral_Model_Item::API_ERROR_STATUS);
            } else {
                $registerItem->setUnsubcentralApiStatus(Harapartners_Unsubcentral_Model_Item::API_PROCESSED_STATUS);
            }
            $registerItem->save();
            
        }
        
        
        echo "<h3>The following is the opt-out results</h3><br/>";
        
        /*Unsubcribe API requests, subscribe will automatically exclude from the register list*/
        $collection=Mage::getModel('unsubcentral/item')->getCollection()
                ->addFieldToFilter('unsubcentral_api_status', Harapartners_Unsubcentral_Model_Item::API_PROCESSING_UNSUBSCRIBE_STATUS);
        $unsubcentral_filename = BP.DS.'cron'.DS."tmp".DS."unsubcentral-".$date_today.".txt";
        $temp_file_string ='';        
        foreach($collection as $item){
            $fields_string = "";
            $params = $_POST;
            $email = $item->getData('subscriber_email');    
            $urlencoded_email = urlencode($email);
        

            //set POST variables - activate a user on the opt out list   (LIVE 113, TEST 130)
            $url = 'https://login8.unsubcentral.com/uc/address_upload.pl?';
            $fields = array(
                    'login' => $login,
                    'password' => urlencode($password),
                    'listID' => $optOuptList,
                    'md5' => 'false',
                    'suppressed_text' => urlencode($email)
            );
            //url-ify the data for the POST
            foreach($fields as $key => $value) { 
                $fieldsStringArray[] = $key.'='.$value; 
            }
            
            $ch = curl_init();
            curl_setopt($ch,CURLOPT_URL, $url);
            curl_setopt($ch,CURLOPT_POST, count($fields));
            curl_setopt($ch,CURLOPT_POSTFIELDS, implode('&', $fieldsStringArray));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            curl_close($ch);
            
            echo $result.'<br/>';
            /*$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            echo  '<br/>'.$httpCode;*/
            
            if (!(strcasecmp(substr(trim($result), 0, 7), 'WORKING') == 0)){
                if (strcasecmp(substr(trim($result), 0, 5), 'ERROR') == 0){
                    $item->setErrorMessage($result);
                } else {
                    $item->setErrorMessage('unknown error');
                }
                $item->setUnsubcentralApiStatus(Harapartners_Unsubcentral_Model_Item::API_ERROR_STATUS);
            } else {
                $item->setUnsubcentralApiStatus(Harapartners_Unsubcentral_Model_Item::API_PROCESSED_STATUS);
            }
            $item->save();
            
            $temp_file_string .=  "{$email}\t{$date_today}\t0\t\n";
            
            
            //to download file, plx use "https://login8.unsubcentral.com/uc/list_download.pl?login=api_test&password=password1&format=plaintext&zipped=false&listID=130"
            //or use "https://login8.unsubcentral.com/uc/list_download.pl?login=api_test&password=password1&format=plaintext&zipped=false&listID=130&start_date=2011-10-30&end_date=2012-11-30"
        }
        
        //$temp_file = file_put_contents($unsubcentral_filename,"{$email}\t{$date_today}\t0\t\n");
        $temp_file = file_put_contents($unsubcentral_filename,$temp_file_string);
        //deactivate a user on the registered list (116)
        $url = "https://login8.unsubcentral.com/uc/add_remove_address.pl?";
        $fields = array(
                                'login'=>$login,
                                'password'=>urlencode($password),
                                'listID'=>$registerList,
                                'file'=>"@{$unsubcentral_filename}",
                                'email_col'=>"0",
                                'action_col'=>"2",
                                'date_col'=>"1"
                        );
        
        //url-ify the data for the POST
        foreach($fields as $key => $value) { 
            $fieldsStringArray[] = $key.'='.$value; 
        }
                        
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_POST,TRUE);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$fields);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE); 
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        curl_close($ch);                
        echo "<h3>The following is the unsubcribe from register results</h3><br/>";
        echo $result.'<br/>';
        
        //remove the unsubcentral upload file after it is used for upload
        if (file_exists($unsubcentral_filename)) {
            unlink($unsubcentral_filename);
        }
        
        echo ('<h3>done</h3>');
        echo ('<br/>in order to check, plx use https://login8.unsubcentral.com/uc/list_download.pl?login=api_test&password=password1&format=plaintext&zipped=false&listID='.$optOuptList);
        echo ('<br/>in order to check register list, plx use https://login8.unsubcentral.com/uc/list_download.pl?login=api_test&password=password1&format=plaintext&zipped=false&listID='.$registerList);
    }
    
}
