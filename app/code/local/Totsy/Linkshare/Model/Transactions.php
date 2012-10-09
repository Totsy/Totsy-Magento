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
        try {
            $this->save();
            $this->unsetData();
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    public function sendUpdates(){
        $transactions = Mage::getModel('linkshare/transactions')->getCollection();
        $transactions->getSelect()->where('trans_status= "Failed" or (trans_status= "Success" and order_status= "New")');
        foreach($transactions as $transaction){
            try {
                $order_id = $transaction->getOrderId();
                $order_record = Mage::getModel('sales/order')->getCollection();
                $order_record->getSelect()->where('increment_id = "' . $order_id . '"');
                $customertracker = Mage::getModel('customertracking/record')->load($transaction->getCustomertrackingId());
                $trackingInfo = $customertracker->getRegistrationParam();
                $order_record = $order_record->getFirstItem();
                $regParams = json_decode($trackingInfo, true);
                
                if($transaction->getTransStatus() == 'Failed') {
                    $encoded = Mage::helper("linkshare/linkshare")->prepareTransactionData($transaction->getRawData());
                    $result = Mage::helper('linkshare/linkshare')->sendTransaction($encoded, $order_id, 'New');

                    if ($result['success']) {
                        $transaction->setData('trans_id', $result['trans_id']);
                        $transaction->setData('updated_at', date('Y-m-d H:i:s'));
                        $transaction->setData('trans_status', 'Success');
                        $transaction->save();
                    } elseif(is_numeric($result['trans_id'])) {
                        $message = Mage::helper('linkshare/linkshare')->linkshareRaw($order_record, $regParams['subID'],$order_record->getCreatedAt(),$order_record->getStatus());
                        
                        $encode = Mage::helper('linkshare/linkshare')->prepareTransactionData($message);
                        $result = Mage::helper('linkshare/linkshare')->sendTransaction($encode, $order_record->getIncrementId(), $order_record->getStatus());
                        
                        if(!$result['success']) {
                            $transaction->setData('trans_status', 'Failed');
                        } else {
                            $transaction->setData('trans_status', 'Success');
                        }
                        $transaction->setData('trans_id', $result['trans_id']);
                        $transaction->setData('updated_at', date('Y-m-d H:i:s'));
                        $transaction->save();
                    }
                    continue;
                }
            } catch( Exception $e) {
                 Mage::logException($e);
            }

           if( $transaction->getOrderStatus() == 'New'  && $transaction->getTransStatus() == 'Success') {
                try{
                    switch($order_record->getStatus()) {
                        case 'complete':
                            $transaction->setData('order_status', 'Complete');
                            $transaction->save();
                            break;
                        case 'updated':
                            $inc = (int)$order_record->getEditIncrement() + 1;
                            $dashpos = strpos($order_id, '-');

                            if($dashpos) {
                                $order_id = substr($order_id, 0,$dashpos);
                                $order_id .= '-' . $inc;
                            } else {
                                $order_id .= '-1';
                            }

                            $new_order = Mage::getModel('sales/order')->getCollection();
                            $new_order->getSelect()->where('increment_id="' . $order_id . '"');
                            $new_order = $new_order->getFirstItem();

                            $message = Mage::helper('linkshare/linkshare')->linkshareRaw($new_order, $regParams['subID'],$new_order->getUpdatedAt(),'new');
                            $encode = Mage::helper('linkshare/linkshare')->prepareTransactionData($message);
                            $result = Mage::helper('linkshare/linkshare')->sendTransaction($encode, $new_order->getIncrementId(), $new_order->getStatus());
                            $result['customertracking_id'] = $transaction->getCustomertrackingId();
                            $result['raw_data'] = $message;
                            $result['order_status'] = 'New';
                            $this->recordTransaction($result);
                            
                            $transaction->setData('order_status', 'Updated');
                            $transaction->save();
                            break;
                        case 'canceled':
                            $message = Mage::helper('linkshare/linkshare')->linkshareRaw($order_record, $regParams['subID'],$order_record->getUpdatedAt(),'cancel');
                            $encode = Mage::helper('linkshare/linkshare')->prepareTransactionData($message);
                            $result = Mage::helper('linkshare/linkshare')->sendTransaction($encode, $order_record->getIncrementId(), $order_record->getStatus());
                            
                            if(!$record['success']) {
                                $transaction->setData('trans_status', 'Failed');
                            } else {
                                $transaction->setData('trans_status', 'Success');
                            }
                            
                            $transaction->setData('trans_id', $result['trans_id']);
                            $transaction->setData('order_status', 'Canceled');
                            $transaction->setData('raw_data', $message);
                            $transaction->save();
                            break;
                        default:
                            continue;
                    }
                } catch(Exception $e) {
                    Mage::logException($e);
                }
            }
        }
    }
}
