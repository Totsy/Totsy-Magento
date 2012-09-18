<?php
 
 class Harapartners_Customertracking_Helper_Linkshare extends Mage_Core_Helper_Abstract
 {

    const MERCHANT_ID = 36138;

    /**
    *
    * IMPORTED FROM LITHIUM PLATFORM MUST CHANGE
    **/
    public static function linkshareRaw($order, $trackid, $entryTime, $trans_type){
        $raw = '';
        $raw .= 'ord=' . $order->getIncrementId() . '&';
        if(($trans_type)){
            $raw .= 'tr=' . $trackid . '&';
            $raw .= 'land=' . date('Ymd_Hi', strtotime($entryTime)) . '&';
            $raw .= 'date=' . date('Ymd_Hi', strtotime($order->getCreatedAt())) . '&';
        } else {
            $raw .= 'mid=36138&';
        }
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
            if(!in_array($item->getSku(), $skulist)) {
                $skulist[] = $item->getSku();
                $namelist[] = urlencode($item->getName());
                $qlist[] =  $qty;
                $amtlist[] = ( ($trans_type == 'cancel') || $item_cancel ) ? (-round($item->getOriginalPrice(), 2) * $qty) * 100 : (round($item->getOriginalPrice(), 2) * $qty) * 100 ;
            }
        }
        
        if($order->getCouponCode() || $order->getBaseDiscountAmount()){
            $raw .= 'skulist=' . implode('|', $skulist) . '|Discount&';
            $raw .= 'namelist=' . implode('|', $namelist) . '|Discount&';
            $raw .= 'qlist=' . implode('|' , $qlist) . '|0&';
            $raw .= 'cur=USD&';
            $raw .= 'amtlist='. implode('|', $amtlist) . '|' . number_format($order->getBaseDiscountAmount(), 2) * 100;
        }else{
            $raw .= 'skulist=' . implode('|', $skulist) . '&';
            $raw .= 'namelist=' . implode('|', $namelist) . '&';
            $raw .= 'qlist=' . implode('|' , $qlist) . '&';
            $raw .= 'cur=USD&';
            $raw .= 'amtlist='. implode('|', $amtlist);
        }
        return $raw;
    }

    /**
    * IMPORTED FROM LITHIUM PLATFORM MUST CHANGE
    * This function sends order transactions to linkshare.
    * @params $data is the information that needs to be passes
    **/
   /* public static function transaction($data, $affiliate, $orderid, $trans_type = 'new') {
        static::meta('source','affiliate.log');
        $transaction = static::collection()->count(array(
                            'order_id' => $orderid, 
                            'trans_type' => $trans_type,
                            'success' => true
                        ));
        if( $transaction >= 1){
            return true;
        }
        $parser = xml_parser_create();
        xml_parse_into_struct($parser, file_get_contents($data), $response, $index);
        xml_parser_free($parser);
        $status = $response[1]['value'];
        if( $status == 'Access denied' ) {
             $success = false;
        }else{
            $success = ( (bool) $response[5]['value'] ) ? (bool) $response[5]['value'] : (bool) $response[7]['value'];
        }
        $trans['trans_id'] = $response[1]['value'];
        $trans['affiliate'] = $affiliate;
        $trans['success'] = $success;
        $trans['order_id'] = $orderid;
        $trans['data'] = ;
        $trans['md5'] = ;
        $trans['created_date'] = date(strtotime('now'));
        return static::collection()->save($trans);
    }*/


    
 }  

?>
