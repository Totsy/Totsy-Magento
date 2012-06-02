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

class Harapartners_Rushcheckout_Model_Observer {
    
    const CUSTOMER_VALIDATION_CHECK_URL = 'customer/revalidate/index';
    const CUSTOMER_REVALIDATE_TIMER_LIMIT = 3600;
    const DEFAULT_COLLECTION_SIZE_LIMIT = 10; //in case the collection is rather large
    
    /**
     * Double validation check HP
     */
    public function checkLastValidation($session){
        $session->setData('revalidate_before_auth_url', Mage::helper('core/url')->getCurrentUrl());
        $lastValidationTime = $session->getData('CUSTOMER_LAST_VALIDATION_TIME');
        $timeDiff = strtotime(now()) - strtotime($lastValidationTime);
        
        if ( $timeDiff >= self::CUSTOMER_REVALIDATE_TIMER_LIMIT ) {
            $session->setCheckLastValidationFlag(false);   
			$url = Mage::getBaseUrl() . self::CUSTOMER_VALIDATION_CHECK_URL;
			Mage::app()->getFrontController()->getResponse()->setRedirect($url);
        } else {
            $session->setCheckLastValidationFlag(true);
        }
    }
    
    public function customerRevalidate($observer){    
    
   		//$test = Mage::getStoreConfig('config/rushcheckout_timer/limit_timer');
    
        $session = $observer->getCustomerSession();    
        if ( $session->isLoggedIn() && !!$session->getData('CUSTOMER_LAST_VALIDATION_TIME') ){
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
            
            $controllerName = Mage::app()->getRequest()->getControllerName();
            $moduleName = Mage::app()->getRequest()->getModuleName();
            $actionName = Mage::app()->getRequest()->getActionName();
            
            foreach ( $moduleArrary as $module => $controllers ){
                if ( $moduleName == $module 
                        && in_array($controllerName, $controllers)
                        && $actionName != 'forgotpassword'
                        && $actionName != 'forgotpasswordpost'
                        && $actionName != 'logoutAction'
                        && $actionName != 'logoutSuccess'
                        && $actionName != 'resetPasswordAction'
                        && $actionName != 'resetPasswordPost' ){
                    $this->checkLastValidation($session);
                }
            }
        }
    }
    
    public function updateReservationByQuoteItem($observer){
        $quoteItem = $observer->getEvent()->getItem();
        Mage::helper('rushcheckout/reservation')->updateReservationByQuoteItem($quoteItem);
        return $this;
    }
    
    public function updateReservationByStockItem($observer){
        $stockItem = $observer->getEvent()->getItem();
        Mage::helper('rushcheckout/reservation')->updateReservationByStockItem($stockItem);
        return $this;
    }
    
    // ===== Cronjob related ===== //
    public function cleanExpiredQuotes() {
        $itemCount = 0;
        $itemQtyCount = 0;
        $cartCount = 0;
        $lifetimes = Mage::getConfig()->getStoresConfigByPath('config/rushcheckout_timer/limit_timer');
        
        //Separate for different stores
        foreach ($lifetimes as $storeId => $lifetime) {
        	//Optimization for large collections, since sales/quote is a flat table, this is relatively efficient
        	//Entity ID offset is safer than LIMIT clause offset, this would safe guard against endless loop
        	//LIMIT clause offset is vulnerable if other processes also update the table
        	$entityIdOffset = 0;
        	do{
	            $quoteCollection = Mage::getModel('sales/quote')->getCollection();
	            $quoteCollection->addFieldToFilter('store_id', $storeId)
						->addFieldToFilter('entity_id', array('gt' => $entityIdOffset))
	            		->addFieldToFilter('items_count', array('gt' => 0))
	            		->addFieldToFilter('updated_at', array('to' => date("Y-m-d H:i:s", time() - $lifetime)));
            	$quoteCollection->getSelect()
            			->limit(self::DEFAULT_COLLECTION_SIZE_LIMIT)
            			->order('entity_id ASC');
	            foreach($quoteCollection as $quote){
	                foreach($quote->getAllItems() as $item){
	                    $item->isDeleted(true);
	                    $item->delete(); //Cart reservation logic: item qty is release back to available by observer
	                    
	                    //Avoid double counting parent/children items
	                    if(!$item->getParentItemId()){
	                    	$itemQtyCount += $item->getQty(); 
	                    	$itemCount++;
	                    }
	                    
	                    unset($item);
	                }
	                $entityIdOffset = $quote->getId();
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
            }while($collectionSize >= self::DEFAULT_COLLECTION_SIZE_LIMIT);
        }

        Mage::log(
            sprintf('Pruned %d line items (%d total qty) out of %d carts.', $itemCount, $itemQtyCount, $cartCount),
            Zend_Log::INFO,
            'cart_cleaner.log'
        );

        return $this;
    }
}
