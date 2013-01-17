<?php
/**
 * Insparq Inc.
 *
 * @category   Insparq
 * @package    Insparq_Product
 * @copyright  Copyright (c) 2012 Insparq Inc. (http://www.insparq.com)
 */
class Insparq_Product_Helper_Data extends Mage_Core_Helper_Abstract {

  /**
   * Checks whether widget is enabled for frontend in system config
   *
   * @return bool
   */
  public function isWidgetEnabled() {
    return Mage::getStoreConfig( 'product/settings/enabled' );
  }

  /**
   * Checks whether the default widget holder is enabled in system config
   *
   * @return bool
   */
  public function isDefaultPlaceHolderEnabled() {
    return Mage::getStoreConfig( 'product/settings/automaticplacement' );
  }

  /**
   * Checks whether the Open Graph (OG) tags toggle is enabled in system config
   *
   * @return bool
   */
  public function isOpenGraphEnabled() {
    return Mage::getStoreConfig( 'product/embeds/ogtags' );
  }

  /**
   * Checks whether the rich snippets toggle is enabled in system config
   *
   * @return bool
   */
  public function isRichSnippetEnabled() {
    return Mage::getStoreConfig( 'product/embeds/richsnippets' );
  }

  /**
   * Returns product currently being displayed, or false
   *
   * @return obj | false
   */
  public function getCurrentProduct() {
    return Mage::registry( 'product' );
  }

  /**
   * Returns the publisher Id currently entered in the configuration, or the host
   */
  public function getPublisherId() {
    return trim( Mage::getStoreConfig( 'insparqall/settings/pubid' ) ) === "" ?
      $_SERVER[ 'HTTP_HOST' ] :
      Mage::getStoreConfig( 'insparqall/settings/pubid' );
  }

  /**
   * Returns the product name
   */
  public function getProductName() {
    return $this->getCurrentProduct() ?
      $this->getCurrentProduct()->getName() :
      'No Product';
  }

   /**
    * Returns the product ID
    */
   public function getProductId() {
     return $this->getCurrentProduct() ?
       $this->getCurrentProduct()->getId() :
       '';
   }

  /**
   * Returns the product description
   */
  public function getProductDescription() {
    return $this->getCurrentProduct() ?
      $this->getCurrentProduct()->getShortDescription() :
      'No Product';
  }

}
?>
