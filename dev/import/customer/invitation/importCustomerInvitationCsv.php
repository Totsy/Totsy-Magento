<?php
/**
 * Note: Li Lu, Harapartners
 * Prerequisites:
 * Step 1: enterprise_invitation, enterprise_invitation_status_history, enterprise_invitation_track are tables related to invitation import. duplicated entries will be duplicated instead of updating, so truncate table if neccessary.
 * Step 2: temporarily comment the line $this->makeSureCustomerNotExists() (around line 157) in _beforeSave method in app\code\local\Enterprise\Invitation\Model\Invitation.php
 * Step 3: temporarily comment the lines before return $this (around line 125) in invitationToCustomer method to prevent interfering credits
 * Usage: put the script in the same folder where the csv file is located, the name of the csv MUST be customer_invitation.csv (you can change it to args if you like)
 * Input: csv file with header including at least customer_email(required), email(required), status(required), message(can be empty), invitation_date(can be empty), signup_date(can be empty)
 * Output: SAVED = success; SKIP = customer email does not exist; ERROR = exception caught
 * Please remember to change the code back otherwise it will influence some functionality
 */
ini_set('memory_limit', '2048M');	
ini_set("max_execution_time",0); 
$magePath = '../';
$mageFilename = '../../../../app/Mage.php';
require_once $mageFilename;
umask(0);
$mageRunCode = isset($_SERVER['MAGE_RUN_CODE']) ? $_SERVER['MAGE_RUN_CODE'] : '';
$mageRunType = isset($_SERVER['MAGE_RUN_TYPE']) ? $_SERVER['MAGE_RUN_TYPE'] : 'store';
Mage::app($mageRunCode, $mageRunType);//->loadArea('frontend');
//Varien_Profiler::enable();



$importCsvFile 	= 'customer_invitation.csv';
$delimiter		= ',';
$header			= array();
$row 			= 0;
$count			= 0;
if(($handle = fopen($importCsvFile,'r')) !== FALSE){
	while(($data = fgetcsv($handle, 4096, $delimiter, '"'))){
		$row++;
		if($row === 1){
			$header = array_flip($data);
			continue;
		}
		if($data[$header['customer_email']] == ''){
			echo $row." Skipped *********************************************************** \r\n";
			continue;
		}

		//Start
		$invitation = Mage::getModel('enterprise_invitation/invitation');
		$customer = Mage::getModel('customer/customer')->setWebsiteId(1)->loadByEmail(_trimGmail($data[$header['customer_email']]));
		if(!!$customer && $customer->getId()){
			$invitation->setData(array(
                        'email'    => $data[$header['email']],
                        'customer' => $customer,
                        'message'  => $data[$header['message']], 
                    ));
			
			try {
				//First save for FK
				$invitation->save();
				$invitation->setData('invitation_date', $data[$header['invitation_date']] ? $data[$header['invitation_date']] : $invitation->getResource()->formatDate(time()));
				$invitation->setData('status', $data[$header['status']]);
				if( $data[$header['status']] == 'accepted') {
					if($data[$header['email']] ) {
						$invitee = Mage::getModel( 'customer/customer' )->setWebsiteId( 1 )->loadByEmail( _trimGmail($data[$header['email']]) );
						if( $invitee->getId() ) {
							$invitation->setReferralId($invitee->getId());
							if( $data[$header['signup_date']] ) {
					            $invitation->setSignupDate($data[$header['signup_date']] ? $data[$header['signup_date']] : $invitation->getResource()->formatDate(time()));
							}
				            $invitation->save();
							$invitation->getResource()->trackReferral($customer->getId(),$invitee->getId());
						}
					}
				} else {
					$invitation->save();
				}
				echo '[SAVED] Row: '.$row. 'Customer Email: '.$data[$header['email']]."\r\n";
				$count++;  //For testing.
			}catch (Exception $e){
				echo '[ERROR]' . $e->getMessage()."\r\n";
			}
//			if($count > 5){
//				exit();
//			}
		}else {		
			echo '[SKIP] Customer Does not exist: '.$data[$header['customer_email']]."\r\n";
		}

	}
}
//Varien_Profiler::stop('test');
echo 'DONE!';
fclose($handle);

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
