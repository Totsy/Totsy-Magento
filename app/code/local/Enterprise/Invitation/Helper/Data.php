<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Enterprise
 * @package     Enterprise_Invitation
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

/**
 * Invitation data helper
 *
 * @category   Enterprise
 * @package    Enterprise_Invitation
 */
class Enterprise_Invitation_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_isRegistrationAllowed = null;

    /**
     * Return max Invitation amount per send by config.
     * Depricated. Config model 'enterprise_invitation/config' should be used directly.
     *
     * @return int
     */
    public function getMaxInvitationsPerSend()
    {
        return Mage::getSingleton('enterprise_invitation/config')->getMaxInvitationsPerSend();
    }

    /**
     * Return config value for required cutomer registration by invitation
     * Depricated. Config model 'enterprise_invitation/config' should be used directly.
     *
     * @return boolean
     */
    public function getInvitationRequired()
    {
        return Mage::getSingleton('enterprise_invitation/config')->getInvitationRequired();
    }


    /**
     * Return config value for use same group as inviter
     * Depricated. Config model 'enterprise_invitation/config' should be used directly.
     *
     * @return boolean
     */
    public function getUseInviterGroup()
    {
        return Mage::getSingleton('enterprise_invitation/config')->getUseInviterGroup();
    }

    /**
     * Check whether invitations allow to set custom message
     * Depricated. Config model 'enterprise_invitation/config' should be used directly.
     *
     * @return bool
     */
    public function isInvitationMessageAllowed()
    {
        return Mage::getSingleton('enterprise_invitation/config')->isInvitationMessageAllowed();
    }

    /**
     * Return text for invetation status
     *
     * @return Enterprise_Invitation_Model_Invitation $invitation
     * @return string
     */
    public function getInvitationStatusText($invitation)
    {
        return Mage::getSingleton('enterprise_invitation/source_invitation_status')->getOptionText($invitation->getStatus());
    }

    /**
     * Return invitation url
     *
     * @param Enterprise_Invitation_Model_Invitation $invitation
     * @return string
     */
    public function getInvitationUrl($invitation)
    {
        return Mage::getModel('core/url')->setStore($invitation->getStoreId())
            ->getUrl('enterprise_invitation/customer_account/create', array(
                'invitation' => Mage::helper('core')->urlEncode($invitation->getInvitationCode()),
                '_store_to_url' => true,
                '_nosid' => true
            ));
    }

    /**
     * Return account dashboard invitation url
     *
     * @return string
     */
    public function getCustomerInvitationUrl()
    {
        return $this->_getUrl('enterprise_invitation/');
    }

    /**
     * Return invitation send form url
     *
     * @return string
     */
    public function getCustomerInvitationFormUrl()
    {
        return $this->_getUrl('enterprise_invitation/index/send');
    }

    /**
     * Checks is allowed registration in invitation controller
     *
     * @param boolean $isAllowed
     * @return boolean
     */
    public function isRegistrationAllowed($isAllowed = null)
    {
        if ($isAllowed === null && $this->_isRegistrationAllowed === null) {
            $result = Mage::helper('customer')->isRegistrationAllowed();
            if ($this->_isRegistrationAllowed === null) {
                $this->_isRegistrationAllowed = $result;
            }
        } elseif ($isAllowed !== null) {
            $this->_isRegistrationAllowed = $isAllowed;
        }

        return $this->_isRegistrationAllowed;
    }

    /**
     * Retrieve configuration for availability of invitations
     * Depricated. Config model 'enterprise_invitation/config' should be used directly.
     *
     * @return boolean
     */
    public function isEnabled()
    {
        return Mage::getSingleton('enterprise_invitation/config')->isEnabled();
    }
    
    public function getGenericInvitationLink() {
        $inviter = Mage::getSingleton( 'customer/session' );
        $inviterId = 0;
        $inviterName = '';         
        if( $inviter && $inviter->getCustomer() && $inviter->getCustomer()->getId() ) {
            $inviterId = $inviter->getCustomer()->getId();
            $inviterName = $inviter->getCustomer()->getFirstname() . $inviter->getCustomer()->getLastname();
            $inviterEmail = $inviter->getCustomer()->getEmail();
            //if (empty($inviterName)) {
               $inviterEmailArray = explode( "@", $inviterEmail );
               $inviterName = preg_replace("/[^a-zA-Z0-9\s]/", "", $inviterEmailArray[0]);
            //}
        }
        $customerInfo = array(
                'id' => $inviterId
        );
        $genericInvitationKey = base64_encode( Mage::getModel( 'core/encryption' )->encrypt( serialize( $customerInfo ) ) );
        //return Mage::getUrl( 'invitation/customer_account/genericcreate/invitation/' . $genericInvitationKey );
        //return Mage::getUrl( 'invitation/customer_account/genericcreate/invitation/' . strtolower( $inviterName ) . '_' . $inviterId );
        return Mage::getUrl( 'invite/' . strtolower( $inviterName ) . '_' . $inviterId, array('_nosid' => true) );
    }
    
    public function getGenericProductInvitationLink() {
        $inviter = Mage::getSingleton( 'customer/session' );
        $inviterId = 0;
        $inviterName = '';         
        $productId = Mage::registry('current_product')->getId();
        if( $inviter && $inviter->getCustomer() && $inviter->getCustomer()->getId() ) {
            $inviterId = $inviter->getCustomer()->getId();
            $inviterName = $inviter->getCustomer()->getFirstname() . $inviter->getCustomer()->getLastname();
        }
        /*$customerInfo = array(
                'id' => $inviterId
        );*/
        #$genericInvitationKey = base64_encode( Mage::getModel( 'core/encryption' )->encrypt( serialize( $customerInfo ) ) );
        //return Mage::getUrl( 'invitation/customer_account/genericcreate/invitation/' . $genericInvitationKey );
        return Mage::getUrl( 'invitation/customer_account/genericcreate/invitation/' . strtolower( $inviterName ) . '_' . $inviterId . '/product/' . $productId);
        //return Mage::getUrl( 'invite/' . strtolower( $inviterName ) . '_' . $inviterId . '/product/' ) . $productId ;
    }    
    
    public function getInviterId( $genericInvitationKey ) {
        //return unserialize( Mage::getModel( 'core/encryption' )->decrypt( base64_decode( $genericInvitationKey ) ) );
        $inviterArray = explode( '_', $genericInvitationKey );
        return $inviterArray[ count( $inviterArray ) - 1 ];
    }
}
