<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ryan.street
 * Date: 10/29/12
 * Time: 4:54 PM
 * To change this template use File | Settings | File Templates.
 */
class Harapartners_HpCheckout_Block_Checkout_Shipping extends Mage_Tax_Block_Checkout_Shipping {

    /**
     * Get shipping amount exclude tax
     *
     * @return float
     */
    public function getShippingExcludeTax()
    {
        $splitCart = Mage::getSingleton('checkout/session')->getSplitCartFlag();

        if($splitCart) {
            $extra = Mage::getStoreConfig('checkout/cart/split_cart_price');
        }
        else {
            $extra = '0.0000';
        }
        return $extra + $this->getTotal()->getAddress()->getShippingAmount();
    }
}