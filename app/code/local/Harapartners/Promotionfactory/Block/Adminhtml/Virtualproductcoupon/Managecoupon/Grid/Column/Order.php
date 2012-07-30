<?php

/**
 * @category    Harapartners
 * @package     Harapartners_Promotionfactory_Block_Adminhtml_Virtualproductcoupon_Managecoupon_Grid_Column_Order
 * @author      Tom Royer <troyer@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */
class Harapartners_Promotionfactory_Block_Adminhtml_Virtualproductcoupon_Managecoupon_Grid_Column_Order
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * Render Order Od linked to its order edit page
     *
     * @param   Varien_Object $row
     * @return  string
     */
    protected function _getValue(Varien_Object $row)
    {
        if (!$row->getOrderId()) {
            return '';
        }
        return '<a href="' . $this->getUrl('adminhtml/sales_order/view', array('order_id' => $row->getOrderId())) . '">'
            . $this->htmlEscape($row->getData($this->getColumn()->getIndex())) . '</a>';
    }
}
