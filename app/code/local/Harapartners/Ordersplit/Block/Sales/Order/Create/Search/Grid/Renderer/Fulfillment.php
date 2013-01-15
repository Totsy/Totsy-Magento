<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ryan.street
 * Date: 11/5/12
 * Time: 5:11 PM
 * To change this template use File | Settings | File Templates.
 */
class Harapartners_Ordersplit_Block_Sales_Order_Create_Search_Grid_Renderer_Fulfillment extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text {


    /**
     * Render product name to add Configure link
     *
     * @param   Varien_Object $row
     * @return  string
     */
    public function render(Varien_Object $row)
    {
        $product = Mage::getModel('catalog/product')->load($row->getEntityId());

        return $product->getFulfillmentType();
    }
}