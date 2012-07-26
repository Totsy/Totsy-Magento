<?php
/**
 * @category    Totsy
 * @package     Harapartners_Customertracking_Helper
 * @author      Tharsan Bhuvanendran <tbhuvanendran@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */
 
class Harapartners_Customertracking_Helper_CustomPixel
{
    const TRIALPAY_SHARED_KEY = '98b57df81d5e93501539700286314bedd73802e25df127db026c196eefef3d8d';

    /**
     * Generate a TrialPay pixel query string.
     * This includes parameters tp_t (Unix timestamp), tp_sid (clickId from user
     * registration), and tp_v1 (HMAC-MD5 signed hash of other parameters).
     *
     * @return string
     */
    public function trialpay()
    {
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $trackingInfo = Mage::getSingleton('customer/session')
            ->getData('affiliate_info');
        $regParams = json_decode($trackingInfo['registration_param'], true);
        $regParams = array_change_key_case($regParams);

        $queryParams = array(
            'tp_t' => time(),
            'tp_sid' => $regParams['subid'],
        );

        $queryParams['tp_v1'] = hash_hmac(
            'md5',
            http_build_query($queryParams),
            self::TRIALPAY_SHARED_KEY
        );

        return http_build_query($queryParams);
    }
}
