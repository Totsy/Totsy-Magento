<?php
class Totsy_Sales_Block_Order_Item_Renderer_Default extends Mage_Sales_Block_Order_Item_Renderer_Default
{
    public function isOrderPaid() {
        $order = $this->getOrder();

        foreach($order->getInvoiceCollection() as $invoice) {
            if($invoice->getState() == Mage_Sales_Model_Order_Invoice::STATE_PAID) {
                return true;
            }
        }

        return false;

    }
}