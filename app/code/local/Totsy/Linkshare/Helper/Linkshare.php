<?php

 class Totsy_Linkshare_Helper_Linkshare extends Mage_Core_Helper_Abstract 
{
    const MERCHANT_ID = 36138;
    const TRANSMIT_URL = "https://track.linksynergy.com/nvp";

    /**
    *
    **/
    public static function linkshareRaw($order, $trackid, $entryTime, $trans_type) {
        $raw = '';

        if($order) {
            $raw .= 'ord=' . $order->getIncrementId() . '&';
            
            $raw .= 'tr=' . $trackid . '&';
            $raw .= 'land=' . date('Ymd_Hi', strtotime($entryTime)) . '&';
            $raw .= 'date=' . date('Ymd_Hi', strtotime($order->getCreatedAt())) . '&';

            $skulist = array();
            $namelist = array();
            $qlist = array();
            $amtlist = array();
            $items = $order->getItemsCollection();
            $item_cancel = false;
            foreach($items as $item) {
                $qty = $item->getQtyOrdered() - $item->getQtyCanceled();
                
                if(($trans_type != 'cancel') && $qty == 0 ) {
                        $item_cancel = true;
                }
                if(is_null($item->getParentItemId())) {
                    $skulist[] = $item->getSku();
                    $namelist[] = urlencode($item->getName());
                    $qlist[] =  $qty;
                    $amtlist[] = ( ($trans_type == 'cancel') || $item_cancel ) ? (-round($item->getOriginalPrice(), 2) * $qty) * 100 : (round($item->getOriginalPrice(), 2) * $qty) * 100 ;
                }
            }
            
            if($order->getCouponCode() || $order->getBaseDiscountAmount() != 0){
                $raw .= 'skulist=' . implode('|', $skulist) . '|Discount&';
                $raw .= 'namelist=' . implode('|', $namelist) . '|Discount&';
                $raw .= 'qlist=' . implode('|' , $qlist) . '|0&';
                $raw .= 'cur=USD&';
                $raw .= 'amtlist='. implode('|', $amtlist) . '|' . number_format($order->getBaseDiscountAmount(), 2) * 100;
            } else {
                $raw .= 'skulist=' . implode('|', $skulist) . '&';
                $raw .= 'namelist=' . implode('|', $namelist) . '&';
                $raw .= 'qlist=' . implode('|' , $qlist) . '&';
                $raw .= 'cur=USD&';
                $raw .= 'amtlist='. implode('|', $amtlist);
            }
        }
        return $raw;
    }

    public function dataEncode($raw) {
        //Encrypting raw message
        $base64 = base64_encode($raw);
        $msg = str_replace('/','_',str_replace('+','-',$base64));

        //Used for authenticity
        $md5_raw = hash_hmac('md5', $raw, 'Ve3YGHn7', true);
        $md5 = base64_encode($md5_raw);
        $md5 = str_replace('/','_',str_replace('+','-',$md5));
        return array('msg' => $msg, 'md5' => $md5);
    }

    public function prepareTransactionData($raw_data) {
        $encodings = $this->dataEncode($raw_data);
        $encoded_data = self::TRANSMIT_URL . "?mid=" . self::MERCHANT_ID . "&msg=" . $encodings['msg'] . "&md5=" . $encodings['md5'] . "&xml=1";
        return $encoded_data;
    }

    /**
    * This function sends order transactions to linkshare.
    **/
    public function sendTransaction($data, $affiliate, $orderid, $trans_type = 'new') {
        if( $transaction >= 1){
            return true;
        }

        $parser = xml_parser_create();
        xml_parse_into_struct($parser, file_get_contents($data), $response, $index);
        xml_parser_free($parser);
        $status = $response[1]['value'];
        if( $status == 'Access denied' ) {
             $success = false;
             $message = 'Access Denied';
        }else{
            $success = ( (bool) $response[5]['value'] && $response[7]['value'] != 1 ) ? true : false;
            if(!$success)
                $message = "Badly formatted NVP message";
        }
        $out = print_r($response, true);
        $trans['trans_id'] = $status;
        $trans['success'] = $success;
        $trans['order_id'] = $orderid;
        $trans['message'] = $message;
        return $trans;
    }
}
    ?>