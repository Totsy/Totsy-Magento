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

        // rewrite the $from and $to dates from keyade-requested formats
        if (preg_match('/\d{6}/', $from)) {
            $from = substr($from, 0, 4) . '-' . substr($from, 4, 2) . '-' . substr($from, 6, 2);
        }
        if (preg_match('/\d{6}/', $to)) {
            $to = substr($to, 0, 4) . '-' . substr($to, 4, 2) . '-' . substr($to, 6, 2);
        }

        // set time endpoints for the date range
        $from .= ' 00:00:00';
        $to   .= ' 23:59:59';

        // authenticate the request
        if (!$token) {
            $this->getResponse()
                ->setHeader('Content-Type', 'text/plain', true)
                ->setHttpResponseCode(400)
                ->setBody("A valid authorization token must be specified.");
            return;
        }

        // ensure the the request is for a valid affiliate code
        $affiliateCode = Mage::getSingleton('core/encryption')->decrypt($token);
        $affiliate = Mage::getSingleton('affiliate/record')->loadByAffiliateCode($affiliateCode);
        if (!$affiliate || !$affiliate->getId()) {
            $this->getResponse()
                ->setHeader('Content-Type', 'text/plain', true)
                ->setHttpResponseCode(400)
                ->setBody("A valid affiliate code must be specified.");
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

        $xml = new SimpleXMLElement($xmlStr);

        $adapter = Mage::getSingleton('core/resource')->getConnection('core_read');
        $select  = new Zend_Db_Select($adapter);
        $staticOne = new Zend_Db_Expr("'1'");
        $staticConfirmed = new Zend_Db_Expr("'confirmed'");

        $from = Mage::getModel('core/date')->gmtDate(null, $from);
        $to = Mage::getModel('core/date')->gmtDate(null, $to);

        switch ($type) {
            case 'signups':
                $select->from(array('c' => 'customertracking_record'), 'c.registration_param')
                    ->joinInner(
                        array('e' => 'customer_entity'),
                        'c.customer_id = e.entity_id',
                        array('eventMerchantId' => 'e.entity_id')
                    )->columns(array('time' => 'UNIX_TIMESTAMP(c.created_at)'))
                    ->columns(array('count1' => $staticOne))
                    ->columns(array('eventStatus' => $staticConfirmed))
                    ->where('c.affiliate_code = ?', $affiliateCode)
                    ->where("c.created_at BETWEEN '$from' AND '$to'")
                    ->where('c.level = 0');
                break;

            case 'referralsignups':
                $select->from(array('c' => 'customertracking_record'), 'c.registration_param')
                    ->joinInner(
                        array('e' => 'customer_entity'),
                        'c.customer_id = e.entity_id',
                        array('eventMerchantId' => 'e.entity_id')
                    )->columns(array('time' => 'UNIX_TIMESTAMP(c.created_at)'))
                    ->columns(array('count1' => $staticOne))
                    ->columns(array('eventStatus' => $staticConfirmed))
                    ->where('c.affiliate_code = ?', $affiliateCode)
                    ->where("c.created_at BETWEEN '$from' AND '$to'")
                    ->where("c.level = 1");
                break;

            case 'sales':
                $select->from(array('c' => 'customertracking_record'), 'c.registration_param')
                    ->joinInner(
                        array('e' => 'customer_entity'),
                        'c.customer_id = e.entity_id',
                        array('lifetimeId' => 'e.entity_id')
                    )->joinInner(
                        array('s' => 'sales_flat_order'),
                        's.customer_id = e.entity_id',
                        array('eventMerchantId' => 's.increment_id')
                    )->columns(array('count1' => $staticOne))
                    ->columns(array('value1' => 's.grand_total'))
                    ->columns(array('time' => 'UNIX_TIMESTAMP(c.created_at)'))
                    ->columns(array('eventStatus' => $staticConfirmed))
                    ->where('c.affiliate_code = ?', $affiliateCode)
                    ->where("c.created_at BETWEEN '$from' AND '$to'")
                    ->where('c.level = 0');

                if ($period) {
                    $select->where("DATEDIFF(s.created_at, e.created_at) < ?", $period);
                }

                break;

            case 'referringsales':
                $select->from(array('c' => 'customertracking_record'), 'c.registration_param')
                    ->joinInner(
                        array('e' => 'customer_entity'),
                        'c.customer_id = e.entity_id',
                        array('lifetimeId' => 'e.entity_id')
                    )->joinInner(
                        array('s' => 'sales_flat_order'),
                        's.customer_id = e.entity_id',
                        array('eventMerchantId' => 's.increment_id')
                    )->columns(array('count1' => $staticOne))
                    ->columns(array('value1' => 's.grand_total'))
                    ->columns(array('time' => 'UNIX_TIMESTAMP(c.created_at)'))
                    ->columns(array('eventStatus' => $staticConfirmed))
                    ->where('c.affiliate_code = ?', $affiliateCode)
                    ->where("c.created_at BETWEEN '$from' AND '$to'")
                    ->where("c.level = 1");

                if ($period) {
                    $select->where("DATEDIFF(s.created_at, e.created_at) < ?", $period);
                }

                break;

            default:
                $this->getResponse()
                    ->setHeader('Content-Type', 'text/plain', true)
                    ->setHttpResponseCode(400)
                    ->setBody("Invalid feed name: $type");
                return;
        }

        foreach ($select->query() as $entry) {
            if ($this->_rewriteEntry($entry)) {
                $xmlEntry = $xml->addChild('entry');
                foreach ($entry as $key => $value) {
                    $xmlEntry->addAttribute($key, $value);
                }
            }
        }

        $this->getResponse()
            ->setBody($xml->asXML())
            ->setHeader('Content-Type', 'application/xml', true);
    }

    /**
     * Rewrite a feed entry, represented as an associative array.
     *
     * @param array $entry
     *
     * @return bool
     */
    protected function _rewriteEntry(&$entry)
    {
        if (isset($entry['registration_param'])) {
            $regParams = json_decode($entry['registration_param'], true);
            if (isset($regParams['clickId'])) {
                $entry['clickId'] = $regParams['clickId'];
            } else if(isset($regParams['K_18733'])) {
                $entry['clickId'] = $regParams['K_18733'];
            } else {
                return false;
            }

            unset($entry['registration_param']);
        }
        return true;
    }
}
