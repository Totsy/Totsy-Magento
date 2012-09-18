<?php

class Unirgy_RapidFlow_Model_Feed extends Mage_AdminNotification_Model_Feed
{
    const FEED_URL = 'download.unirgy.com/Unirgy_RapidFlow-notifications.feed';

    public function getFeedUrl()
    {
        if (is_null($this->_feedUrl)) {
            $this->_feedUrl = (Mage::getStoreConfigFlag(self::XML_USE_HTTPS_PATH) ? 'https://' : 'http://')
            . self::FEED_URL;
        }
        return $this->_feedUrl;
    }

    public function getLastUpdate()
    {
        return Mage::app()->loadCache('urapidflow_notifications_lastcheck');
    }

    public function setLastUpdate()
    {
        Mage::app()->saveCache(time(), 'urapidflow_notifications_lastcheck');
        return $this;
    }
}