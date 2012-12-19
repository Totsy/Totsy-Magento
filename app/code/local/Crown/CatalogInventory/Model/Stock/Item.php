<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ryan.street
 * Date: 12/14/12
 * Time: 2:35 PM
 * To change this template use File | Settings | File Templates.
 */
class Crown_CatalogInventory_Model_Stock_Item extends Mage_CatalogInventory_Model_Stock_Item {

    /**
     * Check quantity requested against items in quotes
     *
     * @param decimal $qty
     * @return bool
     */
    public function checkQty($qty) {
        if (!$this->getManageStock() || Mage::app()->getStore()->isAdmin()) {
            return true;
        }

        $id = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($this->getProduct()->getId());

        if($id) {
            $product_id = $id[0];
        }
        else {
            $product_id = $this->getProductId();
        }

        $resource = Mage::getSingleton('core/resource');

        $quoteTable = $resource->getTableName('sales/quote_item');

        $connection = $resource->getConnection('core_read');

        $quote_id = Mage::getSingleton('checkout/cart')->getQuote()->getId();

        $select = $connection->select(array('qty'))
            ->from(array('a' => $quoteTable), array('reserved' => 'SUM(qty)'))
            ->where('a.product_id=?', $product_id)
            ->where('quote_id != ? ', $quote_id);

        $reserved = $connection->fetchOne($select);

        if($reserved) {
            $totalRemaining = $this->getQty() - $reserved;

            if($totalRemaining - $qty < 0) {
                return FALSE;
            }
        }

        if ($this->getQty() - $qty < 0) {
            switch ($this->getBackorders()) {
                case Mage_CatalogInventory_Model_Stock::BACKORDERS_YES_NONOTIFY:
                case Mage_CatalogInventory_Model_Stock::BACKORDERS_YES_NOTIFY:
                    break;
                default:
                    return false;
                    break;
            }
        }
        return true;
    }

}