<?php
class Harapartners_EmailFactory_Model_Mysql4_Sailthruqueue extends Mage_Core_Model_Resource_Db_Abstract
{

	protected function _construct()
	{
		$this->_init('emailfactory/sailthruqueue', 'id');
	}

}
?>