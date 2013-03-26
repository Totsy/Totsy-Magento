<?php
/**
 * Insparq Inc.
 *
 * @category   Insparq
 * @package    Insparq_Purchaseconfirm
 * @copyright  Copyright (c) 2012 Insparq Inc. (http://www.insparq.com)
 */
class Insparq_Purchaseconfirm_Helper_Data extends Mage_Core_Helper_Abstract {

  /**
   * Checks whether the widget is enabled for frontend in system config
   *
   * @return bool
   */
  public function isWidgetEnabled() {
    return Mage::getStoreConfig( 'purchaseconfirm/settings/enabled' );
  }

  /**
   * Returns the publisher Id currently entered in the configuration, or the host
   */
  public function getPublisherId() {
    return trim( Mage::getStoreConfig( 'insparqall/settings/pubid' ) ) === "" ?
      $_SERVER[ 'HTTP_HOST' ] :
      Mage::getStoreConfig( 'insparqall/settings/pubid' );
  }

}
?>
