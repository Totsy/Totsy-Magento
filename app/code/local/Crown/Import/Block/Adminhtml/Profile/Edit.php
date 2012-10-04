<?php
/**
 *
 * @category 	Crown
 * @package 	Crown_Import
 * @since 		1.0.0
 */
class Crown_Import_Block_Adminhtml_Profile_Edit extends Unirgy_RapidFlow_Block_Adminhtml_Profile_Edit {

	/**
	 * Rewrite buttons to limit access according to status
	 * @since 1.0.0
	 * @return void
	 */
	public function __construct() {
		$profile = Mage::registry('profile_data');
		$id = $profile->getId ();

		$importHistoryProfileIds = array();

		if (!is_null($id)) {
			$importCollection = Mage::getModel('crownimport/importhistory')->getCollection();
			$importCollection->getSelect()->reset(Zend_Db_Select::COLUMNS)
				->columns(array('urapidflow_profile_id','urapidflow_profile_id_product_extra'));

			foreach ($importCollection as $_id) {
				$importHistoryProfileIds[] = $_id->getUrapidflowProfileId();
			}

			foreach ($importCollection as $_id) {
				$importHistoryProfileIds[] = $_id->getUrapidflowProfileIdProductExtra();
			}
		}

		if ( !is_null($id) && in_array($id, $importHistoryProfileIds) ) {
			Mage_Adminhtml_Block_Widget_Form_Container::__construct();

			$this->_removeButton ( 'reset' );
			$this->_removeButton ( 'save' );

	        $this->_objectId = 'id';
	        $this->_blockGroup = 'urapidflow';
	        $this->_controller = 'adminhtml_profile';

			return;
		} else {
			parent::__construct();
		}
	}
}