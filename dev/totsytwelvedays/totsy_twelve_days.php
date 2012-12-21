<?php

/**
 * $templateId can be set to numeric or string type value.
 * You can use Id of transactional emails (found in
 * "System->Trasactional Emails"). But better practice is
 * to create a config for this and use xml path to fetch
 * email template info (whatever from file/db).
 */

ini_set("display_errors", 1);

require_once __DIR__ . '../../../app/Mage.php';
Mage::app();

/**
 * $sender can be of type string or array. You can set identity of
 * diffrent Store emails (like 'support', 'sales', etc.) found
 * in "System->Configuration->General->Store Email Addresses"
 */
$senderName = "Totsy Contestant";
$senderEmail = $_POST['email'];
$recipientEmail = "yhoshino@totsy.com";

$sender = array('name' => $senderName,
	'email' => $senderEmail);

$name = 'Tester';
$clue_one = $_POST['clue_1'];
$clue_two = $_POST['clue_2'];
$clue_three = $_POST['clue_3'];
$clue_four = $_POST['clue_4'];
$clue_five = $_POST['clue_5'];
$clue_six = $_POST['clue_6'];
$clue_seven = $_POST['clue_7'];
$clue_eight = $_POST['clue_8'];
$clue_nine = $_POST['clue_9'];
$clue_ten = $_POST['clue_10'];
$clue_eleven = $_POST['clue_11'];
$clue_twelve = $_POST['clue_12'];

$contestantEmail = $_POST['email'];
$twitter_account = $_POST['twitter_account'];

$vars = array();

$translate = Mage::getSingleton('core/translate');
$translate->setTranslateInline(false);

$storeId = Mage::app()->getStore()->getId();
$templateId = Mage::getModel('core/email_template')->loadByCode('_trans_Twelve_Days_Of_Totsy')->getId();

$vars = array("twitter_account" => $twitter_account,
	"contestantEmail" => $contestantEmail,
	"clue_one" => $clue_one,
	"clue_two" => $clue_two,
	"clue_three" => $clue_three,
	"clue_four" => $clue_four,
	"clue_five" => $clue_five,
	"clue_six" => $clue_six,
	"clue_seven" => $clue_seven,
	"clue_eight" => $clue_eight,
	"clue_nine" => $clue_nine,
	"clue_ten" => $clue_ten,
	"clue_eleven" => $clue_eleven,
	"clue_twelve" => $clue_twelve);

Mage::getModel('core/email_template')->setTemplateSubject('12 Days of Totsy')
->sendTransactional( $templateId,
	$sender,
	$recipientEmail,
	$name,
	$vars );

$translate->setTranslateInline(true);

return false;

?>