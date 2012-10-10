<?php
/**
 * @category    Totsy
 * @package     Totsy_Customer_Block_Address_Book
 * @author      Tom Royer <troyer@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */
 
class Totsy_Customer_Block_Address_Book extends Mage_Customer_Block_Address_Book
{
    public function isAddressLinkWithPaymentProfile($addressId)
    {
        $linked = false;
        if($addressId) {
            $profile = Mage::getModel('paymentfactory/profile')->load($addressId, 'address_id');
            if($profile->getId()) {
                $linked = true;
            }
        }
        return $linked;
    }

    public function isAddressLinkWithPaymentProfileHidden($addressId)
    {
        $linked = false;
        $profile = Mage::getModel('paymentfactory/profile')->load($addressId, 'address_id');
        if($profile->getId() && $profile->getIsDefault()) {
            $linked = true;
        }
        return $linked;
    }
}
