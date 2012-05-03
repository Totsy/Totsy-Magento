<?php
/*
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User Software Agreement (EULA).
 * It is also available through the world-wide-web at this URL:
 * http://www.harapartners.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to eula@harapartners.com so we can send you a copy immediately.
 * 
 */

class Harapartners_MobileApi_Model_CreditCard
{
    protected $_fields = array(
              'type'                => 'cc_type',
              'number'            => 'cc_number',
              'expire_year'        => 'cc_exp_year',
              'expire_month'        => 'cc_exp_month',
              'cardholder_name'    =>    'cc_owner',
            'cvv'                =>    'cc_cid',
              
          );
    public function prepareCcInfo($ccInfo)
      {
          $cc = new Varien_Object();
          $order = new Varien_Object();
          $billing = new Varien_Object();
          
          $billing->setFirstname('Song');
          $billing->setLastname('Gao');
          $billing->setCompany('HP');
          $billing->setStreet('136 W15th Street');
          $billing->setCity('New York');
          $billing->setRegion('New York');
          $billing->setCountry('US');
          $billing->setTelephone('1234567890');
          $billing->setPostcode('10018');
          
          $order->setBillingAddress($billing);
          $order->setCustomerEmail('s.gao@harapartners.com');
          $order->setBaseCurrencyCode('USD');
          
          $cc->setOrder($order);
          
          foreach($this->_fields as $key => $value){
              if(isset($ccInfo[$key])){
                  $cc->setData($value, $ccInfo[$key]);
              }
              
          }
          $result = Mage::getModel('paymentfactory/tokenize')->create($cc);
          $cc;
          return $cc;
      }    
}