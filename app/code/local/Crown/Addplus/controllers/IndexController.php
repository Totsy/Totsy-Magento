<?php
/**
 * @category    Crown
 * @package     Crown_Addproduct
 * @author      Crown Partners
 */

class Crown_Addplus_IndexController extends Mage_Core_Controller_Front_Action {
    public function indexAction() {
        $customer = Mage::helper('customer')->getCustomer();

        if(Mage::helper('crownclub')->isClubMember($customer)) {
            Mage::getSingleton('core/session')->addError('<div style="width:100%;text-align:center;">You are already a TotsyPLUS member.</div>');
            $this->_redirect('plus/dashboard');
            return;
        }
        $cart = Mage::helper('checkout/cart')->getCart();
        if($cart->getItemsCount() >= 1) {
            Mage::getSingleton('core/session')->addError('To purchase a TotsyPLUS membership, it must be the only item in your cart. To proceed please remove other items from your shopping cart.');
            $this->_redirect('plus');
            return;
        }

//        $this->loadLayout();
//        $this->renderLayout();
    }

    public function agreeAction() {
        $customer = Mage::helper('customer')->getCustomer();
        if(Mage::helper('crownclub')->isClubMember($customer)) {
            Mage::getSingleton('core/session')->addError('<div style="width:100%;text-align:center;">You are already a TotsyPLUS member.</div>');
            $this->_redirect('plus/dashboard');
        } else {
            $cart = Mage::helper('checkout/cart')->getCart();
            if($cart->getItemsCount() >= 1) {
                Mage::getSingleton('core/session')->addError('To purchase a TotsyPLUS membership, it must be the only item in your cart. To proceed please remove other items from your shopping cart.');
                $this->_redirect('plus');
                return;
            } else {
                $product_id = Mage::getStoreConfig('Crown_Club/clubgeneral/club_product_id');
                $return_url = '/checkout/cart/';
                $this->_redirect('checkout/cart/add?product='.$product_id.'&qty=1&hideSuccess=1&return_url='.$return_url);
            }
        }
    }

    public function disagreeAction() {
        $this->_redirect('/event');
    }
}