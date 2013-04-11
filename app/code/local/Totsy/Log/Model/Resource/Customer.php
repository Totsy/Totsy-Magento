<?php
/**
 * @category    Totsy
 * @package     Totsy_Log_Model
 * @author      Tharsan Bhuvanendran <tbhuvanendran@totsy.com>
 * @copyright   Copyright (c) 2013 Totsy LLC
 */

class Totsy_Log_Model_Resource_Customer extends Mage_Log_Model_Resource_Customer
{
    /**
     * Get a Zend_Db_Select object for loading entries.
     * Skip the parent (Mage_Log_Model_Resource_Customer) implementation because
     * it joins on log_* tables that are no longer written to.
     *
     * @param string                  $field
     * @param mixed                   $value
     * @param Mage_Log_Model_Customer $object
     *
     * @return Varien_Db_Select|Zend_Db_Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = Mage_Core_Model_Resource_Db_Abstract::_getLoadSelect($field, $value, $object);

        return $select->order('login_at DESC')->limit(1);
    }
}
