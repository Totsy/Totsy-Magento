<?php
/**
 * @category    Totsy
 * @package     Totsy_Sales_Model
 * @author      Tom Royer <troyer@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

class Totsy_Sales_Model_Order extends Mage_Sales_Model_Order
{
    const STATUS_BATCH_CANCEL_CSR_REVIEW = 'batch_cancel_csr_review';
   /**
     * This method will check the order for items that have been canceled
     *
     * @return bool
     */
    public function containsCanceledItems() {
        foreach($this->getItemsCollection() as $item) {
            if($item->getQtyCanceled()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Retrieve order cancel availability
     *
     * @return bool
     */
    public function canCancel()
    {
        if ($this->canUnhold()) {  // $this->isPaymentReview()
            return false;
        }

        $allInvoiced = true;
        foreach ($this->getAllItems() as $item) {
            if ($item->getQtyToInvoice()) {
                $allInvoiced = false;
                break;
            }
        }

        // 201207 - CJD - Added in logic so that you can cancel an order that has had the items canceled individually
        $allCanceled = true;
        foreach($this->getAllItems() as $item) {
            if(!$item->getParentItemId() && ($item->getQtyCanceled() != $item->getQtyOrdered())) {
                $allCanceled = false;
                break;
            }
        }

        if ($allInvoiced  && !$allCanceled) {
            return false;
        }

        $state = $this->getState();
        if ($this->isCanceled() || $state === self::STATE_COMPLETE || $state === self::STATE_CLOSED) {
            return false;
        }

        if ($this->getActionFlag(self::ACTION_FLAG_CANCEL) === false) {
            return false;
        }

        /**
         * Use only state for availability detect
         */
        /*foreach ($this->getAllItems() as $item) {
            if ($item->getQtyToCancel()>0) {
                return true;
            }
        }
        return false;*/
        return true;
    }

    /**
     * Retrieve order total due value
     *
     * @return float
     */
    public function getTotalDue()
    {
        $total = $this->getGrandTotal()-$this->getTotalPaid()-$this->getTotalCanceled();
        $total = Mage::app()->getStore($this->getStoreId())->roundPrice($total);
        return max($total, 0);
    }

    /**
     * Retrieve order total due value
     *
     * @return float
     */
    public function getBaseTotalDue()
    {
        $total = $this->getBaseGrandTotal()-$this->getBaseTotalPaid()-$this->getBaseTotalCanceled();
        $total = Mage::app()->getStore($this->getStoreId())->roundPrice($total);
        return max($total, 0);
    }
    
    /**
     * Send email with order data
     *
     * @return Mage_Sales_Model_Order
     */
    public function sendNewOrderEmail()
    {
        $storeId = $this->getStore()->getId();

        if (!Mage::helper('sales')->canSendNewOrderEmail($storeId)) {
            return $this;
        }
        
        if($this->getStatus() == 'payment_failed') {
            return false;
        }
        // Get the destination email addresses to send copies to
        $copyTo = $this->_getEmails(self::XML_PATH_EMAIL_COPY_TO);
        $copyMethod = Mage::getStoreConfig(self::XML_PATH_EMAIL_COPY_METHOD, $storeId);

        // Start store emulation process
        $appEmulation = Mage::getSingleton('core/app_emulation');
        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);

        try {
            // Retrieve specified view block from appropriate design package (depends on emulated store)
            $paymentBlock = Mage::helper('payment')->getInfoBlock($this->getPayment())
                ->setIsSecureMode(true);
            $paymentBlock->getMethod()->setStore($storeId);
            $paymentBlockHtml = $paymentBlock->toHtml();
        } catch (Exception $exception) {
            // Stop store emulation process
            $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
            throw $exception;
        }

        // Stop store emulation process
        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);

        // Retrieve corresponding email template id and customer name
        if ($this->getCustomerIsGuest()) {
            $templateId = Mage::getStoreConfig(self::XML_PATH_EMAIL_GUEST_TEMPLATE, $storeId);
            $customerName = $this->getBillingAddress()->getName();
        } else {
            $templateId = Mage::getStoreConfig(self::XML_PATH_EMAIL_TEMPLATE, $storeId);
            $customerName = $this->getCustomerName();
        }

        $mailer = Mage::getModel('core/email_template_mailer');
        $emailInfo = Mage::getModel('core/email_info');
        $emailInfo->addTo($this->getCustomerEmail(), $customerName);
        if ($copyTo && $copyMethod == 'bcc') {
            // Add bcc to customer email
            foreach ($copyTo as $email) {
                $emailInfo->addBcc($email);
            }
        }
        $mailer->addEmailInfo($emailInfo);

        // Email copies are sent as separated emails if their copy method is 'copy'
        if ($copyTo && $copyMethod == 'copy') {
            foreach ($copyTo as $email) {
                $emailInfo = Mage::getModel('core/email_info');
                $emailInfo->addTo($email);
                $mailer->addEmailInfo($emailInfo);
            }
        }

        // Set all required params and send emails
        $mailer->setSender(Mage::getStoreConfig(self::XML_PATH_EMAIL_IDENTITY, $storeId));
        $mailer->setStoreId($storeId);
        $mailer->setTemplateId($templateId);
        $mailer->setTemplateParams(array(
                'order'        => $this,
                'billing'      => $this->getBillingAddress(),
                'payment_html' => $paymentBlockHtml
            )
        );
        $mailer->send();

        $this->setEmailSent(true);
        $this->_getResource()->saveAttribute($this, 'email_sent');

        return $this;
    }
}