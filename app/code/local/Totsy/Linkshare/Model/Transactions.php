<?php
/**
 * @category    Totsy
 * @package     Totsy_linkshare_Model
 * @author      Lawrenberg Hanson <lhanson@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

class Totsy_Linkshare_Model_Transactions extends Mage_Core_Model_Abstract
{
	/**
     * Init resource model
     */
    protected function _construct()
    {
        $this->_init('linkshare/transactions');
    }

    public function recordTransaction($record) {

        $dataObj = new Varien_Object();

        if(!$record['success']) {
            $dataObj->setData('trans_status', 'Failed');
        } else {
            $dataObj->setData('trans_status', 'Success');
        }

        $dataObj->setData('customertracking_id', $record['customertracking_id']);
        $dataObj->setData('message', $record['message']);
        $dataObj->setData('order_id', $record['order_id']);
        $dataObj->setData('raw_data', $record['raw_data']);
        $dataObj->setData('created_at', date('Y-m-d H:i:s'));
        $dataObj->setData('updated_at', date('Y-m-d H:i:s'));
        $dataObj->setData('order_status', $record['order_status']);
        $dataObj->setData('trans_id', $record['trans_id']);

        $this->setData($dataObj->getData());
        $this->save();
        $this->unsetData();
    }

    /*public function cancellations(){
        $transactions = $this->getCollection();
        $transactions->getSelect()->where('trans_status= "failed" or (trans_status= "success" and order_status= "New")');
        
        foreach($transactions as $transaction){

            $order_id = $transaction->getOrderId();

            if((bool)$transaction->getTransStatus()) {
                $encoded = Mage::helper("customertracking/linkshare")->prepareTransactionData($transaction->getRawData());
                $result = Mage::helper('customertracking/linkshare')->sendTransaction($encode, 'linkshare', $order_id, 'new');

                if ($result['success']) {
                    $transaction->setData('trans_status', 'success');
                    $transaction->save();
                }
                continue;
            }

            if( $transaction->getOrderStatus() == 'new'  && $transaction->getTransStatus()) {
                $order_record = Mage::getModel('sales/order')->load($order_id);

                switch($order_record->getStatus()) {
                    case 'Complete':
                        $transaction->setData('order_status', 'Complete');
                        $transaction->save();
                        break;
                    case 'Updated':
                        $inc = $order_record->getEditIncrement() + 1;
                        $dashpos = strpos($order_id, '-');
                        if($dashpos) {
                            $order_id = substr(0,$dashpos, $order_id);
                        }

                        $new_order = Mage::getModel('sales/order')->load($order_id . '-' . $inc);

                        $message = Mage::helper('customertracking/linkshare')->linkshareRaw($new_order, $regParams['subID'],$new_order->getUpdatedAt(),'Updated');
                        $encode = Mage::helper('customertracking/linkshare')->prepareTransactionData($message);
                        $result = Mage::helper('customertracking/linkshare')->sendTransaction($encode, 'linkshare', $new_order->getIncrementId(), $new_order->getStatus());
                        $transaction->setData('order_status', 'Canceled');
                        $transaction->save();
                        break;
                    case 'Canceled':
                        break;
                    case default:
                        continue;
                }
            }
        }
    } */   
}
