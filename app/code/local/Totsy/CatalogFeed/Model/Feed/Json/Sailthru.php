<?php
/**
 * @category    Totsy
 * @package     Totsy_CatalogFeed_Model
 * @author      Tharsan Bhuvanendran <tbhuvanendran@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

class Totsy_CatalogFeed_Model_Feed_Json_Sailthru
    extends Totsy_CatalogFeed_Model_Feed_Json
{
    public function __construct(array $options = array())
    {
        parent::__construct($options);

        $this->_content = array(
            'events'  => array(),
            'pending' => array(),
            'closing' => array()
        );
    }

    protected function _processEvent(
        Mage_Catalog_Model_Category $event = null,
        array $categoryInfo = array()
    ) {
        $mediaBaseUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);

        $url = Mage::getModel('core/url_rewrite')
            ->setStoreId(Mage::app()->getStore()->getId())
            ->loadByIdPath('category/'.$categoryInfo['entity_id'])
            ->getRequestPath();

        // this event is currently live
        if (strtotime($categoryInfo['event_start_date']) < time() &&
            strtotime($categoryInfo['event_end_date']) > time()
        ) {
            $this->_content['events'][] = array(
                'id'          => $categoryInfo['entity_id'],
                'name'        => $categoryInfo['name'],
                'url'         => $url,
                'description' => $categoryInfo['description'],
                'short'       => $categoryInfo['short_description'],
                'availableItems' => 'YES',
                'image'          => $mediaBaseUrl . '/' . $categoryInfo['image'],
                'image_small'    => $mediaBaseUrl . '/' . $categoryInfo['small_image'],
                'discount'       => $categoryInfo['max_discount_pct'],
                'start_date'     => $categoryInfo['event_start_date'],
                'end_date'       => $categoryInfo['event_end_date'],
                'categories'     => $categoryInfo['department'],
                'ages'           => $categoryInfo['age'],
                'tags'           => $categoryInfo['department'],
            );
        }

        // this event is closing in the next 24 hours
        if (strtotime($categoryInfo['event_end_date']) < time() + 60*60*24 &&
            strtotime($categoryInfo['event_end_date']) > time()
        ) {
            $this->_content['closing'][] = array(
                'name'     => $categoryInfo['name'],
                'url'      => $url,
                'end_date' => $categoryInfo['event_end_date']
            );
        }

        // this event is starting in the next 24 hours
        if (strtotime($categoryInfo['event_start_date']) > time() &&
            strtotime($categoryInfo['event_start_date']) < time() + 60*60*24
        ) {
            $this->_content['pending'][] = array(
                'name'     => $categoryInfo['name'],
                'url'      => $url,
                'start_date' => $categoryInfo['event_start_date']
            );
        }
    }

    protected function _processProduct(
        Mage_Catalog_Model_Category $event,
        Mage_Catalog_Model_Product $product,
        Mage_Catalog_Model_Product $parent = null
    ) {
    }
}
