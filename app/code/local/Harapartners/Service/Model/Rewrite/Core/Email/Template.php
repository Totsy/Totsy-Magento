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

class Harapartners_Service_Model_Rewrite_Core_Email_Template extends Mage_Core_Model_Email_Template {
	
    public function send($email, $name = null, array $variables = array()) {
        if (!$this->isValidForSend()) {
            Mage::logException(new Exception('This letter cannot be sent.')); // translation is intentionally omitted
            return false;
        }
        $emails = array_values((array)$email);
        $names = is_array($name) ? $name : (array)$name;
        $names = array_values($names);
        foreach ($emails as $key => $email) {
            if (!isset($names[$key])) {
                $names[$key] = substr($email, 0, strpos($email, '@'));
            }
        }
        $variables['email'] = reset($emails);
        $variables['name'] = reset($names);
        //ini_set('SMTP', Mage::getStoreConfig('system/smtp/host'));
        //ini_set('smtp_port', Mage::getStoreConfig('system/smtp/port'));
        $mail = $this->getMail();
        $setReturnPath = Mage::getStoreConfig(self::XML_PATH_SENDING_SET_RETURN_PATH);
        switch ($setReturnPath) {
            case 1:
                $returnPathEmail = $this->getSenderEmail();
                break;
            case 2:
                $returnPathEmail = Mage::getStoreConfig(self::XML_PATH_SENDING_RETURN_PATH_EMAIL);
                break;
            default:
                $returnPathEmail = null;
                break;
        }
        if ($returnPathEmail !== null) {
            $mailTransport = new Zend_Mail_Transport_Sendmail("-f".$returnPathEmail);
            Zend_Mail::setDefaultTransport($mailTransport);
        }
        foreach ($emails as $key => $email) {
            $mail->addTo($email, '=?utf-8?B?' . base64_encode($names[$key]) . '?=');
        }
        $this->setUseAbsoluteLinks(true);
        $text = $this->getProcessedTemplate($variables, true);
        if($this->isPlain()) {
            $mail->setBodyText($text);
        } else {
            $mail->setBodyHTML($text);
        }
        $mail->setSubject('=?utf-8?B?' . base64_encode($this->getProcessedTemplateSubject($variables)) . '?=');
        $mail->setFrom($this->getSenderEmail(), $this->getSenderName());
        try {
            //Harapartners sailthru//            
            /* @UPDATED 2012.04.05 [added back DG-2012.04.26]: allows 1:1 template match between Magento and Sailthru*/
            $template_name = $this['template_code'];;
            $temails = "";
            
            /* @UPDATED 2012.05.02: checks for store_id and cross references to code
                - for use in sailthru, conditional logic for serving branded headers/footers 
                - this is actually not being used right now, due to some issue yet to be resolved…
                - the code is correct, it may be issue with DB and/or sailthru… resolve later date
                */
			// look up customer, cross-ref to store
            $customerId = Mage::getModel('newsletter/subscriber')->loadByEmail($email)->getCustomerId();
            $customer = Mage::getModel('customer/customer')->load($customerId);
            $store = "";
            $store = Mage::getModel('core/store')->load($customer['store_id']);
            // check which store and put in variable for use in sailthru template
            if ($store['code']=="default" || $store['code']=="mobile") {
                $store_code = "totsy"; 
            } else {
                $store_code = "mamasource";
            }
            $vars = array('store'=>$store_code);
             
            $evars = array();
            $options = array("behalf_email" => Mage::getStoreConfig('sailthru_options/email/sailthru_sender_email'));
            for($i = 0; $i < count($emails); $i++) {
                $evars[$emails[$i]] = array("content" => $text, "subj" => $this->getProcessedTemplateSubject($variables));
                $temails .= $emails[$i].",";
            }
            
            $temails = substr($temails, 0, -1);
            $isNewRegister = Mage::registry('new_account');
            $queue = Mage::getModel('emailfactory/sailthruqueue');
            $queueData = array(
            	'call' => array(
					'class' => 'emailfactory/sailthruconfig',
            		'methods' => array(
            			'getHandle',
            			'multisend'
            		)
            	),
            	'params' => array(
            		'multisend' => compact('template_name', 'temails', 'vars', 'evars', 'options')
            	)
            );
            $isNewRegister = Mage::registry('new_account');
            if (isset($isNewRegister)){
            	$queueData['additinal_calls'] = array(
            		array(
            			'class' =>	'emailfactory/record',
            			'methods' => array(
            				'setCustomerEmail',
            				'setSendId',
            				'save'
            			),
            			'params' => array(
            				'setCustomerEmail' => $temails,
            				'setSendId' => array( 
            					'#useExtVar#' => array(
            						'name'=>'result',
            						'elements'=>'send_id'
            					)
            				)
            			)	
            		)		
            	);
            }
            $queue->addToQueue($queueData);
            unset($queue, $queueData);
            
            //echo 'check the queue';
            //exit(0);
            
           // $sailthru = Mage::getSingleton('emailfactory/sailthruconfig')->getHandle();
            //send template, rule: http://docs.sailthru.com/api/send?s[]=send
           // $success = $sailthru->multisend($template_name, $temails, $vars, $evars, $options);
            //error message is a 2 values array
           // if(count($success) == 2) {  
                //final try, to create a email template, rule http://docs.sailthru.com/api/template?s[]=savetemplate
                /* @DG-2012.05.16: removing the $temp's here as they incorrectly overwrite existing templates on Sailthru
                //$tempvars = array("content_html" => "{content}", "subject" => "{subj}");
                //$tempsuccess = $sailthru->saveTemplate($template_name, $tempvars);
                */
             //   $success = $sailthru->multisend($template_name, $temails, $vars, $evars, $options);
                //not success, use magento default send…
                //if(count($success) == 2) {
					//magento default//
					//$mail->send(); /* commenting out Magento's default send mail functionality… */
					//magento default//
					
                	//Mage::throwException($this->__($success["errormsg"]));
               // }
           // }
            /*
            $isNewRegister = Mage::registry('new_account');
            if (isset($isNewRegister)){
                $sendId = $success['send_id'];
                $record = Mage::getModel('emailfactory/record');
                $record->setCustomerEmail($temails);
                $record->setSendId($sendId);
                $record->save();
            }
            */
            //Harapartners sailthru//
            $this->_mail = null;
        }
        catch (Exception $e) {
            $this->_mail = null;
            Mage::logException($e);
            return false;
        }

        return true;
    }
}