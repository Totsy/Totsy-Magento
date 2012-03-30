<?php 

require_once( '../../app/Mage.php' );
Mage::app();

header('Cache-Control: no-cache, must-revalidate');
header('Content-type: application/json');

	$out = array('events'=>array(),'pending'=>array(),'closing'=>array());
	if (isset($token)){
		$out['token'] = $token;
	} 




	$categoryId = '8'; //totsy events category id
	$topEventCategoryId = '24'; //totsy top events category id
	$storeId = Mage::app()->getStore()->getId(); //totsy store id


	//get a live product array
   	$defaultTimezone = date_default_timezone_get();
	$mageTimezone = Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE);
	date_default_timezone_set($mageTimezone);
	$sortDate = now("Y-m-d");
   	date_default_timezone_set($defaultTimezone);
   	
   	/*if user want to check upcomming products, put this parameter to url*/
   	$sortentryObject = Mage::getModel('categoryevent/sortentry')->loadByDate($sortDate, $storeId, false);
	$sortentry = $sortentryObject->getLiveQueue();
	$upcomingEntry = $sortentryObject->getUpcomingQueue();

	try{
		$fullEventArray = json_decode($sortentry, true);
	}catch(Exception $e){
		$fullEventArray = array();
	}

	foreach ($fullEventArray as $item){
		 $eventArray[] = $item['entity_id'];
	}
		
	$maxOff = 0;
	//push top event
	$topCategory = Mage::getModel('catalog/category')->load($topEventCategoryId);
	if ($topCategory && $topCategory->getId()) {
		$_topCollection = $topCategory->getCollection();
		$_topCollection->addIdFilter($topCategory->getChildren())
					   ->addAttributeToSelect('*');
		$_topCollection->load();
		foreach($_topCollection as $_category){
			//$_category = Mage::getModel('catalog/category')->load($_categoryShort->getEntityId());
			$maxOff = max($maxOff, getLargestSaveByCategory($_category));
			
			getEventApiOutput($_category,'events',$out);
		}
	}
		
		
		
		
	//push live events and closing events

     $category = Mage::getModel('catalog/category')->load($categoryId);
     if ($category && $category->getId()) {
         /*filter event/category collection*/
         $_collection = $category->getCollection();
         $_collection->addAttributeToFilter('is_active',1)
         	 	->addAttributeToSelect('*')
             	->addFieldToFilter('entity_id',array("in"=>$eventArray))
			 	->addAttributeToSort('event_start_date', 'desc')
             	->addIdFilter($category->getChildren())
         ;
         $_collection->addFieldToFilter('event_end_date', array( "gt"=>$sortDate ));
         $_collection->load();
         
         

		$productsId = array();      
			foreach($_collection as $_category){
			//$_category = Mage::getModel('catalog/category')->load($_categoryShort->getEntityId());
		    $maxOff = max($maxOff, getLargestSaveByCategory($_category));
		    getEventApiOutput($_category,'events',$out);
			$now = strtotime("now");
			$tomorrow = strtotime("+1 day");
			$endDate = strtotime($_category->getEventEndDate());  //$_category->getEventEndDate() is equal to date('Y-m-d H:i:s' , strtotime($_category->getEventEndDate()))
			if (($endDate>$now)&&($endDate<$tomorrow)){
				getEventApiOutput($_category,'closing',$out);
			}
		}
            
	}
		
	//pending events       
    try{
		$fullPendingEventArray = json_decode($upcomingEntry, true);
	}catch(Exception $e){
		$fullPendingEventArray = array();
	}

	foreach ($fullPendingEventArray as $item){
		 $pendingEventArray[] = $item['entity_id'];
	}
        
        
     $category = Mage::getModel('catalog/category')->load($categoryId);
     if ($category && $category->getId()) {
         /*filter event/category collection*/
         $_upcomingCollection = $category->getCollection();
         $_upcomingCollection->addAttributeToFilter('is_active',1)
         	 	->addAttributeToSelect('*')
            	->addFieldToFilter('entity_id',array("in"=>$pendingEventArray))
				->addAttributeToSort('event_start_date', 'desc')
          	    ->addIdFilter($category->getChildren())
         ;
         $_upcomingCollection->addFieldToFilter('event_end_date', array( "gt"=>$sortDate ));
         $_upcomingCollection->load();

		$productsId = array();
		foreach($_upcomingCollection as $_category){
			//$_category = Mage::getModel('catalog/category')->load($_categoryShort->getEntityId());
			getEventApiOutput($_category,'pending',$out);
		}           
    }
        
		
	
	$out['max_off'] = floor($maxOff);
	echo json_encode($out);


