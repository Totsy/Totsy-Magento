<?php
/**
 * @category    Totsy
 * @package     Totsy_Log_Model
 * @author      Tharsan Bhuvanendran <tbhuvanendran@totsy.com>
 * @copyright   Copyright (c) 2013 Totsy LLC
 */

/**
 * Override various _save* methods to skip saving those data points.
 */
class Totsy_Log_Model_Resource_Visitor extends Mage_Log_Model_Resource_Visitor
{
    protected function _saveUrlInfo($visitor)
    {
        return $this;
    }

    protected function _saveVisitorInfo($visitor)
    {
        return $this;
    }

    protected function _saveVisitorUrl($visitor)
    {
        return $this;
    }
}
