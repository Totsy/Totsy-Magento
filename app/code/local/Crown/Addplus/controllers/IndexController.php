<?php
/**
 * @category    Crown
 * @package     Crown_Addproduct
 * @author      Crown Partners
 */

class Crown_Addplus_IndexController extends Mage_Core_Controller_Front_Action {
    public function indexAction() {
        if(Mage::helper('checkout/cart')->getCart()->getItemsCount() >= 1) {
            Mage::getSingleton('core/session')->addError('In order to sign up for TotsyPLUS, please remove all existing items from your cart.');
        } else {
            // set the qty and the TotsyPLUS product ID by pulling it from store_config
            $params = array(
                'qty' => 1
            );
            $cart = Mage::getSingleton('checkout/cart');
            $product = new Mage_Catalog_Model_Product();
            $product->load(Mage::getStoreConfig('Crown_Club/clubgeneral/club_product_id'));
            $cart->addProduct($product, $params);
            $cart->save();
            Mage::getSingleton('checkout/session')->setCartWasUpdated(true);
        }
        $this->_redirect('checkout/cart');
    }
}