<?php
/**
 * @category    Totsy
 * @package     Totsy_Customer_Model
 * @author      Tharsan Bhuvanendran <tbhuvanendran@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

class Totsy_Customer_Model_Group
    extends Mage_Customer_Model_Group
{
    protected function _beforeSave()
    {
        $this->_prepareData();

        if ($this->getId()) {
            Mage::throwException(
                'Modification of existing customer groups is forbidden.'
            );
        }

        return parent::_beforeSave();
    }
}
