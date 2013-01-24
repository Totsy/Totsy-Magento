<?php
/**
 * Insparq Inc.
 *
 * @category   Insparq
 * @package    Insparq_Product
 * @copyright  Copyright (c) 2012 Insparq Inc. (http://www.insparq.com)
 */
class Insparq_Product_Block_Holder extends Mage_Core_Block_Template {

  /**
   * product widget helper
   */
  protected $pwHelper;


  /**
   * Block Initialization
   *
   * @return Insparq_Product_Block_Holder | void
   */
  protected function _construct() {

    $this->pwHelper = $this->helper( 'product' );

    if ( $this->pwHelper->isWidgetEnabled() && $this->pwHelper->isDefaultPlaceHolderEnabled() && $this->pwHelper->getCurrentProduct() ) {
      $this->setTemplate( 'insparq_product/holder.phtml' );
      parent::_construct();
    }

    return;

  }

}
?>
