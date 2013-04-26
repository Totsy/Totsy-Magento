<?php
/**
 * PHP Version 5.3
 *
 * @category  Totsy
 * @package   Totsy_Sailthru
 * @author    Slavik Koshelevskyy <skosh@totsy.com>
 * @copyright 2012 Totsy LLC Copyright (c) 
 */

class Totsy_Sailthru_Model_Feed extends Mage_Core_Model_Abstract 
{
	private $_cache = null;
	private $_feed = null;
	private $_output = array(
		'events'=>array(), 
		'pending'=>array(), 
		'closing'=>array(),
		'errors'=>array(),
		'max_off'=>0,

	);
	private $_shortLenght = 45;

	/**
	* Class construction method
	*
	* @return void
	*/
	public function __construct()
	{
		$this->_cache = Mage::helper('sailthru/cache');
		$this->_feed  = Mage::helper('sailthru/feed');
		parent::__construct();
	}

	public function runner($return = false)
	{
		$this->_feed->processor();

		if ($this->_feed->getType() == 'events'){
			$this->runEvents();
		} else if ($this->_feed->getType() == 'products'){
			$this->runProducts();
		}

	    if ($this->getFeedHelper()->filterErrors()){
	    	$this->_output['errors'] = null;
	    }

    	if ($return){
    		return $this->_output;
    	}

	}

	public function runProducts(){

		$this->_output['type'] = 'products';
		$this->_formatProducts(
	    	$this->_getListOfEvents(
	    		$this->getFeedHelper()->getInclude()
	    	)
	    );
	    
	}

	public function runEvents(){
		$this->_output['type'] = 'events';
		//open&top events
		$this->_formatter(
			$this->getFeedHelper()->goingLive(
				$this->_getSortEvents('live')
			),
			'events'
		);

		// closing events
		$this->_formatter(
			$this->getFeedHelper()->filter(
				array_merge(
					//$this->_getSortEvents('live'),
					$this->_getSortEvents('live','+1 day')
				)
			),
			'closing'
		);

		//pending events       
		$this->_formatter(
			$this->getFeedHelper()->filter(
				$this->_getSortEvents('upcoming'),
				'start'
			),
			'pending'
		);

		$validator = new Totsy_Sailthru_Helper_Validator_Feed();
       	if (!$validator->process($this->_output)){
    		$this->_output['errors'] = array_merge(
    			$this->_output['errors'],
    			$validator->getErrors()
    		);
    	}

	}

	public function getOutPut(){
		$json = json_encode($this->_output);
		$this->_cache->_setRightHttpHost($json);
		return $json;
	}

	/**
	* Returns Sailthru Cache Helper
	*
	* @return Totsy_Sailthru_Helper_Cache object
	*/
	public function getCacheHelper()
	{
		return $this->_cache;
	}

	/**
	* Returns Sailthru Feed Helper
	*
	* @return Totsy_Sailthru_Helper_Feed object
	*/
	public function getFeedHelper()
	{
		return $this->_feed;
	}

	private function _getProductsIds($event_id){
    	$productCollection = Mage::getModel('catalog/category')
	        ->load($event_id)
	        ->getProductCollection()
	        ->addAttributeToSelect('entity_id')
	        ->setVisibility(
	            Mage::getSingleton('catalog/product_visibility')
	                ->getVisibleInCatalogIds()
	        );
	    $productIds = array();
	    foreach ($productCollection as $product){
	        $productIds[] = $product->getId();
	    }
	    return $productIds;
	}

	private function _getProducts($event){
    	$productsCollection = Mage::getModel('catalog/category')
	        ->load($event->getEntityId())
	        ->getProductCollection()
	        ->addAttributeToSelect('entity_id');
	        
	    $exclude = $this->getFeedHelper()->getExcludeList();
	    if ( !empty($exclude) ){
		    $productsCollection->addAttributeToFilter(
		    	'entity_id', 
		    	array('nin' => $exclude)
		    );
		}
		return $productsCollection;
	}

    private function _formatter ($events,$type){
        $max_off = null;
        if (empty($events) || !is_array($events)){
            return; 
        } 

        foreach ($events as $key => $event){

            $event_tmp = array();
            if ($type == 'events'){

                $event['products'] = $this->_getProductsIds($event['entity_id']);
                $this->getFeedHelper()->preFormatEvent($event);
                $event_tmp = $this->getFeedHelper()->formatEvent($event);

                if ($event_tmp['discount']>$max_off){
                    $max_off = $event_tmp['discount'];
                }

            } else if ($type=='pending'){

            	$this->getFeedHelper()->preFormatEvent($event,array('products','discount'));
            	$event_tmp = $this->getFeedHelper()->formatPCEvent($event,'end');

            } else if ($type=='closing'){

            	$this->getFeedHelper()->preFormatEvent($event,array('products','discount'));
            	$event_tmp = $this->getFeedHelper()->formatPCEvent($event,'start');

            }

            $this->_output[$type][$key] = $event_tmp;
            $class = 'Totsy_Sailthru_Helper_Validator_'.ucfirst($type);
            $validator = new $class();
        	if (!$validator->process($event_tmp)){
        		$errors = $validator->getErrors();
        		if (!empty($errors)){
        			if (!empty($this->_output['errors'][$type]['validator'])){
	        			$errors = array_merge($this->_output['errors'][$type]['validator'],$errors);
	        			$errors = array_unique($errors);
        			}
        			$this->_output['errors'][$type]['validator'] = $errors;
        		}
        		$errors = $this->getFeedHelper()->getErrors();
        		if (!empty($errors)){

        			if (!empty($this->_output['errors'][$type]['helper'])){
	        			$errors = array_merge($this->_output['errors'][$type]['helper'],$errors);
	        			$errors = array_unique($errors);
        			}
        			$this->_output['errors'][$type]['helper'] = $errors;
        		}
        	}
        }
        if (!is_null($max_off)){
            $this->_output['max_off'] = $max_off;
        }
    }

