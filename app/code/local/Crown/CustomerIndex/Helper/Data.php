<?php
class Crown_CustomerIndex_Helper_Data extends Mage_Core_Helper_Abstract {
	
	/**
	 * Refreshes the customer grid flat table.
	 * Can be used to refresh the whole table or a specific entity.
	 * 
	 * @param int $entityId
	 * @return boolean
	 */
	public function reindexCustomerFlat( $entityId = null ) {
		if ( null !== $entityId ) {
			$flatEntity = Mage::getModel('CustomerIndex/CustomerIndex')->load($entityId);
			if ( !$flatEntity->getEntityId() )
				$flatEntity = false;
		} else {
			$flatEntity = null;
		}
		
		$db = Mage::getSingleton('core/resource')->getConnection('core_write');
		$coreResource = Mage::getSingleton('core/resource');
		
		$customerEntity = Mage::getModel('customer/customer')->getCollection()->getEntity();
		$customerAddressEntity = Mage::getModel('customer/address')->getCollection()->getEntity();
		
		$customerEntityType = $customerEntity->getType();
		$customerEntityId = $customerEntity->getTypeId();
		$customerAddressEntityType = $customerAddressEntity->getType();
		
		$customerAttributes = array(
			'default_billing','firstname','lastname','prefix','middlename','suffix'
		);
		
		$addressAttributes = array(
			'country_id','region','postcode','city','telephone'
		);
		
		$attributeValues = array();
		$attributeTables = array();
		
		foreach ( $customerAttributes as $attribute ) {
			$_attribute = Mage::getSingleton('eav/config')->getCollectionAttribute($customerEntityType, $attribute);
			$attributeValues[$attribute] = $_attribute->getId();
			$attributeTables[$attribute] = $_attribute->getBackendTable();
		}
		
		foreach ( $addressAttributes as $attribute ) {
			$_attribute = Mage::getSingleton('eav/config')->getCollectionAttribute($customerAddressEntityType, $attribute);
			$attributeValues[$attribute] = $_attribute->getId();
			$attributeTables[$attribute] = $_attribute->getBackendTable();
		}
		
		extract($attributeValues, EXTR_OVERWRITE);
			
		// Create flat grid structure of data
		if ( false === $flatEntity ) {
			$query = null;
			$queryExtra = "AND `e`.`entity_id` = '{$entityId}'";
		} elseif ( is_object( $flatEntity ) ) {
			$query = "DELETE FROM `{$coreResource->getTableName('CustomerIndex/CustomerIndex')}` WHERE `entity_id` = '{$flatEntity->getEntityId()}';";
			$queryExtra = "AND `e`.`entity_id` = '{$flatEntity->getEntityId()}'";
		} else {
			$query = "TRUNCATE TABLE `{$coreResource->getTableName('CustomerIndex/CustomerIndex')}`;";
			$queryExtra = null;
		}
		$query .= "
		INSERT IGNORE INTO `{$coreResource->getTableName('CustomerIndex/CustomerIndex')}`
			(`entity_id`,`website_id`,`email`,`group_id`,`created_at`,`firstname`, `lastname`,`customer_name`,`billing_postcode`,`billing_telephone`,`billing_region`,`billing_country_id`,`store_id`)
			(
			SELECT
				`e`.`entity_id`, 
				`e`.`website_id`, 
				`e`.`email`, 
				`e`.`group_id`, 
				`e`.`created_at`, 
				`at_firstname`.`value` AS 'firstname',
				`at_lastname`.`value` AS 'lastname',
				CONCAT_WS(' ', IF(at_prefix.value IS NOT NULL AND at_prefix.value != '', LTRIM(RTRIM(at_prefix.value)), ''), LTRIM(RTRIM(at_firstname.value)), IF(at_middlename.value IS NOT NULL AND at_middlename.value != '', LTRIM(RTRIM(at_middlename.value)), ''), LTRIM(RTRIM(at_lastname.value)), IF(at_suffix.value IS NOT NULL AND at_suffix.value != '', LTRIM(RTRIM(at_suffix.value)), '')) AS `name`, 
				`at_billing_postcode`.`value` AS `billing_postcode`, 
				`at_billing_telephone`.`value` AS `billing_telephone`, 
				`at_billing_region`.`value` AS `billing_region`, 
				`at_billing_country_id`.`value` AS `billing_country_id`, 
				`e`.`store_id` AS `store_id` 
			FROM 
			`{$coreResource->getTableName('customer/entity')}` AS `e`
			LEFT JOIN `{$attributeTables['prefix']}` 
				AS `at_prefix` ON (`at_prefix`.`entity_id` = `e`.`entity_id`) AND (`at_prefix`.`attribute_id` = '{$prefix}')
			LEFT JOIN `{$attributeTables['firstname']}` 
				AS `at_firstname` ON (`at_firstname`.`entity_id` = `e`.`entity_id`) AND (`at_firstname`.`attribute_id` = '{$firstname}')
			LEFT JOIN `{$attributeTables['middlename']}` 
				AS `at_middlename` ON (`at_middlename`.`entity_id` = `e`.`entity_id`) AND (`at_middlename`.`attribute_id` = '{$middlename}')
			LEFT JOIN `{$attributeTables['lastname']}` 
				AS `at_lastname` ON (`at_lastname`.`entity_id` = `e`.`entity_id`) AND (`at_lastname`.`attribute_id` = '{$lastname}')
			LEFT JOIN `{$attributeTables['suffix']}` 
				AS `at_suffix` ON (`at_suffix`.`entity_id` = `e`.`entity_id`) AND (`at_suffix`.`attribute_id` = '{$suffix}')
			LEFT JOIN `{$attributeTables['default_billing']}` 
				AS `at_default_billing` ON (`at_default_billing`.`entity_id` = `e`.`entity_id`) AND (`at_default_billing`.`attribute_id` = '{$default_billing}')
			LEFT JOIN `{$attributeTables['postcode']}` 
				AS `at_billing_postcode` ON (`at_billing_postcode`.`entity_id` = `at_default_billing`.`value`) AND (`at_billing_postcode`.`attribute_id` = '{$postcode}')
			LEFT JOIN `{$attributeTables['city']}` 
				AS `at_billing_city` ON (`at_billing_city`.`entity_id` = `at_default_billing`.`value`) AND (`at_billing_city`.`attribute_id` = '{$city}')
			LEFT JOIN `{$attributeTables['telephone']}` 
				AS `at_billing_telephone` ON (`at_billing_telephone`.`entity_id` = `at_default_billing`.`value`) AND (`at_billing_telephone`.`attribute_id` = '{$telephone}')
			LEFT JOIN `{$attributeTables['region']}` 
				AS `at_billing_region` ON (`at_billing_region`.`entity_id` = `at_default_billing`.`value`) AND (`at_billing_region`.`attribute_id` = '{$region}')
			LEFT JOIN `{$attributeTables['country_id']}` 
				AS `at_billing_country_id` ON (`at_billing_country_id`.`entity_id` = `at_default_billing`.`value`) AND (`at_billing_country_id`.`attribute_id` = '{$country_id}') 
			WHERE (`e`.`entity_type_id` = '{$customerEntityId}')
			{$queryExtra}
			);
		";
		return $db->query($query);
	}
}