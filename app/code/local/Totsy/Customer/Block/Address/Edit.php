<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class Totsy_Customer_Block_Address_Edit extends Mage_Customer_Block_Address_Edit
{
     protected $_type;
     
     protected function _prepareLayout()
     {
          parent::_prepareLayout();
          $this->_type = $this->getRequest()->getParam('type');
          if('billing' != $this->_type && 'shipping' != $this->_type && isset($this->_type))
          {
               $this->_address->setData(array());
                $this->_address->setPrefix($this->getCustomer()->getPrefix())
                ->setFirstname($this->getCustomer()->getFirstname())
                ->setMiddlename($this->getCustomer()->getMiddlename())
                ->setLastname($this->getCustomer()->getLastname())
                ->setSuffix($this->getCustomer()->getSuffix());
                
                if ($headBlock = $this->getLayout()->getBlock('head')) {
                    $headBlock->setTitle($this->getTitle());
                }
          }
      }
      
      public function isShowDefaultBilling()
      {
          if(!$this->isDefaultShipping() && $this->isDefaultBilling()) {
               return true;     
           }elseif($this->isDefaultShipping() && $this->isDefaultBilling()){
                return 'billing' == $this->_type;
           }else{
               return false;
           }
      }
      
       public function isShowDefaultshipping()
      {
           if($this->isDefaultShipping() && !$this->isDefaultBilling()) {
               return true;      
           }elseif($this->isDefaultShipping() && $this->isDefaultBilling()){
                return 'shipping' == $this->_type;
           }else{
               return false;
           }
      }   
}
?>
