<?php
class Harapartners_HpCheckout_Model_Observer {

    public function checkForSplit($observer) {

        if($observer->getEvent()->getControllerAction()->getFullActionName() == 'checkout_cart_updatePost') {
            $splitCart = $observer->getEvent()->getControllerAction()->getRequest()->getParam('splitCart', FALSE);
            $cartFlag = Mage::getSingleton('checkout/session')->getSplitCartFlag();

            if($splitCart && !$cartFlag) {
                Mage::getSingleton('checkout/session')->setSplitCartFlag('1');
            }
            else {
                Mage::getSingleton('checkout/session')->unsSplitCartFlag();
            }

        }
    }

}