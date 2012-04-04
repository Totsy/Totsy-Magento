<?php
/**
 * Harapartners
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Harapartners License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.Harapartners.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@Harapartners.com so we can send you a copy immediately.
 *
 * 
 */

class Harapartners_Categoryevent_Block_Index_Topnav extends Mage_Core_Block_Template {
	
	//CONST notVisibleIndividuallyCode = '1';
		
	protected $_memcache = null;
	protected $_cdsLifeTime = 1800;
	
	public function getMemcache(){
    	if(!$this->_memcache){
    		$this->_memcache = Mage::getSingleton('memcachedb/resource_memcache');
    	}
    	return $this->_memcache;
    }
    
    public function getCdsDataObject(){
    	
		$cdsData = array();
		//please make an unique key
		$pageName = 'layerlistpage';
		$storeCode = Mage::app()->getStore()->getCode();
		$memcacheKey = 'DATA_' . $storeCode . '_' . $pageName;
		//For yang, there is only one home page for EACH STORE, one key will be enough
		//For Edward, need to put request parameter in the key
		$params = Mage::app()->getRequest()->getParams();
		if(isset($params) && !!$params){
			foreach ($params as $key => $value){
				$memcacheKey .= '_' . $key . '_' . $value;
			}
		}

		$cdsData = $this->getMemcache()->read($memcacheKey);
		if(!$this->_validateCdsData($cdsData)){
			$cdsData = $this->_getCdsDataFromDb();
			$this->getMemcache()->write($memcacheKey, $cdsData, $cdsDataLifeTime);
		}
		
		//return Varien_Object($cdsData);
		return $cdsData;
    }
    
	protected function _validateCdsData($cdsData){
		if(empty($cdsData['category_product_complete_data'])){
			return false;
		}
		return true;
	}
    
    protected function _getCdsDataFromDb(){
		$cdsData = array();
		$cdsData = $this->topLayerNav();
		return $cdsData;
    }
	
