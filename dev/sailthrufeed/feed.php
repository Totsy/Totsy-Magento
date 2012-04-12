<?php 
//error_reporting(E_ALL | E_STRICT);
//ini_set('display_errors', 1);
$rootDir = dirname(dirname(__DIR__));

$compilerConfig = $rootDir.'/includes/config.php';
if (file_exists($compilerConfig)) {
    include $compilerConfig;
}

require_once( $rootDir.'/app/Mage.php' );

Mage::app();
//Mage::setIsDeveloperMode(true);
//header('Cache-Control: no-cache, must-revalidate');
//header('Content-type: application/json');

$out = array('events'=>array(),'pending'=>array(),'closing'=>array());

/*### DEFINE STORE AND CATEGORY  ###*/
$categoryId = '8'; //totsy events category id
$topEventCategoryId = '24'; //totsy top events category id
$storeId = Mage::app()->getStore()->getId(); //totsy store id


/*### DEFINE DATE&TIME AND VAR ###*/
$defaultTimezone = date_default_timezone_get();
$mageTimezone = Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE);
date_default_timezone_set($mageTimezone);

$maxOff = 0;
$start_date = strtotime(date('Y-m-d'));
$start_time = '19:00:00';
$order_desc = false;

if (!empty($_GET['order'])){
	if (strtolower($_GET['order']) == 'desc'){
		$order_desc = true; //DESC
	}
}
	
if (!empty($_GET['start_date']) && preg_match('/[\d]{4}[\-][\d]{2}[\-][\d]{2}/i',$_GET['start_date'],$m)){
	$start_date = strtotime($m[0]);
}
	
if (!empty($_GET['start_time']) && preg_match('/[\d]{2}/',$_GET['start_time'])){
	if (strtolower($_GET['start_time']) == 'am'){
		$start_time = '08:00:00';
	}
}
	
date_default_timezone_set($defaultTimezone);

/*### PROCESS DATA ###*/
/*if user want to check upcomming products, put this parameter to url*/
//$sortentryObject = Mage::getModel('categoryevent/sortentry')->loadByDate(date('Y-m-d',$start_date), $storeId, false);
//$eventArray = processEventsJson($sortentryObject->getLiveQueue(),'entity_id');
//$pendingEventArray = processEventsJson($sortentryObject->getUpcomingQueue(),'entity_id');
$eventArray = $pendingEventArray = array();

$category = Mage::getModel('catalog/category'); //->load($categoryId);

//open&top events
if ($category && $category->getId()) {
	/*filter event/category collection*/
	$_collection = loadCollection('event_start_date'); 
	foreach($_collection as $_category){
		$maxOff = max($maxOff, getLargestSaveByCategory($_category));
		getEventApiOutput($_category,'events',$out);
	}
	unset($_collection);
}

// closing events
if ($category && $category->getId()) {
	/*filter event/category collection*/
	$_collection = loadCollection('event_close_date','+1 day');
	foreach($_collection as $_category){
		getEventApiOutput($_category,'closing',$out);
	}
	unset($_collection);
}
	
//pending events       
if ($category && $category->getId()) {
/*filter event/category collection*/
	$_collection = loadCollection('event_start_date','+2 days');
	foreach($_collection as $_category){
		getEventApiOutput($_category,'pending',$out);
	}           
}
$out['max_off'] = floor($maxOff);


/*### OUTPUT JSON ###*/
echo json_encode($out);

/*### END ###*/

function getLargestSaveByCategory(Mage_Catalog_Model_Category $_category){
	
    /*Getting how many percent of money save for a event  START*/
	$storeId = Mage::app()->getStore()->getId();
  	$layer = Mage::getSingleton('catalog/layer')->setStore($storeId);
	//$productCollection = Mage::getModel('catalog/product')->getCollection();
	$currentCategory = $layer->setCurrentCategory($_category);
	//$layer->prepareProductCollection($productCollection);
	//$productCollection->addCountToCategories($_collection);
	
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
		if ($_regularPrice != $_finalPrice && !empty($_regularPrice) && !empty($_finalPrice)){
			$getpercentage = number_format($_finalPrice / $_regularPrice * 100, 2);
			$finalpercentage = 100 - $getpercentage;
			if ($finalpercentage < 100){
				$percent = max($percent,number_format($finalpercentage, 0));
			}
		}
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
	
	if(empty($productsId)){
		$productsId = array();
	}
	
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
		$evnt['end_date'] = date('m-d-y g:i:s A',strtotime($_category->getEventEndDate())) ;
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

function processEventsJson($json, $field){
	$eventArray = array();
	$fullArray = json_decode($json, true);
	if (json_last_error() == JSON_ERROR_NONE){
		foreach ($fullArray as $item){
			$eventArray[] = $item[$field];
		}
		unset($fullArray);
	} 
	return $eventArray;
}

function loadCollection ($filed,$plus = null){
	
	global $category, $eventArray, $start_date, $start_time;
	
	$event_date = array (
		'gteq' => date('Y-m-d',$start_date). ' '.$start_time,
		'lteq' => date('Y-m-d',$start_date).' 23:59:59'
	);
	if (!empty($plus)){
		$event_date['lteq'] = date('Y-m-d',strtotime($plus,$start_date)).' 23:59:59';
	}
	
	$_collection = $category->getCollection();
	$_collection->addAttributeToFilter('is_active',1)
		->addAttributeToSelect('*')
		//->addFieldToFilter('entity_id',array("in"=>$eventArray))
		->addAttributeToSort($field, 'desc')
		->addIdFilter($category->getChildren())
		->addFieldToFilter($field, $event_date)
		->load();
	return $_collection;	
}