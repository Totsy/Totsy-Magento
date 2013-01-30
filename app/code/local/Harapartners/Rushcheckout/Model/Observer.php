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

class Harapartners_Rushcheckout_Model_Observer
{
    const CUSTOMER_VALIDATION_CHECK_URL = 'customer/revalidate/index';
    const CUSTOMER_REVALIDATE_TIMER_LIMIT = 3600;
    const DEFAULT_COLLECTION_SIZE_LIMIT = 100;

    /**
     * Double validation check HP
     */
    public function checkLastValidation($session)
    {
        if (!$this->isValid($session)) {
            $this->setValidationRedirect($session);
            $session->setCheckLastValidationFlag(false);
            Mage::app()->getFrontController()
                ->getResponse()
                ->setRedirect($this->getValidationUrl());
        } else {
            $session->setCheckLastValidationFlag(true);
        }
    }

    public function isValid($session)
    {
        $lastValidation = $session->getData('CUSTOMER_LAST_VALIDATION_TIME');
        if (-1 === $lastValidation) {
            return false;
        }

        $timeDiff = strtotime(now()) - strtotime($lastValidation);
        if ($timeDiff >= self::CUSTOMER_REVALIDATE_TIMER_LIMIT) {
            return false;
        }

        return true;
    }

    public function setValidationRedirect($session, $url=null)
    {
        $session_url = $session->getData('revalidate_before_auth_url');
        if (!empty($session_url)){
            return;
        }
        unset($session_url);

        if (is_null($url)) {
            $url = Mage::helper('core/url')->getCurrentUrl();
        }

        $session->setData('revalidate_before_auth_url', $url);
    }

    public function getValidationUrl()
    {
        return Mage::getBaseUrl() . self::CUSTOMER_VALIDATION_CHECK_URL;
    }

    public function customerRevalidate($observer)
    {
        $session = $observer->getCustomerSession();    
        if ($session->isLoggedIn() &&
            $session->getData('CUSTOMER_LAST_VALIDATION_TIME')
        ) {
            $moduleArrary = array(
                'customer' => array(
                    'account',
                    'address',
                    'review'
                ),
                'checkout' => array(
                    'index',
                    'cart',
                    'multishipping',
                    'onepage'
                ),
                'hpcheckout' => array(
                    'checkout',
                )
            );

            $excludedActions = array(
                'forgotpassword',
                'forgotpasswordpost',
                'logout',
                'logoutsuccess',
                'resetpassword',
                'resetpasswordpost'
            );

            $controllerName = Mage::app()->getRequest()->getControllerName();
            $moduleName = Mage::app()->getRequest()->getModuleName();
            $actionName = Mage::app()->getRequest()->getActionName();

            foreach ($moduleArrary as $module => $controllers) {
                if (strcasecmp($moduleName, $module) == 0 &&
                    in_array(strtolower($controllerName), $controllers) &&
                    !in_array(strtolower($actionName), $excludedActions)
                ) {
                    $this->checkLastValidation($session);
                }
            }
        }
    }

    public function updateReservationByQuoteItem($observer)
    {
        $quoteItem = $observer->getEvent()->getItem();
        Mage::helper('rushcheckout/reservation')
            ->updateReservationByQuoteItem($quoteItem);
        return $this;
    }

    public function updateReservationByStockItem($observer)
    {
        $stockItem = $observer->getEvent()->getItem();
        Mage::helper('rushcheckout/reservation')->updateReservationByStockItem($stockItem);
        return $this;
    }

    // ===== Cronjob related ===== //
    public function cleanExpiredQuotes()
    {
        $itemCount = 0;
        $itemQtyCount = 0;
        $cartCount = 0;
        $lifetimes = Mage::getConfig()->getStoresConfigByPath(
            'config/rushcheckout_timer/limit_timer'
        );

        //Separate for different stores
        foreach ($lifetimes as $storeId => $lifetime) {
            //Optimization for large collections, since sales/quote is a flat table, this is relatively efficient
            //LIMIT clause offset is vulnerable if other processes also update the table
            do {
                $quoteCollection = Mage::getModel('sales/quote')
                    ->getCollection();
                $quoteCollection->addFieldToFilter('store_id', $storeId)
                        ->addFieldToFilter('is_active', 1)
                        ->addFieldToFilter('items_count', array('gt' => 0))
                        ->addFieldToFilter('updated_at', array('to' => date("Y-m-d H:i:s", time() - $lifetime)));
                $quoteCollection->getSelect()
                        ->limit(self::DEFAULT_COLLECTION_SIZE_LIMIT);
                foreach ($quoteCollection as $quote) {
                    foreach ($quote->getAllItems() as $item) {
                        //Cart reservation logic: item qty is release back to available by observer
                        $item->isDeleted(true);
                        $item->delete();

                        //Avoid double counting parent/children items
                        if (!$item->getParentItemId()) {
                            $itemQtyCount += $item->getQty();
                            $itemCount++;
                        }

                        unset($item);
                    }

                    $quote->setData('items_count', 0)
                        ->setData('items_qty', 0)
                        ->setData('grand_total', 0)
                        ->setData('base_grand_total', 0)
                        ->setData('subtotal', 0)
                        ->setData('base_subtotal', 0)
                        ->setData('subtotal_with_discount', 0)
                        ->setData('base_subtotal_with_discount', 0)
                        ->save();

                    $cartCount++;
                    unset($quote);
                }

                $collectionSize = count($quoteCollection);
                unset($quoteCollection);
            } while ($collectionSize >= self::DEFAULT_COLLECTION_SIZE_LIMIT);
        }

        Mage::log(
            sprintf(
                'Pruned %d line items (%d total qty) out of %d carts.',
                $itemCount,
                $itemQtyCount,
                $cartCount
            ),
            Zend_Log::INFO,
            'cart_cleaner.log'
        );

        return $this;
    }
}
