<?php

/*
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User Software Agreement (EULA).
 * It is also available through the world-wide-web at this URL:
 * http://www.harapartners.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to eula@harapartners.com so we can send you a copy immediately.
 * 
 */

class Harapartners_Service_Model_Rewrite_Catalog_Convert_Adapter_Product extends Mage_Catalog_Model_Convert_Adapter_Product {
    
    const MULTI_DELIMITER   = ','; //Harapartners, Jun
    const DEFAULT_FIELD_DELIMITER   = ','; //Harapartners, Jun

    public function saveRow(array $importData){
        $product = $this->getProductModel()
            ->reset();

        if (empty($importData['store'])) {
            if (!is_null($this->getBatchParams('store'))) {
                $store = $this->getStoreById($this->getBatchParams('store'));
            } else {
                $message = Mage::helper('catalog')->__(
                    'Skipping import row, required field "%s" is not defined.',
                    'store'
                );
                Mage::throwException($message);
            }
        } else {
            $store = $this->getStoreByCode($importData['store']);
        }

        if ($store === false) {
            $message = Mage::helper('catalog')->__(
                'Skipping import row, store "%s" field does not exist.',
                $importData['store']
            );
            Mage::throwException($message);
        }

        if (empty($importData['sku'])) {
            $message = Mage::helper('catalog')->__('Skipping import row, required field "%s" is not defined.', 'sku');
            Mage::throwException($message);
        }
        $product->setStoreId($store->getId());
        $productId = $product->getIdBySku($importData['sku']);
        $new = true; //Hara Partners, Richu
        if ($productId) {
            $product->load($productId);
            $new = false; //Hara Partners, Richu
            
            //Harapartners, Jun, when import scritp updates an existing product, calculate the qty_delta for PO transactions
            $oldStockItem = $product->getStockItem();
            if($oldStockItem && $oldStockItem->getId() && isset($importData['qty'])){
            	$qtyDelta = $importData['qty'] - $oldStockItem->getQty();
            	Mage::register('temp_product_import_qty_delta_for_po_' . $importData['sku'], $qtyDelta);
            }
            
        } else {
            $productTypes = $this->getProductTypes();
            $productAttributeSets = $this->getProductAttributeSets();

            /**
             * Check product define type
             */
            if (empty($importData['type']) || !isset($productTypes[strtolower($importData['type'])])) {
                $value = isset($importData['type']) ? $importData['type'] : '';
                $message = Mage::helper('catalog')->__(
                    'Skip import row, is not valid value "%s" for field "%s"',
                    $value,
                    'type'
                );
                Mage::throwException($message);
            }
            $product->setTypeId($productTypes[strtolower($importData['type'])]);
            /**
             * Check product define attribute set
             */
            if (empty($importData['attribute_set']) || !isset($productAttributeSets[$importData['attribute_set']])) {
                $value = isset($importData['attribute_set']) ? $importData['attribute_set'] : '';
                $message = Mage::helper('catalog')->__(
                    'Skip import row, the value "%s" is invalid for field "%s"',
                    $value,
                    'attribute_set'
                );
                Mage::throwException($message);
            }
            $product->setAttributeSetId($productAttributeSets[$importData['attribute_set']]);

            foreach ($this->_requiredFields as $field) {
                $attribute = $this->getAttribute($field);
                if (!isset($importData[$field]) && $attribute && $attribute->getIsRequired()) {
                    $message = Mage::helper('catalog')->__(
                        'Skipping import row, required field "%s" for new products is not defined.',
                        $field
                    );
                    Mage::throwException($message);
                }
            }
            
            
            /**
             * Hara Partners START
             * Richu
             */
        
            if ($importData['type'] == 'configurable') {
                $product->setCanSaveConfigurableAttributes(true);
                $configAttributeCodes = explode(self::DEFAULT_FIELD_DELIMITER, $importData['configurable_attribute_codes']);
                $usingAttributeIds = array();
                foreach($configAttributeCodes as $attributeCode) {
                    $attributeCode = trim($attributeCode);
                    $attribute = $product->getResource()->getAttribute($attributeCode);
                    if ($product->getTypeInstance()->canUseAttribute($attribute)) {
                        if ($new) { // fix for duplicating attributes error
                            $usingAttributeIds[] = $attribute->getAttributeId();
                        }
                    }
                }
                if (!empty($usingAttributeIds)) {
                    $product->getTypeInstance()->setUsedProductAttributeIds($usingAttributeIds);
                    $product->setConfigurableAttributesData($product->getTypeInstance()->getConfigurableAttributesAsArray());
                    $product->setCanSaveConfigurableAttributes(true);
                    $product->setCanSaveCustomOptions(true);
                }
                if (isset($importData['conf_simple_products'])) {
                    $product->setConfigurableProductsData($this->getProductIdFromSku($importData['conf_simple_products'], $product));
                }
            }
            /**
             * Hara Partners END
             * Richu
             */
        }

        $this->setProductTypeInstance($product);

        if (isset($importData['category_ids'])) {
            $product->setCategoryIds($importData['category_ids']);
        }

        foreach ($this->_ignoreFields as $field) {
            if (isset($importData[$field])) {
                unset($importData[$field]);
            }
        }

        if ($store->getId() != 0) {
            $websiteIds = $product->getWebsiteIds();
            if (!is_array($websiteIds)) {
                $websiteIds = array();
            }
            if (!in_array($store->getWebsiteId(), $websiteIds)) {
                $websiteIds[] = $store->getWebsiteId();
            }
            $product->setWebsiteIds($websiteIds);
        }

        if (isset($importData['websites'])) {
            $websiteIds = $product->getWebsiteIds();
            if (!is_array($websiteIds) || !$store->getId()) {
                $websiteIds = array();
            }
            $websiteCodes = explode(',', $importData['websites']);
            foreach ($websiteCodes as $websiteCode) {
                try {
                    $website = Mage::app()->getWebsite(trim($websiteCode));
                    if (!in_array($website->getId(), $websiteIds)) {
                        $websiteIds[] = $website->getId();
                    }
                } catch (Exception $e) {}
            }
            $product->setWebsiteIds($websiteIds);
            unset($websiteIds);
        }

        $ordersplit = Mage::helper('ordersplit');
        if(!in_array($importData['fulfillment_type'], $ordersplit->getAllowedFulfillmentTypeArray())){
            $error_message = 'fulfillment type "'.$importData['fulfillment_type'].'" is unknown.  Valid fulfillment types are: ';
            foreach($ordersplit->getAllowedFulfillmentTypeArray() as $fulfillment_type)
                $error_message .= $fulfillment_type.', ';
            Mage::throwException(substr($error_message,0,-2));
        }

        if($importData['case_pack_qty'] && !is_numeric($importData['case_pack_qty']))
            Mage::throwException('case_pack_qty must be a valid number when included, please correct or remove the current value: "'.$importData['case_pack_qty'].'".');

        foreach ($importData as $field => $value) {
            if (in_array($field, $this->_inventoryFields)) {
                continue;
            }
            if (is_null($value)) {
                continue;
            }

            $attribute = $this->getAttribute($field);
            if (!$attribute) {
                continue;
            }

            $isArray     = false;
            $setValue     = $value;

            if ($attribute->getFrontendInput() == 'multiselect') {
                //Harapartners, Jun, START
                if(!$value || empty($value)){
                    continue;
                }
                $value = explode(self::MULTI_DELIMITER, $value);
                foreach($value as &$valueEntry){
                    $valueEntry = trim($valueEntry);
                }
                //Harapartners, Jun, END
                $isArray = true;
                $setValue = array();
            }

            if ($value && $attribute->getBackendType() == 'decimal') {
                $setValue = $this->getNumber($value);
            }

            if ($attribute->usesSource()) {
                $options = $attribute->getSource()->getAllOptions(false);

                if ($isArray) {
                    foreach ($options as $item) {
                        if (in_array($item['label'], $value)) {
                            $setValue[] = $item['value'];
                        }
                    }
                } else {
                    $setValue = false;
                    foreach ($options as $item) {
                        if (is_array($item['value'])) {
                            foreach ($item['value'] as $subValue) {
                                if (isset($subValue['value']) && $subValue['value'] == $value) {
                                    $setValue = $value;
                                }
                            }
                        } else if ($item['label'] == $value) {
                            $setValue = $item['value'];
                        }
                    }
                }
            }

            //Hara Partners, Richu
            //Hara, Song added $setValue === 0 && !$value condition to fix the bug when the attribute is boolean type
           
            if( ($setValue === 0 && !$value) || ($setValue === false && !$setValue && !!$value) || (is_array($setValue) && empty($setValue)) ){
                $message = 'the value in the \''.$field.'\' column does not exist. Please correct it or add it to the attribute set.';
                Mage::throwException($message);
            }else{
                $product->setData($field, $setValue);
            }
            
        }

        if (!$product->getVisibility()) {
            $product->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE);
        }

