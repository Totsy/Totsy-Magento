<?php
/**
 * Insparq Inc.
 *
 * @category   Insparq
 * @package    Insparq_Product
 * @copyright  Copyright (c) 2012 Insparq Inc. (http://www.insparq.com)
 */
class Insparq_Product_Block_Script extends Mage_Core_Block_Template {

  /**
   * product widget helper
   */
  protected $pwHelper;


  /**
   * Block Initialization
   *
   * @return Insparq_Product_Block_Script | void
   */
  protected function _construct() {

    $this->pwHelper = $this->helper( 'product' );

    if ( $this->pwHelper->isWidgetEnabled() && $this->pwHelper->getCurrentProduct() ) {
      $this->setTemplate( 'insparq_product/script.phtml' );
      parent::_construct();
    }

    return;

  }

  /**
   * Returns an associative array with the key as the data-type-value, and the value its content.
   *
   * @return array[ dataKey ]  --> example: array( "product-name" => "Product Name" )
   */
  public function getDataTags() {

    $dataTags = array();

    if ( $product = $this->pwHelper->getCurrentProduct() ) {

      /**
       * Set Publisher Id
       */
      $dataTags[ 'publisher-id' ] = $this->pwHelper->getPublisherId();

      /**
       * Set Page Mode
       */
      $dataTags[ 'page-mode' ] = "normal";

      /**
       * Set Page Type
       */
      $dataTags[ 'page-type' ] = "product";

      /**
       * Set Product Name
       */
      $dataTags[ 'product-name' ] = $this->escapeValue( $this->pwHelper->getProductName() );
      /**
       * Set Product ID
       */
      $dataTags[ 'product-id' ] = $this->escapeValue( $this->pwHelper->getProductId() );
      /**
       * Set Product Long Description
       */
      if ( $description = $this->escapeValue( $product->getDescription() ) ) {
         if ( strlen( $description ) > 5 ) {
            $dataTags[ 'product-description-full' ] = $description;
         }
      }

      /**
       * Set Product Short Description
       */
      if ( $shortDescription = $this->escapeValue( $product->getShortDescription() ) ) {
         if ( strlen( $shortDescription ) > 5 ) {
            $dataTags[ 'product-description-short' ] = $shortDescription;
         }
      }

      /**
       * Set Product Url
       */
      $dataTags[ 'product-url' ] = $this->helper( 'core/url' )->getCurrentUrl();

      /**
       * Set Product Price Value
       */
      $dataTags[ 'product-price-value' ] = $product->getFinalPrice();

      /**
       * Set Product Currency Code
       */
      $dataTags[ 'product-price-currency' ] = 'USD';

      /**
       * Set Product Image URL
       */
      $dataTags[ 'product-image-url' ] = $this->helper( 'catalog/image' )->init( $product, 'thumbnail' );

      /**
       * Set Product Buy Button (HTML ID)
       */
      $dataTags[ 'product-buy-action-selector' ] = '#addToCart';


      /**
       * Check for Cart Items
       */
      $_cart = Mage::getSingleton('checkout/cart');
      if( $_cart->getSummaryQty() > 0 ) {

        /**
         * Set Total Cart Value
         */
        $dataTags[ 'user-cart-items-value-total' ] = round( $_cart->getQuote()->getGrandTotal(), 2 );

        /**
         * Set Total Cart Items
         */
        $dataTags[ 'user-cart-items-quantity' ] = $_cart->getSummaryQty();

      }

      /**
       * Check for Customer Information
       */
      $_cHelper = $this->helper( 'customer' );
      if( $_customer = $_cHelper->getCustomer() ) {
         /**
          * Set Name
          */
         $dataTags[ 'user-name' ] = $this->escapeValue( $_customer->getFirstname() . " " . $_customer->getLastname() );

         /**
          * Set Email
          */
         $dataTags[ 'user-email' ] = $_customer->getEmail();
      }

    }

    return $dataTags;

  }


  /**
   * Returns the value escaped of tags, and string length, spaces, single quotes, etc.
   *
   * @return string value
   */
  public function escapeValue( $value ) {

    $value = stripslashes(strip_tags( $value ));
    $value = preg_replace( array( '/\s+/', '/\'/' ), array( ' ', '\\\'' ), $value );
    return ( strlen( $value ) > 254 ) ? trim( substr( $value, 0, 250 ) ) . '...' : $value;

  }

}
?>
