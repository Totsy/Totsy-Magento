<?php

class Totsy_CatalogInventory_InventoryController extends Mage_Core_Controller_Front_Action
{
	public function laststockstatusAction() {

		$data = $this->getRequest()->getParams();

		$product_id = $data['id'];

		$product = Mage::getModel('catalog/product')->load($product_id);
		$stock_count = 0;

		if($product) {
			if($product->getTypeId() == "configurable"){

				$related_product = $product->getTypeInstance()->getProductByAttributes($data['super_attribute'], $product_id);
				if($related_product) {
					$stock_status = Mage::getModel('cataloginventory/stock_status')->getProductData($related_product->getEntityId(), Mage::app()->getStore()->getId());
					$stock_count = $stock_status[$related_product->getEntityId()]['qty'];
				}
			} else{
				$stock_status = Mage::getModel('cataloginventory/stock_status')->getProductData($product->getEntityId(), Mage::app()->getStore()->getId());
				$stock_count = $stock_status[$product->getEntityId()]['qty'];
			}
		}

		echo json_encode(array('stock_count' => $stock_count));

	}
}

?>