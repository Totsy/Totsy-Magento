<?php
/*
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User Software Agreement (EULA).
 * It is also available through the world-wide-web at this URL:
 * http://www.harapartners.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to eula@harapartners.com so we can send you a copy immediately.
 * 
 */

$installer = $this;
$installer->startSetup();

$installer->run("
	ALTER TABLE fulfillmentfactory_itemqueue DROP FOREIGN KEY `FK_FULFILLMENT_ITEMQUEUE_QUOTE_ITEM_ID_SALES_FLAT_QUOTE_ITEM_ID`;
	ALTER TABLE fulfillmentfactory_itemqueue DROP FOREIGN KEY `FK_FULFILLMENT_ITEMQUEUE_PRODUCT_ID_CAT_PRD_ENTT_ENTT_ID`;
	ALTER TABLE fulfillmentfactory_itemqueue DROP KEY `FK_FULFILLMENT_ITEMQUEUE_PRODUCT_ID_CAT_PRD_ENTT_ENTT_ID`;
	ALTER TABLE fulfillmentfactory_itemqueue DROP KEY `FK_FULFILLMENT_ITEMQUEUE_QUOTE_ITEM_ID_SALES_FLAT_QUOTE_ITEM_ID`;
	ALTER TABLE fulfillmentfactory_itemqueue MODIFY `order_id` int(10) unsigned COMMENT 'item_id from sales_flat_order_item';
	ALTER TABLE fulfillmentfactory_itemqueue MODIFY `order_item_id` int(10) unsigned COMMENT 'item_id from sales_flat_order_item';
	ALTER TABLE fulfillmentfactory_itemqueue DROP FOREIGN KEY `FK_FULFILLMENT_ITEMQUEUE_ID_SALES_FLAT_ORDER_ENTITY_ID`;
	ALTER TABLE fulfillmentfactory_itemqueue ADD CONSTRAINT `FK_FULFILLMENT_ITEMQUEUE_ID_SALES_FLAT_ORDER_ENTITY_ID` FOREIGN KEY (`order_id`) REFERENCES `sales_flat_order` (`entity_id`) ON DELETE SET NULL ON UPDATE CASCADE;
	ALTER TABLE fulfillmentfactory_itemqueue DROP FOREIGN KEY `FK_FULFILLMENT_ITEMQUEUE_ORDER_ITEM_ID_SALES_FLAT_ORDER_ITEM_ID`;
	ALTER TABLE fulfillmentfactory_itemqueue ADD CONSTRAINT `FK_FULFILLMENT_ITEMQUEUE_ORDER_ITEM_ID_SALES_FLAT_ORDER_ITEM_ID` FOREIGN KEY (`order_item_id`) REFERENCES `sales_flat_order_item` (`item_id`) ON DELETE SET NULL ON UPDATE CASCADE;
	ALTER TABLE fulfillmentfactory_itemqueue DROP FOREIGN KEY `FK_FULFILLMENT_ITEMQUEUE_STORE_ID_CORE_STORE_STORE_ID`;
	ALTER TABLE fulfillmentfactory_itemqueue ADD CONSTRAINT `FK_FULFILLMENT_ITEMQUEUE_STORE_ID_CORE_STORE_STORE_ID` FOREIGN KEY (`store_id`) REFERENCES `core_store` (`store_id`) ON DELETE SET NULL ON UPDATE CASCADE;
");
    
$installer->endSetup();

?>