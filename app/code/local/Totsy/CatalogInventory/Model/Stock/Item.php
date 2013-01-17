<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ryan.street
 * Date: 12/14/12
 * Time: 2:35 PM
 * To change this template use File | Settings | File Templates.
 */
class Totsy_CatalogInventory_Model_Stock_Item extends Mage_CatalogInventory_Model_Stock_Item {

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

        $reserved = Mage::helper('totsy_cataloginventory')->getReserveCount($this->getProductId());

        $calcQty = $this->getQty();
        if($reserved != false) {
            $calcQty = $this->getQty() - $reserved;
        }

        if ($calcQty - $qty < 0) {
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
