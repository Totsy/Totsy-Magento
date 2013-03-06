<?php

class Totsy_Checkout_Block_Total_Nominal extends Mage_Checkout_Block_Total_Nominal
{
    /**
     * Getter for details row label
     *
     * @param Varien_Object $row
     * @return string
     */
    public function getItemDetailsRowLabel(Varien_Object $row)
    {
        $label = $row->getLabel();
        switch($label) {
            case 'Regular Payment':
                $label = 'Monthly Membership';
                break;
            case 'Tax':
                $label = 'Sales Tax (NY and NJ)';
                break;
        }
        return $label;
    }
}