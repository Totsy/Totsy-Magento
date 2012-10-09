<?php
/**
 * 
 * @category 	Crown
 * @package 	Crown_Import 
 * @since 		1.0.0
 */
class Crown_Import_Model_Cron {
	
	/**
	 * Delete not finished import data
	 * @since 1.0.0
	 * @return void
	 */
	public function clearExpiredImports() {
		$date = Zend_Date::now()->subDay(2)->toString('YYYY-MM-dd HH:mm:ss');
		$collection = Mage::getModel ( 'crownimport/importhistory' )->getCollection ()
			->addFilter('status',Crown_Import_Model_Importhistory::IMPORT_STATUS_NEW);
		$collection->getSelect()->where('updated_at <= ?', $date);
		foreach ($collection as $history) {
			if ( $history->getUrapidflowProfileId() )
				Mage::getModel('urapidflow/profile')->load($history->getUrapidflowProfileId())->delete();
			if ( $history->getUrapidflowProfileIdProductExtra() )
				Mage::getModel('urapidflow/profile')->load($history->getUrapidflowProfileIdProductExtra())->delete();
			$history->delete();
		}
	}
	
	/**
	 * Deletes old uRapidflow profiles from completed profiles
	 * @since 1.0.0
	 * @return void
	 */
	public function clearOldProfiles() {
		$date = Zend_Date::now()->subDay(7)->toString('YYYY-MM-dd HH:mm:ss');
		$collection = Mage::getModel ( 'crownimport/importhistory' )->getCollection ()
			->addFilter('status',Crown_Import_Model_Importhistory::IMPORT_STATUS_COMPLETE);
		$collection->getSelect()->where('updated_at <= ?', $date);
		$collection->getSelect()->where('urapidflow_profile_id IS NOT NULL OR urapidflow_profile_id_product_extra IS NOT NULL');
		foreach ($collection as $history) {
			if ( $history->getUrapidflowProfileId() )
				Mage::getModel('urapidflow/profile')->load($history->getUrapidflowProfileId())->delete();
			if ( $history->getUrapidflowProfileIdProductExtra() )
				Mage::getModel('urapidflow/profile')->load($history->getUrapidflowProfileIdProductExtra())->delete();
		}
	}
}