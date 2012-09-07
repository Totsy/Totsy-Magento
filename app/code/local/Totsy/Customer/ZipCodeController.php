<?php
class Totsy_Customer_ZipCodeController extends Mage_Customer_AddressController {

	public function zipCodeLookup()
	{
		$fake = array(
			'New York' => 'NY',
			'Miami' => 'FL',
			'Philadelphia' => 'PA',
			'Crystal Springs' => 'MI'
		);
		echo json_encode();
	}
	
}

?>