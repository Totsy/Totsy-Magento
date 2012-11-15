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
class TinyBrick_OrderEdit_Model_Edit_Updater_Type_Shipping extends TinyBrick_OrderEdit_Model_Edit_Updater_Type_Abstract
{
	public function edit(TinyBrick_OrderEdit_Model_Order $order, $data = array())
	{
        unset($data['entity_id']);
		$shipping = $order->getShippingAddress();
		$oldArray = $shipping->getData();
		$data['street'] = $data['street1'];
		if($data['street2']) {
			$data['street'] .= "\n" . $data['street2'];
		}
        #check if infos are empty
        foreach($data as $key => $value) {
            if($key == 'street' || $key == 'city' || $key == 'firstname' || $key == 'lastname') {
                if(!$value) {
                    return "Error updating shipping address, you should fill all the fields required.";
                }
            }
        }
        #If address is identical, dont save it
        $duplicate = Mage::helper('orderedit')->checkDuplicate($shipping, $data);
        if($duplicate) {
            return 'not_updated';
        }
		$shipping->addData($data);
		try{
			$shipping->save();
            Mage::helper('orderedit')->createCustomerAddressFromData($data, $order->getCustomerId());
			$newArray = $shipping->getData();
			$results = array_diff($oldArray, $newArray);
			$count = 0;
			$comment = "";
			foreach($results as $key => $result) {
				if(array_key_exists($key, $newArray)) {
					$comment .= "Changed " . $key . " FROM: " . $oldArray[$key] . " TO: " . $newArray[$key] . "<br />";
					$count++;
				}
			}
			return false;
		}catch(Exception $e){
            return "Error updating shipping address" . $e->getMessage();
		}
	}
}