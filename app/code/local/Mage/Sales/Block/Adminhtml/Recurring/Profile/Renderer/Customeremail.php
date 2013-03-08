<?php
/**
 * Created by Crown Partners
 *
 * Custom profile renderer for customer_email
 *
 * Date: 3/8/13
 */

class Mage_Sales_Block_Adminhtml_Recurring_Profile_Renderer_Customeremail extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    /**
     * Add a renderer for the customer_email column
     *
     * @param Varien_Object $row
     * @return mixed
     */
    public function render(Varien_Object $row) {
        $value = $row->getData($this->getColumn()->getIndex());
        $value = unserialize($value);
        return $value['customer_email'];
    }
}