<?php
/**
 * @category    Totsy
 * @package     Totsy
 * @author      Tharsan Bhuvanendran <tbhuvanendran@totsy.com>
 * @copyright   Copyright (c) 2013 Totsy LLC
 */

class Totsy_Adminhtml_Block_Widget_Grid_Column_Filter_Text
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Text
{
    public function getCondition()
    {
        $wildcard = $this->getColumn()->getData('wildcard');

        return ($wildcard !== null)
            ? array('like' => str_replace($wildcard, '%', $this->getValue()))
            : parent::getCondition();
    }
}