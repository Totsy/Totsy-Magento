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
    public function containsCanceledItems()
    {
        foreach ($this->getItemsCollection() as $item) {
            if ($item->getQtyCanceled()) {
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

        // 201207 - CJD - Added in logic so that you can cancel an order that
        // has had the items canceled individually
        $allCanceled = true;
        foreach ($this->getAllItems() as $item) {
            if (!$item->getParentItemId() &&
                ($item->getQtyCanceled() != $item->getQtyOrdered())
            ) {
                $allCanceled = false;
                break;
            }
        }

        if ($allInvoiced  && !$allCanceled) {
            return false;
        }

        $state = $this->getState();
        if ($this->isCanceled() ||
            $state === self::STATE_COMPLETE ||
            $state === self::STATE_CLOSED
        ) {
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
        
        if ($this->getStatus() == 'payment_failed') {
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
        $mailer->setTemplateParams(
            array(
                'order'        => $this,
                'billing'      => $this->getBillingAddress(),
                'payment_html' => $paymentBlockHtml
            )
        );
        $mailer->send();

        $this->setEmailSent(true);
        $this->_getResource()->saveAttribute($this, 'email_sent');

        // also send an e-mail for each virtual product that is part of this
        // order, with the appropriate redemption code
        foreach ($this->getAllItems() as $orderItem) {
            $product = Mage::getModel('catalog/product')
                ->load($orderItem->getProduct()->getId());

            if ($product->getIsVirtual()) {

                // Is it a discount vault If so, skip it.
                if($product->getOneTimePurchase())
                    continue;

                $shortDescription = $product->getShortDescription();
                $description = $product->getDescription();
                $title = $product->getName();

                $options = $orderItem->getProductOptions();
                $temp = explode("\n", $options['options'][0]['value']);
                $virtualProductCode = $temp[0];

                //picking the right template by the id set in the admin
                // (transactional emails section)
                $templateId =  Mage::getModel('core/email_template')
                    ->loadByCode('_trans_Virtual_Product_Redemption')->getId();

                $store = Mage::app()->getStore();
                $email = $this->getCustomer()->getEmail();

                if ($this->getStatus() != 'payment_failed') {
                    Mage::getModel('core/email_template')->sendTransactional(
                        $templateId,
                        "sales",
                        $email,
                        NULL,
                        array(
                            "virtual_product_code" => $virtualProductCode,
                            "order" => $this,
                            "store" => $store,
                            "title" => $title,
                            "description" => $description,
                            "short_description" => $shortDescription
                        )
                    );
                }

                //Mage::register('coupon_code_email_sent',true);
            }
        }
        return $this;
    }

    /**
     * Calculate the amount of profit on this order.
     *
     * @return float
     */
    public function getProfit()
    {
        $profit = 0;
        foreach ($this->getAllVisibleItems() as $item) {
            $productId = $item->getProductId();
            $product = Mage::getModel('catalog/product')->load($productId);
            $priceDiff = $product->getPrice() - $product->getSpecialPrice();
            $profit += $priceDiff * $item->getQtyToInvoice();
        }

        $this->setData('profit', (float) $profit);
        return $this->getData('profit');
    }
    
    /**
     * Check order state before saving
     */
    protected function _checkState()
    {
        if (!$this->getId()) {
            return $this;
        }

        $userNotification = $this->hasCustomerNoteNotify() ? $this->getCustomerNoteNotify() : null;

        if (!$this->isCanceled()
            && !$this->canUnhold()
            && !$this->canInvoice()
            && !$this->canShip()) {
            if (0 == $this->getBaseGrandTotal() || $this->canCreditmemo()) {
                if ($this->getState() !== self::STATE_COMPLETE && $this->getState() !== 'updated') {
                    $this->_setState(self::STATE_COMPLETE, true, '', $userNotification);
                }
            }
            /**
             * Order can be closed just in case when we have refunded amount.
             * In case of "0" grand total order checking ForcedCanCreditmemo flag
             */
            elseif (floatval($this->getTotalRefunded()) || (!$this->getTotalRefunded()
                && $this->hasForcedCanCreditmemo())
            ) {
                if ($this->getState() !== self::STATE_CLOSED) {
                    $this->_setState(self::STATE_CLOSED, true, '', $userNotification);
                }
            }
        }

        if ($this->getState() == self::STATE_NEW && $this->getIsInProcess()) {
            $this->setState(self::STATE_PROCESSING, true, '', $userNotification);
        }
        return $this;
    }
    
    public function isVirtual()
    {
        $isVirtual = true;
        $countItems = 0;
        foreach ($this->getItemsCollection() as $_item) {

            if ($_item->isDeleted() || $_item->getParentItemId()) {
                continue;
            }
            $countItems ++;
            if (!$_item->getProduct()->getIsVirtual()) {
                $isVirtual = false;
            }
        }
        return $countItems == 0 ? false : $isVirtual;
    }

    /**
     * Check all child order items to ensure they are either in READY or
     * CANCELLED status.
     *
     * @return bool
     */
    public function isReadyForFulfillment()
    {
        // locate all order items (those that belong to the same order)
        // including itself
        $orderItems = Mage::getModel('fulfillmentfactory/itemqueue')->getCollection();
        $orderItems->addFieldToFilter('order_id', $this->getId());

        // inspect each order item's status
        $orderReady = true;
        foreach ($orderItems as $item) {
            if (Harapartners_Fulfillmentfactory_Model_Itemqueue::STATUS_READY != $item->getStatus() &&
                Harapartners_Fulfillmentfactory_Model_Itemqueue::STATUS_CANCELLED != $item->getStatus()
            ) {
                $orderReady = false;
            }
        }

        return $orderReady;
    }
    
    /**
     * Cancel order
     *
     * @return Mage_Sales_Model_Order
     */
    public function cancel()
    {
        if ($this->canCancel()) {
            $this->getPayment()->cancel();
            $this->registerCancellation();

            //Sync Item Stock Status with Item Stock
            foreach($this->getItemsCollection() as $item) {
                $indexerStock = Mage::getModel('cataloginventory/stock_status');
                $indexerStock->updateStatus($item->getProductId());
                //Make Sure that parent product status stay 1
                $configurableProductModel = Mage::getModel('catalog/product_type_configurable');
                $parentIds = $configurableProductModel->getParentIdsByChild($item->getProductId());
                if ($parentIds) {
                    foreach ($parentIds as $parentId) {
                        $stockStatus = Mage::getModel('cataloginventory/stock_status')->load($parentId,'product_id');
                        $stockStatus->setData('stock_status','1')
                            ->save();
                    }
                }
            }

            Mage::dispatchEvent('order_cancel_after', array('order' => $this));
        }

        return $this;
    }

    public function hasFulfillmentType($fulfillmentType) {
        foreach($this->getAllItems() as $item) {
            if($item->getParentItemId()) {
                continue;
            }
            $product = Mage::getModel ( 'catalog/product' )->load ( $item->getProductId () );
            if($product->getIsVirtual() && $fulfillmentType == 'virtual') {
                return true;
            } elseif($product->getFulfillmentType() == $fulfillmentType) {
                return true;
            }
        }
        return false;
    }

    /**
     * Retrieve label of order status
     *
     * @return string
     */
    public function getStatusLabel()
    {
        if($this->getStatus() == 'complete' && $this->getIsVirtual()) {
            return 'Emailed';
        } else {
            return $this->getConfig()->getStatusLabel($this->getStatus());
        }
    }
}
