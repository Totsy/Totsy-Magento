<?php
/**
 * @copyright   Copyright (c) 2009-11 Amasty
 */
class Amasty_Promo_Model_Observer
{
    protected $_isHandled = array();
    /**
     * Process sales rule form creation
     * @param   Varien_Event_Observer $observer
     */
    public function handleFormCreation($observer)
    {
        $actionsSelect = $observer->getForm()->getElement('simple_action');
        if ($actionsSelect){
            $vals = $actionsSelect->getValues();
            $vals[] = array(
                'value' => 'ampromo_items',
                'label' => Mage::helper('ampromo')->__('Auto add promo items with products'),
                
            );
            $vals[] = array(
                'value' => 'ampromo_cart',
                'label' => Mage::helper('ampromo')->__('Auto add promo items for the whole cart'),
                
            );
            $vals = $vals;
            $actionsSelect->setValues($vals);
            $actionsSelect->setOnchange('ampromo_hide()');
            
            $fldSet = $observer->getForm()->getElement('action_fieldset');
            $fldSet->addField('promo_sku', 'text', array(
                'name'     => 'promo_sku',
                'label' => Mage::helper('ampromo')->__('Promo Items'),
                'note'  => Mage::helper('ampromo')->__('Comma separated list of the SKUs'),
                ),
                'discount_amount'
            );            
        }
        
        return $this; 
    }
    
    /**
     * Process quote item validation and discount calculation
     * @param   Varien_Event_Observer $observer
     */
    public function handleValidation($observer) 
    {
        $rule = $observer->getEvent()->getRule();
        if (!in_array($rule->getSimpleAction(), array('ampromo_items','ampromo_cart'))){
            return $this;
        }
       
        if (isset($this->_isHandled[$rule->getId()])){
            return $this;
        }
 
        $this->_isHandled[$rule->getId()] = true;
        
        $promoSku = $rule->getPromoSku();
        if (!$promoSku){
            return $this;     
        }  
        
        $quote = $observer->getEvent()->getQuote();
        
        $qty = $this->_getFreeItemsQty($rule, $quote);
        if (!$qty){
            //@todo  - add new field for label table
            // and show message like "Add 2 more products to get free items"
            return $this;         
        }
        
        $session = Mage::getSingleton('checkout/session');
        if ($session->getAmpromoId() != $quote->getId()){
            $session->setAmpromoDeletedItems(null);
            $session->setAmpromoMessages(null);
            $session->setAmpromoId($quote->getId());
        }
            
		$promoSku = explode(',', $promoSku);
		foreach ($promoSku as $sku){
		    $sku = trim($sku);
		    if (!$sku){
		        continue;
		    }
            $product = $this->_loadProduct($sku, $qty);
            if (!$product){
                continue;
            }
            if ($this->_addProductToQuote($quote, $product, $qty, $rule)){
            	$message = $rule->getStoreLabel(Mage::app()->getStore());
            	if ($message){
            		$this->_showMessage($message, false);	
            	}
            }
		}

        return $this;
    }
    
    public function initFreeItems($observer) 
    { 
        $this->_isHandled = array();
        
        $quote = $observer->getQuote();
        if (!$quote) 
            return $this;
            
        foreach ($quote->getItemsCollection() as $item) {
            if (!$item){
                continue;
            }
                
            if (!$item->getOptionByCode('ampromo_rule')){
                continue;
            }

            Mage::unregister('ampromo_del');
            Mage::register('ampromo_del', $item->getId());
            
            $item->isDeleted(true);
            $item->setData('qty_to_add', '0.0000');
            $quote->removeItem($item->getId());
        }
        return $this;
    }
    
    public function removeFreeItems($observer) 
    {
        $item = $observer->getEvent()->getQuoteItem();
        if ($item->getId() != Mage::registry('ampromo_del')){
            $allowDelete = Mage::getStoreConfig('ampromo/general/allow_delete');    
            if ($allowDelete){
                $arr = Mage::getSingleton('checkout/session')->getAmpromoDeletedItems();
                if (!is_array($arr)){
                    $arr = array();
                }
                $arr[$item->getSku()] = true;
                Mage::getSingleton('checkout/session')->setAmpromoDeletedItems($arr);
                Mage::getSingleton('checkout/session')->setAmpromoId($item->getQuote()->getId());
            }
        }
    } 
    
    public function updateFreeItems($observer) 
    { 
        $info = $observer->getInfo();
        $quote = $observer->getCart()->getQuote();
        foreach (array_keys($info) as $itemId) {
            $item = $quote->getItemById($itemId);
            if (!$item) 
                continue;
                
            if (!$item->getOptionByCode('ampromo_rule')) 
                continue;
                
            if (empty($info[$itemId]))
                continue;
                
            $info[$itemId]['remove'] = true;
        }
        
        return $this;
    }  
    
