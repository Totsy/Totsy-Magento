<?php
// Mage_Sales_Model_Quote
class Crown_Club_Model_Sales_Quote extends Webshopapps_Shipdiscount_Sales_Model_Quote
{

    /**
     * Create recurring payment profiles basing on the current items
     *
     * @return array
     */
    public function prepareRecurringPaymentProfiles()
    {
        if (!$this->getTotalsCollectedFlag()) {
            // Whoops! Make sure nominal totals must be calculated here.
            throw new Exception ('Quote totals must be collected before this operation.');
        }

        $result = array();
        foreach ($this->getAllVisibleItems() as $item) {
            $product = $item->getProduct();
            if (is_object($product) && ($product->isRecurring()) && $profile = Mage::getModel('sales/recurring_profile')->importProduct($product)) {
                $profile->importQuote($this);
                $profile->importQuoteItem($item);
                $result [] = $profile;
                $productModel = Mage::getModel('catalog/product')->load($product->getEntityId());
                if ($productModel->getIsClubSubscription()) {
                    $profile->setData('is_club_profile', true);
                } else {
                    $profile->setData('is_club_profile', false);
                }
            }
        }
        return $result;
    }
}