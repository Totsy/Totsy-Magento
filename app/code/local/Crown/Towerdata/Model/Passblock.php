<?php
class Crown_Towerdata_Model_Passblock
{
	public function toOptionArray()
	{
		return array(
			array('value' => 1, 'label' => 'Pass'),
			array('value' => 0, 'label' => 'Block')
		);
	}
}
