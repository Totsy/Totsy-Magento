<?php
/**
 * Crown Partners LLC
 *
 * @category    {category}
 * @package     {package}
 * @author: chris.davidowski
 */

class Crown_Club_Block_Cart_Banner extends Mage_Core_Block_Abstract
{
    public function _toHtml()
    {
        if(Mage::helper('crownclub')->isClubMember(Mage::getSingleton('customer/session')->getCustomer())) {
            return '';
        }
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        foreach($quote->getAllItems() as $item) {
            if($item->getProductId() == Mage::getStoreConfig('Crown_Club/clubgeneral/club_product_id')) {
                return '';
            }
        }

        $block = Mage::getModel('cms/block')
            ->setStoreId(Mage::app()->getStore()->getId())
            ->load('plus_cart_banner');

        $array = array();
        $array['savings_amount'] = Mage::helper('core')->currency(Mage::helper('crownclub')->estimatePossibleRewards($quote));
        $filter = Mage::getModel('cms/template_filter');
        $filter->setVariables($array);
        return $filter->filter($block->getContent());
    }
}