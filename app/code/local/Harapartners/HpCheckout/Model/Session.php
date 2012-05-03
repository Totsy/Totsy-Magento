<?php
/*
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User Software Agreement (EULA).
 * It is also available through the world-wide-web at this URL:
 * http://www.harapartners.com/license [^]
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to eula@harapartners.com so we can send you a copy immediately.
 *
 */
class Harapartners_HpCheckout_Model_Session extends Mage_Checkout_Model_Session {
    
    protected $_inpageSessionQuote = null;
    //protected $_inpageSessionStaticQuote = null; //Please make sure static quote NEVER contains any quote items
    
    public function getQuote() {
        if ( Mage::registry ( 'is_inpagecheckout' ) ) {
            if (! $this->_inpageSessionQuote) {
                $inpageSessionQuoteId = $this->getInpageSessionQuoteId ();
                $this->_inpageSessionQuote = Mage::getModel ( 'sales/quote' )->load ( $inpageSessionQuoteId );
                
                foreach ( $this->_inpageSessionQuote->getAllItems () as $item ) {
                    $this->_inpageSessionQuote->removeItem ( $item->getId () );
                }
                $this->_inpageSessionQuote->save ();
                //Save guarantees the ID for the quote
                $this->setInpageSessionQuoteId ( $this->_inpageSessionQuote->getId () );
            
            }
            return $this->_inpageSessionQuote;
        } else {
            return parent::getQuote ();
        }
    }
    
//    public function updateStaticQuote($dynamicQuote) {
//        $staticQuote = $this->_getStaticQuote ();
//        $newStaticQuote = clone $dynamicQuote;
//        $newStaticQuote->setId ( $staticQuote->getId () );
//        foreach ( $newStaticQuote->getAllItems () as $item ) {
//            $newStaticQuote->removeItem ( $item->getId () );
//        }
//        $newStaticQuote->save ();
//    }
    
//    protected function _getStaticQuote(){
//        //This is the static quote, one per customer, saved after every use, it's a container for addresses, payment methods, coupons, etc.
//        if( ! $this->_inpageSessionStaticQuote ){
//            //Try to load from session first
//            $inpageSessionStaticQuoteId = $this->getInpageSessionStaticQuoteId();
//            $this->_inpageSessionStaticQuote = Mage::getModel( 'sales/quote' )->load( $inpageSessionStaticQuoteId );
//            
//            //force a ID for this quote
//            if( ! $this->_inpageSessionStaticQuote->getId() ){
//                $this->_inpageSessionStaticQuote->save();
//            }
//            $this->setInpageSessionStaticQuoteId( $this->_inpageSessionStaticQuote->getId() );
//        }
//        return $this->_inpageSessionStaticQuote;
//    }

}