    // find qty 
    // (for the whole cart it is $rule->getDiscountQty()
    // for items it is (qty * (number of matched non-free items) / step)
    protected function _getFreeItemsQty($rule, $quote)
    {  
        $amount = max(1, $rule->getDiscountAmount());
        $qty    = 0;
        if ('ampromo_cart' == $rule->getSimpleAction()){
            $qty = $amount;
        }
        else {
            $step = max(1, $rule->getDiscountStep());
            foreach ($quote->getItemsCollection() as $item) {
                if (!$item) 
                    continue;
                    
                if ($item->getOptionByCode('ampromo_rule')) 
                    continue;
                    
                if (!$rule->getActions()->validate($item)) {
                    continue;
                }
                
               $qty = $qty + $item->getQty();
            } 
            
            $qty = floor($qty / $step) * $amount; 
            $max = $rule->getDiscountQty();
            if ($max){
                $qty = min($max, $qty);
            }
        }
        return $qty;        
    }  
    
    protected function _loadProduct($sku, $qty)
    {
        // don't add already removed items
        $arr = Mage::getSingleton('checkout/session')->getAmpromoDeletedItems();
        if (!is_array($arr)){
            $arr = array();
        }
        if (isset($arr[$sku])){
        	if (Mage::app()->getRequest()->getControllerName() == 'cart'){
        		$message  = Mage::helper('ampromo')->__(
                	'Your cart has deleted free items. <a href="%s">Restore them</a>?', Mage::getUrl('ampromo/cart/restore')
            	);
            	$this->_showMessage($message, false, true);
        	}    	
            return false;
        }
       
	    $product = Mage::getModel('catalog/product')->reset();
	    $product->load($product->getIdBySku($sku)); // we have to load each product individually
	    
	    if (!$product->getId()){
            $this->_showMessage(Mage::helper('ampromo')->__(
                'We apologise, but there is no promo item with the SKU `%s` in the catalog', $sku
            ));	
	        return false;
	    }
	    if (Mage_Catalog_Model_Product_Status::STATUS_ENABLED != $product->getStatus()){
            $this->_showMessage(Mage::helper('ampromo')->__(
                'We apologise, but promo item with the SKU `%s` is not available', $sku
            ));		        
	        return false;
	    }
        $hasQty  = $product->getStockItem()->checkQty($qty);
        $inStock = $product->getStockItem()->getIsInStock();
        if (!$inStock || !$hasQty){
            $this->_showMessage(Mage::helper('ampromo')->__(
                'We apologise, but there are no %d item(s) with the SKU `%s` in the stock', $qty, $sku
            ));
            return false;
        }
        return $product;        
    }    
    
    protected function _addProductToQuote($quote, $product, $qty, $rule)
    {
        try {
            if ('multishipping' === Mage::app()->getRequest()->getControllerName()){
                return false;
            }
            
            $product->addCustomOption('ampromo_rule', $rule->getId());
            
            $item  = $quote->getItemByProduct($product);
            if ($item) {  
                return false;       
            }
            
            // we need this line in case the initial quote was virtual
            if (!$product->isVirtual()){
                $quote->getBillingAddress()->setTotalAmount('subtotal', 0);
            }
            
            $item = $quote->addProduct($product, $qty);
            // required custom options or configurable product
            if (!is_object($item)){ 
                throw new Exception($item);   
            }
            
            $item->setCustomPrice(0); 
            $item->setOriginalCustomPrice(0); 
            
            $prefix = Mage::getStoreConfig('ampromo/general/prefix');
            if ($prefix){
                $item->setName($prefix . ' ' . $item->getName());
            }
            
            $customMessage = Mage::getStoreConfig('ampromo/general/message');
            if ($customMessage){
                $item->setMessage($customMessage);
            }            
        }
        catch (Exception $e){
            $this->_showMessage(Mage::helper('ampromo')->__(
                'We apologise, but there is an error while adding free items to the cart: %s', $e->getMessage()
            ));            
            return false;   
            
        }
        return true;        
    }
       
    protected function _showMessage($message, $isError = true, $showEachTime=false) 
    { 
        // show on cart page only
        $all = Mage::getSingleton('checkout/session')->getMessages(false)->toString();
        if (false !== strpos($all, $message))
            return;
            
        if ($isError && isset($_GET['debug'])){
            Mage::getSingleton('checkout/session')->addError($message);
        }
        else {
            $arr = Mage::getSingleton('checkout/session')->getAmpromoMessages();
            if (!is_array($arr)){
                $arr = array();
            }
            if (!in_array($message, $arr) || $showEachTime){
                Mage::getSingleton('checkout/session')->addNotice($message);
                $arr[] = $message;
                Mage::getSingleton('checkout/session')->setAmpromoMessages($arr);
            }
        }
    } 
}