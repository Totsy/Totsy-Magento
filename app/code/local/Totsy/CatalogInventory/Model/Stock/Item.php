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

        if (!$this->hasReserved()) {
            $reserved = Mage::helper('totsy_cataloginventory')->getReserveCount($this->getProductId());
            $this->setReserved($reserved);
        } else {
            $reserved = $this->getReserved();
        }

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

    /**
     * Checking quote item quantity
     *
     * Second parameter of this method specifies quantity of this product in whole shopping cart
     * which should be checked for stock availability
     *
     * @param mixed $qty quantity of this item (item qty x parent item qty)
     * @param mixed $summaryQty quantity of this product
     * @param mixed $origQty original qty of item (not multiplied on parent item qty)
     * @return Varien_Object
     */
    public function checkQuoteItemQty($qty, $summaryQty, $origQty = 0)
    {

        $result = new Varien_Object();
        $result->setHasError(false);

        /** @var $_helper Mage_CatalogInventory_Helper_Data */
        $_helper = Mage::helper('cataloginventory');

        if (!is_numeric($qty)) {
            $qty = Mage::app()->getLocale()->getNumber($qty);
        }

        /**
         * Check quantity type
         */
        $result->setItemIsQtyDecimal($this->getIsQtyDecimal());
        if (!$this->getIsQtyDecimal()) {
            $result->setHasQtyOptionUpdate(true);
            $qty = intval($qty);

            /**
              * Adding stock data to quote item
              */
            $result->setItemQty($qty);

            if (!is_numeric($qty)) {
                $qty = Mage::app()->getLocale()->getNumber($qty);
            }
            $origQty = intval($origQty);
            $result->setOrigQty($origQty);
        }

        if ($this->getMinSaleQty() && ($qty) < $this->getMinSaleQty()) {
            $result->setHasError(true)
                ->setMessage(
                    $_helper->__('The minimum quantity allowed for purchase is %s.', $this->getMinSaleQty() * 1)
                )
                ->setQuoteMessage($_helper->__('Some of the products cannot be ordered in requested quantity.'))
                ->setQuoteMessageIndex('qty');
            return $result;
        }

        if ($this->getMaxSaleQty() && ($qty) > $this->getMaxSaleQty()) {
            $result->setHasError(true)
                ->setMessage(
                    $_helper->__('The maximum quantity allowed for purchase is %s.', $this->getMaxSaleQty() * 1)
                )
                ->setQuoteMessage($_helper->__('The maximum quantity allowed for purchase is %s.', $this->getMaxSaleQty() * 1))
                ->setQuoteMessageIndex('qty');
            return $result;
        }

        $result->addData($this->checkQtyIncrements($qty)->getData());

        if ($result->getHasError()) {
            return $result;
        }

        if (!$this->getManageStock()) {
            return $result;
        }

        if (!$this->getIsInStock()) {
            $result->setHasError(true)
                ->setMessage($_helper->__('This product is currently out of stock.'))
                ->setQuoteMessage($_helper->__('Some of the products are currently out of stock'))
                ->setQuoteMessageIndex('stock');
            $result->setItemUseOldQty(true);
            return $result;
        }

        if (!$this->checkQty($summaryQty) || !$this->checkQty($qty)) {
            $message = $_helper->__('The requested quantity for "%s" is not available.', $this->getProductName());
            $result->setHasError(true)
                ->setMessage($message)
                ->setQuoteMessage($message)
                ->setQuoteMessageIndex('qty');
            return $result;
        } else {
            if (($this->getQty() - $summaryQty) < 0) {
                if ($this->getProductName()) {
                    if ($this->getIsChildItem()) {
                        $backorderQty = ($this->getQty() > 0) ? ($summaryQty - $this->getQty()) * 1 : $qty * 1;
                        if ($backorderQty > $qty) {
                            $backorderQty = $qty;
                        }

                        $result->setItemBackorders($backorderQty);
                    } else {
                        $orderedItems = $this->getOrderedItems();
                        $itemsLeft = ($this->getQty() > $orderedItems) ? ($this->getQty() - $orderedItems) * 1 : 0;
                        $backorderQty = ($itemsLeft > 0) ? ($qty - $itemsLeft) * 1 : $qty * 1;

                        if ($backorderQty > 0) {
                            $result->setItemBackorders($backorderQty);
                        }
                        $this->setOrderedItems($orderedItems + $qty);
                    }

                    if ($this->getBackorders() == Mage_CatalogInventory_Model_Stock::BACKORDERS_YES_NOTIFY) {
                        if (!$this->getIsChildItem()) {
                            $result->setMessage(
                                $_helper->__('This product is not available in the requested quantity. %s of the items will be backordered.', ($backorderQty * 1))
                            );
                        } else {
                            $result->setMessage(
                               $_helper->__('"%s" is not available in the requested quantity. %s of the items will be backordered.', $this->getProductName(), ($backorderQty * 1))
                            );
                        }
                    } elseif (Mage::app()->getStore()->isAdmin()) {
                        $result->setMessage(
                            $_helper->__('The requested quantity for "%s" is not available.', $this->getProductName())
                        );
                    }
                }
            } else {
                if (!$this->getIsChildItem()) {
                    $this->setOrderedItems($qty + (int)$this->getOrderedItems());
                }
            }
            // no return intentionally
        }

        return $result;
    }
}
