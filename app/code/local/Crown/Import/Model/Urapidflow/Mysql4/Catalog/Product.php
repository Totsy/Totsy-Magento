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

                if (!$isNew) {
                    $k = "description";
                    $logger->setColumn ( isset ( $this->_fieldsCodes [$k] ) ? $this->_fieldsCodes [$k] + 1 : 0 );
                    $this->_newData [$sku] [$k] = $oldProduct [$k];
                }

                if (!$isNew) {
                    $k = "short_description";
                    $logger->setColumn ( isset ( $this->_fieldsCodes [$k] ) ? $this->_fieldsCodes [$k] + 1 : 0 );
                    $this->_newData [$sku] [$k] = $oldProduct [$k];
                }

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
                    $inAttrSet = true;
                    //$inAttrSet = empty ( $attr ['attribute_id'] ) || ! empty ( $attrSetFields [$k] );
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
                    if ( is_array($attr) && array_key_exists('frontend_input', $attr) && $attr['frontend_input']=='media_image') {
                        try {
                            $mediaHlper->checkForValidImageFiles( $newValue, $profile );
                        } catch (Exception $e ) {
                            $errorMessageId = Mage::getStoreConfig ( 'crownimport/urapidflow/missing_image_error' );
                            switch( $errorMessageId ) {
                                case Crown_Import_Model_Adminhtml_Source_Errorlevels::LEVEL_WARNING:
                                    $profile->addValue ( 'num_warnings' );
                                    $logger->warning ( $this->__ ( $e->getMessage() . ' Column ' . ucfirst($k) ) );
                                    break;
                                case Crown_Import_Model_Adminhtml_Source_Errorlevels::LEVEL_ERROR:
                                default:
                                    $profile->addValue ( 'num_errors' );
                                    $logger->error ( $this->__ (  $e->getMessage() . ' Column ' . ucfirst($k) ) );
                                    $this->_valid [$sku] = false;
                            }
                        }
                    }

                } // foreach ($p as $k=>$newValue)

                // Media Gallery validation check
                if ( $mediaGallery = unserialize($profile->getData('error_messages')) ) {
                    if(isset($mediaGallery[$sku])) {
                        foreach ($mediaGallery[$sku] as $_message) {
                            $errorMessageId = Mage::getStoreConfig ( 'crownimport/urapidflow/missing_image_error' );
                            switch( $errorMessageId ) {
                                case Crown_Import_Model_Adminhtml_Source_Errorlevels::LEVEL_WARNING:
                                    $profile->addValue ( 'num_warnings' );
                                    $logger->warning ( $this->__ ( $_message ) );
                                    break;
                                case Crown_Import_Model_Adminhtml_Source_Errorlevels::LEVEL_ERROR:
                                default:
                                    $profile->addValue ( 'num_errors' );
                                    $logger->error ( $this->__ (  $_message ) );
                                    $this->_valid [$sku] = false;
                            }
                        }
                    }
                }

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

    protected function _importProcessDataDiff()
    {
        $profile = $this->_profile;
        $logger = $profile->getLogger();
        $storeId = $this->_storeId;
        $dryRun = $profile->getData('options/import/dryrun');
        $stockZeroOut = $profile->getData('options/import/stock_zero_out');

        $deleteOldCat = $profile->getData('options/import/delete_old_category_products');

        $importImageFiles = $profile->getData('options/import/image_files');
        $imagesFromDir = $profile->getImagesBaseDir();
        $imagesToDir = Mage::getBaseDir('media').DS.'catalog'.DS.'product';

        $hasCategoryIds = Mage::helper('urapidflow')->hasMageFeature('product.category_ids');
        $hasRequiredOptions = Mage::helper('urapidflow')->hasMageFeature('product.required_options');

        $oldValues = array();

        // find changed data
        foreach ($this->_newData as $sku=>$p) {
            try {
                if (!$this->_valid[$sku]) {
                    continue;
                }
                $logger->setLine($this->_skuLine[$sku]);

                // check if the product is new
                $isNew = empty($this->_skus[$sku]);

                // create new product
                if ($isNew) {
                    $hasOptions = $p['product.type']=='configurable' || $p['product.type']=='bundle';

                    $this->_insertEntity[$sku] = array(
                        'entity_type_id' => $this->_entityTypeId,
                        'attribute_set_id' => $p['product.attribute_set'],
                        'type_id' => $p['product.type'],
                        'sku' => $sku,
                        'created_at' => now(),
                        'updated_at' => now(),
                        'has_options' => $hasOptions || !empty($p['product.has_options']) ? 1 : 0,
                    );
                    if ($hasRequiredOptions) {
                        $this->_insertEntity[$sku]['required_options'] = $hasOptions || !empty($p['product.required_options']) ? 1 : 0;
                    }
                    $pId = null;
                } else {
                    $pId = $this->_skus[$sku];
                }
                $isUpdated = false;

                if ($stockZeroOut && isset($p['stock.qty'])) {
                    $p['stock.is_in_stock'] = $p['stock.qty'] > 0;
                    if (!isset($this->_fieldsCodes['stock.is_in_stock'])) {
                        $this->_fieldsCodes['stock.is_in_stock'] = $this->_fieldsCodes['stock.qty'];
                    }
                }

                // walk the attributes
                foreach ($p as $k=>$newValue) {
                    $logger->setColumn(isset($this->_fieldsCodes[$k]) ? $this->_fieldsCodes[$k]+1 : 0);

                    $oldValue = !$pId ? null : (
                    isset($this->_products[$pId][$storeId][$k]) ? $this->_products[$pId][$storeId][$k] : (
                    isset($this->_products[$pId][0][$k]) ? $this->_products[$pId][0][$k] : null
                    )
                    );
                    $attr = $this->_attr($k);

                    // some validation happens here as well
                    $this->_cleanupValues($attr, $oldValue, $newValue);

                    if (strpos($k, 'stock.')===0) {
                        if ($oldValue!==$newValue && !is_null($newValue)) {
                            list(, $f) = explode('.', $k, 2);
                            $this->_changeStock[$sku][$f] = $newValue;
                            if (!$isNew && isset($this->_fieldsCodes[$k])) {
                                $logger->success();
                            }
                            $isUpdated = true;
                        }
                        continue;
                    }
                    if (!$isNew) {
                        if ($k==='product.attribute_set' && $this->_products[$pId][0]['product.attribute_set'] != $newValue) {
                            $this->_updateEntity[$pId]['attribute_set_id'] = $newValue;
                            $isUpdated = true;
                        }
                        if ($k==='product.type' && $this->_products[$pId][0]['product.type'] != $newValue) {
                            $this->_updateEntity[$pId]['type_id'] = $newValue;
                            $isUpdated = true;
                        }
                        if ($k==='product.has_options' && $this->_products[$pId][0]['product.has_options'] != $newValue) {
                            $this->_updateEntity[$pId]['has_options'] = $newValue;
                            $isUpdated = true;
                        }
                        if (Mage::helper('urapidflow')->hasMageFeature('product.required_options')) {
                            if ($k==='product.required_options' && $this->_products[$pId][0]['product.required_options'] != $newValue) {
                                $this->_updateEntity[$pId]['required_options'] = $newValue;
                                $isUpdated = true;
                            }
                        }
                    }
                    if ($k==='product.websites') {
                        $oldValue = (array)$oldValue;
                        $newValue = (array)$newValue;
                        $oldValue = Mage::helper('urapidflow')->filterEeGwsWebsiteIds($oldValue);
                        $newValue = Mage::helper('urapidflow')->filterEeGwsWebsiteIds($newValue);
                        $insert = array_diff($newValue, $oldValue);
                        $delete = array_diff($oldValue, $newValue);
                        if ($insert || $delete) {
                            $this->_changeWebsite[$sku] = array('I'=>$insert, 'D'=>$delete);
                            if (!$isNew) {
                                $logger->success();
                            }
                            $isUpdated = true;
                        }
                        continue;
                    }
                    if (($k==='category.ids' || $k==='category.path' || $k==='category.name') && ($newValue || $deleteOldCat)) {
                        $newValue = array_unique((array)$newValue);
                        $oldValue = !empty($this->_products[$pId][0][$k]) ? (array)$this->_products[$pId][0][$k] : array();

                        $insert1 = array_diff($newValue, $oldValue);
                        $insert = array();
                        $pos = !empty($this->_products[$pId][0]['category.position']) ? max($this->_products[$pId][0]['category.position']) : 0;
                        foreach ($insert1 as $cId) {
                            $insert[$cId] = ++$pos;
                        }

                        $delete = $deleteOldCat ? array_diff($oldValue, $newValue) : array();

                        if ($insert || $delete) {
                            if ($isNew || !$deleteOldCat) {
                                $this->_changeCategoryProduct[$sku]['D'] = array();
                                if (empty($this->_changeCategoryProduct[$sku]['I'])) {
                                    $this->_changeCategoryProduct[$sku]['I'] = array();
                                }
                                foreach ($insert as $cId=>$pos) {
                                    $this->_changeCategoryProduct[$sku]['I'][$cId] = $pos;
                                }
                            } else {
                                $this->_changeCategoryProduct[$sku] = array('I' => $insert, 'D'=>$delete);
                            }
                            if ($hasCategoryIds) {
                                if ($isNew) {
                                    if (!empty($this->_insertEntity[$sku]['category_ids'])) {
                                        $newValue = array_unique(array_merge(
                                            explode(',', $newValue),
                                            $this->_insertEntity[$sku]['category_ids']
                                        ));
                                    }
                                    $this->_insertEntity[$sku]['category_ids'] = join(',', $newValue);
                                } else {
                                    if ($deleteOldCat) {
                                        $newValue = $newValue;
                                    } else {
                                        $ueCids = !empty($this->_updateEntity[$pId]['category_ids'])
                                            ? explode(',', $this->_updateEntity[$pId]['category_ids'])
                                            : array();
                                        $newValue = array_unique(array_merge($oldValue, $newValue, $ueCids));
                                    }
                                    $this->_updateEntity[$pId]['category_ids'] = join(',', $newValue);
                                }
                            }
                            if (!$isNew) {
                                $logger->success();
                            }
                            $isUpdated = true;
                        }
                        continue;
                    }
                    if (empty($attr['attribute_id']) || empty($attr['backend_type']) || $attr['backend_type']=='static') {
                        continue;
                    }
                    // existing attribute values
                    $isValueChanged = false;
                    if ($attr['frontend_input']=='media_image' && $newValue) {
                        if ($importImageFiles) {
                            if (!$dryRun) {
                                $isValueChanged = $this->_copyImageFile($imagesFromDir, $imagesToDir, $newValue, true, $oldValue);
                                if (is_null($isValueChanged)) {
                                    $isValueChanged = $newValue!==$oldValue;
                                }
                            } else {
                                $isValueChanged = false;
                            }
                        } else {
                            if ($this->_validateImageFile($newValue, $imagesToDir)) {
                                $isValueChanged = $newValue!==$oldValue;
                            } else {
                                $isValueChanged = false;
                            }
                        }
                        if ($newValue!=$oldValue && !$isNew) {
                            $this->_mediaChanges[$sku.'-'.$k] = array($newValue, $oldValue, $sku);
                        }
                    } elseif (is_array($newValue)) {
                        $oldValue = (array)$oldValue;
                        $isValueChanged = array_diff($newValue, $oldValue) || array_diff($oldValue, $newValue);
                    } else {
                        $isValueChanged = $newValue!==$oldValue;
                    }
                    // add updated attribute values
                    $empty = $newValue==='' || is_null($newValue) || $newValue===array();
                    if (($isNew && !$empty) || $isValueChanged) {
                        #$profile->getLogger()->log('DIFF', $sku.'/'.$k.': '.print_r($oldValue,1).';'.print_r($newValue,1));
                        $oldValues[$sku][$k] = $oldValue;
                        $this->_changeAttr[$sku][$k] = $newValue;
                        if (!$isNew) {
                            $logger->success();
                        }
                        if ($storeId && $attr['is_global']==2 && !empty($attr['attribute_id'])) {
                            $aId = $attr['attribute_id'];
                            $this->_websiteScope[$sku][$aId] = 1;
                            $this->_websiteScopeAttributes[$aId] = 1;
                            if ($pId) {
                                $this->_websiteScopeProducts[$pId] = 1;
                            }
                        }
                        $isUpdated = true;
                    }
                } // foreach ($p as $k=>$newValue)

                if ($isUpdated) {
                    $profile->addValue('rows_success');
                    if (!$isNew) {
                        $this->_updateEntity[$pId]['updated_at'] = now();
                    }
                } else {
                    $profile->addValue('rows_nochange');
                }
            } catch (Unirgy_RapidFlow_Exception_Row $e) {
                $logger->error($e->getMessage());
                $profile->addValue('rows_error');
            }
        } // foreach ($this->_newData as $p)
    }

    public function import()
    {
        $benchmark = false;

        $tune = Mage::getStoreConfig('urapidflow/finetune');
        if (!empty($tune['import_page_size']) && $tune['import_page_size']>0) {
            $this->_pageRowCount = (int)$tune['import_page_size'];
        }
        if (!empty($tune['page_sleep_delay'])) {
            $this->_pageSleepDelay = (int)$tune['page_sleep_delay'];
        }

        $profile = $this->_profile;
        $logger = $profile->getLogger();

        #$this->_saveAttributesMethod = Mage::getStoreConfig('urapidflow/finetune/save_attributes_method');
        $this->_saveAttributesMethod =''; #$profile->getData('options/import/save_attributes_method');
        $this->_insertAttrChunkSize = (int)$profile->getData('options/import/insert_attr_chunk_size');
        if (!$this->_insertAttrChunkSize) {
            $this->_insertAttrChunkSize = 100;
        }

        $dryRun = $profile->getData('options/import/dryrun');

        if (Mage::app()->isSingleStoreMode()) {
            $storeId = 0;
        } else {
            $storeId = $profile->getStoreId();
        }
        $this->_storeId = $storeId;
        $this->_entityTypeId = $this->_getEntityType($this->_entityType, 'entity_type_id');

        $useTransactions = $profile->getUseTransactions();

        $this->_profile->activity($this->__('Retrieving number of rows'));

        $profile->ioOpenRead();
        $count = -1;
        while ($profile->ioRead()) {
            $count++;
        }
        $profile->setRowsFound($count)->setStartedAt(now())->sync(true, array('rows_found', 'started_at'), false);
        $profile->ioSeekReset();

        $this->_profile->activity('Preparing data');

        $this->_importPrepareColumns();
        $this->_prepareAttributes(array_keys($this->_fieldsCodes));
        $this->_prepareSystemAttributes();
        $this->_importValidateColumns();
        $this->_prepareWebsites();
        $this->_prepareCategories();

        #$profile->ioSeekReset(6700);

        $eventVars = array(
            'profile' => &$this->_profile,
            'logger' => &$logger,
            'old_data' => &$this->_products,
            'new_data' => &$this->_newData,
            'skus' => &$this->_skus,
            'attr_value_ids' => &$this->_attrValueIds,
            'valid' => &$this->_valid,
            'insert_entity' => &$this->_insertEntity,
            'update_entity' => &$this->_updateEntity,
            'change_attr' => &$this->_changeAttr,
            'change_website' => &$this->_changeWebsite,
            'change_stock' => &$this->_changeStock,
            'change_category_product' => &$this->_changeCategoryProduct,
            'dry_run' => $dryRun,
            'product_ids_updated' => $this->_productIdsUpdated
        );

        $this->_profile->activity('Importing');
#memory_get_usage(true);
        if ($benchmark) Mage::log("============================= IMPORT START: ".memory_get_usage(true).', '.memory_get_peak_usage(true));

        $this->_isLastPage = false;

        // data will loaded page by page to conserve memory
        for ($page = 0; ; $page++) {
            $this->_startLine = 2+$page*$this->_pageRowCount;
            try {
                $this->_checkLock();

                if ($useTransactions && !$dryRun) {
                    $this->_write->beginTransaction();
                }
#memory_get_usage(true);
                if ($benchmark) Mage::log("================ PAGE START: ".memory_get_usage(true).', '.memory_get_peak_usage(true));
                $this->_importResetPageData();
#memory_get_usage(true);
                if ($benchmark) Mage::log("_importResetPageData: ".memory_get_usage(true).', '.memory_get_peak_usage(true));
                $this->_importFetchNewData();
#memory_get_usage(true);
                if ($benchmark) Mage::log("_importFetchNewData: ".memory_get_usage(true).', '.memory_get_peak_usage(true));
                $this->_importFetchOldData();
#memory_get_usage(true);
                if ($benchmark) Mage::log("_importFetchOldData: ".memory_get_usage(true).', '.memory_get_peak_usage(true));
                $this->_fetchAttributeValues($storeId, true);
#memory_get_usage(true);
                if ($benchmark) Mage::log("_fetchAttributeValues: ".memory_get_usage(true).', '.memory_get_peak_usage(true));
                $this->_fetchWebsiteValues();
#memory_get_usage(true);
                if ($benchmark) Mage::log("_fetchWebsiteValues: ".memory_get_usage(true).', '.memory_get_peak_usage(true));
                $this->_fetchStockValues();
#memory_get_usage(true);
                if ($benchmark) Mage::log("_fetchStockValues: ".memory_get_usage(true).', '.memory_get_peak_usage(true));
                $this->_fetchCategoryValues();
#memory_get_usage(true);
                if ($benchmark) Mage::log("_fetchCategoryValues: ".memory_get_usage(true).', '.memory_get_peak_usage(true));

                $this->_importProcessNewData();
#memory_get_usage(true);
                if ($benchmark) Mage::log("_importProcessNewData: ".memory_get_usage(true).', '.memory_get_peak_usage(true));

                $this->_checkLock();

                Mage::dispatchEvent('urapidflow_product_import_after_fetch', array('vars'=>$eventVars));
                $this->_importValidateNewData();
#memory_get_usage(true);
                if ($benchmark) Mage::log("_importValidateNewData: ".memory_get_usage(true).', '.memory_get_peak_usage(true));
                Mage::dispatchEvent('urapidflow_product_import_after_validate', array('vars'=>$eventVars));
                $this->_importProcessDataDiff();
#memory_get_usage(true);
                if ($benchmark) Mage::log("_importProcessDataDiff: ".memory_get_usage(true).', '.memory_get_peak_usage(true));
                Mage::dispatchEvent('urapidflow_product_import_after_diff', array('vars'=>$eventVars));

                if (!$dryRun) {
                    $this->_importSaveEntities();
#memory_get_usage(true);
                    if ($benchmark) Mage::log("_importSaveEntities: ".memory_get_usage(true).', '.memory_get_peak_usage(true));
                    $this->_importCopyImageFiles();
#memory_get_usage(true);
                    if ($benchmark) Mage::log("_importCopyImageFiles: ".memory_get_usage(true).', '.memory_get_peak_usage(true));
                    $this->_importGenerateAttributeValues();
#memory_get_usage(true);
                    if ($benchmark) Mage::log("_importGenerateAttributeValues: ".memory_get_usage(true).', '.memory_get_peak_usage(true));

                    $this->_importSaveAttributeValues();
#memory_get_usage(true);
                    if ($benchmark) Mage::log("_importSaveAttributeValues: ".memory_get_usage(true).', '.memory_get_peak_usage(true));
                    $this->_importSaveWebsiteValues();
#memory_get_usage(true);
                    if ($benchmark) Mage::log("_importSaveWebsiteValues: ".memory_get_usage(true).', '.memory_get_peak_usage(true));
                    $this->_importSaveProductCategories();
#memory_get_usage(true);
                    if ($benchmark) Mage::log("_importSaveProductCategories: ".memory_get_usage(true).', '.memory_get_peak_usage(true));
                    $this->_importSaveStockValues();
#memory_get_usage(true);
                    if ($benchmark) Mage::log("_importSaveStockValues: ".memory_get_usage(true).', '.memory_get_peak_usage(true));

                    #$this->_importReindexProducts();
                    #$this->_importRefreshRewrites();
                    $this->_importUpdateImageGallery();
#memory_get_usage(true);
                    if ($benchmark) Mage::log("_importUpdateImageGallery: ".memory_get_usage(true).', '.memory_get_peak_usage(true));

                    Mage::dispatchEvent('urapidflow_product_import_after_save', array('vars'=>$eventVars));

                    #$this->_profile->realtimeReindex(array_keys($this->_productIdsUpdated));
                    $this->_importRealtimeReindex();

                    Mage::dispatchEvent('urapidflow_product_import_after_rtidx', array('vars'=>$eventVars));
                }

                $profile->setMemoryUsage(memory_get_usage(true))->setMemoryPeakUsage(memory_get_peak_usage(true))
                    #$profile->setMemoryUsage(memory_get_usage(true))->setMemoryPeakUsage(memory_get_peak_usage(true))
                    ->setSnapshotAt(now())->sync();

                if ($useTransactions && !$dryRun) {
                    $this->_write->commit();
                }
            } catch (Exception $e) {
                if ($useTransactions && !$dryRun) {
                    $this->_write->rollback();
                }
#print_r($e);
                throw $e;
            }
            if ($this->_isLastPage) {
                break;
            }
            if ($this->_pageSleepDelay) {
                sleep($this->_pageSleepDelay);
            }
        }

        $profile->ioClose();

        $this->_afterImport();
    }
}