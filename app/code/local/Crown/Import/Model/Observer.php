<?php
/**
 * 
 * @category 	Crown
 * @package 	Crown_Import 
 * @since 		1.0.0
 */
class Crown_Import_Model_Observer {
	
	/**
	 * Stores stock trasnaction history
	 * @since 1.0.0
	 * @param unknown_type $observer
	 * @return void
	 */
	public function savePurchaseOrderTransaction($observer) {
		$vars = $observer->getEvent ()->getVars ();
		$newProducts = $vars ['change_stock'];
		$_afterFetchData = Mage::registry('import_data_old_data');
		Mage::unregister('import_data_old_data');
		$oldData = $_afterFetchData['old_data'];
		$skuMap = $_afterFetchData['skus'];
		
		$importModel = Mage::helper ( 'crownimport' )->getImportModel();
		
		foreach ( $newProducts as $sku => $changeArray ) {
			$new = ( !isset($skuMap[$sku]) || !isset($oldData[ $skuMap[$sku] ][0]['stock.qty']) );
			
			if ( $new ) {
				$newStock = $changeArray ['qty'];
				$qtyDelta = $newStock;
			} else {
				$newStock = $changeArray ['qty'];
				$id = $skuMap[$sku];
				$oldStock = $oldData[ $id ][0]['stock.qty'];
				$qtyDelta = $newStock - $oldStock;
			}
			
			$stockhistoryTransaction = Mage::getModel ( 'stockhistory/transaction' );
			
			$product = Mage::getModel ( 'catalog/product' )->load ( $id );
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
				$stockhistoryTransaction->importData ( $dataObj )->save (); //exceptions will be caught and added to $this->_errorMessage
			}
		}
	}
	
	/**
	 * Stores the difference data for delta qty comparison
	 * @since 2.1.2
	 * @param unknown_type $observer
	 * @return void
	 */
	public function getCurrentProductQtyForDiff($observer) {
		$vars = $observer->getEvent ()->getVars ();
		Mage::register('import_data_old_data', $vars);
	}
}