<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    Unirgy_RapidFlow
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

/**
 * Special attributes:
 *
 * EXPORT ONLY:
 * category.path
 * category.name
 *
 * const.value
 *
 * product.store
 *
 * IMPORT/EXPORT:
 * stock.use_config_manage_stock
 * stock.manage_stock
 * stock.is_in_stock
 * stock.qty
 *
 * product.attribute_set
 * product.type
 * product.websites
 */

class Unirgy_RapidFlow_Model_Mysql4_Catalog_Product
    extends Unirgy_RapidFlow_Model_Mysql4_Catalog_Product_Abstract
{
    protected $_csvRows = array();

    /**
     * actual export of formated product data to file
     */
    public function export()
    {
        $benchmark = false;

        $tune = Mage::getStoreConfig('urapidflow/finetune');
        if (!empty($tune['export_page_size']) && $tune['export_page_size']>0) {
            $this->_pageRowCount = (int)$tune['export_page_size'];
        }
        if (!empty($tune['page_sleep_delay'])) {
            $this->_pageSleepDelay = (int)$tune['page_sleep_delay'];
        }

        $profile = $this->_profile;
        $logger = $profile->getLogger();

        $this->_entityTypeId = $this->_getEntityType($this->_entityType, 'entity_type_id');

        $this->_profile->activity('Preparing data');

        $this->_prepareAttributes($profile->getAttributeCodes());
        $this->_prepareSystemAttributes();

        $storeId = $profile->getStoreId();
        $this->_storeId = $storeId;
        $manageStock = Mage::getStoreConfig('cataloginventory/item_options/manage_stock', $storeId);

        $pUrl = Mage::getSingleton('catalog/url');
        $secure = $profile->getData('options/export/image_https');
        $baseUrl = Mage::app()->getStore($storeId)->getBaseUrl('web', $secure);
        $mediaUrl = Mage::app()->getStore($storeId)->getBaseUrl('media', $secure);
        $mediaDir = Mage::getBaseDir('media');
        $imgModel = Mage::getModel('catalog/product_image');

        $exportImageFiles = $profile->getData('options/export/image_files');
        $imagesFromDir = Mage::getBaseDir('media').DS.'catalog'.DS.'product';
        $imagesToDir = $profile->getImagesBaseDir();

        $this->_profile->activity($this->__('Loading products'));
        // main product table
        $table = $this->_t('catalog/product');
        // start select
        $select = $this->_read->select()->from(array('e'=>$table));
        $this->_attrJoined = array();
        $columns = $profile->getColumns();

        $exportInvalidValues = $profile->getData('options/export/invalid_values');
        $exportInternalValues = $profile->getData('options/export/internal_values');
        $skipOutOfStock = $profile->getData('options/export/skip_out_of_stock');
        $manageStock = (int)Mage::getStoreConfig('cataloginventory/item_options/manage_stock', $storeId);

        $defaultSeparator = $profile->getData('options/csv/multivalue_separator');
        if (!$defaultSeparator) {
            $defaultSeparator = '; ';
        }

        $this->_fields = array();
        $this->_fieldsCodes = array();
        if ($columns) {
            foreach ($columns as $i=>&$f) {
                if (empty($f['alias'])) {
                    $f['alias'] = $f['field'];
                }
                if (!empty($f['default']) && is_array($f['default'])) {
                    $f['default'] = join(!empty($f['separator']) ? $f['separator'] : $defaultSeparator, $f['default']);
                }
                $f['column_num'] = $i+1;
                $this->_fields[$f['alias']] = $f;
                $this->_fieldsCodes[$f['field']] = true;
                if ($f['field'] == 'product.configurable_parent_sku') {
                    $this->_configurableParentSku = $f;
                }
            }
            unset($f);
        } else {
            $columns = array();
            $i = 1;
            foreach ($this->_attributesByCode as $k=>$a) {
                if ($k=='product.entity_id') {
                    continue;
                }
                $columns[$i-1] = array('field' => $k, 'title' => $k, 'alias' => $k, 'default' => '');
                $this->_fields[$k] = array('field' => $k, 'title' => $k, 'alias' => $k, 'column_num' => $i++);
                $this->_fieldsCodes[$k] = true;
            }
            $this->_configurableParentSku = array();
        }

        if ($this->_hasColumnsLike('category.')) {
            $this->_prepareCategories();
#memory_get_usage(true);

        }

        if ($skipOutOfStock) {
            $select->where("entity_id in (select product_id from {$this->_t('cataloginventory_stock_item')} where (qty>0 && is_in_stock=1) or not if(use_config_manage_stock,{$manageStock},manage_stock))");
        }
        $condProdIds = $profile->getConditionsProductIds();
        if (is_array($condProdIds)) {
            $select->where('entity_id in (?)', $condProdIds);
        }

        $countSelect = clone $select;
        $countSelect->reset(Zend_Db_Select::FROM)->reset(Zend_Db_Select::COLUMNS)->from(array('e'=>$table), array('count(*)'));
        $count = $this->_read->fetchOne($countSelect);
        unset($countSelect);

        $profile->setRowsFound($count)->setStartedAt(now())->sync(true, array('rows_found', 'started_at'), false);
        $profile->activity($this->__('Exporting'));
#memory_get_usage(true);
if ($benchmark) Mage::log("============================= IMPORT START: ".memory_get_usage().', '.memory_get_peak_usage());

        // open export file
        $profile->ioOpenWrite();

        // write headers to the file
        $headers = array();
        foreach ($columns as $c) {
            $_hAlias = !empty($c['alias']) ? $c['alias'] : $c['field'];
            $headers[$_hAlias] = $_hAlias;
        }
        $profile->ioWriteHeader($headers);

        $rowNum = 1;

        // batch size
        // repeat until data available
        // data will loaded page by page to conserve memory
        for ($page = 0; ; $page++) {
#memory_get_usage(true);
if ($benchmark) Mage::log("================ PAGE START: ".memory_get_usage().', '.memory_get_peak_usage());
            // set limit for current page
            $select->limitPage($page+1, $this->_pageRowCount);
            // retrieve product entity data and attributes in filters
            $rows = $this->_read->fetchAll($select);
            if (!$rows) {
                break;
            }

            $this->_importResetPageData();

            unset($this->_products);

            // fill $this->_products associated by product id
            $this->_products = array();
            foreach ($rows as $p) {
                $this->_products[$p['entity_id']][0] = $p;
            }
            unset($rows);
#memory_get_usage(true);
if ($benchmark) Mage::log("_readRows: ".memory_get_usage().', '.memory_get_peak_usage());

            $this->_productIds = array_keys($this->_products);

            $this->_fetchAttributeValues($storeId, true);
#memory_get_usage(true);
if ($benchmark) Mage::log("_fetchAttributeValues: ".memory_get_usage().', '.memory_get_peak_usage());
            $this->_fetchWebsiteValues();
#memory_get_usage(true);
if ($benchmark) Mage::log("_fetchWebsiteValues: ".memory_get_usage().', '.memory_get_peak_usage());
            $this->_fetchStockValues();
#memory_get_usage(true);
if ($benchmark) Mage::log("_fetchStockValues: ".memory_get_usage().', '.memory_get_peak_usage());
            $this->_fetchCategoryValues();
#memory_get_usage(true);
if ($benchmark) Mage::log("_fetchCategoryValues: ".memory_get_usage().', '.memory_get_peak_usage());

            $this->_csvRows = array();

            $this->_exportProcessPrice();
            $this->_exportConfigurableParentSku();

            Mage::dispatchEvent('urapidflow_catalog_product_export_before_format', array('vars'=>array(
                'profile' => $this->_profile,
                'products' => &$this->_products,
                'fields' => &$this->_fields,
            )));

            // format product data as needed
            foreach ($this->_products as $id=>$p) {
                $logger->setLine(++$rowNum)->setColumn(0);
                $csvRow = array();
                $value = null;
                foreach ($columns as $c) {
                    $attr = $c['field'];
                    $f = $this->_fields[$c['alias']];
                    $inputType = $this->_attr($attr, 'frontend_input');
                    $sourceModel = $this->_attr($attr, 'source_model');

                    // retrieve correct value for current row and field
                    if (($v = $this->_attr($attr, 'force_value'))) {
                        $value = $v;
                    } elseif (!empty($this->_fieldAttributes[$attr])) {
                        $a = $this->_fieldAttributes[$attr];
                        $value = isset($p[$storeId][$a]) ? $p[$storeId][$a] : (isset($p[0][$a]) ? $p[0][$a] : null);
                    } else {
                        $value = isset($p[$storeId][$attr]) ? $p[$storeId][$attr] : (isset($p[0][$attr]) ? $p[0][$attr] : null);
                    }

                    if ((is_null($value) || $value==='') && !empty($c['default'])) {
                        $value = $c['default'];
                    }

                    // replace raw numeric values with source option labels
                    if ((!$exportInternalValues || strpos($attr, 'category.')===0)
                        && ($inputType=='select' || $inputType=='multiselect' || $sourceModel)
                        && ($options = $this->_attr($attr, 'options'))
                    ) {
                        if (!is_array($value) && $inputType=='multiselect') {
                            $value = explode(',', $value);
                        } elseif (!is_array($value)) {
                            $value = array($value);
                        }
                        foreach ($value as $k=>&$v) {
                            if ($v==='') {
                                continue;
                            }
                            if (!isset($options[$v])) {
                                $profile->addValue('num_warnings');
                                $logger->setColumn($f['column_num'])
                                    ->warning($this->__("Unknown option '%s' for product '%s' attribute '%s'", $v, $p[0]['sku'], $attr));
                                if (!$exportInvalidValues) {
                                    unset($value[$k]);
                                }
                                continue;
                            }
                            $v = $options[$v];
                        }
                        unset($v);
                    }

                    // combine multiselect values
                    if (is_array($value)) {
                        $value = join(!empty($f['separator']) ? $f['separator'] : $defaultSeparator, $value);
                    }

                    // process special cases of loaded attributes
                    switch ($attr) {
                    // product url
                    case 'url_path':
                        if (!empty($f['format']) && $f['format']=='url') {
                            $value = $baseUrl.$value;
                        }
                        break;

                    case 'const.value':
                        $value = isset($c['default']) ? $c['default'] : '';
                        break;

                    case 'const.function':
                        $value = '';
                        if (!empty($c['default'])) {
                            try {
                                list($class, $func) = explode('::', $c['default']);
                                if (strpos($class, '/')!==false) {
                                    $model = Mage::getSingleton($class);
                                } else {
                                    $model = $class;
                                }
                                $value = call_user_func(array($model, $func), $p, $c, $storeId);
                            } catch (Exception $e) {
                                $logger->setColumn($f['column_num'])
                                    ->warning($this->__("Exception for product '%s' attribute '%s': %s", $p[0]['sku'], $attr, $e->getMessage()));
                            }
                        }
                        break;
                    }

                    switch ($this->_attr($attr, 'backend_type')) {
                    case 'decimal':
                        if (!is_null($value) && !empty($f['format'])) {
                            $value =  sprintf($f['format'], $value);
                        }
                        break;

                    case 'datetime':
                        if (!is_empty_date($value)) {
                            $value = date(!empty($f['format']) ? $f['format'] : 'Y-m-d H:i:s', strtotime($value));
                        }
                        break;
                    }

                    switch ($this->_attr($attr, 'frontend_input')) {
                    case 'media_image':
                        if ($value=='no_selection') {
                            $value = '';
                        }
                        if ($value!=='' && $exportImageFiles) {
                            $logger->setColumn($f['column_num']);
                            $this->_copyImageFile($imagesFromDir, $imagesToDir, $value);
                        }
                        if (!empty($f['format']) && $f['format']=='url' && !empty($value)) {
                            try {
                                $path = $imgModel->setBaseFile($value)->getBaseFile();
                                $path = str_replace($mediaDir . DS, "", $path);
                                $value = $mediaUrl . str_replace(DS, '/', $path);

                            } catch (Exception $e) {
                                $value = '';
                            }
                        }
                        break;
                    }

                    if (empty($csvRow[$c['alias']])) {
                        $csvRow[$c['alias']] = $value;
                    }
                }

                $csvRow = $this->_convertEncoding($csvRow);
                #$profile->ioWrite($csvRow);
                $this->_csvRows[] = $csvRow;

                $profile->addValue('rows_processed');
            } // foreach ($this->_products as $id=>&$p)

            Mage::dispatchEvent('urapidflow_catalog_product_export_before_output', array('vars'=>array(
                'profile' => $this->_profile,
                'products' => &$this->_products,
                'fields' => &$this->_fields,
                'rows' => &$this->_csvRows,
            )));

            foreach ($this->_csvRows as $row) {
                $profile->ioWrite($row);
                $profile->addValue('rows_success');
            }

            $profile->setMemoryUsage(memory_get_usage(true))->setMemoryPeakUsage(memory_get_peak_usage(true))
                ->setSnapshotAt(now())->sync();

            $this->_checkLock();

            // stop repeating if this is the last page
            if (sizeof($this->_products)<$this->_pageRowCount) {
                break;
            }
            if ($this->_pageSleepDelay) {
                sleep($this->_pageSleepDelay);
            }
        } // while (true)
        $profile->ioClose();

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

    public function fetchSystemAttributes()
    {
        $this->_entityTypeId = $this->_getEntityType($this->_entityType, 'entity_type_id');
        $this->_prepareSystemAttributes();
        return $this->_attributesByCode;
    }

    protected function _cleanupValues($attr, &$oldValue, &$newValue)
    {
        // trying to work around PHP's weakly typed mess...
        if (!empty($attr['frontend_input'])) {
            switch ($attr['frontend_input']) {
            case 'media_image':
                if (!is_null($oldValue)) {
                    if ($oldValue=='no_selection') {
                        $oldValue = '';
                    }
                }
                break;

            case 'multiselect':
                if (is_null($oldValue)) {
                    $oldValue = array();
                }
                if ($newValue==='') {
                    $newValue = array();
                }
                break;
            }
        }
        if (!empty($attr['backend_type'])) {
            switch ($attr['backend_type']) {
            case 'int':
                if (!is_null($newValue) && !is_array($newValue)) {
                    if ($newValue==='') {
                        $newValue = null;
                    } else {
                        $newValue = $this->_locale->getNumber($newValue);
                        if ($newValue != (int)$newValue) {
                            $this->_profile->addValue('num_errors');
                            $this->_profile->getLogger()->error($this->__("Invalid int value"));
                        } else {
                            $newValue = (int)$newValue;
                        }
                    }
                }
                if (!is_null($oldValue) && !is_array($oldValue)) {
                    if ($oldValue==='') {
                        $oldValue = null;
                    } else {
                        $oldValue = (int)$oldValue;
                    }
                }
                break;

            case 'decimal':
                if (!is_null($newValue)) {
                    if ($newValue==='') {
                        $newValue = null;
                    } else {
                        $newValue = $this->_locale->getNumber($newValue);
                        if (!is_numeric($newValue)) {
                            $this->_profile->addValue('num_errors');
                            $this->_profile->getLogger()->error($this->__("Invalid decimal value"));
                        } else {
                            $newValue *= 1.0;
                        }
                    }
                }
                if (!is_null($oldValue)) {
                    if ($oldValue==='') {
                        $oldValue = null;
                    } else {
                        $oldValue *= 1.0;
                    }
                }
                break;

            case 'datetime':
                if (!is_null($newValue)) {
                    if ($newValue==='') {
                        $newValue = null;
                    } else {
                    static $_dp;
                    	if (null === $_dp) {
                    		$_dp = Mage::getStoreConfig('urapidflow/import_options/date_processor');
                    		if ($_dp == 'date_parse_from_format' && !version_compare(phpversion(), '5.3.0', '>=')) {
                    			$_dp = 'strtotime';
                    		}
                    	}
                    	static $_attrFormat = array();
                    	$_attrCode = $attr['attribute_code'];
                    	if (!isset($_attrFormat[$_attrCode])) {
                    		if (isset($this->_fields[$_attrCode]['format'])) {
                    			$_attrFormat[$_attrCode] = $this->_fields[$_attrCode]['format'];
                    		} else {
                    			$_attrFormat[$_attrCode] = $this->_profile->getDefaultDatetimeFormat();
                    		}
                    		if ($_dp == 'zend_date') {
                    			$_attrFormat[$_attrCode] = Zend_Locale_Format::convertPhpToIsoFormat($_attrFormat[$_attrCode]);
                    		}
                    	}
                    	switch ($_dp) {
                    		case 'zend_date':
                    			static $_zendDate;
                    			if (null === $_zendDate) {
                    				$_zendDate = new Zend_Date($newValue, $_attrFormat[$_attrCode], $this->_profile->getProfileLocale());
                    			} else {
                    				$_zendDate->set($newValue, $_attrFormat[$_attrCode]);
                    			}
                    			$newValue = $_zendDate->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
                    			break;
                    		case 'date_parse_from_format':
                    			$_phpDatetime = DateTime::createFromFormat($_attrFormat[$_attrCode], $newValue);
                    			$newValue = $_phpDatetime->format("Y-m-d H:i:s");
                    			break;
                    		default:
                    			$newValue = date("Y-m-d H:i:s", strtotime($newValue));
                    			break;
                    	}
                        if (!$newValue) {
                            $this->_profile->addValue('num_errors');
                            $this->_profile->getLogger()->error($this->__("Invalid datetime value"));
                        }
                    }
                }
                if (!is_null($oldValue)) {
                    if ($oldValue==='') {
                        $oldValue = null;
                    }
                }
                break;

            case 'varchar': case 'text':
                if ($oldValue==='' && is_null($newValue)) {
                    $newValue = '';
                } elseif (is_null($oldValue) && $newValue==='') {
                    $newValue = null;
                } elseif (is_numeric($newValue)) {
                    $newValue = (string)$newValue;
                }
                break;
            }
        }
    }

    /**
     * retrieve attr record by id or code, with optional record field and value
     */
    protected function _attr($attribute, $field=null, $value=null)
    {
        if (is_numeric($attribute)) {
            $attr = $this->_attributesById[$attribute];
        } elseif (isset($this->_attributesByCode[$attribute])) {
            $attr = $this->_attributesByCode[$attribute];
        } else {
            return false;
        }
        if (!is_null($field) && !is_null($value)) {
            if ($field=='options_bytext') {
                $value = strtolower($value);
            }
            return isset($attr[$field][$value]) ? $attr[$field][$value] : false;
        } elseif (!is_null($field)) {
            return isset($attr[$field]) ? $attr[$field] : false;
        } else {
            return $attr;
        }
    }




    /**
    * put your comment there...
    *
    * @return boolean last page
    */
    protected function _importFetchNewData()
    {
        $profile = $this->_profile;
        $logger = $profile->getLogger();

        $defaultSeparator = $profile->getData('options/csv/multivalue_separator');
        if (!$defaultSeparator) {
            $defaultSeparator = ';';
        }

        // read rows from file into memory and collect skus
        $this->_newData = array();
        // $i1 should be preserved during the loop
        for ($i1=0; $i1<$this->_pageRowCount; $i1++) {
            $error = false;
            $row = $profile->ioRead();
            if (!$row) {
                // last row
                $this->_isLastPage = true;
#var_dump($this->_newData);
                return true;
            }

            $empty = true;
            foreach ($row as $v) {
                if (trim($v)!=='') {
                    $empty = false;
                    break;
                }
            }
            if ($empty) {
                $profile->addValue('rows_empty');
                continue;
            }

            $profile->addValue('rows_processed');
            $logger->setLine($this->_startLine+$i1);
            if (empty($row[$this->_skuIdx])) {
                $profile->addValue('rows_errors')->addValue('num_errors');
                $logger->setColumn($this->_skuIdx+1)->error($this->__('Empty SKU'));
                continue;
            }
            if (!empty($this->_newData[$row[$this->_skuIdx]])) {
                $profile->addValue('rows_errors')->addValue('num_errors');
                $logger->setColumn($this->_skuIdx+1)->error($this->__('Duplicate SKU'));
                continue;
            }
            $sku = $row[$this->_skuIdx];
            $this->_skuLine[$sku] = $this->_startLine+$i1;
            $this->_newData[$sku] = $this->_newDataTemplate;
            $this->_defaultUsed[$sku] = $this->_newDataTemplate;

            $error = false;
            foreach ($row as $col=>$v) {
                if (!isset($this->_fieldsIdx[$col]) && $v!=='') {
                    $profile->addValue('num_warnings');
                    $logger->setColumn($col+1)
                        ->warning($this->__('Column is out of boundaries, ignored'));
                    continue;
                }
                $_kk = (array)$this->_fieldsIdx[$col];
                $_v = $v;
                foreach ($_kk as $k) {
                    $v = $_v;
                    if ($k===false || in_array($k,array('const.value','const.function'))) {
                        continue;
                    }
                    $input = $this->_attr($k, 'frontend_input');
                    $multiselect = $input=='multiselect';
                    $separator = trim(!empty($this->_fields[$k]['separator']) ? $this->_fields[$k]['separator'] : $defaultSeparator);
                    try {
                        $v = $this->_convertEncoding($v);
                    } catch (Exception $e) {
                        $profile->addValue('num_warnings');
                        $logger->setColumn($col+1)->warning($e);
                        #$error = true;
                    }
                    if ($v!=='') {
                        // options and multiselect
                        if ($input=='select') {
                            $v = trim($v);
                        } elseif ($multiselect) {
                            $values = explode($separator, $v);
                            $v = array();
                            foreach ($values as $v1) {
                                $v1 = trim($v1);
                                if ($v1!=='') {
                                    $v[] = $v1;
                                }
                            }
                            // check if field is category.path and if it is, make sure values are unique
                            if (in_array($k, array('category.path', 'category.name'))) {
                                $v = array_unique($v);
                            }
                        }
                    }
                    if (!isset($this->_defaultUsed[$sku][$k]) || $v!=='' && $v!==array()) {
                        $this->_newData[$sku][$k] = $v;
                        unset($this->_defaultUsed[$sku][$k]);
                    }
                }
            }
            if ($error) {
                unset($this->_newData[$sku]);
            }
        }
        return false;
    }

    protected function _importValidateNewData()
    {
        $profile = $this->_profile;
        $logger = $profile->getLogger();
        $storeId = $this->_storeId;
        $autoCreateAttributeSets = $profile->getData('options/import/create_attributesets');
        $autoCreateOptions = $profile->getData('options/import/create_options');
        $autoCreateCategories = $profile->getData('options/import/create_categories');
        $changeTypeSet = $profile->getData('options/import/change_typeset');
        $actions = $profile->getData('options/import/actions');
        $allowSelectIds = $profile->getData('options/import/select_ids');
        $allowNotApplicable = $profile->getData('options/import/not_applicable');

        // find changed data
        foreach ($this->_newData as $sku=>$p) {
            try {
                $logger->setLine($this->_skuLine[$sku]);
                // check if the product is new
                $isNew = empty($this->_skus[$sku]);
                $oldProduct = $isNew ? array() : $this->_products[$this->_skus[$sku]][0];

                if ($isNew && $actions=='update' || !$isNew && $actions=='create') {
                    $profile->addValue('rows_nochange');
                    $this->_valid[$sku] = false;
                    continue;
                }

                // validate required attributes
                $this->_valid[$sku] = true;

                $k = "product.type";
                $logger->setColumn(isset($this->_fieldsCodes[$k]) ? $this->_fieldsCodes[$k]+1 : 0);
                if (isset($p[$k])) {
                    if (isset($this->_defaultUsed[$sku][$k])) {
                        $typeId = $p[$k];
                    } else {
                        $typeId = $this->_attr($k, 'options_bytext', $p[$k]);
                        if (!$isNew) {
                            if (!$changeTypeSet && $typeId!=$oldProduct[$k]) {
                                $this->_newData[$sku][$k] = $oldProduct[$k];
                                $profile->addValue('num_warnings');
                                $logger->warning($this->__('Will not change product type for an existing product'));
                            }
                        } elseif (!$typeId) {
                            $profile->addValue('num_errors');
                            $logger->error($this->__('Empty or invalid product type for a new product'));
                            $this->_valid[$sku] = false;
                        }
                    }
                } else { // not set
                    if ($isNew) {
                        $profile->addValue('num_errors');
                        $logger->error($this->__('Empty or invalid product type for a new product'));
                        $this->_valid[$sku] = false;
                    } else {
                        $typeId = $this->_products[$this->_skus[$sku]][0]['product.type'];
                    }
                }

                $k = "product.attribute_set";
                $logger->setColumn(isset($this->_fieldsCodes[$k]) ? $this->_fieldsCodes[$k]+1 : 0);
                if (isset($p[$k])) {
                    if (isset($this->_defaultUsed[$sku][$k])) {
                        $attrSetId = $p[$k];
                    } else {
                        if ($allowSelectIds && ($v = $this->_attr($k, 'options', $p[$k]))) {
                            $attrSetId = $p[$k];
                        } else {
                            $attrSetId = $this->_attr($k, 'options_bytext', $p[$k]);
                        }
                        if (!$isNew) {
                            if (!$changeTypeSet && $attrSetId!=$oldProduct[$k]) {
                                $attrSetId = $oldProduct[$k];
                                $profile->addValue('num_warnings');
                                $logger->warning($this->__('Will not change attribute set for an existing product'));
                            }
                        } elseif (!$attrSetId) {
                            if ($p[$k] && $autoCreateAttributeSets) {
                                $attrSetId = $this->_importCreateAttributeSet($p[$k]);
                                $profile->addValue('num_warnings');
                                $logger->warning($this->__("Created a new attribute set '%s'", $p[$k]));
                            } else {
                                $profile->addValue('num_errors');
                                $logger->error($this->__('Empty or invalid attribute set for a new product'));
                                $this->_valid[$sku] = false;
                            }
                        }
                    }
                } else {
                    if ($isNew) {
                        $profile->addValue('num_errors');
                        $logger->error($this->__('Empty or invalid attribute set for a new product'));
                        $this->_valid[$sku] = false;
                    } else {
                        $attrSetId = $this->_products[$this->_skus[$sku]][0]['product.attribute_set'];
                    }
                }

                // continue on error
                if (!$this->_valid[$sku]) {
                    $profile->addValue('rows_errors');
                    continue;
                }

                $p[$k] = $attrSetId;
                $this->_newData[$sku][$k] = $attrSetId;

                $attrSetFields = $this->_getAttributeSetFields($attrSetId);
                $typeId = !empty($typeId) ? $typeId : $oldProduct['type_id'];
                $isParentProduct = $typeId=='configurable' || $typeId=='grouped' || $typeId=='bundle';

                $dynamic = $this->__('Dynamic');
                $dynPrice = ($typeId=='configurable' || $typeId=='bundle') && (isset($p['price_type']) && !empty($p['price_type'])  && (in_array($p['price_type'], array($dynamic, 1))) || !isset($p['price_type']) && !empty($oldProduct['price_type']));
                $dynWeight = ($typeId=='configurable' || $typeId=='bundle') && (isset($p['weight_type']) && !empty($p['weight_type']) && (in_array($p['weight_type'], array($dynamic, 1))) || !isset($p['weight_type']) && !empty($oldProduct['weight_type']));

                if ($isNew) {
                    // check missing required columns
                    foreach ($this->_attributesByCode as $k=>$attr) {
                        if (isset($p[$k]) || empty($attr['is_required'])) {
                            continue;
                        }
                        $appliesTo = empty($attr['apply_to']) || !empty($attr['apply_to'][$typeId]);
                        $inAttrSet = empty($attr['attribute_id']) || !empty($attrSetFields[$k]);
                        $dynAttr = $k=='price' && $dynPrice || $k=='weight' && $dynWeight;
                        $parentQty = $k=='stock.qty' && $isParentProduct;

                        if ($appliesTo && $inAttrSet && !$dynAttr && !$parentQty) {
                            $profile->addValue('num_errors');
                            $logger->setColumn(1);
                            $logger->error($this->__("Missing required value for '%s'", $k));
                            $this->_valid[$sku] = false;
                        }
                    }
                }

                // walk the attributes
                foreach ($p as $k=>$newValue) {
                    $attr = $this->_attr($k);
                    $logger->setColumn(isset($this->_fieldsCodes[$k]) ? $this->_fieldsCodes[$k]+1 : -1);

                    $empty = is_null($newValue) || $newValue==='' || $newValue===array();
                    $required = !empty($attr['is_required']);
                    $visible = !empty($attr['is_visible']);
                    $appliesTo = empty($attr['apply_to']) || !empty($attr['apply_to'][$typeId]);
                    $inAttrSet = empty($attr['attribute_id']) || !empty($attrSetFields[$k]);
                    $selectable = !empty($attr['frontend_input']) && ($attr['frontend_input']=='select' || $attr['frontend_input']=='multiselect' || !empty($attr['source_model']));
                    $dynAttr = $k=='price' && $dynPrice || $k=='weight' && $dynWeight;
                    $parentQty = $k=='stock.qty' && $isParentProduct;

                    if (!$empty && $visible && (!$appliesTo || !$inAttrSet || $dynAttr) && !$allowNotApplicable) {
    #var_dump($k, $newValue); echo "<hr>";
                        #$this->_newData[$sku][$k] = null;
                        unset($this->_newData[$sku][$k]);
                        $newValue = null;
                        $profile->addValue('num_warnings');
                        if (!$appliesTo) {
                            $logger->warning($this->__("The attribute '%s' does not apply to product type '%s', and will not be imported", $k, $typeId));
                        } elseif (!$inAttrSet) {
                            $attrSetName = $this->_attr('product.attribute_set', 'options', $attrSetId);
                            $logger->warning($this->__("The attribute '%s' does not apply to attribute set '%s', and will not be imported", $k, $attrSetName));
                        } elseif ($dynAttr) {
                            $logger->warning($this->__("The attribute '%s' is not used, as it is a dynamic value in this product, and will not be imported", $k));
                        }
                    } elseif ($empty && $required && $appliesTo && $inAttrSet && !$dynAttr && !$parentQty) {
                        if ($typeId=='configurable' && $selectable
                            && !empty($attr['is_global']) && !empty($attr['is_configurable'])
                        ) {
                            if (!$isNew && !$this->_write->fetchOne("select product_id from {$this->_t('catalog/product_super_attribute')} where product_id={$this->_skus[$sku]} and attribute_id={$attr['attribute_id']}")) {
                                $profile->addValue('num_warnings');
                                $logger->warning($this->__("If the attribute '%s' will not used in configurable subproducts, this value might be missing", $k));
                            }
                        } else {
                            $profile->addValue('num_errors');
                            $logger->error($this->__("Missing required value for '%s'", $k));
                            $this->_valid[$sku] = false;
                            continue;
                        }
                    }

                    if ($selectable && !$empty && $k!='product.attribute_set') {
                        if ($attr['frontend_input']=='multiselect' && is_array($newValue)) {
                            $newValue = array_unique($newValue);
                        }
                        foreach ((array)$newValue as $i=>$v) {
                            $vLower = strtolower(trim($v));
                            if ($k=='category.name') {
                                $delimiter = !empty($this->_fields[$k]['delimiter']) ? $this->_fields[$k]['delimiter'] : '>';
                                $vLower = str_replace($delimiter, '>', $vLower);
                            }
                            if (isset($this->_defaultUsed[$sku][$k])
                            	&& !in_array($k, array('category.name', 'category.path'))
                            ) {
                                // default value used, no mapping required
                            } elseif (isset($attr['options_bytext'][$vLower])) {
                                $vId = $attr['options_bytext'][$vLower];
                                if (is_array($newValue)) {
                                    #if (!is_array($this->_newData[$sku][$k])) {
                                    #    if ($vId!=$this->_newData[$sku][$k]) {
                                    #        $this->_newData[$sku][$k] = array($this->_newData[$sku][$k], $vId);
                                    #    }
                                    #} else {
                                        $this->_newData[$sku][$k][$i] = $vId;
                                    #}
                                } else {
                                    $this->_newData[$sku][$k] = $vId;
                                }
                            } elseif ($allowSelectIds && isset($attr['options'][$v])) {
                                // select ids used, no mapping required
                            } else {
                                if ($k=='category.name') {
                                    if ($autoCreateCategories) {
                                        $newOptionId = $this->_importCreateCategory($v);
                                        if (is_array($newValue)) {
                                            $this->_newData[$sku][$k][$i] = $newOptionId;
                                        } else {
                                            $this->_newData[$sku][$k] = $newOptionId;
                                        }
                                        $profile->addValue('num_warnings');
                                        $logger->warning($this->__("Created a new category '%s'", $v));
                                    } else {
                                        $profile->addValue('num_errors');
                                        $logger->error('Invalid category: '.$v);
                                        $this->_valid[$sku] = false;
                                    }
                                } elseif ($autoCreateOptions && !empty($attr['attribute_id']) && (empty($attr['source_model']) || $attr['source_model']=='eav/entity_attribute_source_table')) {
                                    $newOptionId = $this->_importCreateAttributeOption($attr, $v);
                                    if (is_array($newValue)) {
                                        $this->_newData[$sku][$k][$i] = $newOptionId;
                                    } else {
                                        $this->_newData[$sku][$k] = $newOptionId;
                                    }
                                    $profile->addValue('num_warnings');
                                    $logger->warning($this->__("Created a new option '%s' for attribute '%s'", $v, $k));
                                } else {
                                    if ($k!='product.websites'
                                        || !Mage::helper('urapidflow')->hasEeGwsFilter()
                                    ) {
                                        $profile->addValue('num_errors');
                                        $logger->error($this->__("Invalid option '%s'", $v));
                                        $this->_valid[$sku] = false;
                                    }
                                }
                            }
                        } // foreach ((array)$newValue as $v)
                        if ($k=='product.websites'
                            && Mage::helper('urapidflow')->hasEeGwsFilter()
                        ) {
                            $wIdsOrig = (array)$this->_newData[$sku][$k];
                            $this->_newData[$sku][$k] = Mage::helper('urapidflow')->filterEeGwsWebsiteIds($wIdsOrig);
                            if (($wIdsSkipped = array_diff($wIdsOrig, $this->_newData[$sku][$k]))) {
                                $logger->warning($this->__("You are not allowed to associate products with this websites: %s", implode(',', $wIdsSkipped)));
                            }
                        }
                    }
                } // foreach ($p as $k=>$newValue)

                if (!$this->_valid[$sku]) {
                    $profile->addValue('rows_errors');
                }
            } catch (Unirgy_RapidFlow_Exception_Row $e) {
                $logger->error($e->getMessage());
                $profile->addValue('rows_error');
            }
        } // foreach ($this->_newData as $p)
        unset($p);
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
    /*
    $logger->setColumn(0);
    if (!empty($oldValues[$sku])) $logger->success('OLD: '.print_r($oldValues[$sku],1));
    if (!empty($this->_changeStock[$sku])) $logger->success('STOCK: '.print_r($this->_changeStock[$sku],1));
    if (!empty($this->_changeWebsite[$sku])) $logger->success('WEBSITE: '.print_r($this->_changeWebsite[$sku],1));
    if (!empty($this->_changeAttr[$sku])) $logger->success('ATTR: '.print_r($this->_changeAttr[$sku],1));
    */
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
/*
var_dump($this->_newData);
echo '<table><tr><td>';
var_dump($oldValues);
echo '</td><td>';
var_dump($this->_changeAttr);
echo '</td></tr></table>';
var_dump($this->_changeCategoryProduct);
var_dump($this->_changeStock);
var_dump($this->_changeWebsite);
echo '<hr>';
*/
    }

    protected function _rtIdxRegisterAttrChange($pId, $attrCode, $value, $isSku=true)
    {
        $sku = $pId;
        $pId = $isSku ? $this->_skus[$pId] : $pId;
        $attr = $this->_attr($attrCode);
        if (!empty($attr['rtidx_stock'])) {
            $this->_realtimeIdx['cataloginventory_stock'][$pId] = true;
        }
        if (!empty($attr['rtidx_eav'])) {
            $this->_realtimeIdx['catalog_product_attribute'][$pId] = true;
        }
        if (!empty($attr['rtidx_price'])) {
            $this->_realtimeIdx['catalog_product_price'][$pId] = true;
        }
        if (!empty($attr['rtidx_tag'])) {
            $this->_realtimeIdx['tag_summary'][$pId] = true;
        }
        if (!empty($attr['rtidx_category'])) {
            $this->_realtimeIdx['catalog_category_product'][$pId] = true;
        }
        if (!empty($attr['rtidx_url'])) {
            $this->_rtIdxRegisterByWebsites($sku, $this->_realtimeIdx['catalog_url']['full'], array('I','D'));
        }
        if (!empty($attr['rtidx_search'])) {
            $this->_rtIdxRegisterByWebsites($sku, $this->_realtimeIdx['catalogsearch_fulltext']['full'], array('I','D'));
        }
        if (in_array($attrCode, $this->_rtIdxFlatAttrCodes)) {
            if ($attrCode == 'status') {
                $this->_rtIdxRegisterByWebsites($sku, $this->_realtimeIdx['catalog_product_flat']['status'][$value], array('I','D'));
            } else {
                $this->_rtIdxRegisterByWebsites($sku, $this->_realtimeIdx['catalog_product_flat']['by_attr'][$attrCode], array('I','D'));
            }
        }
    }

    protected function _rtIdxRegisterNewProduct($pId, $isSku=true)
    {
        $sku = $pId;
        $pId = $isSku ? $this->_skus[$pId] : $pId;
        $this->_realtimeIdx['cataloginventory_stock'][$pId] = true;
        $this->_realtimeIdx['catalog_product_attribute'][$pId] = true;
        $this->_realtimeIdx['catalog_product_price'][$pId] = true;
        $this->_realtimeIdx['tag_summary'][$pId] = true;
        $this->_realtimeIdx['catalog_category_product'][$pId] = true;
        $this->_rtIdxRegisterByWebsites($sku, $this->_realtimeIdx['catalog_url']['full'], array('I','D'));
        $this->_rtIdxRegisterByWebsites($sku, $this->_realtimeIdx['catalogsearch_fulltext']['full'], array('I','D'));
        $this->_rtIdxRegisterByWebsites($sku, $this->_realtimeIdx['catalog_product_flat']['full'], array('I','D'));
    }

    protected function _rtIdxRegisterWebsiteChange($pId, $wData, $isSku=true)
    {
        $sku = $pId;
        $pId = $isSku ? $this->_skus[$pId] : $pId;
        $this->_realtimeIdx['cataloginventory_stock'][$pId] = true;
        $this->_realtimeIdx['catalog_product_attribute'][$pId] = true;
        $this->_realtimeIdx['catalog_product_price'][$pId] = true;
        $this->_realtimeIdx['tag_summary'][$pId] = true;
        $this->_realtimeIdx['catalog_category_product'][$pId] = true;
        $this->_rtIdxRegisterByWebsites($sku, $this->_realtimeIdx['catalog_url']['website'], array('C'));
        $this->_rtIdxRegisterByWebsites($sku, $this->_realtimeIdx['catalogsearch_fulltext']['website'], array('C'));
        $this->_rtIdxRegisterByWebsites($sku, $this->_realtimeIdx['catalog_product_flat']['website'], array('C'));
    }

    protected function _rtIdxRegisterCategoryChange($pId, $cData, $isSku=true)
    {
        $sku = $pId;
        $pId = $isSku ? $this->_skus[$pId] : $pId;
        $this->_rtIdxRegisterByWebsites($sku, $this->_realtimeIdx['catalog_url']['full'], array('I','D'));
        $this->_realtimeIdx['catalog_category_product'][$pId] = true;
    }

    protected function _rtIdxRegisterStockChange($pId, $sData, $isSku=true)
    {
        $sku = $pId;
        $pId = $isSku ? $this->_skus[$pId] : $pId;
        $this->_realtimeIdx['cataloginventory_stock'][$pId] = true;
    }

    protected function _rtIdxRegisterByWebsites($sku, &$indexStorage, $excludeActions=array())
    {
        $pId = $this->_skus[$sku];
        $current = !empty($this->_products[$pId][0]['product.websites'])
            ? $this->_products[$pId][0]['product.websites'] : array();
        $insert = !empty($this->_changeWebsite[$sku]['I'])
            ? $this->_changeWebsite[$sku]['I'] : array();
        $delete = !empty($this->_changeWebsite[$sku]['D'])
            ? $this->_changeWebsite[$sku]['D'] : array();
        $current = array_diff($current, $delete);
        $current = array_unique(array_merge($current, $insert));
        if (!in_array('C', $excludeActions)) {
            foreach ($current as $wId) {
                $indexStorage['C'][$wId][$pId] = true;
            }
        }
        if (!in_array('I', $excludeActions)) {
            foreach ($insert as $wId) {
                $indexStorage['I'][$wId][$pId] = true;
            }
        }
        if (!in_array('D', $excludeActions)) {
            foreach ($delete as $wId) {
                $indexStorage['D'][$wId][$pId] = true;
            }
        }
    }
}