    public function smallImageHelper(Mage_Catalog_Model_Product $product, $attributeName, $imageFile=null)
    {
        $this->_reset();
        $this->_setModel(Mage::getModel('catalog/product_image'));
        $this->_getModel()->setDestinationSubdir($attributeName);
        $this->setProduct($product);

        $this->setWatermark(Mage::getStoreConfig("design/watermark/{$this->_getModel()->getDestinationSubdir()}_image"));
        $this->setWatermarkImageOpacity(Mage::getStoreConfig("design/watermark/{$this->_getModel()->getDestinationSubdir()}_imageOpacity"));
        $this->setWatermarkPosition(Mage::getStoreConfig("design/watermark/{$this->_getModel()->getDestinationSubdir()}_position"));
        $this->setWatermarkSize(Mage::getStoreConfig("design/watermark/{$this->_getModel()->getDestinationSubdir()}_size"));

        if ($imageFile) {
            $this->setImageFile($imageFile);
        }
        else {
            // add for work original size
            $this->_getModel()->setBaseFile( $this->getProduct()->getData($this->_getModel()->getDestinationSubdir()) );
        }
        return $this;
    }
	
	
	public function topLayerNav(){
		//Default data structure/values
		$cdsData = array(
				'attr_text_label' => 'Results',
				'category_product_complete_data' => array()
		);
		
		try{
			$defaultTimezone = date_default_timezone_get();
			$mageTimezone = Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE);
			date_default_timezone_set($mageTimezone);
			$sortDate = now("Y-m-d");
			date_default_timezone_set($defaultTimezone);
			$storeId = Mage::app()->getStore()->getId();
			
			//To Jun: Note for rebuild script try not to get from cached or indexed data??
			
			// ---------- //
			// Load sorted live category info

			$sortentryLive = Mage::getModel('categoryevent/sortentry')->loadByDate($sortDate, $storeId, false)->getLiveQueue();
			$liveCategoryInfoArray = json_decode($sortentryLive, true);
			$liveCategoryIdArray = array();
			foreach ($liveCategoryInfoArray as $category){
				if(isset($category['entity_id']) && $category['entity_id']){
					$liveCategoryIdArray[] = $category['entity_id'];
				}
			}
			
			//To Jun: these code need to moved into the resource models
			// ---------- //
			// Load category <==> product relationship
			//Important validation
			if(!$liveCategoryIdArray){
				throw new Exception('No event available');
			}
			
			$readAdapter = Mage::getSingleton('core/resource')->getConnection('core_read');
			$selectQuery = $readAdapter->select()->from('catalog_category_product')->where('`category_id` IN(' . implode(',', $liveCategoryIdArray) . ')');
			$categoryProductRelations = $readAdapter->fetchAll($selectQuery);
			// Prepare unique product ID for the product collection query
			// Also, group products by category
			$uniqueProductIds = array();
			$categoryProductCompleteData = array();
			foreach($categoryProductRelations as $relation){
				if(isset($relation['category_id']) 
						&& !!$relation['category_id']
						&& isset($relation['product_id']) 
						&& !!$relation['product_id']){
							
					if(!in_array($relation['product_id'], $uniqueProductIds)){
						$uniqueProductIds[] = $relation['product_id'];
					}
					if(!array_key_exists($relation['category_id'], $categoryProductCompleteData)){
						$categoryProductCompleteData[$relation['category_id']] = array('product_list' => array());
					}
					if(!array_key_exists($relation['product_id'], $categoryProductCompleteData[$relation['category_id']]['product_list'])){
						$categoryProductCompleteData[$relation['category_id']]['product_list'][$relation['product_id']] = array();
					}
				}
			}
			
			// ---------- //
			// Load product collection
			
			//Important validation
			if(!$uniqueProductIds){
				throw new Exception('No product available');
			}
			
			//$type = Mage::registry('attrtype');
			$type = Mage::app()->getRequest()->getParam('type');
			//$value = Mage::registry('attrvalue');
			$value = Mage::app()->getRequest()->getParam('value');
			$typeAttributes = Mage::getModel('catalog/product')->getResource()->getAttribute($type);
			$valueId = $typeAttributes->getSource()->getOptionId($value);
			$label = Mage::helper('catalog')->__($value);
			
			$productCollection = Mage::getModel('catalog/product')->getCollection();
			$productCollection->getSelect()->where('`e`.`entity_id` IN(' . implode(',', $uniqueProductIds) . ')');
			$productCollection->addFieldToFilter($type, array('like' => '%'.$valueId.'%'));
			$productCollection->addFieldToFilter('visibility', array("in" => array(
					Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG,
					Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH
			)));
			$productCollection->addAttributeToSelect(array(
					'name', 
	        		'type_id', 
	        		'small_image', 
	        		'thumbnail',
	        		'url_path',
					'special_price',
					'original_price',
					'price')
	        );
			$productInfoArray = $productCollection->load()->toArray();
			
			foreach($liveCategoryInfoArray as &$liveCategoryInfo){
				$liveCategoryInfo['product_info_array'] = array();
				foreach ($categoryProductRelations as $relation){
					if(isset($relation['category_id'])
							&& isset($relation['product_id'])
							&& isset($liveCategoryInfo['entity_id'])
							&& $relation['category_id'] == $liveCategoryInfo['entity_id']){
						$productId = $relation['product_id'];
							
					}
				}
			}
			
			// ---------- //
			// Assemble data here
			// Note pass array by reference!
			$mediaBaseDir = Mage::getSingleton('catalog/product_media_config')->getBaseMediaPath();
			$emptyCategoryIds = array();
			foreach($categoryProductCompleteData as $categoryId => &$categoryInfoContainer){
				//put in product info
				if(empty($categoryInfoContainer['product_list'])){
					unset($categoryProductCompleteData[$categoryId]);
				}
				$categoryHasProduct = false;
				foreach($categoryInfoContainer['product_list'] as $containerProductId => &$containerProductInfo){
					$productFound = false;
					foreach($productInfoArray as $productId => $productInfo){
						if($containerProductId == $productId){
							$containerProductInfo = $productInfo;
							if(isset($productInfo['small_image'])){
								//$containerProductInfo['small_image'] = $mediaBaseDir . str_ireplace('/', DS, $productInfo['small_image']);    //it call from file system like F:\www\totsy\media\catalog\product\n\e\newborn_tiered_dots_top_2pc_diaper_set.jpg
								if ($productInfo['small_image'] != 'no_selection'){
									$containerProductInfo['small_image'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'catalog/product'.$productInfo['small_image'];
								}else {
									$containerProductInfo['small_image'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'catalog/product/placeholder/small_image.jpg';
								}
							}
							$categoryHasProduct = true;
							$productFound = true;
							break;
						}
					}
					if(!$productFound){
						unset($categoryInfoContainer['product_list'][$containerProductId]);
					}
				}
				if(!$categoryHasProduct){
					unset($categoryProductCompleteData[$categoryId]);
				}
				
				//put in category info
				$isCategoryLive = false;
				foreach($liveCategoryInfoArray as $liveCategoryInfo){
					if(isset($liveCategoryInfo['entity_id'])
							&& $liveCategoryInfo['entity_id'] == $categoryId){
						$categoryInfoContainer['category_info'] = $liveCategoryInfo;
						$categoryInfoContainer['prepare_timer'] = $this->prepareTimer($liveCategoryInfo['event_end_date']);
						
						$isCategoryLive = true;
						break;
					}
				}
				if(!$isCategoryLive){
					unset($categoryProductCompleteData[$categoryId]);
				}
			}
			
			$cdsData['attr_text_label'] = $label;
			$cdsData['category_product_complete_data'] = $categoryProductCompleteData;
		}catch(Exception $e){
			$cdsData ['category_product_complete_data'] = array();
		}
		
		return $cdsData;
	}
	
	public function getLayerCategoryCollection(){
		
		//get a live product array 
		$defaultTimezone = date_default_timezone_get();
		$mageTimezone = Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE);
		date_default_timezone_set($mageTimezone);
		$sortDate = now("Y-m-d");
		date_default_timezone_set($defaultTimezone);
		$storeId = Mage::app()->getStore()->getId();
		$sortentryLive = Mage::getModel('categoryevent/sortentry')->loadByDate($sortDate, $storeId, false)->getLiveQueue();
		
		//prase live product array which is like ["25","3","4","9","10","14","11","12","13","5","26"]
		try{
			$fulllivearray = json_decode($sortentryLive, true);
		}catch(Exception $e){
			$fulllivearray = array();
		}
		$liveCategoryIdArray = array();
		foreach ($fulllivearray as $item){
			 $liveCategoryIdArray[] = $item['entity_id'];
		}
		//get layer attribute and value, put them to collection filter
        $type = Mage::registry('attrtype');
		$value = Mage::registry('attrvalue');
		
		$subcatIds = array();
    	$sort_comp_date = date("Y-m-d G:i:s", (strtotime($sortDate)+86400 * 3));
        $store = Mage::app()->getStore($storeId);
        $storeCollection = Mage::getModel('catalog/category')->getCollection();
/*        if (!!$store->getId()) {
        	$storeRootId = $store->getRootCategoryId();
        	$storeCollection->addFieldToFilter('parent_id', $storeRootId)->load();
        }
        foreach ($storeCollection as $subcat){
        	array_push($subcatIds, $subcat->getId());        
        }*/
        $collection = Mage::getModel('catalog/category')->getCollection();
        $collection->getSelect()->where('e.entity_id IN (?)', $liveCategoryIdArray);
        $collection->addAttributeToSelect(array(
        		'name', 
        		'tags', 
        		'ages',
        		'departments',
        		'url_path',
        		'description', 
        		'thumbnail',
        		'event_start_date', 
        		'event_end_date')
        );
        $collection->load();
        return $collection;
	}
	
	
	public function getLayerCategoryLabel(){
		//get layer attribute and value, put them to collection filter
        $type = Mage::registry('attrtype');
		$value = Mage::registry('attrvalue');
		$attrOptions = Mage::getModel('catalog/category')->getResource()->getAttribute($type);
		$attrText = $attrOptions->getSource()->getOptionText($value);
		return $attrText;
	}
	
