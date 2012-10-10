<?php

class Crown_Vouchers_Model_Mysql4_Setup extends Mage_Core_Model_Resource_Setup {
	
	/**
	 * Installs the modules data associations
	 */
	public function installModule() {
		$this->createAttributes();
		$this->createTables();
	}
	
	/**
	 * Creates the attributes for the Module
	 */
	private function createAttributes() {
		$this->createPurchaseAttribute();
		$this->createVoucherCodeAttribute();
	}
	
	/**
	 * Creates the tables for the module
	 */
	private function createTables() {
		$this->createPurchaseTable();
	}
	
	/**
	 * Creates the purchase table
	 */
	private function createPurchaseTable() {
		$this->run ( "
			CREATE TABLE IF NOT EXISTS `{$this->getTable('vouchers/association')}` (
		  		`id` int(11) NOT NULL AUTO_INCREMENT,
		  		`product_id` int(11) DEFAULT NULL,
		  		`customer_id` int(11) DEFAULT NULL,
		 		 PRIMARY KEY (`id`)
			)ENGINE=InnoDB DEFAULT CHARSET=utf8;
            
		" );
		
		return;
	}
	
	/**
	 * Creates the one-time purchase attribute
	 */
	private function createPurchaseAttribute() {
		$this->createAttribute('one_time_purchase', 'Is One Time Purchase?', 'boolean', 'virtual');
	} 
	
	private function createVoucherCodeAttribute() {
		$this->createAttribute('voucher_code', 'Voucher Code', 'text', 'virtual');
	}
	
	
	/**
	 * Creates product attributes
	 * 
	 * @param string $code the attribute code
	 * @param string $label frontend label
	 * @param string $attribute_type text|textarea|date|boolean|multiselect|select|price|media_image|weee
	 * @param unknown_type $product_type simple|configurable|bundle|grouped|downloadable|virtual|giftcard
	 */
	private function createAttribute($code, $label, $attribute_type, $product_type) {
			$_attribute_data = array (
				'attribute_code' => $code, 
				'is_global' => '1', 
				'frontend_input' => $attribute_type, //'boolean', 
				'default_value_text' => '', 
				'default_value_yesno' => '0', 
				'default_value_date' => '', 
				'default_value_textarea' => '', 
				'is_unique' => '0', 
				'is_required' => '0', 
				'apply_to' => array ( $product_type ), //array('grouped') 
				'is_configurable' => '0', 
				'is_searchable' => '0', 
				'is_visible_in_advanced_search' => '0', 
				'is_comparable' => '0', 
				'is_used_for_price_rules' => '0', 
				'is_wysiwyg_enabled' => '0', 
				'is_html_allowed_on_front' => '1', 
				'is_visible_on_front' => '0', 
				'used_in_product_listing' => '0', 
				'used_for_sort_by' => '0', 
				'frontend_label' => array ($label)
			);
			
			$model = Mage::getModel ( 'catalog/resource_eav_attribute' );
			if (! isset ( $_attribute_data ['is_configurable'] )) {
				$_attribute_data ['is_configurable'] = 0;
			}
			if (! isset ( $_attribute_data ['is_filterable'] )) {
				$_attribute_data ['is_filterable'] = 0;
			}
			if (! isset ( $_attribute_data ['is_filterable_in_search'] )) {
				$_attribute_data ['is_filterable_in_search'] = 0;
			}
			if (is_null ( $model->getIsUserDefined () ) || $model->getIsUserDefined () != 0) {
				$_attribute_data ['backend_type'] = $model->getBackendTypeByInput ( $_attribute_data ['frontend_input'] );
			}
			$defaultValueField = $model->getDefaultValueByInput ( $_attribute_data ['frontend_input'] );
			if ($defaultValueField) {
				$_attribute_data ['default_value'] = '';
			}
			$model->addData ( $_attribute_data );
			$model->setEntityTypeId ( Mage::getModel ( 'eav/entity' )->setType ( 'catalog_product' )->getTypeId () );
			$model->setIsUserDefined ( 1 );
			try {
				$model->save ();
			} catch ( Exception $e ) {
				echo '<p>Sorry, error occured while trying to save the attribute. Error: ' . $e->getMessage () . '</p>';
			}
	}
	
}