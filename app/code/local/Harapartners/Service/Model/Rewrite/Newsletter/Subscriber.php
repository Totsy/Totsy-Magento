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

class Harapartners_Service_Model_Rewrite_Newsletter_Subscriber extends Mage_Newsletter_Model_Subscriber
{

    /**
     * Sends out confirmation success email
     *
     * @return Mage_Newsletter_Model_Subscriber
     */
    public function sendConfirmationSuccessEmail()
    {
        $newsletterList = "";
        
        if( Mage::app()->getStore()->getCode()=="default" || Mage::app()->getStore()->getCode()=="mobile" ){
            $newsletterList = "registered";
        } else {
            $newsletterList = "Mamasource";
        }
        
        //Harapartners, Edward, Disable ConfirmationSuccessEmail for first register
        $isNewRegister = Mage::registry('new_account');
        if (isset($isNewRegister)){
            return $this;
        }
        //Harapartners, Edward, Disable ConfirmationSuccessEmail for first register
        
        if ($this->getImportMode()) {
            return $this;
        }

        if(!Mage::getStoreConfig(self::XML_PATH_SUCCESS_EMAIL_TEMPLATE)
           || !Mage::getStoreConfig(self::XML_PATH_SUCCESS_EMAIL_IDENTITY)
        ) {
            return $this;
        }

        $translate = Mage::getSingleton('core/translate');
        /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(false);

        $email = Mage::getModel('core/email_template');

        $email->sendTransactional(
            Mage::getStoreConfig(self::XML_PATH_SUCCESS_EMAIL_TEMPLATE),
            Mage::getStoreConfig(self::XML_PATH_SUCCESS_EMAIL_IDENTITY),
            $this->getEmail(),
            $this->getName(),
            array('subscriber'=>$this)
        );
        
        $sailthru = Mage::getSingleton('emailfactory/sailthruconfig')->getHandle();
        $sailthru->setEmail($this->getEmail(), Array(), Array( $newsletterList=>1 ));

        $translate->setTranslateInline(true);

        return $this;
    }
}