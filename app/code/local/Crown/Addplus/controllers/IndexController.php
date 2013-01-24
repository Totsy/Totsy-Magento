<?php
/**
 * @category    Crown
 * @package     Crown_Addproduct
 * @author      Crown Partners
 */

class Crown_Addplus_IndexController extends Mage_Core_Controller_Front_Action {
    public function indexAction() {
        // set the qty and the TotsyPLUS product ID by pulling it from store_config
        $params = array(
            'product' => Mage::getStoreConfig('Crown_Club/clubgeneral/club_product_id'),
            'qty' => 1,
        );
        $cart = Mage::getSingleton('checkout/cart');
        $product = new Mage_Catalog_Model_Product();
        $product->load($params['product']);
        $cart->addProduct($product, $params);
        $cart->save();
        Mage::getSingleton('checkout/session')->setCartWasUpdated(true);

        $this->_redirect('checkout/cart');
    }
}