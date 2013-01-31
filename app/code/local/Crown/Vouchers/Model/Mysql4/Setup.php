<?php

class Crown_Vouchers_Model_Mysql4_Setup extends Mage_Eav_Model_Entity_Setup {
	
	/**
	 * Installs the modules data associations
	 */
	public function installModule() {
		$this->createAttributes();
		$this->createTables();
	}

    /**
     *
     */
    public function upgradeModule_1_1() {
        $this->createRegularVoucherPriceAttribute();
        $this->createDiscountVoucherPriceAttribute();
    }

    public function upgradeModule_1_2() {
        $this->createEntertainmentSavingsAttribute();
    }

    public function upgradeModule_1_3() {
        $this->createEntertainmentStateAttribute();
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

    private function createRegularVoucherPriceAttribute() {
        $this->createAttribute('regular_voucher_price', 'Regular Voucher Price', 'price', 'virtual');
    }

    private function createDiscountVoucherPriceAttribute() {
        $this->createAttribute('discount_voucher_price', 'Discount Voucher Price', 'price', 'virtual');
    }

    private function createEntertainmentSavingsAttribute() {
        $this->createAttribute('entertainment_savings', 'Is Entertainment Savings Voucher?', 'boolean', 'virtual');
    }

    private function createEntertainmentStateAttribute() {
        $this->addAttribute('catalog_product', 'entertainment_savings_state', array(
            'backend'       => 'eav/entity_attribute_backend_array',
            'frontend'      => '',
            'label'         => 'Entertainment Savings State',
            'input'         => 'multiselect',
            'class'         => '',
            'source'        => 'eav/entity_attribute_source_table',
            'global'        => true,
            'visible'       => true,
            'required'      => false,
            'user_defined'  => true,
            'default'       => '',
            'apply_to'      => 'simple',
            'is_configurable'  => 0,
            'visible_on_front' => false,
            'option' => array (
                'value' => array(
                    'AL'=> array('AL', 'AL'),
                    'AK'=> array('AK', 'AK'),
                    'AZ'=> array('AZ', 'AZ'),
                    'AR'=> array('AR', 'AR'),
                    'CA'=> array('CA', 'CA'),
                    'CO'=> array('CO', 'CO'),
                    'CT'=> array('CT', 'CT'),
                    'DE'=> array('DE', 'DE'),
                    'DC'=> array('DC', 'DC'),
                    'FL'=> array('FL', 'FL'),
                    'GA'=> array('GA', 'GA'),
                    'HI'=> array('HI', 'HI'),
                    'ID'=> array('ID', 'ID'),
                    'IL'=> array('IL', 'IL'),
                    'IN'=> array('IN', 'IN'),
                    'IA'=> array('IA', 'IA'),
                    'KS'=> array('KS', 'KS'),
                    'KY'=> array('KY', 'KY'),
                    'LA'=> array('LA', 'LA'),
                    'ME'=> array('ME', 'ME'),
                    'MD'=> array('MD', 'MD'),
                    'MA'=> array('MA', 'MA'),
                    'MI'=> array('MI', 'MI'),
                    'MN'=> array('MN', 'MN'),
                    'MS'=> array('MS', 'MS'),
                    'MO'=> array('MO', 'MO'),
                    'MT'=> array('MT', 'MT'),
                    'NE'=> array('NE', 'NE'),
                    'NV'=> array('NV', 'NV'),
                    'NH'=> array('NH', 'NH'),
                    'NJ'=> array('NJ', 'NJ'),
                    'NM'=> array('NM', 'NM'),
                    'NY'=> array('NY', 'NY'),
                    'NC'=> array('NC', 'NC'),
                    'ND'=> array('ND', 'ND'),
                    'OH'=> array('OH', 'OH'),
                    'OK'=> array('OK', 'OK'),
                    'OR'=> array('OR', 'OR'),
                    'PA'=> array('PA', 'PA'),
                    'PR'=> array('PR', 'PR'),
                    'RI'=> array('RI', 'RI'),
                    'SC'=> array('SC', 'SC'),
                    'SD'=> array('SD', 'SD'),
                    'TN'=> array('TN', 'TN'),
                    'TX'=> array('TX', 'TX'),
                    'UT'=> array('UT', 'UT'),
                    'VT'=> array('VT', 'VT'),
                    'VA'=> array('VA', 'VA'),
                    'WA'=> array('WA', 'WA'),
                    'WV'=> array('WV', 'WV'),
                    'WI'=> array('WI', 'WI'),
                    'WY'=> array('WY', 'WY')
                )
            )
        )
    );

    }


	/**
	 * Creates product attributes
	 * 
	 * @param string $code the attribute code
	 * @param string $label frontend label
	 * @param string $attribute_type text|textarea|date|boolean|multiselect|select|price|media_image|weee
	 * @param string $product_type simple|configurable|bundle|grouped|downloadable|virtual|giftcard
     * @param array $options The options available for the attribute, if applicable
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
				echo '<p>Sorry, error occurred while trying to save the attribute. Error: ' . $e->getMessage () . '</p>';
			}
	}



}