        $stockData = array();
        $inventoryFields = isset($this->_inventoryFieldsProductTypes[$product->getTypeId()])
            ? $this->_inventoryFieldsProductTypes[$product->getTypeId()]
            : array();
        foreach ($inventoryFields as $field) {
            if (isset($importData[$field])) {
                if (in_array($field, $this->_toNumber)) {
                    $stockData[$field] = $this->getNumber($importData[$field]);
                } else {
                    $stockData[$field] = $importData[$field];
                }
            }
        }
        $product->setStockData($stockData);

        $mediaGalleryBackendModel = $this->getAttribute('media_gallery')->getBackend();

        $arrayToMassAdd = array();

        foreach ($product->getMediaAttributes() as $mediaAttributeCode => $mediaAttribute) {
            if (isset($importData[$mediaAttributeCode])) {
                $file = trim($importData[$mediaAttributeCode]);
                if (!empty($file) && !$mediaGalleryBackendModel->getImage($product, $file)) {
                    $arrayToMassAdd[] = array('file' => trim($file), 'mediaAttribute' => $mediaAttributeCode);
                }
            }
        }

        $addedFilesCorrespondence = $mediaGalleryBackendModel->addImagesWithDifferentMediaAttributes(
            $product,
            $arrayToMassAdd, Mage::getBaseDir('media') . DS . 'import',
            false,
            false
        );

