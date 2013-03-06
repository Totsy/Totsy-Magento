<?php
/**
 * @category    Totsy
 * @package     Totsy_Scheduler_Model
 * @author      Tharsan Bhuvanendran <tbhuvanendran@totsy.com>
 * @copyright   Copyright (c) 2013 Totsy LLC
 */

class Totsy_Scheduler_Model_Schedule extends Aoe_Scheduler_Model_Schedule
{
    public function notify()
    {
        $env = (string) Mage::getConfig()->getNode('environment');
        if ('production' == $env) {
            parent::notify();
        }
    }
}
