<?php
/**
 * Insparq Inc.
 *
 * @category   Insparq
 * @package    Insparq_Product
 * @copyright  Copyright (c) 2012 Insparq Inc. (http://www.insparq.com)
 */
class Insparq_Product_Block_Ogtags extends Mage_Core_Block_Template {

  /**
   * product widget helper
   */
  protected $pwHelper;

  /**
   * Block Initialization
   *
   * @return Insparq_Product_Block_Ogtags | void
   */
  protected function _construct() {

    $this->pwHelper = $this->helper( 'product' );

    if ( $this->pwHelper->isOpenGraphEnabled() && $this->pwHelper->getCurrentProduct() ) {
      $this->setTemplate( 'insparq_product/ogtags.phtml' );
      parent::_construct();
    }

    return;

  }

  /**
   * Returns an associative array with the key as the open graph type, and the value its content. 
   *
   * @return array[ ogkey ]  --> example: array( "title" => "OG Title" )
   */
  public function getOGTags() {

    $ogTags = array();

    if ( $product = $this->pwHelper->getCurrentProduct() ) {

      /**
       * Set Title
       */
      $ogTags[ 'title' ] = $this->escapeValue( $this->pwHelper->getProductName() );

      /**
       * Set Description
       */
      if ( $shortDescription = $this->escapeValue( $product->getShortDescription() ) ) {
         if ( strlen( $shortDescription ) > 5 ) {
            $ogTags[ 'description' ] = $shortDescription;
         } else if ( $description = $this->escapeValue( $product->getDescription() ) ) {
            if ( strlen( $description ) > 5 ) {
               $ogTags[ 'description' ] = $description;
            }
         }
      }

      /**
       * Set Url
       */
      $ogTags[ 'url' ] = $this->helper( 'core/url' )->getCurrentUrl();

      /**
       * Set Image
       */
      $ogTags[ 'image' ] = $this->helper( 'catalog/image' )->init( $product, 'thumbnail' );

    }

    return $ogTags;

  }


  /**
   * Returns the value escaped of tags, and string length, spaces, double quotes, etc.
   *
   * @return string value
   */
  public function escapeValue( $value ) {

    $value = strip_tags( $value );
    $value = preg_replace( array( '/\s+/', '/"/' ), array( ' ', '\\"' ), $value );
    return ( strlen( $value ) > 254 ) ? trim( substr( $value, 0, 250 ) ) . '...' : $value;

  }

}
?>
