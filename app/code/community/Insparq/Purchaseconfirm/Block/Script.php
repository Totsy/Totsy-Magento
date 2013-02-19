<?php
/**
 * Insparq Inc.
 *
 * @category   Insparq
 * @package    Insparq_Purchaseconfirm
 * @copyright  Copyright (c) 2012 Insparq Inc. (http://www.insparq.com)
 */
class Insparq_Purchaseconfirm_Block_Script extends Mage_Core_Block_Template {

  /**
   * product widget helper
   */
  protected $pwHelper;


  /**
   * Block Initialization
   *
   * @return Insparq_Purchaseconfirm_Block_Script | void
   */
  protected function _construct() {

    $this->pwHelper = $this->helper( 'purchaseconfirm' );

    if ( $this->pwHelper->isWidgetEnabled() ) {
      $this->setTemplate( 'insparq_purchaseconfirm/script.phtml' );
      parent::_construct();
    }

    return;

  }

  /**
   * Returns the publisher ID set.
   */
  public function getPublisherId() {
    /**
     * Return Publisher ID
     */
    return $this->pwHelper->getPublisherId();
  }


  /**
   * Returns the data needed for the querystring, in the API call.
   */
  public function getDataQueryString() {
     $queryString = array();

     $sessOrder = Mage::getSingleton('checkout/session')->getLastOrderId();
     $order = ($sessOrder) ?
       Mage::getModel('sales/order')->load($sessOrder) :
       Mage::getModel('sales/order')->load($this->getLastOrderId());

     if ($orderData = $order->getData()) {
        $productNames = array();
        $productIDs = array();
        $productPrices = array();
        $buyerID = ($order->getCustomerId() === NULL) ? '' : $order->getCustomerId();
        $couponCode = $order->getCouponCode();
        $items = $order->getAllItems();
        foreach ($items as $itemId => $item){
           $productNames[] = $item->getName();
           $productPrices[] = $item->getPrice();
           $productIDs[] = $item->getProductId();
        }

       /**
        * Set Page Type
        */
       array_push($queryString, "pageType=purcon");

       /**
        * Set Beacon
        */
       array_push($queryString, "beacon=1");

       /**
        * Set Order Total Items
        */
       array_push($queryString, 'cartItemCount='.urlencode($orderData['total_item_count']));

       /**
        * Set Order Value
        */
       array_push($queryString, 'cartValue='.urlencode($orderData['grand_total']));

       /**
        * Set Order Currency Code
        */
       array_push($queryString, 'currencyCode='.urlencode($orderData['order_currency_code']));

       /**
        * Set Purchaser ID
        */
       array_push($queryString, 'buyerID='.urlencode($buyerID));

       /**
        * Set Purchaser Email
        */
       array_push($queryString, 'buyerEmail='.urlencode($orderData['customer_email']));

       /**
        * Set Purchaser Name
        */
       array_push($queryString, 'buyerName='.urlencode($orderData['customer_firstname'].' '.$orderData['customer_lastname']));

       /**
        * Set Purchased Product IDs
        */
       array_push($queryString, 'productIDs='.urlencode(implode('|',$productIDs)));
       /**
        * Set Purchased Product Names
        */
       array_push($queryString, 'productNames='.urlencode(implode('|',$productNames)));
       /**
        * Set Purchased Product Names
        */
       array_push($queryString, 'productPrices='.urlencode(implode('|',$productPrices)));
       /**
        * Set Coupon code
        */
       array_push($queryString, 'couponsUsed='.urlencode($couponCode));

    }

     return implode("&", $queryString);
  }
}
?>
