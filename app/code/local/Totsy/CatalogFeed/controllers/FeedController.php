<?php
/**
 * @category    Totsy
 * @package     Totsy_CatalogFeed
 * @author      Tharsan Bhuvanendran <tbhuvanendran@totsy.com>
 * @copyright   Copyright (c) 2013 Totsy LLC
 */

class Totsy_CatalogFeed_FeedController extends Mage_Core_Controller_Front_Action
{
    public function sailthruAction()
    {
        $feed = Mage::getSingleton('catalogfeed/feed_json_sailthru')->generate();

        $this->getResponse()->setHeader('Content-Type', 'application/json', true)
            ->setBody($feed);
    }
}
