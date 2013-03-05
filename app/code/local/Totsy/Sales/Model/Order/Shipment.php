<?php

class Totsy_Sales_Model_Order_Shipment extends Mage_Sales_Model_Order_Shipment
{
    /**
     * After object save manipulations
     *
     * @return Mage_Sales_Model_Order_Shipment
     */
    protected function _afterSave()
    {
        if (null !== $this->_items) {
            foreach ($this->_items as $item) {
                $item->save();
                //20130301 - CJD - Adding in Order Item save to ensure qty_shipped changes are saved to order item.
                $item->getOrderItem()->save();
            }
        }

        if (null !== $this->_tracks) {
            foreach($this->_tracks as $track) {
                $track->save();
            }
        }

        if (null !== $this->_comments) {
            foreach($this->_comments as $comment) {
                $comment->save();
            }
        }

        return parent::_afterSave();
    }
}