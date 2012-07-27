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

// fixes an issue after 1.6.1 where a file was not declared abstract 
if (version_compare(Mage::getVersion(), '1.6.0', '>')===true) {
class TinyBrick_OrderEdit_Model_Resource_Order_Item extends Mage_Sales_Model_Resource_Abstract
{
    protected function _construct()
    {
        $this->_init('orderedit/order_item', 'item_id');
    }
    
}
}else{
class TinyBrick_OrderEdit_Model_Resource_Order_Item extends Mage_Sales_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('orderedit/order_item', 'item_id');
    }
}
}
