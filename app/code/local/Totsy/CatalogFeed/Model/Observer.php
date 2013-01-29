<?php
/**
 * @category    Totsy
 * @package     Totsy_CatalogFeed_Model
 * @author      Tharsan Bhuvanendran <tbhuvanendran@totsy.com>
 * @copyright   Copyright (c) 2013 Totsy LLC
 */

require 'aws.phar';

class Totsy_CatalogFeed_Model_Observer
{
    /**
     * Generate a product feed for Insparq, and submit it to their AWS S3 file
     * store.
     *
     * @return bool
     */
    public function insparqSubmit()
    {
        $env = (string) Mage::getConfig()->getNode('environment');
        if ('dev' !== $env) {
            return false;
        }

        $cfgRepo = Mage::getStoreConfig('catalogfeed/repository/insparq');

        $feed = Mage::getSingleton('catalogfeed/feed_csv_insparq')->generate();

        try {
            $client = \Aws\S3\S3Client::factory(
                array(
                     'key'    => $cfgRepo['credentials']['key'],
                     'secret' => $cfgRepo['credentials']['secret'],
                )
            );

            $client->putObject(
                array(
                     'Bucket'        => $cfgRepo['bucket']['name'],
                     'Key'           => 'products_' . date('y_m_d_H_i_s'),
                     'Content-Type'  => 'text/csv',
                     'Cache-Control' => 'max-age=86400',
                     'Expires'       => strtotime("+1 day"),
                     'Body'          => $feed
                )
            );
        } catch (Exception $e) {
            Mage::logException($e);
        }

        return true;
    }
}