	public function getLayerProductCollection($category){
		Varien_Profiler::start('edwardlayernavproduct');
		//product should not be non-visible
		$visibility = 'visibility';	
		$notVisibleIndividuallyCode = '1';	
		
		//get type valueId pair
		$type = Mage::registry('attrtype');
		$value = Mage::registry('attrvalue');
		$typeAttributes = Mage::getModel('catalog/product')->getResource()->getAttribute($type);
		$valueId=$typeAttributes->getSource()->getOptionId($value);
		
		//get layer attribute and value, put them to collection filter
		$filterProducts=$category->getProductCollection()
				//->addAttributeToSelect('*')
				->addAttributeToSelect('name')
				->addAttributeToSelect('type_id')
				->addAttributeToSelect('visibility')
				->addAttributeToSelect('departments')
				->addAttributeToSelect('ages')
				->addAttributeToSelect('small_image')
				->addAttributeToSelect('path_url')
				->addFieldToFilter('visibility', array("neq" => $notVisibleIndividuallyCode))
				->addFieldToFilter($type, array('like' => '%'.$valueId.'%'))
				->load();
		Varien_Profiler::start('edwardlayernavproduct');		
		return $filterProducts;
	}
	
	public function prepareTimer($eventEndDate){
		
		$defaultTimezone = date_default_timezone_get();
		$mageTimezone = Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE);
		$endcount_utc = strtotime($eventEndDate);
		
		//$endcount_lc: local end count date
		date_default_timezone_set($mageTimezone);
		$endcount_lc = date("F j, Y, G:i:s", $endcount_utc);
		date_default_timezone_set($defaultTimezone);
		
		if ( !Mage::getSingleton('customer/session')->hasData('countdown_timer') ) {
			$timer = 0;
			Mage::getSingleton('customer/session')->setData('countdown_timer', $timer++);
			$timer = Mage::getSingleton('customer/session')->getData('countdown_timer');
		} else {	
			$timer = Mage::getSingleton('customer/session')->getData('countdown_timer');
			Mage::getSingleton('customer/session')->setData('countdown_timer', ++$timer);
		}
		
		$returnparam=array('endcount_lc'=>$endcount_lc, 'timer'=>$timer);
		return $returnparam;
	}
}