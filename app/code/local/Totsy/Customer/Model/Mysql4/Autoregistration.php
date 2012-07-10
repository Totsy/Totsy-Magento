<?php
/**
 * @category    Totsy
 * @package     Totsy_Customer_Model_Mysql4
 * @author      Tharsan Bhuvanendran <tbhuvanendran@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

class Totsy_Customer_Model_Mysql4_Autoregistration
    extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init(
            'totsycustomer/autoregistration',
            'customer_autoregistration_id'
        );
    }

    /**
     * Load an Autoregistration model, fetched by the token value.
     *
     * @param string $token
     *
     * @return array The data for the Autoregistration model.
     */
    public function loadByToken($token)
    {
        $readAdapter = $this->_getReadAdapter();
        $select = $readAdapter->select()
            ->from($this->getMainTable())
            ->where('token = :token');
        $result = $readAdapter->fetchRow(
            $select,
            array('token' => $token)
        );

        if (!$result) {
            $result = array();
        }

        return $result;
    }
}
