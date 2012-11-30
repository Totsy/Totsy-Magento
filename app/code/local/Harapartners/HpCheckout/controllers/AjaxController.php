<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ryan.street
 * Date: 11/8/12
 * Time: 12:48 PM
 * To change this template use File | Settings | File Templates.
 */
class Harapartners_HpCheckout_AjaxController extends Mage_Core_Controller_Front_Action {

    public function splitCartAction() {

        $flag = $this->getRequest()->getParam('id');

        Mage::getSingleton('checkout/session')->setSplitCartFlag($flag);
        echo 'true';
    }

}