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
 */

class Mage_Rss_Block_Catalog_Event extends Mage_Rss_Block_Catalog_Abstract
{
    protected function _construct()
    {
        /*
        * setting cache to save the rss for 10 minutes
        */
        $this->setCacheKey('rss_catalog_event_'
            . $this->getRequest()->getParam('cid') . '_'
            . $this->getRequest()->getParam('store_id') . '_'
            . Mage::getModel('customer/session')->getId()
        );
        $this->setCacheLifetime(600);
    }
    
    
    
    protected function _getLargestSaveByCategory($_category){
              /*Getting how many percent of money save for a event  START*/
            $layer = Mage::getSingleton('catalog/layer')->setStore($storeId);
            $storeId = $this->_getStoreId();
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
            $_finalPrice = $this->helper('tax')->getPrice($_product, $_product->getFinalPrice());
            $_regularPrice = $this->helper('tax')->getPrice($_product, $_product->getPrice());
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
    
    
    public function importRssToXml($categoryId, $storeId, $rssObj, $upcomming = NULL){ 
        //get a live product array
        $defaultTimezone = date_default_timezone_get();
        $mageTimezone = Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE);
        date_default_timezone_set($mageTimezone);
        $sortDate = now("Y-m-d");
        date_default_timezone_set($defaultTimezone);
        $storeId = Mage::app()->getStore()->getId();

        /*if user want to check upcomming products, put this parameter to url*/
        $isCheckingUpcomming = $upcomming;
        $sortentryObject = Mage::getModel('categoryevent/sortentry')->loadByDate($sortDate);
        if (!isset($isCheckingUpcomming)){
            $sortentry = $sortentryObject->getLiveQueue();
        }else {
            $sortentry = $sortentryObject->getUpcomingQueue();
        }
        //prase live product array which is like a two diminational array
        try{
            $fullEventArray = json_decode($sortentry, true);
        }catch(Exception $e){
            $fullEventArray = array();
        }
        $eventArray = array();
        foreach ($fullEventArray as $item){
             $eventArray[] = $item['entity_id'];
        }
        if ($categoryId) {
            $category = Mage::getModel('catalog/category')->load($categoryId);
            if ($category && $category->getId()) {
                //want to load all products no matter anchor or not
                $category->setIsAnchor(true);
                $newurl = $category->getUrl();
                $title = $category->getName();
                /*filter event/category collection*/
                $_collection = $category->getCollection();
                $_collection->addAttributeToSelect('url_path')
                    ->addAttributeToSelect('name')
                    ->addAttributeToSelect('meta_keywords')
                    ->addAttributeToSelect('is_anchor')
                    ->addAttributeToFilter('is_active',1)
                    ->addAttributeToSelect('image')
                    ->addAttributeToSelect('short_description')
                    ->addAttributeToSort('event_start_date', 'desc')
                    ->addFieldToFilter('entity_id',array("in"=>$eventArray))
                    ->addIdFilter($category->getChildren()) 
                ;
                if (!isset($isCheckingUpcomming)){
                       $_collection->addFieldToFilter('event_end_date', array( "gt"=>$sortDate ));
                }
                $_collection->load();
                if (!isset($isCheckingUpcomming)){
                    $maxEvnetSave = 0;
                    foreach($_collection as $_category){
                        $maxEvnetSave = max($maxEvnetSave, $this->_getLargestSaveByCategory($_category));
                    }
                    $data = array('title' => $title,
                            'description' => $title,
                            'link'        => $newurl,
                            'hightestsave'       => $maxEvnetSave,
                            'charset'     => 'UTF-8',
                            );
                            
                    $rssObj->_addHeader($data);
                }
                /*push events/categories information to xml*/
                if ($_collection->getSize()>0) {
                       $args = array('rssObj' => $rssObj);
                    foreach($_collection as $_category){
                        $this->_getLargestSaveByCategory($_category);
                        $args['category'] = $_category;
                        $_category->setEventStatus((!isset($isCheckingUpcomming))?'live':'upcoming');
                        $this->addNewItemXmlCallback($args);
                    }
                } 
            }
        }
    }
    
    
    protected function _toHtml()
    {
        /*Start get event/category collection*/        
        $categoryId = $this->getRequest()->getParam('cid');
        $storeId = $this->_getStoreId();
        $rssObj = Mage::getModel('rss/rss');
        $this->importRssToXml($categoryId, $storeId, $rssObj);  //get Live product
        $this->importRssToXml($categoryId, $storeId, $rssObj, true);  //get upcomming product
        return $rssObj->createRssXml();
    }

    /**
     * Preparing data and adding to rss object
     *
     * @param array $args
     */
    public function addNewItemXmlCallback($args)
    {
        $category = $args['category'];
        $category->setAllowedInRss(true);
        $category->setAllowedPriceInRss(true);

        Mage::dispatchEvent('rss_catalog_event_xml_callback', $args);

        if (!$category->getAllowedInRss()) {
            return;
        }

        $description = '<table><tr>'
                     . '<td><a href="'.Mage::getBaseUrl().$category->getUrlPath().'"><img src="'
                     . Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'catalog/category/'.$category->getImage()
                     . '" border="0" align="left" height="263" width="228"></a></td>'
                     . '<td  style="text-decoration:none;">' . utf8_encode($category->getShortDescription());

        $description .= '</td></tr></table>';
        $rssObj = $args['rssObj'];
        $image_url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'catalog/category/'.$category->getImage();
        $short_description = utf8_encode($category->getShortDescription());
        $data = array(
                'title'         => $category->getName(),
                'shortdescription' => $short_description,
                'image'            => $image_url,
                'save'            => $category->getSavePercentage(),
                'keywords'      => $category->getMetaKeywords(),
                'status'        => $category->getEventStatus(),
                'link'          => Mage::getBaseUrl().$category->getUrlPath(),
                'description'   => $description,
            );

        $rssObj->_addEntry($data);
    }
}