function getLargestSaveByCategory(Mage_Catalog_Model_Category $_category){
	
	
	
	
    /*Getting how many percent of money save for a event  START*/
	$storeId = Mage::app()->getStore()->getId();
  	$layer = Mage::getSingleton('catalog/layer')->setStore($storeId);
	$productCollection = Mage::getModel('catalog/product')->getCollection();
	$currentCategory = $layer->setCurrentCategory($_category);
	$layer->prepareProductCollection($productCollection);
	$productCollection->addCountToCategories($_collection);
	
	$_category->getProductCollection()->setStoreId($storeId);
	
	$_productCollection = $currentCategory
	    ->getProductCollection()
	    ->addAttributeToSelect('*')
	    ->addAttributeToSort('updated_at','desc')
	    ->setVisibility(Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds())
	    ->setCurPage(1)
	    ->setPageSize(50)
	;
	$percent = 0;
    foreach ($_productCollection as $_product){
		$_finalPrice = Mage::helper('tax')->getPrice($_product, $_product->getFinalPrice());
		$_regularPrice = Mage::helper('tax')->getPrice($_product, $_product->getPrice());
		if ($_regularPrice != $_finalPrice):
		$getpercentage = number_format($_finalPrice / $_regularPrice * 100, 2);
		$finalpercentage = 100 - $getpercentage;
		if ($finalpercentage < 100){
			$percent = max($percent,number_format($finalpercentage, 0));
		}
		endif;
    }
    if ($percent!=0){
    	$_category->  setSavePercentage($percent);
    }
    /*Getting how many percent of money save for a event  END*/
    return $percent;
}

function getEventApiOutput(Mage_Catalog_Model_Category $_category , $type, &$out){
	
	
	$_categoryCode = 'departments';
	$_ageCode = 'ages';
	
	
	
	$save = getLargestSaveByCategory($_category);
	$shortDesc = $_category->getShortDescription();
	$keyword = $_category->getMetaKeywords();
	$collection = $_category->getProductCollection();
    foreach ($collection as $product) {
        $productsId[] = $product->getEntityId();
    }
    $availableItems = count($collection);
	$evnt = array();
	$short = $_category->getShortDescription();
	$evnt['name'] = $_category->getName();
	$evnt['url'] = Mage::getBaseUrl().$_category->getUrlPath();
	
	$categories = $_category->getDepartments();
	$categoriesArray = explode(',', $categories);
	$categoriesTranslateArray = array();
	foreach ($categoriesArray as $categorydepts){
		// Load product collection
		$attrOptions = Mage::getModel('catalog/category')->getResource()->getAttribute($_categoryCode);
		$attrText = $attrOptions->getSource()->getOptionText($categorydepts);
		$categoriesTranslateArray[] = $attrText;
	}
	
	
	$ages = $_category->getAges();
	$ageArray = explode(',', $ages);
	$agesTranslateArray = array();
	foreach ($ageArray as $categoryages){
		// Load product collection
		$attrOptions = Mage::getModel('catalog/category')->getResource()->getAttribute($_ageCode);
		$attrText = $attrOptions->getSource()->getOptionText($categoryages);
		$agesTranslateArray[] = $attrText;
	}	
	
	
	$description = $_category->getDescription();
	
	if (strcmp($type,'events')==0){
		$evnt['id'] = $_category->getEntityId();
		$evnt['description'] = (!isset($description))?"":$description;
		$evnt['short'] = (!isset($short))?eventsReview_default_json_cut_string($description,45):$short;
		$evnt['availableItems'] = (!!$availableItems)?'YES':'NO';
		$evnt['brandName'] = $_category->getVendor();
		$evnt['image'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'catalog/category/'.$_category->getImage();
		$evnt['image_small'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'catalog/category/'.$_category->getThumbnail();
		$evnt['discount'] = floor($save);
		$evnt['start_date'] = date('m-d-y g:i:s A',strtotime($_category->getEventStartDate())) ;
		$evnt['categories'] = (!isset($categories))?array():$categoriesTranslateArray;
		$evnt['ages'] = (!isset($ages))?array():$agesTranslateArray;
		$evnt['items'] = $productsId;
		$evnt['tag'] = $_category->getAges();
	}
	
	if (strcmp($type,'pending')!=0){
		$evnt['end_date'] = date('m-d-y g:i:s A',strtotime($_category->getEventEndDate())) ; //date('m-d-y g:i:s A',$event['end_date']['sec']);
	}
	
	$out[$type][] = $evnt;
}


function eventsReview_default_json_cut_string($str,$length=null){
	$return = '';
	$str = strip_tags($str);
	$split = preg_split("/[\s]+/",$str);
	$len = 0;
	if (is_array($split) && count($split)>0){
		foreach($split as $splited){
			$tmp_len = $len + strlen($splited) +1;
			if ($tmp_len < $length){
				$len = $tmp_len;
				$return.= $splited.' ';
			} else {
				break;
			}
		}
	}
	
	if (strlen($return)>0){
		return $return;
	} else {
		return $str;
	}
}