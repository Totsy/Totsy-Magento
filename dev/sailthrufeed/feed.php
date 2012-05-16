<?php 


$chash = __DIR__. '/' .md5($_SERVER['REQUEST_URI']).'.json';
$TTL = 30*60;
if (file_exists($chash)){
	$time = time() - filectime($chash);
	if ($time<$TTL){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		echo file_get_contents($chash);
		
		exit(0);
	}
}



require_once( '../../app/Mage.php' );
umask(0);
$mageRunCode = isset($_SERVER['MAGE_RUN_CODE']) ? $_SERVER['MAGE_RUN_CODE'] : '';
$mageRunType = isset($_SERVER['MAGE_RUN_TYPE']) ? $_SERVER['MAGE_RUN_TYPE'] : 'store';
Mage::app($mageRunCode, $mageRunType);	 


header('Cache-Control: no-cache, must-revalidate');
header('Content-type: application/json');

$out = array('events'=>array(), 'pending'=>array(), 'closing'=>array());

/*### DEFINE STORE AND CATEGORY  ###*/
//$categoryId = '8'; //totsy events category id
//$topEventCategoryId = '24'; //totsy top events category id
$storeId = Mage::app()->getStore()->getId(); //totsy store id


/*### DEFINE DATE&TIME AND VAR ###*/
$defaultTimezone = date_default_timezone_get();
$mageTimezone = Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE);
date_default_timezone_set($mageTimezone);

$maxOff = 0;
$start_date = strtotime(date('Y-m-d'));
$start_time = '1:00:00';
$order_desc = false;

if (!empty($_GET['order'])){
	if (strtolower($_GET['order']) == 'desc'){
		$order_desc = true; //DESC
	}
}
	
if (!empty($_GET['start_date']) && preg_match('/[\d]{4}[\-][\d]{2}[\-][\d]{2}/i',$_GET['start_date'],$m)){
	$start_date = strtotime($m[0]);
}

if (!empty($_GET['start_date']) && preg_match('/[\d]{2}[\-][\d]{2}[\-][\d]{4}/i',$_GET['start_date'],$m)){
	$ori_start_date = $m[0];
	$date_array = explode("-", $ori_start_date); // split the array
	$var_day = $date_array[0]; //day seqment
	$var_month = $date_array[1]; //month segment
	$var_year = $date_array[2]; //year segment
	$new_date_format = $var_year.'-'.$var_month.'-'.$var_day; // join them together
	$start_date = strtotime($new_date_format);
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

//open&top events
	/*filter event/category collection*/
	$_collection = loadCollection('event_start_date'); 
	foreach($_collection as $_category){
		$maxOff = max($maxOff, getLargestSaveByCategory($_category));
		getEventApiOutput($_category,'events',$out);
	}
	unset($_collection);


// closing events

	/*filter event/category collection*/
	$_collection = loadCollection('event_end_date', '+1 day');
	foreach($_collection as $_category){
		getEventApiOutput($_category,'closing',$out);
	}
	unset($_collection);

	
//pending events       

/*filter event/category collection*/
	$_collection = loadCollection('event_start_date', '+2 days');
	foreach($_collection as $_category){
		getEventApiOutput($_category,'pending',$out);
	}           

$out['max_off'] = floor($maxOff);


/*### OUTPUT JSON ###*/

$data = json_encode($out);
$fh = fopen($chash,'w');
fwrite($fh,$data);
fclose($fh);
echo $data;
/*### END ###*/





/*########################################################################*/
/*Getting how many percent of money save for a event*/
function getLargestSaveByCategory(Mage_Catalog_Model_Category $_category){
	
	//Lazy loading, note 0 is allowed
	if(!is_numeric($_category->getLargestSavePercentage())){
		$storeId = Mage::app()->getStore()->getId();
		$_category->getProductCollection()->setStoreId($storeId);
		
		$_productCollection = $_category
		    ->getProductCollection()
		    ->addAttributeToSelect('*')
		    ->addAttributeToSort('updated_at', 'desc')
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
					$percent = max($percent, number_format($finalpercentage, 0));
				}
			}
	    }
	    $_category->setLargestSavePercentage($percent);
	}
	
    return $_category->getLargestSavePercentage();
}

function getEventApiOutput(Mage_Catalog_Model_Category $_category , $type, &$out){
	
	$departmentOptionSource = Mage::getModel('catalog/product')->getResource()->getAttribute('departments')->getSource();
	$ageOptionSource  = Mage::getModel('catalog/product')->getResource()->getAttribute('ages')->getSource();
	
	$save = getLargestSaveByCategory($_category);
	$short = $_category->getShortDescription();
	$description = $_category->getDescription();
	$keyword = $_category->getMetaKeywords();

	$evnt = array();
	$evnt['name'] = $_category->getName();
	$evnt['url'] = Mage::getBaseUrl().$_category->getUrlPath();
	
	$productCollection = $_category->getProductCollection()->addAttributeToSelect(array('departments', 'ages'));
	$availableItems = count($productCollection);
	foreach ($productCollection as $product){
		$productsId[] = $product->getId();
		$dept = $product->getDepartments();
		$deptArray = explode(',', $dept);
		foreach($deptArray as $deptCode){
			$attrText = $departmentOptionSource->getOptionText($deptCode);
			if ($attrText != false){
				$rawCategoriesArray[] = $attrText;
				$rawCategoriesTranslateArray[] = Mage::helper('core')->__($attrText);
			}
		}
		$ages = $product->getAges();
		$agesArray = explode(',', $ages);
		foreach($agesArray as $ageCode){
			$attrTextAge = $ageOptionSource->getOptionText($ageCode);
			if ($attrTextAge != false){
				$rawAgeArray[] = $attrTextAge;
				$rawAgeTranslateArray[] = Mage::helper('core')->__($attrTextAge);
			}
		}
	}
	
	//Cleaning up duplicate
	$categoriesTranslateArray = array_values(array_unique($rawCategoriesTranslateArray));
	$agesTranslateArray = array_values(array_unique($rawAgeTranslateArray));
	$uniqueAgeArray = array_values(array_unique($rawAgeArray));
	$ageTag = implode(', ', $uniqueAgeArray);
	
	
	//Preparing results
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
		$evnt['categories'] = (count($categoriesTranslateArray))?$categoriesTranslateArray:array();
		$evnt['ages'] = (count($agesTranslateArray))?$agesTranslateArray:array();
		$evnt['items'] = $productsId;
		$evnt['tag'] = $ageTag;
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

function loadCollection ($field, $plus = null){
	
	global $eventArray, $start_date, $start_time;
	
	$event_date = array (
		'from' => date('Y-m-d', $start_date). ' '.$start_time,
		'to' => date('Y-m-d', $start_date).' 23:59:59'
	);
	if (!empty($plus)){
		$event_date['lteq'] = date('Y-m-d', strtotime($plus, $start_date)).' 23:59:59';
	}
	
	$_collection = Mage::getModel('catalog/category')->getCollection();
	$_collection->addAttributeToFilter('is_active', 1)
			->addAttributeToSelect('*')
			->addAttributeToSort($field, 'desc')
			->addFieldToFilter($field, $event_date)
			->load();
	return $_collection;	
}