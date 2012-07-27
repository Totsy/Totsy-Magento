<?php

/**
 * @category    Totsy
 * @package     Harapartners_Affiliate
 * @author      Tharsan Bhuvanendran <tbhuvanendran@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

class Harapartners_Affiliate_FeedsController
    extends Mage_Core_Controller_Front_Action
{
    const COUNTER_LIMIT = 2000;

    /**
     * The XML feed being constructed.
     *
     * @var SimpleXmlElement
     */
    protected $_xml;

    /**
     * The collection of entries in this feed.
     *
     * @var object
     */
    protected $_entries;

    /**
     * Default action.
     *
     * @return void
     */
    public function indexAction()
    {
        $request = $this->getRequest();

        $from   = $request->getParam('from');
        $to     = $request->getParam('to');
        $type   = $request->getParam('type');
        $token  = $request->getParam('token');
        $period = $request->getParam('period');

        if ($period) {
            $period = 3600 * 24 * $period;
        }

        // rewrite the $from and $to dates from keyade-requested formats
        if (preg_match('/\d{6}/', $from)) {
            $from = substr($from, 0, 4) . '-' . substr($from, 4, 2) . '-' . substr($from, 6, 2);
        }
        if (preg_match('/\d{6}/', $to)) {
            $to = substr($to, 0, 4) . '-' . substr($to, 4, 2) . '-' . substr($to, 6, 2);
        }

        if ($token != '7cf7e9d58a213b2ebb401517d342475e') {
            $this->getResponse()
                ->setHeader('Content-Type', 'text/plain', true)
                ->setHttpResponseCode(400)
                ->setBody("A valid authorization token must be specified.");
            return;
        }

        $affiliateCode = $request->getParam('affiliate_code');
        if (empty($affiliateCode)) {
            $this->getResponse()
                ->setHeader('Content-Type', 'text/plain', true)
                ->setHttpResponseCode(400)
                ->setBody("An affiliate code must be specified.");
            return;
        }

        if (empty($from) || empty($to)) {
            $this->getResponse()
                ->setHeader('Content-Type', 'text/plain', true)
                ->setHttpResponseCode(400)
                ->setBody("A valid date range must be specified.");
            return;
        }

        // create a bare XML document with just the root element
        $xmlStr = <<<XML
<?xml version="1.0" encoding="utf-8" standalone="yes" ?>
<!DOCTYPE report PUBLIC "report" "https://dtool.keyade.com/dtd/conversions_v5.dtd">
<report></report>
XML;

        $this->_xml = new SimpleXMLElement($xmlStr);

        $this->_entries = Mage::getModel('customertracking/record')
            ->getCollection()
            ->addFieldToFilter('affiliate_code', $affiliateCode)
            ->addFieldToFilter('level', 0);

        switch ($type) {
            case 'signups':
                $this->_createSignups($from, $to);
                break;

            case 'referralsignups':
                $this->_createSignupsByReferral($from, $to);
                break;

            case 'sales':
                $this->_createSales($from, $to, $period);
                break;

            case 'referringsales':
                $this->_createReferringSales($from, $to, $period);
                break;

            default:
                $this->getResponse()
                    ->setHeader('Content-Type', 'text/plain', true)
                    ->setHttpResponseCode(400)
                    ->setBody("An affiliate code must be specified.");
                return;
        }

        $this->getResponse()
            ->setBody($this->_xml->asXML())
            ->setHeader('Content-Type', 'application/xml', true);
    }

    protected function _createSignups($from, $to)
    {
        $this->_entries->addFieldToFilter(
            'created_at',
            array('from' => $from, 'to' => $to, 'date'=> true)
        );
        foreach ($this->_entries as $record) {
            $this->_signupsEntry($record);
        }
    }

    protected function _createSignupsByReferral($from, $to)
    {
        $this->_entries->addFieldToFilter(
            'created_at',
            array('from' => $from, 'to' => $to, 'date'=> true)
        )->addFieldToFilter('level', 1);
        foreach ($this->_entries as $record) {
            $this->_signupsEntry($record);
        }
    }

    protected function _createSales($from, $to, $period)
    {
       
        $this->_entries->addFieldToFilter(
            'created_at',
            array('to' => $to, 'from' => $from, 'date'=>true)
        )->addFieldToFilter('customer_id', array( "notnull" => true));

        foreach ($this->_entries as $record) {
            // record may not have accurate customerId
            $clickId = $this->_extractClickId($record);
            $orderCollection = Mage::getModel('sales/order')->getCollection()
                ->addFieldToFilter(
                    'created_at',
                    array('to' => $to, 'from' => $from, 'date'=>true)
                )->addFieldToFilter('customer_id', $record->getCustomerId())
                ->addFieldToFilter('status', 'complete');

            foreach ($orderCollection as $order) {
                $this->_salesEntry($record, $order, $clickId, $period);
            }
        }
    }

    protected function _createReferringSales($from, $to, $period)
    {
        $this->_entries->addFieldToFilter(
            'created_at',
            array('to' => $to, 'from' => $from, 'date'=>true)
        )->addFieldToFilter('customer_id', array("notnull" => true));

        foreach ($this->_entries as $record) {
            // record may not have accurate customerId
            $clickId = $this->_extractClickId($record);
            $orderCollection = Mage::getModel('sales/order')->getCollection()
                ->addAttributeToFilter(
                    'created_at',
                    array('from' => $from, 'to' => $to, 'date'=>true)
                )->addFieldToFilter('customer_id', $record->getCustomerId())
                ->addFieldToFilter('status', 'complete');

            foreach ($orderCollection as $order) {
                $this->_salesEntry($record, $order, $clickId, $period);
            }            
        }
    }

    protected function _signupsEntry($record)
    {
        $clickId = $this->_extractClickId($record);
        if (false === $clickId) {
            return false;
        }

        $entry = $this->_xml->addChild('entry');
        $entry->addAttribute('clickId', $clickId);
        $entry->addAttribute('eventMerchantId', $record->getCustomerId());
        $entry->addAttribute('count1', 1);
        $entry->addAttribute('time', strtotime($record->getCreatedAt()));
        $entry->addAttribute('eventStatus', 'confirmed');

        return $entry;
    }

    protected function _salesEntry($record, $order, $clickId, $period)
    {
        $salesTime = strtotime($order->getCreatedAt());
        $registrationTime = strtotime($record->getCreatedAt());
        $lte = !($salesTime - $registrationTime <= $period);

        if ($period > 0 && $lte === false) {
            return false;
        }

        $entry = $this->_xml->addChild('entry');
        $entry->addAttribute('clickId', $clickId);
        $entry->addAttribute('lifetimeId', $record->getCustomerId());
        $entry->addAttribute('eventMerchantId', $order->getIncrementId());
        $entry->addAttribute('value1', $order->getGrandTotal());
        $entry->addAttribute('time', $salesTime);

        return $entry;
    }

    protected function _extractClickId($record)
    {
        $data = json_decode($record->getRegistrationParam(), true);
        $data = array_change_key_case($data, CASE_LOWER);

        return isset($data['clickid']) ? $data['clickid'] : false;
    }
}
