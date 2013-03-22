<?php

class Harapartners_Paymentfactory_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_PAYMENT_FAILED_TEMPLATE      = 'checkout/payment_failed/template';
    const XML_PATH_PAYMENT_FAILED_IDENTITY      = 'checkout/payment_failed/identity';
    const XML_PATH_PAYMENT_FAILED_RECIPIENT     = 'checkout/payment_failed/reciever';
    const XML_PATH_PAYMENT_FAILED_COPY_TO       = 'checkout/payment_failed/copy_to';

    /**
     * Sends Payment Failed email
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     */
    public function sendPaymentFailedEmail($payment)
    {
        $order      = $payment->getOrder();
        $storeId    = $order->getStoreId();

        /* @var $mailer Mage_Core_Model_Email_Template_Mailer */
        $mailer     = Mage::getModel('core/email_template_mailer');
        /* @var $emailInfo Mage_Core_Model_Email_Info */
        $emailInfo  = Mage::getModel('core/email_info');
        $emailInfo->addTo($order->getCustomerEmail(), $order->getCustomerName());
        $copyTo     = $this->getPaymentFailedEmailCopyTo($storeId);
        foreach ($copyTo as $email) {
            $emailInfo->addBcc($email);
        }
        $mailer->addEmailInfo($emailInfo);

        // Set all required params and send emails
        $mailer->setSender($this->getPaymentFailedEmailIdentity($storeId));
        $mailer->setStoreId($storeId);
        $mailer->setTemplateId($this->getPaymentFailedEmailTemplate($storeId));
        $mailer->setTemplateParams(array(
            'order'     => $order,
            'edit_link' => $this->_getUrl('sales/order_billing/edit', array(
                'order_id'  => $order->getId(),
                '_secure'   => true,
            )),
        ));

        $mailer->send();
    }

    /**
     * Returns Payment Failed Email Template
     *
     * @param null|int|string|Mage_Core_Model_Store $store
     * @return string
     */
    public function getPaymentFailedEmailTemplate($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_PAYMENT_FAILED_TEMPLATE, $store);
    }

    /**
     * Returns Payment Failed Email Sender
     *
     * @param null|int|string|Mage_Core_Model_Store $store
     * @return string
     */
    public function getPaymentFailedEmailIdentity($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_PAYMENT_FAILED_IDENTITY, $store);
    }

    /**
     * Returns list of Payment Failed Email copy to
     *
     * @param null|int|string|Mage_Core_Model_Store $store
     * @return array
     */
    public function getPaymentFailedEmailCopyTo($store = null)
    {
        $emails     = array();
        $identity   = Mage::getStoreConfig(self::XML_PATH_PAYMENT_FAILED_RECIPIENT, $store);
        $emails[]   = trim(Mage::getStoreConfig(sprintf('trans_email/ident_%s/name', $identity), $store));

        $copyTo     = Mage::getStoreConfig(self::XML_PATH_PAYMENT_FAILED_COPY_TO, $store);
        if (!empty($copyTo)) {
            foreach (explode(',', $copyTo) as $email) {
                if (!empty($email)) {
                    $emails[] = trim($email);
                }
            }
        }

        return $emails;
    }
}
