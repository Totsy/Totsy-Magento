<?php
/**
 * Harapartners
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Harapartners License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.Harapartners.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@Harapartners.com so we can send you a copy immediately.
 *
 */
class Harapartners_Childrenlist_Block_Form_Edit extends Mage_Core_Block_Template
{
    protected $_subscription = null;

    public function getCustomer()
    {
        return Mage::getSingleton('customer/session')->getCustomer();
    }

    public function getChildInfo()
    {
        $childId = Mage::app()->getRequest()->getParam('childid', false);
        $child = Mage::getModel('childrenlist/child')->load($childId);
        return $child; 
    }

    public function isCustomerOwnTheChild()
    {
        return ($this->getChildInfo()->getCustomerId() == $this->getCustomer()->getId());
    }
    
    public function isEditMode(){
        $childId = Mage::app()->getRequest()->getParam('childid', false);
        return !empty($childId);
    }
    
    
    public function getAccountUrl()
    {
        return Mage::getUrl('childrenlist/index/edit');
    }

    /**
     * Get back url in account dashboard
     *
     * This method is copypasted in:
     * Mage_Wishlist_Block_Customer_Wishlist  - because of strange inheritance
     * Mage_Customer_Block_Address_Book - because of secure url
     *
     * @return string
     */
    public function getBackUrl()
    {
        // the RefererUrl must be set in appropriate controller
        if ($this->getRefererUrl()) {
            return $this->getRefererUrl();
        }
        return $this->getUrl('customer/account/');
    }    
}
