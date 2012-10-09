<?php
/**
 *
 * @category Crown
 * @package Crown_Import
 * @since 1.0.1
 */
class Crown_Import_Model_Urapidflow_Mysql4_Catalog_Product extends Unirgy_RapidFlow_Model_Mysql4_Catalog_Product {

	/**
	 * (non-PHPdoc)
	 * @see Unirgy_RapidFlow_Model_Mysql4_Catalog_Product::_importValidateNewData()
	 */
	protected function _importValidateNewData() {
		$profile = $this->_profile;
		$logger = $profile->getLogger ();
		$storeId = $this->_storeId;
		$autoCreateAttributeSets = $profile->getData ( 'options/import/create_attributesets' );
		$autoCreateOptions = $profile->getData ( 'options/import/create_options' );
		$autoCreateCategories = $profile->getData ( 'options/import/create_categories' );
		$changeTypeSet = $profile->getData ( 'options/import/change_typeset' );
		$actions = $profile->getData ( 'options/import/actions' );
		$allowSelectIds = $profile->getData ( 'options/import/select_ids' );
		$allowNotApplicable = $profile->getData ( 'options/import/not_applicable' );

		// find changed data
		foreach ( $this->_newData as $sku => $p ) {
			try {
				$logger->setLine ( $this->_skuLine [$sku] );
				// check if the product is new
				$isNew = empty ( $this->_skus [$sku] );
				$oldProduct = $isNew ? array () : $this->_products [$this->_skus [$sku]] [0];

				if ($isNew && $actions == 'update' || ! $isNew && $actions == 'create') {
					$profile->addValue ( 'rows_nochange' );
					$this->_valid [$sku] = false;
					continue;
				}

				// validate required attributes
				$this->_valid [$sku] = true;

				$k = "product.type";
				$logger->setColumn ( isset ( $this->_fieldsCodes [$k] ) ? $this->_fieldsCodes [$k] + 1 : 0 );
				if (isset ( $p [$k] )) {
					if (isset ( $this->_defaultUsed [$sku] [$k] )) {
						$typeId = $p [$k];
					} else {
						$typeId = $this->_attr ( $k, 'options_bytext', $p [$k] );
						if (! $isNew) {
							if (! $changeTypeSet && $typeId != $oldProduct [$k]) {
								$this->_newData [$sku] [$k] = $oldProduct [$k];
								$profile->addValue ( 'num_warnings' );
								$logger->warning ( $this->__ ( 'Will not change product type for an existing product' ) );
							}
						} elseif (! $typeId) {
							$profile->addValue ( 'num_errors' );
							$logger->error ( $this->__ ( 'Empty or invalid product type for a new product' ) );
							$this->_valid [$sku] = false;
						}
					}
				} else { // not set
					if ($isNew) {
						$profile->addValue ( 'num_errors' );
						$logger->error ( $this->__ ( 'Empty or invalid product type for a new product' ) );
						$this->_valid [$sku] = false;
					} else {
						$typeId = $this->_products [$this->_skus [$sku]] [0] ['product.type'];
					}
				}

				$k = "product.attribute_set";
				$logger->setColumn ( isset ( $this->_fieldsCodes [$k] ) ? $this->_fieldsCodes [$k] + 1 : 0 );
				if (isset ( $p [$k] )) {
					if (isset ( $this->_defaultUsed [$sku] [$k] )) {
						$attrSetId = $p [$k];
					} else {
						if ($allowSelectIds && ($v = $this->_attr ( $k, 'options', $p [$k] ))) {
							$attrSetId = $p [$k];
						} else {
							$attrSetId = $this->_attr ( $k, 'options_bytext', $p [$k] );
						}
						if (! $isNew) {
							if (! $changeTypeSet && $attrSetId != $oldProduct [$k]) {
								$attrSetId = $oldProduct [$k];
								$profile->addValue ( 'num_warnings' );
								$logger->warning ( $this->__ ( 'Will not change attribute set for an existing product' ) );
							}
						} elseif (! $attrSetId) {
							if ($p [$k] && $autoCreateAttributeSets) {
								$attrSetId = $this->_importCreateAttributeSet ( $p [$k] );
								$profile->addValue ( 'num_warnings' );
								$logger->warning ( $this->__ ( "Created a new attribute set '%s'", $p [$k] ) );
							} else {
								$profile->addValue ( 'num_errors' );
								$logger->error ( $this->__ ( 'Empty or invalid attribute set for a new product' ) );
								$this->_valid [$sku] = false;
							}
						}
					}
				} else {
					if ($isNew) {
						$profile->addValue ( 'num_errors' );
						$logger->error ( $this->__ ( 'Empty or invalid attribute set for a new product' ) );
						$this->_valid [$sku] = false;
					} else {
						$attrSetId = $this->_products [$this->_skus [$sku]] [0] ['product.attribute_set'];
					}
				}

				// continue on error
				if (! $this->_valid [$sku]) {
					$profile->addValue ( 'rows_errors' );
					continue;
				}

				$p [$k] = $attrSetId;
				$this->_newData [$sku] [$k] = $attrSetId;

				$attrSetFields = $this->_getAttributeSetFields ( $attrSetId );
				$typeId = ! empty ( $typeId ) ? $typeId : $oldProduct ['type_id'];
				$isParentProduct = $typeId == 'configurable' || $typeId == 'grouped' || $typeId == 'bundle';

				$dynamic = $this->__ ( 'Dynamic' );
				$dynPrice = ($typeId == 'configurable' || $typeId == 'bundle') && (isset ( $p ['price_type'] ) && ! empty ( $p ['price_type'] ) && (in_array ( $p ['price_type'], array ($dynamic, 1 ) )) || ! isset ( $p ['price_type'] ) && ! empty ( $oldProduct ['price_type'] ));
				$dynWeight = ($typeId == 'configurable' || $typeId == 'bundle') && (isset ( $p ['weight_type'] ) && ! empty ( $p ['weight_type'] ) && (in_array ( $p ['weight_type'], array ($dynamic, 1 ) )) || ! isset ( $p ['weight_type'] ) && ! empty ( $oldProduct ['weight_type'] ));

				if ($isNew) {
					// check missing required columns
					foreach ( $this->_attributesByCode as $k => $attr ) {
						if (isset ( $p [$k] ) || empty ( $attr ['is_required'] )) {
							continue;
						}
						$appliesTo = empty ( $attr ['apply_to'] ) || ! empty ( $attr ['apply_to'] [$typeId] );
						$inAttrSet = empty ( $attr ['attribute_id'] ) || ! empty ( $attrSetFields [$k] );
						$dynAttr = $k == 'price' && $dynPrice || $k == 'weight' && $dynWeight;
						$parentQty = $k == 'stock.qty' && $isParentProduct;

						if ($appliesTo && $inAttrSet && ! $dynAttr && ! $parentQty) {
							$profile->addValue ( 'num_errors' );
							$logger->setColumn ( 1 );
							$logger->error ( $this->__ ( "Missing required value for '%s'", $k ) );
							$this->_valid [$sku] = false;
						}
					}
				}

				// walk the attributes
				foreach ( $p as $k => $newValue ) {
					$attr = $this->_attr ( $k );
					$logger->setColumn ( isset ( $this->_fieldsCodes [$k] ) ? $this->_fieldsCodes [$k] + 1 : - 1 );

					$empty = is_null ( $newValue ) || $newValue === '' || $newValue === array ();
					$required = ! empty ( $attr ['is_required'] );
					$visible = ! empty ( $attr ['is_visible'] );
					$appliesTo = empty ( $attr ['apply_to'] ) || ! empty ( $attr ['apply_to'] [$typeId] );
					$inAttrSet = empty ( $attr ['attribute_id'] ) || ! empty ( $attrSetFields [$k] );
					$selectable = ! empty ( $attr ['frontend_input'] ) && ($attr ['frontend_input'] == 'select' || $attr ['frontend_input'] == 'multiselect' || ! empty ( $attr ['source_model'] ));
					$dynAttr = $k == 'price' && $dynPrice || $k == 'weight' && $dynWeight;
					$parentQty = $k == 'stock.qty' && $isParentProduct;

					if (! $empty && $visible && (! $appliesTo || ! $inAttrSet || $dynAttr) && ! $allowNotApplicable) {
						unset ( $this->_newData [$sku] [$k] );
						$newValue = null;
						$profile->addValue ( 'num_warnings' );
						if (! $appliesTo) {
							$logger->warning ( $this->__ ( "The attribute '%s' does not apply to product type '%s', and will not be imported", $k, $typeId ) );
						} elseif (! $inAttrSet) {
							$attrSetName = $this->_attr ( 'product.attribute_set', 'options', $attrSetId );
							$logger->warning ( $this->__ ( "The attribute '%s' does not apply to attribute set '%s', and will not be imported", $k, $attrSetName ) );
						} elseif ($dynAttr) {
							$logger->warning ( $this->__ ( "The attribute '%s' is not used, as it is a dynamic value in this product, and will not be imported", $k ) );
						}
					} elseif ($empty && $required && $appliesTo && $inAttrSet && ! $dynAttr && ! $parentQty) {
						if ($typeId == 'configurable' && $selectable && ! empty ( $attr ['is_global'] ) && ! empty ( $attr ['is_configurable'] )) {
							if (! $isNew && ! $this->_write->fetchOne ( "select product_id from {$this->_t('catalog/product_super_attribute')} where product_id={$this->_skus[$sku]} and attribute_id={$attr['attribute_id']}" )) {
								$profile->addValue ( 'num_warnings' );
								$logger->warning ( $this->__ ( "If the attribute '%s' will not used in configurable subproducts, this value might be missing", $k ) );
							}
						} else {
							$profile->addValue ( 'num_errors' );
							$logger->error ( $this->__ ( "Missing required value for '%s'", $k ) );
							$this->_valid [$sku] = false;
							continue;
						}
					}

					if ($selectable && ! $empty && $k != 'product.attribute_set') {
						if ($attr ['frontend_input'] == 'multiselect' && is_array ( $newValue )) {
							$newValue = array_unique ( $newValue );
						}
						foreach ( ( array ) $newValue as $i => $v ) {
							$vLower = strtolower ( trim ( $v ) );
							if ($k == 'category.name') {
								$delimiter = ! empty ( $this->_fields [$k] ['delimiter'] ) ? $this->_fields [$k] ['delimiter'] : '>';
								$vLower = str_replace ( $delimiter, '>', $vLower );
							}
							if (isset ( $this->_defaultUsed [$sku] [$k] ) && ! in_array ( $k, array ('category.name', 'category.path' ) )) {
								// default value used, no mapping required
							} elseif (isset ( $attr ['options_bytext'] [$vLower] )) {
								$vId = $attr ['options_bytext'] [$vLower];
								if (is_array ( $newValue )) {
									$this->_newData [$sku] [$k] [$i] = $vId;
								} else {
									$this->_newData [$sku] [$k] = $vId;
								}
							} elseif ($allowSelectIds && isset ( $attr ['options'] [$v] )) {
								// select ids used, no mapping required
							} else {
								if ($k == 'category.name') {
									if ($autoCreateCategories) {
										$newOptionId = $this->_importCreateCategory ( $v );
										if (is_array ( $newValue )) {
											$this->_newData [$sku] [$k] [$i] = $newOptionId;
										} else {
											$this->_newData [$sku] [$k] = $newOptionId;
										}
										$profile->addValue ( 'num_warnings' );
										$logger->warning ( $this->__ ( "Created a new category '%s'", $v ) );
									} else {
										$profile->addValue ( 'num_errors' );
										$logger->error ( 'Invalid category: ' . $v );
										$this->_valid [$sku] = false;
									}
								} elseif ($autoCreateOptions && ! empty ( $attr ['attribute_id'] ) && (empty ( $attr ['source_model'] ) || $attr ['source_model'] == 'eav/entity_attribute_source_table')) {
									$newOptionId = $this->_importCreateAttributeOption ( $attr, $v );
									if (is_array ( $newValue )) {
										$this->_newData [$sku] [$k] [$i] = $newOptionId;
									} else {
										$this->_newData [$sku] [$k] = $newOptionId;
									}
									$profile->addValue ( 'num_warnings' );
									$logger->warning ( $this->__ ( "Created a new option '%s' for attribute '%s'", $v, $k ) );
								} else {
									if ($k != 'product.websites' || ! Mage::helper ( 'urapidflow' )->hasEeGwsFilter ()) {
										$profile->addValue ( 'num_errors' );
										$logger->error ( $this->__ ( "Invalid option '%s'", $v ) );
										$this->_valid [$sku] = false;
									}
								}
							}
						} // foreach ((array)$newValue as $v)
						if ($k == 'product.websites' && Mage::helper ( 'urapidflow' )->hasEeGwsFilter ()) {
							$wIdsOrig = ( array ) $this->_newData [$sku] [$k];
							$this->_newData [$sku] [$k] = Mage::helper ( 'urapidflow' )->filterEeGwsWebsiteIds ( $wIdsOrig );
							if (($wIdsSkipped = array_diff ( $wIdsOrig, $this->_newData [$sku] [$k] ))) {
								$logger->warning ( $this->__ ( "You are not allowed to associate products with this websites: %s", implode ( ',', $wIdsSkipped ) ) );
							}
						}
					}

					// Check for invalid characters
                    /* @var Crown_Import_Helper_Encoding $encodingHlpr */
                    $encodingHlpr = Mage::helper('crownimport/encoding');
					if ($selectable) {
						if ($attr ['frontend_input'] == 'multiselect' && is_array ( $newValue )) {
							$newValue = array_unique ( $newValue );
						}
						foreach ( ( array ) $newValue as $i => $v ) {
							try {
                                $encodingHlpr->checkForInvalidCharacter ( $v );
							} catch (Exception $e ) {
								$profile->addValue ( 'num_errors' );
								$logger->error ( $this->__ ( $e->getMessage() ) );
								$this->_valid [$sku] = false;
							}
						}
					} else {
						try {
                            $encodingHlpr->checkForInvalidCharacter ( $newValue );
						} catch (Exception $e ) {
							$profile->addValue ( 'num_errors' );
							$logger->error ( $this->__ ( $e->getMessage() ) );
							$this->_valid [$sku] = false;
						}
					}

                    // Check for media image on server or remote host
                    /* @var $mediaHlper Crown_Import_Helper_Data */
                    $mediaHlper = Mage::helper('crownimport');
                    if ( array_key_exists('frontend_input', $attr) && $attr['frontend_input']=='media_image') {
                        try {
                            $mediaHlper->checkForValidImageFiles( $newValue, $profile );
                        } catch (Exception $e ) {
                            $errorMessageId = Mage::getStoreConfig ( 'crownimport/urapidflow/missing_image_error' );
                            switch( $errorMessageId ) {
                                case Crown_Import_Model_Adminhtml_Source_Errorlevels::LEVEL_WARNING:
                                    $profile->addValue ( 'num_warnings' );
                                    $logger->warning ( $this->__ ( $e->getMessage() ) );
                                    break;
                                case Crown_Import_Model_Adminhtml_Source_Errorlevels::LEVEL_ERROR:
                                default:
                                    $profile->addValue ( 'num_errors' );
                                    $logger->error ( $this->__ ( $e->getMessage() ) );
                                    $this->_valid [$sku] = false;
                            }
                        }
                    }

				} // foreach ($p as $k=>$newValue)

				if (! $this->_valid [$sku]) {
					$profile->addValue ( 'rows_errors' );
				}
			} catch ( Unirgy_RapidFlow_Exception_Row $e ) {
				$logger->error ( $e->getMessage () );
				$profile->addValue ( 'rows_error' );
			}
		} // foreach ($this->_newData as $p)
		unset ( $p );
	}
}