<?php
		ini_set('memory_limit', '2G');	
		$mageFilename = '../app/Mage.php';
		require_once $mageFilename;
		Mage::app();
		umask(0);	
		
		$login = 'api_test';
		$password = 'password1';
		//CHECK THESE TWO LIST ON Harapartners_Unsubcentral_Model_Item
		//$listId = '130';  // should be 113 in live  
		//$registerlistId = '142'  // should be 116 in live
		
		$collection=Mage::getModel('unsubcentral/item')->getCollection()
				->addFieldToFilter('unsubcentral_api_status', Harapartners_Unsubcentral_Model_Item::API_PENDING_STATUS);
		
				
		$date_today = date("Y-m-d");
		
		$unsubcentral_filename = BP.DS.'cron'.DS."tmp".DS."unsubcentral-".$date_today.".txt";
		$temp_file_string ='';		
		foreach($collection as $item){
			
			//if ($item->getUnsubcentralApiStatus()==Harapartners_Unsubcentral_Model_Item::API_PENDING_STATUS){
				$fields_string = "";
		        $params = $_POST;
				$email = $item->getData('subscriber_email');	
				$urlencoded_email = urlencode($email);
			

				//set POST variables - activate a user on the opt out list   (LIVE 113, TEST 130)
				$url = 'https://login8.unsubcentral.com/uc/address_upload.pl?';
				$fields = array(
						'login' => $login,
						'password' => urlencode($password),
						'listID' => Harapartners_Unsubcentral_Model_Item::UNSUBSCRIBE_LIST,
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
					$item->setUnsubcentralApiStatus(Harapartners_Unsubcentral_Model_Item::API_PRECESSED_STATUS);
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
										'listID'=>Harapartners_Unsubcentral_Model_Item::REGISTER_LIST,
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
				
				//remove the unsubcentral upload file after it is used for upload
				unlink($unsubcentral_filename);
		
		
		
		echo ('done');
		echo ('<br/>in order to check, plx use https://login8.unsubcentral.com/uc/list_download.pl?login=api_test&password=password1&format=plaintext&zipped=false&listID=130');