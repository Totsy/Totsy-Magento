<?php
/**
 * Crown Partners LLC
 *
 * @category    {category}
 * @package     {package}
 * @author: chris.davidowski
 */

class Totsy_Sales_Model_Quote_Address_Item extends Mage_Sales_Model_Quote_Address_Item
{

    public function importQuoteItem(Mage_Sales_Model_Quote_Item $quoteItem) {
        parent::importQuoteItem($quoteItem);
        $this->setCategoryId($quoteItem->getCategoryId());
        $this->setCategoryName($quoteItem->getCategoryName());
        return $this;
    }
}