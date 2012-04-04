<?php
		
		/* 
		 * 	Now this script is only for reference and no longer work, to view the script with is really work,
		 * 	Please go to cron/unsubcentraladdscript.php
		 */
		
		
		ini_set('memory_limit', '2G');	
		$mageFilename = '../../app/Mage.php';
		require_once $mageFilename;
		Mage::app();
		umask(0);	
		
		$login = 'api_test';
		$password = 'password1';
		$listId = '130';
		 
		
		$collection=Mage::getModel('unsubcentral/item')->getCollection()
				->addFieldToFilter('unsubcentral_api_status', Harapartners_Unsubcentral_Model_Item::API_PENDING_STATUS);
		
		foreach($collection as $item){
			
			//if ($item->getUnsubcentralApiStatus()==Harapartners_Unsubcentral_Model_Item::API_PENDING_STATUS){
				$fields_string = "";
		        $params = $_POST;
				$email = $item->getData('subscriber_email');	
				$urlencoded_email = urlencode($email);
				//set POST variables - activate a user on the opt out list
				$url = 'https://login8.unsubcentral.com/uc/address_upload.pl?';
				$fields = array(
						'login' => $login,
						'password' => urlencode($password),
						'listID' => $listId,
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
				
				//to download file, plx use "https://login8.unsubcentral.com/uc/list_download.pl?login=api_test&password=password1&format=plaintext&zipped=false&listID=130"
				//or use "https://login8.unsubcentral.com/uc/list_download.pl?login=api_test&password=password1&format=plaintext&zipped=false&listID=130&start_date=2011-10-30&end_date=2012-11-30"

		}
		
		
		echo ('done');
		echo ('<br/>in order to check, plx use https://login8.unsubcentral.com/uc/list_download.pl?login=api_test&password=password1&format=plaintext&zipped=false&listID=130');