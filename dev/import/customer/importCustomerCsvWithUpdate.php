<?php
ini_set('memory_limit', '2048M');	
ini_set("max_execution_time","1800000"); 
$magePath = '../';
$mageFilename = '../../../app/Mage.php';
require_once $mageFilename;
umask(0);
$mageRunCode = isset($_SERVER['MAGE_RUN_CODE']) ? $_SERVER['MAGE_RUN_CODE'] : '';
$mageRunType = isset($_SERVER['MAGE_RUN_TYPE']) ? $_SERVER['MAGE_RUN_TYPE'] : 'store';
Mage::app($mageRunCode, $mageRunType);//->loadArea('frontend');
//Varien_Profiler::enable();

//$importCsvFile 	= 'customer.csv';
$importCsvFile		= $argv[1];
$delimiter		= ',';
$header			= array();
$row 			= 0;
//Varien_Profiler::start('test');
if(($handle = fopen($importCsvFile,'r')) !== FALSE){
	echo "Start Import!\n";
	while(($data = fgetcsv($handle, 4096, $delimiter, '"'))){
		$row++;
		if($row === 1){
			$header = array_flip($data);
			continue;
		}

		if(empty($data[$header['email']])) {
			continue;
		}
		
		createCustomer($header,$data);
		
		//if($row%1000 == 0) {
			echo $row.' customer e-mail: '.$data[$header['email']]."\n";
		//}
	}
}
else {
	echo "File not found!\n";
}
//Varien_Profiler::stop('test');
echo "DONE!\n";
fclose($handle);
exit();

function createCustomer($header,$data){
	try {	
		$email = _trimGmail($data[$header['email']]);

		$customer = Mage::getModel('customer/customer')->setWebsiteId(1)->loadByEmail($email);

		if($customer === null) {
			return false;
		}

		/*
		 * Set New Customer Data Here
		 */
		$customer->setData('is_active','1');
		$customer->setData('website_id', '1');
		$customer->setData('store_id',$data[$header['store_id']]);
		$customer->setData('group_id',$data[$header['group_id']]);
		
		$customer->setData('email', $email);
		$customer->setData('created_in', 'totsy');
		$customer->setData('firstname', $data[$header['firstname']]);
		$customer->setData('lastname', $data[$header['lastname']]);
		$customer->setData('password_hash', $data[$header['password_hash']]);
		$customer->setData('reward_update_notification', '0');
		$customer->setData('reward_warning_notification', '0');
		$customer->setData('legacy_customer','1');
		$customer->setData('facebook_uid',$data[$header['facebook_uid']]);
		
		$customer->setData('created_at',$data[$header['created_at']]);

		$customer->setLoginCounter($data[$header['logincounter']]);
		$customer->setPurchaseCounter($data[$header['purchasecounter']]);
		$customer->setEmailMd5(md5($email));
		$customer->setDeactivated($data[$header['deactivated']]);
		
		//force remove customers
/*
		$addresses = $customer->getAddresses();
		foreach($addresses as $address) {
			$address->delete();
		}
*/
		$customer->save();

	}
	catch (Exception $e){
		echo 'Cannot Save Customer ' . $data[$header['email']] . ' : '.$e->getMessage()."\n";
		$fp = fopen('failed_customer.csv', 'a+');
		fputcsv($fp, $data);
		fclose($fp);	
	}
}

function _trimGmail($email) {
        $strArray = explode('@', $email);

        if(empty($strArray) ||
           empty($strArray[1]) ||
           $strArray[1] != 'gmail.com') {
                return $email;
        }

        //get username, such as 'abcd'
        $username = $strArray[0];
        //Get username string's length
        $len = strlen($username);
        $trimmedGmail = '';

        //iterate chacrates in username string
        for($j=0; $j<$len; $j++) {
                //if encounters '+', discard the rest of the string
                if($username[$j] == '+') {
                        break;
                }

                //check if it is '.', if yes, don't concatenate.
                if($username[$j] != '.') {
                        //concatenate username chacrater
                        $trimmedGmail .= $username[$j];
                }
        }

        $trimmedGmail .= '@gmail.com';

        return $trimmedGmail;
}



?>
