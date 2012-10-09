<?php
/**
 * 
 * @category 	Crown
 * @package 	Crown_Import 
 * @since 		1.0.0
 */
class Crown_Import_Model_Observer {
	
	/**
	 * Stores stock transaction history
	 * @since 1.0.0
	 * @param unknown_type $observer
	 * @return void
	 */
	public function savePurchaseOrderTransaction($observer) {
		$vars 			= $observer->getEvent ()->getVars ();
		$profile		= $vars['profile'];
		$newProducts 	= $vars ['change_stock'];
		$oldData 		= $vars['old_data'];
		$skuMap			= $vars['skus'];
		$importModel	= Mage::getModel('crownimport/importhistory')->load($profile->getId(), 'urapidflow_profile_id');
		
		if (!$importModel->getId()) return;
		
		foreach ( $newProducts as $sku => $changeArray ) {
			$new = ( !isset($skuMap[$sku]) || !isset($oldData[ $skuMap[$sku] ][0]['stock.qty']) );
			
			if (!isset($changeArray ['qty'])) {
				continue;
			} elseif ( $new ) {
				$newStock = $changeArray ['qty'];
				$qtyDelta = $newStock;
				$product = Mage::getModel ( 'catalog/product' )->loadByAttribute ( 'sku', $sku );
			} else {
				$newStock = $changeArray ['qty'];
				$id = $skuMap[$sku];
				$oldStock = $oldData[ $id ][0]['stock.qty'];
				$qtyDelta = $newStock - $oldStock;
				$product = Mage::getModel ( 'catalog/product' )->load ( $id );
			}
			
			try {
				/* @var $stockhistoryTransaction Harapartners_Stockhistory_Model_Transaction */
				$stockhistoryTransaction = Mage::getModel ( 'stockhistory/transaction' );
				
				if (! ! $product && ! ! $product->getId () && $product->getTypeId () == 'simple') {
					$dataObj = new Varien_Object ();
					$dataObj->setData ( 'vendor_id', $importModel->getData ( 'vendor_id' ) );
					$dataObj->setData ( 'vendor_code', $importModel->getData ( 'vendor_code' ) );
					$dataObj->setData ( 'po_id', $importModel->getData ( 'po_id' ) );
					$dataObj->setData ( 'category_id', $importModel->getData ( 'category_id' ) );
					$dataObj->setData ( 'product_id', $product->getId () );
					$dataObj->setData ( 'product_sku', $product->getSku () );
					$dataObj->setData ( 'vendor_style', $product->getVendorStyle () );
					$dataObj->setData ( 'unit_cost', $product->getData ( 'sale_wholesale' ) );
					$dataObj->setData ( 'qty_delta', $qtyDelta );
					$dataObj->setData ( 'action_type', Harapartners_Stockhistory_Model_Transaction::ACTION_TYPE_EVENT_IMPORT );
					$dataObj->setData ( 'comment', date ( 'Y-n-j H:i:s' ) );
					$stockhistoryTransaction->importData ( $dataObj )->save ();
				} elseif (!$product->getId()) {
					throw new Exception('Unable to load product ' . $sku);
				} elseif ($product->getTypeId () != 'simple') {
					throw new Exception('Not a simple product.');
				} else {
					throw new Exception('Unknown error! Sku: ' . $sku);
				}
			} catch( Exception $e) {
				Mage::log(  $e->getMessage(), null, 'import_observer_error.log', true);
			}
		}
	}
}