<?php
ini_set('memory_limit', '4096M');	
ini_set("max_execution_time","1800000"); 
$magePath = '../';
$mageFilename = '../../../app/Mage.php';
require_once $mageFilename;
umask(0);
$mageRunCode = isset($_SERVER['MAGE_RUN_CODE']) ? $_SERVER['MAGE_RUN_CODE'] : '';
$mageRunType = isset($_SERVER['MAGE_RUN_TYPE']) ? $_SERVER['MAGE_RUN_TYPE'] : 'store';
Mage::app($mageRunCode, $mageRunType);//->loadArea('frontend');
//Varien_Profiler::enable();

$importCsvFile		= $argv[1];
$delimiter		= ',';
$header			= array();
$row 			= 0;
//Varien_Profiler::start('test');
if(($handle = fopen($importCsvFile,'r')) !== FALSE){
	echo "Start import!\n";
	while(($data = fgetcsv($handle))){
		$row++;
		if($row === 1){
			$header = array_flip($data);
			continue;
		}
		if($row%1000 == 0) {
			echo $row.' customer e-mail: '.$data[$header['email']]."\n";
			//echo print_r($data, 1) . "\n\n";
		}
	
		createCustomerShippingAddress($header,$data);
		//createCustomerBillingAddress($header,$data);
	}
}
else {
	echo "File not found!\n";
}
//Varien_Profiler::stop('test');
echo "DONE!\n";
fclose($handle);
exit();

function createCustomerShippingAddress($header,$data){
        $customerAddress = Mage::getModel('customer/address');
	$email = _trimGmail($data[$header['email']]);
        $customer = Mage::getModel('customer/customer')->setWebsiteId(1)->loadByEmail($email);
        if($customer->getId()){
                $customerAddress->setData('parent_id', $customer->getId());
                $customerAddress->setData('is_active', '1');
		
		$customerAddress->setData('firstname', $data[$header['firstname']]);
		$customerAddress->setData('lastname', $data[$header['lastname']]);

                $customerAddress->setData('city', $data[$header['city']]);
                $customerAddress->setData('country_id', $data[$header['country']]);
                $customerAddress->setData('region', $data[$header['region']]);
                $customerAddress->setData('postcode', $data[$header['postcode']]);
                $customerAddress->setData('telephone', $data[$header['telephone']]);
                $customerAddress->setData('region_id', Mage::getModel('directory/region')->loadByCode($data[$header['region']],$data[$header['country']])->getRegionId());
                $customerAddress->setData('street', $data[$header['street_full']]);
                $customerAddress->setCustomer($customer);
                try {
                        $customerAddress->save();
                        //$customer->setData('default_shipping',$customerAddress->getId());
                        $customer->save();
                }catch (Exception $e){
                        echo 'Cannot Save Address: '.$e->getMessage()."\n";
			$fp = fopen('failed_address_data.csv', 'a+');
			fputcsv($fp, $data);
			fclose($fp);
                }
        }
}

function createCustomerBillingAddress($header,$data){
        $customerAddress = Mage::getModel('customer/address');
	$email = _trimGmail($data[$header['email']]);
        $customer = Mage::getModel('customer/customer')->setWebsiteId(1)->loadByEmail($email);
        if($customer->getId()){
                $customerAddress->setData('parent_id', $customer->getId());
                $customerAddress->setData('is_active', '1');
		
		$customerAddress->setData('firstname', $data[$header['firstname']]);
		$customerAddress->setData('lastname', $data[$header['lastname']]);

                $customerAddress->setData('city', $data[$header['city']]);
                $customerAddress->setData('country_id', $data[$header['country']]);
                $customerAddress->setData('region', $data[$header['region']]);
                $customerAddress->setData('postcode', $data[$header['postcode']]);
                $customerAddress->setData('telephone', $data[$header['telephone']]);
                $customerAddress->setData('region_id', Mage::getModel('directory/region')->loadByCode($data[$header['region']],$data[$header['country']])->getRegionId());
                $customerAddress->setData('street', $data[$header['street_full']]);
                $customerAddress->setCustomer($customer);
                try {
                        $customerAddress->save();
                        $customer->setData('default_billing',$customerAddress->getId());
                        $customer->save();
                }catch (Exception $e){
                        echo 'Cannot Save Address: '.$e->getMessage()."\n";
			$fp = fopen(' /var/www/magento_db_import/dev/import/customers/import_log/failed_address_data.csv', 'a+');
			fputcsv($fp, $data);
			fclose($fp);
                }
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
