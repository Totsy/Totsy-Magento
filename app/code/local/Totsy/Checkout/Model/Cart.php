<?php
/**
 * Crown Partners LLC
 *
 * @category    Totsy
 * @package     Totsy_Checkout_Model
 * @author: chris.davidowski
 */

class Totsy_Checkout_Model_Cart extends Mage_Checkout_Model_Cart
{
    /**
     * Initialize cart quote state to be able use it on cart page
     */
    public function init()
    {
        parent::init();

        $fulfillmentTypes = array();
        foreach($this->getQuote()->getAllItems() as $item) {
            if($item->getParentItemId()) {
                continue;
            }
            $product = Mage::getModel ( 'catalog/product' )->load ( $item->getProductId () );
            if($product->getIsVirtual() && $product->getFulfillmentType() !== 'nominal') {
                $fulfillmentType = 'virtual';
            } else {
                $fulfillmentType = $product->getFulfillmentType();
            }
            $fulfillmentTypes [$fulfillmentType] [] = $item->getId ();
        }
        if(count($fulfillmentTypes) > 1) {
            $this->getQuote()->setIsMultiShipping(1);
        } else {
            $this->getQuote()->setIsMultiShipping(0);
        }

        return $this;
    }
}