	private function _formatProducts ($events){

        if (empty($events) || !is_array($events)){
            return; 
        } 

        $stores = Mage::app()->getStores(false, true);
        $defaultStore = $stores['default']->getId();

        foreach ($events as $event){

        	$max_off = 0;
            $products = array();
            $productCollection = $this->_getProducts( $event );

		    foreach ($productCollection as $_product){
		    	$obj = Mage::getModel('catalog/product')
		    			->load($_product->getId());
		        $product = array(
		        	'id' 			=> $obj->getEntityId(),
		        	'name'			=> $obj->getName(),
		        	'categories'	=> $obj->getAttributeTextByStore('departments', $defaultStore),
		        	'ages'			=> $obj->getAttributeTextByStore('ages', $defaultStore),
		        	'price'			=> number_format($obj->getSpecialPrice(),2),
		        	'msrp'			=> number_format($obj->getPrice(),2),
		        	'discount'		=> floor(
		        		($obj->getPrice() - $obj->getSpecialPrice())/$obj->getPrice()*100
		        	),
		        	'description'	=> $obj->getDescription(),
		        	'url'			=> Mage::getBaseUrl().$event->getUrlPath().'/'.$obj->getUrlPath,
		        	'image'			=> Mage::getBaseUrl('media').$obj->getImage(),
		        	'image_small'	=> Mage::getBaseUrl('media').$obj->getSmallImage(),
		        );

		        if (!empty($product['categories']) && !is_array($product['categories'])){
					$product['categories'] = array($product['categories']);
				}

		        if (!empty($product['ages']) && !is_array($product['ages'])){
					$product['ages'] = array($product['ages']);
				}
				if (empty($product['description'])){
					$product['description'] = $event->getDescription();
				}
				if ($product['discount']>$max_off){
					$max_off = $product['discount'];
				}

				$products[] = $product;

	          	$validator = new Totsy_Sailthru_Helper_Validator_Products();
	            if (!$validator->process($product)){
	            	$type = $event->getEntityId();
					$errors = $validator->getErrors();
	        		if (!empty($errors)){
	        			if (!empty($this->_output['errors'][$type]['validator'])){
		        			$errors = array_merge($this->_output['errors'][$type]['validator'],$errors);
		        			$errors = array_unique($errors);
	        			}
	        			$this->_output['errors'][$type]['validator'] = $errors;
	        		}
	        		$errors = $this->getFeedHelper()->getErrors();
	        		if (!empty($errors)){

	        			if (!empty($this->_output['errors'][$type]['helper'])){
		        			$errors = array_merge($this->_output['errors'][$type]['helper'],$errors);
		        			$errors = array_unique($errors);
	        			}
	        			$this->_output['errors'][$type]['helper'] = $errors;
	        		}
	            }
		    }

            $out = array(
            	'id'			=> $event->getEntityId(),
            	'name'			=> $event->getName(),
            	'start_date'	=> $event->getEventStartDate(),
            	'end_date'		=> $event->getEventEndDate(),
            	'products'		=> $products,
            	'max_off'		=> $max_off
            );
            unset($products);

            $this->_output['events'][] = $out;
            $validator = new Totsy_Sailthru_Helper_Validator_ProductsEvents();
            if (!$validator->process($out)){
				$errors = $validator->getErrors();
        		if (!empty($errors)){
        			if (!empty($this->_output['errors'][$type]['validator'])){
	        			$errors = array_merge($this->_output['errors'][$type]['validator'],$errors);
	        			$errors = array_unique($errors);
        			}
        			$this->_output['errors'][$type]['validator'] = $errors;
        		}
        		$errors = $this->getFeedHelper()->getErrors();
        		if (!empty($errors)){

        			if (!empty($this->_output['errors'][$type]['helper'])){
	        			$errors = array_merge($this->_output['errors'][$type]['helper'],$errors);
	        			$errors = array_unique($errors);
        			}
        			$this->_output['errors'][$type]['helper'] = $errors;
        		}
            }
        }
    }

    private function _getSortEvents($type,$plus=null){
    	$date = $this->getFeedHelper()->getStartDate();
    	if (!is_null($plus)){
    		$date = $this->getFeedHelper()->getStartDate();
    	}
    	$sort = Mage::getModel('categoryevent/sortentry')->loadByDate(date('Y-m-d',$date));
    	$return = json_decode($sort[$type.'_queue'],true);	
    	return $return;
    }

    private function _getListOfEvents($events){

    	if (!is_array($events)){
    		$events = array($events);
    	}

    	if (empty($events)){
    		return;
    	}

    	$return = array();
    	foreach ($events as $id){
    		$cat = Mage::getModel('catalog/category')->load($id);
    		if (empty($cat)){
    			continue;
    		}
    		$return[] = $cat;
    	}

    	return $return;
    }
}
