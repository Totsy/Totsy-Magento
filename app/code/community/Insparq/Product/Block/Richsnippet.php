<?php
/**
 * Insparq Inc.
 *
 * @category   Insparq
 * @package    Insparq_Product
 * @copyright  Copyright (c) 2012 Insparq Inc. (http://www.insparq.com)
 */
class Insparq_Product_Block_Richsnippet extends Mage_Core_Block_Template {

  /**
   * product widget helper
   */
  protected $pwHelper;


  /**
   * Block Initialization
   *
   * @return Insparq_Product_Block_Richsnippet | void
   */
  protected function _construct() {

    $this->pwHelper = $this->helper( 'product' );

    if ( $this->pwHelper->isRichSnippetEnabled() && $this->pwHelper->getCurrentProduct() ) {
      $this->setTemplate( 'insparq_product/richsnippet.phtml' );
      parent::_construct();
    }

    return;

  }

  /**
   * Returns an associative array with the key as the product key, and the value its content, or false
   *
   * @return array[ productKey ] | false  --> example: array( "name" => "Product Name" )
   */
  public function getProductData() {

    if ( $product = $this->pwHelper->getCurrentProduct() ) {

      $productData = array();

      /**
       * Set Product Name
       */
      $productData[ 'name' ] = $this->escapeValue( $this->pwHelper->getProductName() );

      /**
       * Set Description
       */
      $productData[ 'description' ] = $this->escapeValue( $this->pwHelper->getProductDescription() );

      /**
       * Set Url
       */
      $ogTags[ 'url' ] = $this->helper( 'core/url' )->getCurrentUrl();

      /**
       * Set Image
       */
      $ogTags[ 'image' ] = $this->helper( 'catalog/image' )->init( $product, 'thumbnail' );

      /**
       * Set Type
       */
      $ogTags[ 'type' ] = "product";

      /**
       * Return productData
       */
      return $productData;

    }

    /**
     * Return false
     */
    return false;

  }

  /**
   * Returns an associative array with the key as the offer key and the value its content, or false
   *
   * @return array[ offerKey ] | false  --> example: array( "price" => "10.00" )
   */
  public function getOfferData() {

     if ( $product = $this->pwHelper->getCurrentProduct() ) {

      $offerData = array();

      /**
       * Set Price
       */
      $offerData[ 'price' ] = round( $product->getFinalPrice(), 2, PHP_ROUND_HALF_UP );

      /**
       * Set Price Valid Until
       *
       * ONLY WHEN PRICE != FINAL PRICE
       */
      if ( $product->getPrice() !== $product->getFinalPrice() ) {
        if ( $specialDate = $product->getSpecialToDate() ) {
          $parseDate = explode( " ", strval( $specialDate ) );
          $offerData[ 'priceValidUntil' ] = $parseDate[ 0 ];
        }
      }

      /**
       * Set Availability
       */
      $availability = ( $product->isSaleable() ) ? "in_stock" : "out_of_stock";
      $offerData[ 'availability' ] = $availability;

      /**
       * Set Quantity
       */
      if ( $qty = $product->getQty() ) {
        $offerData[ 'quantity' ] = $qty;
      }

      /**
       * Set Identifier
       */
      if ( $sku = $product->getSku() ) {
        $offerData[ 'identifier' ] = $sku;
      }

      /**
       * Return Offer Data
       */
      return $offerData;

    }

    /**
     * Return false
     */
    return false;

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
