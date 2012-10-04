<?php
/**
 * TinyBrick Commercial Extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the TinyBrick Commercial Extension License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://store.delorumcommerce.com/license/commercial-extension
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@tinybrick.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this package to newer
 * versions in the future. 
 *
 * @category   TinyBrick
 * @package    TinyBrick_OrderEdit
 * @copyright  Copyright (c) 2010 TinyBrick Inc. LLC
 * @license    http://store.delorumcommerce.com/license/commercial-extension
 */
class TinyBrick_OrderEdit_Model_Order extends Mage_Sales_Model_Order
{
	protected $_addresses = null;
	protected $_nitems;

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

    public function removeItem($itemId)
    {
		$this->getItemById($itemId)->delete();
        return $this;
    }
	
	public function getEditItemsCollection($filterByTypes = array(), $nonChildrenOnly = false)
    {
        if (is_null($this->_nitems)) {
            $this->_nitems = Mage::getResourceModel('sales/order_item_collection')
                ->setOrderFilter($this->getId());
                
            if ($filterByTypes) {
                $this->_nitems->filterByTypes($filterByTypes);
            }
            if ($nonChildrenOnly) {
                $this->_nitems->filterByParent();
            }

            if ($this->getId()) {
                foreach ($this->_nitems as $item) {
                    $item->setOrder($this);
                }
            }
        }
        return $this->_nitems;
    }
    
    public function getEditAllVisibleItems()
    {
        $items = array();
        foreach ($this->getEditItemsCollection() as $item) {
            if (!$item->isDeleted() && !$item->getParentItemId()) {
                $items[] =  $item;
            }
        }
        return $items;
    }


	public function getCustomerTaxClassId()
    {
        /*
        * tax class can vary at any time. so instead of using the value from session, we need to retrieve from db everytime
        * to get the correct tax class
        */
        //if (!$this->getData('customer_group_id') && !$this->getData('customer_tax_class_id')) {
            $classId = Mage::getModel('customer/group')->getTaxClassId($this->getCustomerGroupId());
            $this->setCustomerTaxClassId($classId);
        //}
        return $this->getData('customer_tax_class_id');
    }

    /**
     * Add product to quote
     *
     * return error message if product type instance can't prepare product
     *
     * @param   mixed $product
     * @return  Mage_Sales_Model_Quote_Item || string
     */
    public function addProduct(Mage_Catalog_Model_Product $product, $request=null)
    {

        if ($request === null) {
            $request = 1;
        }
        if (is_numeric($request)) {
            $request = new Varien_Object(array('qty'=>$request));
        }
        if (!($request instanceof Varien_Object)) {
            Mage::throwException(Mage::helper('sales')->__('Invalid request for adding product to quote'));
        }

        $cartCandidates = $product->getTypeInstance(true)
            ->prepareForCart($request, $product);

        /**
         * Error message
         */

        if (is_string($cartCandidates)) {
            return $cartCandidates;
        }

        /**
         * If prepare process return one object
         */
        if (!is_array($cartCandidates)) {
            $cartCandidates = array($cartCandidates);
        }

        $parentItem = null;
        $errors = array();
        foreach ($cartCandidates as $candidate) {
            $item = $this->_addCatalogProduct($candidate, $candidate->getCartQty());

            /**
             * As parent item we should always use the item of first added product
             */
            if (!$parentItem) {
                $parentItem = $item;
            }
            if ($parentItem && $candidate->getParentProductId()) {
                $item->setParentItem($parentItem);
            }

            /**
             * We specify qty after we know about parent (for stock)
             */
            $item->addQty($candidate->getCartQty());

            // collect errors instead of throwing first one
            if ($item->getHasError()) {
                $errors[] = $item->getMessage();
            }
        }
        if (!empty($errors)) {
            Mage::throwException(implode("\n", $errors));
        }
        return $item;
    }


