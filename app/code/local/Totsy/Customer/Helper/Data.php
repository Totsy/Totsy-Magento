<?php
/**
 * @category    Totsy
 * @package     Totsy_Customer_Helper
 * @author      Tharsan Bhuvanendran <tbhuvanendran@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

class Totsy_Customer_Helper_Data
    extends Mage_Customer_Helper_Data
{
    public function sanitizeEmail($email)
    {
        if (false === strpos($email, '@')) {
            return $email;
        }

        list($username, $domain) = explode('@', $email);

        if ('gmail.com' == $domain) {
            $username = str_replace('.', '', $username);
            if (false !== ($pos = strpos($username, '+'))) {
                $username = substr($username, 0, $pos);
            }
        }

        return $username . '@' . $domain;
    }
}
