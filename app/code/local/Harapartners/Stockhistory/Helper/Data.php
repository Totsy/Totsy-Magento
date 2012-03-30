<?php 
class Harapartners_Stockhistory_Helper_Data extends Mage_Core_Helper_Abstract 
{
	const STATE_PENDING = 0;
	
	const STATE_PROCESSED = 1;
	
	const STATE_FAILED = 2;
	
	private $csv_header = array('Product ID', 'Product Name', 'Product SKU', 'Size', 'Color', 'Vendor SKU', 'Qty', 'Created At', 'Updated At', 'Status', 'Comment');
	
	private $statusOptions = array(
								array('label' 	=> 'Pending', 'value' => 0), 
								array('label'	=> 'Processed', 'value'=> 1), 
								array('label'	=> 'Failed', 'value' => 2)
							);
	
	public function getCsvHeader()
	{
		return $this->csv_header;
	}
	
	public function getStatusOptions()
	{
		return $this->statusOptions;
	}
}