    /**
     * Collect totals
     *
     * @return Mage_Sales_Model_Quote
     */
    public function collectTotals()
    {
        Mage::dispatchEvent(
            $this->_eventPrefix . '_collect_totals_before',
            array(
                $this->_eventObject=>$this
            )
        );
		
		
        $this->setSubtotal(0);
        $this->setBaseSubtotal(0);

        $this->setSubtotalWithDiscount(0);
        $this->setBaseSubtotalWithDiscount(0);

        $this->setGrandTotal(0);
        $this->setBaseGrandTotal(0);
		$x = 0;
		
        foreach ($this->getAllAddresses() as $address) {

            $address->setSubtotal(0);
            $address->setBaseSubtotal(0);

            $address->setSubtotalWithDiscount(0);
            $address->setBaseSubtotalWithDiscount(0);

            $address->setGrandTotal(0);
            $address->setBaseGrandTotal(0);

            if($this->getCouponCode()) {
                $address->setCouponCode($this->getCouponCode());
            }

			if($x == 0) {
            	$address->collectTotals();
            	$x++;
            }

            $this->setSubtotal((float) $this->getSubtotal()+$address->getSubtotal());
            $this->setBaseSubtotal((float) $this->getBaseSubtotal()+$address->getBaseSubtotal());

            $this->setSubtotalWithDiscount((float) $this->getSubtotalWithDiscount()+$address->getSubtotalWithDiscount());
            $this->setBaseSubtotalWithDiscount((float) $this->getBaseSubtotalWithDiscount()+$address->getBaseSubtotalWithDiscount());

            $this->setGrandTotal((float) $this->getGrandTotal()+$address->getGrandTotal());
            $this->setBaseGrandTotal((float) $this->getBaseGrandTotal()+$address->getBaseGrandTotal());
        }

        Mage::helper('orderedit')->checkQuoteAmount($this, $this->getGrandTotal());
        Mage::helper('orderedit')->checkQuoteAmount($this, $this->getBaseGrandTotal());


        foreach ($this->getEditAllVisibleItems() as $item) {
        	
            if ($item->getParentItem()) {
                continue;
            }

            if (($children = $item->getChildren()) && $item->isShipSeparately()) {
                foreach ($children as $child) {
                    if ($child->getProduct()->getIsVirtual()) {
                        $this->setVirtualItemsQty($this->getVirtualItemsQty() + $child->getQty()*$item->getQty());
                    }
                }
            }

            $this->setItemsCount($this->getItemsCount()+1);
            $this->setItemsQty((float) $this->getItemsQty()+$item->getQty());
        }
		
        $this->setData('trigger_recollect', 0);
        
        $this->_validateCouponCode();

        return $this;
    }

    protected function _validateCouponCode()
    {
        $code = $this->_getData('coupon_code');
        if ($code) {
            $addressHasCoupon = false;
            $addresses = $this->getAllAddresses();
            if (count($addresses)>0) {
                foreach ($addresses as $address) {
                    if ($address->hasCouponCode()) {
                        $addressHasCoupon = true;
                    }
                }
                if (!$addressHasCoupon) {
                    $this->setCouponCode('');
                }
            }
        }
        return $this;
    }

	public function getAllAddresses()
    {
        $addresses = array();
        foreach ($this->getAddressesCol() as $address) {
            if (!$address->isDeleted()) {
                $addresses[] = $address;
            }
        }
        
        return $addresses;
    }
    
    public function getAddressesCol()
    {
    	$this->_addresses = null;
        if (is_null($this->_addresses)) {
        	if(version_compare('1.4.1.0', Mage::getVersion(), '<=')) {
        		$this->_addresses = Mage::getResourceModel('orderedit/order_address_collection')
                	->setOrderFilter($this->getId());
        	} else {
            	$this->_addresses = Mage::getResourceModel('orderedit/order_address_collection')
                	->addAttributeToSelect('*')
                	->setOrderFilter($this->getId());
			}
            if ($this->getId()) {
                foreach ($this->_addresses as $address) {
                    $address->setOrder($this);
                }
            }
        }

        return $this->_addresses;
    }

    /**
     * Get all quote totals (sorted by priority)
     *
     * @return array
     */
    public function getTotals()
    {
        $totals = $this->getShippingAddress()->getTotals();
        foreach ($this->getBillingAddress()->getTotals() as $code => $total) {
            if (isset($totals[$code])) {
                $totals[$code]->setValue($totals[$code]->getValue()+$total->getValue());
            }
            else {
                $totals[$code] = $total;
            }
        }

        $sortedTotals = array();
        foreach ($this->getBillingAddress()->getTotalModels() as $total) {
            /* @var $total Mage_Sales_Model_Quote_Address_Total_Abstract */
            if (isset($totals[$total->getCode()])) {
                $sortedTotals[$total->getCode()] = $totals[$total->getCode()];
            }
        }
        return $sortedTotals;
    }
    
    public function getItemsCol($filterByTypes = array(), $nonChildrenOnly = false)
    {
    	//$this->unsetData();
		$this->_items = null;
        if (is_null($this->_items)) {

            $this->_items = Mage::getModel('orderedit/order_item')
            	->getCollection()
            	->addFieldToFilter('order_id', $this->getId());
                //->setOrderFilter($this->getId());

            if ($filterByTypes) {
                $this->_items->filterByTypes($filterByTypes);
            }
            if ($nonChildrenOnly) {
                $this->_items->filterByParent();
            }

            if ($this->getId()) {
                foreach ($this->_items as $item) {
                    $item->setOrder($this);
                }
            }
        }

        return $this->_items;
    }
	
	public function getOrderItems()
    {
        $items = array();
        foreach ($this->getItemsCol() as $item) {
            if (!$item->isDeleted()) {
                $items[] =  $item;
            }
        }
        return $items;
    }
    
    public function getNewItemById($itemId)
    {
        return $this->getItemsCol()->getNewItemById($itemId);
    }
}