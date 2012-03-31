<?php 
class Harapartners_Stockhistory_Helper_Data extends Mage_Core_Helper_Abstract 
{
	const STATE_PENDING = 0;
	
	const STATE_PROCESSED = 1;
	
	const STATE_FAILED = 2;
	
	private $csv_header = array('Product ID', 'Product Name', 'Product SKU', 'Size', 'Color', 'Vendor SKU', 'Qty', 'Created At', 'Updated At', 'Status', 'Comment');
	
	private $statusOptions = array(
								'Pending' => 0, 
								'Processed' => 1, 
								'Failed'  => 2
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
