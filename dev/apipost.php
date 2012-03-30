<?php 
ini_set('memory_limit', '2G');	
$mageFilename = '../app/Mage.php';
require_once $mageFilename;
Mage::app();
$baseUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . 'mobileapi';



$url = $baseUrl . "/user/13";
$data = array(
	'firstname'		=>	'Song',
	'lastname'		=>	'Gao',
	'street'		=>	'136 SW36th Street',
	'region'		=>	'New York',
	'region_id'		=>	43,
	'city'			=>	'New York City',
	'country_id'	=>	'US',
	'postcode'		=>	10018,
	'telephone'		=>  3522139111,
);

$ch = curl_init();
//curl_setopt ($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
//curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data);
curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
curl_setopt ( $ch, CURLOPT_URL, $url );
curl_setopt ( $ch, CURLOPT_POST, 0);
curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, false );
curl_setopt ( $ch, CURLOPT_FOLLOWLOCATION, true );
$result = curl_exec ( $ch );
curl_close ( $ch );
echo $result;	
?>

<!--form action=<?php echo $url;?> method="POST">
	<input type="hidden" name="json" value='<?php echo $data; ?>'/>
	<button type="submit">Submit</button>
</form-->