        foreach ($product->getMediaAttributes() as $mediaAttributeCode => $mediaAttribute) {
            $addedFile = '';
            if (isset($importData[$mediaAttributeCode . '_label'])) {
                $fileLabel = trim($importData[$mediaAttributeCode . '_label']);
                if (isset($importData[$mediaAttributeCode])) {
                    $keyInAddedFile = array_search($importData[$mediaAttributeCode],
                        $addedFilesCorrespondence['alreadyAddedFiles']);
                    if ($keyInAddedFile !== false) {
                        $addedFile = $addedFilesCorrespondence['alreadyAddedFilesNames'][$keyInAddedFile];
                    }
                }

                if (!$addedFile) {
                    $addedFile = $product->getData($mediaAttributeCode);
                }
                if ($fileLabel && $addedFile) {
                    $mediaGalleryBackendModel->updateImage($product, $addedFile, array('label' => $fileLabel));
                }
            }
        }
        
         
        //Hara Partners, Richu media_gallery Start
        if (!empty($importData['media_gallery'])) {
        	$mediaGalleryImages = explode(self::DEFAULT_FIELD_DELIMITER, $importData['media_gallery']);
        	foreach($mediaGalleryImages as $imageFile){
        		$imageFile = trim($imageFile);
        		$file = Mage::getBaseDir('media') . DS . 'import' . $imageFile;
        		if(!empty($file) && file_exists($file)){
        			$mediaGalleryBackendModel->addImage($product, $file, null, false, false);
        		}
        	}
        }
        //Hara Partners, Richu media_gallery End
     
     

        $product->setIsMassupdate(true);
        $product->setExcludeUrlRewrite(true);

        //Validation mode skips product save and the following re-index logic
        if(!Mage::registry('import_validation_only')){
        	$product->save();
        }

        // Store affected products ids
        $this->_addAffectedEntityIds($product->getId());
        // Update configrable product qty counter
        Mage::helper('product/outofstock')->updateConfigurableProductQty($product);

        return true;
    }

    
    protected function getProductIdFromSku($configurableSkusData,$product) {
        $productIds = array();
        $configurableSkus = explode(',', str_replace(" ", "", $configurableSkusData));
        foreach ($configurableSkus as $productSku) {
            if (($sku = (int)$product->getIdBySku($productSku)) > 0) {
                parse_str("position=", $productIds[$sku]);
            }
        }
        return $productIds;
    }
}
