<?php 

/**
 * Notes from Li Lu, Harapartners
 * Prerequisite: 
 * Step 1: enterprise_reward_history and enterprise_reward are related tables for credit import. duplicated entries will be duplicated instead of updating, so truncate table if neccessary.
 * Step 2: on topnav in magento admin panel -> Customer -> Reward Exchange Rates, change the configuration to: 100 points to 1 currency
 * Step 3: temporarily comment the line "if ((int)$this->getPointsDelta() != 0 || $this->getCappedReward()) {" (around line 141) in _afterSave method in app\code\local\Enterprise\Reward\Model\Reward.php
 * Step 4: temporarily change the line around line 104 to "$this->addData(array('created_at' => $this->getReward()->getCreated() ? $this->getReward()->getCreated() : $this->getResource()->formatDate($now)" in _beforeSave method in app\code\local\Enterprise\Reward\Model\Reward\History.php
 * Usage: put the script in the same folder where the csv is located, and the name MUST be customer_credit.csv (you can change it to args if you like)
 * Input: csv with 1st column => 'customer_email', 2nd column => 'credit_currency_amount', 3rd column => 'comment', 4th column => 'created'
 * Output: UPDATE => success; SKIP => customer email does not exist; ERROR => exception caught
 * Please remember to change the code back otherwise it will influence some functionality
 */

require_once '../../../../app/Mage.php'; 
Mage::app();

$importCsvFile = 'customer_credit.csv';
$delimiter		= ',';

//$defaultStore = Mage::app()->getDefaultStoreView();
$websiteId = 1;

if(($handle = fopen($importCsvFile,'r')) !== FALSE){
	while(($data = fgetcsv($handle, 4096, $delimiter, '"'))){
		$row++;
		if($row === 1){
			continue;
		}
		$customer = Mage::getModel('customer/customer')->setWebsiteId($websiteId)->loadByEmail(_trimGmail($data[0]));
		if( $customer->getId() && is_numeric( $data[1]*100 ) ) {
			$storeId = $customer->getStoreId();
			$reward = Mage::getModel('enterprise_reward/reward')
						    ->setCustomer($customer)
						    ->setWebsiteId($websiteId/*Mage::app()->getStore($storeId)->getWebsiteId()*/)
						    ->loadByCustomer();
			$rewardData = array( 'store_id' => $storeId, 'points_delta' => $data[1]*100, 'comment' => $data[2], 'created' => $data[3] );
			try{
				$reward->addData($rewardData)
				     ->setAction(Enterprise_Reward_Model_Reward::REWARD_ACTION_ADMIN)
				     ->setActionEntity($customer)
				     ->updateRewardPoints();
				echo '[UPDATE] customer_email:' . $customer->getEmail() . ' points_delta: ' . $rewardData['points_delta'] . ' comment: ' . $data[2] . PHP_EOL;
			}catch(Exception $e) {
				echo '[ERROR] customer_email:' . $customer->getEmail() . ' message: ' . $e->getMessage();
			}
		} else {
			echo '[SKIP] customer_email: ' . $data[0] . ' points_delta: ' . $rewardData['points_delta'] . PHP_EOL;
		}
	}
}

fclose($handle);
echo 'DONE!' . PHP_EOL;


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
