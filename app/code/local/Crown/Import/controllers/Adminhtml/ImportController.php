<?php
/**
 * 
 * @category 	Crown
 * @package 	Crown_Import 
 * @since 		1.0.0
 */
class Crown_Import_Adminhtml_ImportController extends Mage_Adminhtml_Controller_action {
	
	/**
	 * @since 1.0.0
	 * @return void
	 */
	protected function _initAction() {
		$this->loadLayout ()->_setActiveMenu ( 'crown/import' );
		return $this;
	}
	
	/**
	 * Initiates the import model used for the wizard. And the admin session
	 * @since 1.0.0
	 * @return Crown_Import_Model_Importhistory
	 */
	protected function _initImportModel() {
		$id = $this->getRequest ()->getParam ( 'id' );
		if (is_numeric($id)) {
			return Mage::helper ( 'crownimport' )->setImportModel($id);
		}
		return Mage::helper ( 'crownimport' )->getImportModel();
	}
	
	/**
	 * Shows all the ran imports
	 * @since 1.0.0
	 * @return void
	 */
	public function indexAction() {
		$this->_initAction ()->renderLayout ();
	}
	
	/**
	 * Deletes a profile.
	 * @since 1.0.0
	 * @return void
	 */
	public function deleteAction() {
		$importModel = $this->_initImportModel();
		if ( $importModel->getUrapidflowProfileId() )
			Mage::getModel('urapidflow/profile')->load($importModel->getUrapidflowProfileId())->delete();
		if ( $importModel->getUrapidflowProfileIdProductExtra() )
			Mage::getModel('urapidflow/profile')->load($importModel->getUrapidflowProfileIdProductExtra())->delete();
		$importModel->delete();
		Mage::getSingleton ( 'adminhtml/session' )->addSuccess('Profile has been deleted');
		$this->_redirect ( '*/*/');
	}
	
	/**
	 * Shows an error message for when a urapidflow profiles is deleted.
	 * @since 1.0.0
	 * @return void
	 */
	public function profilemessageAction() {
		switch ( $this->getRequest ()->getParam ( 'mid' ) ) {
			case 1:
				$message = 'uRapidflow profile has been deleted. Statistics not available.';
				break;
			case 2:
				$message = 'Profile is currently running. Please wait for it to complete.';
				break;
			default:
				$message = 'Unknown error';
		}
		Mage::getSingleton ( 'adminhtml/session' )->addError($message);
		$this->_redirect ( '*/*/');
		return;
	}
	
	/**
	 * Creates a new import process
	 * @since 1.0.0
	 * @return void
	 */
	public function newAction() {
		$importModel = Mage::helper ( 'crownimport' )->setImportModel();
		$this->_redirect ( '*/*/edit', array ('id' => $importModel->getId() ) );
		return;
	}
	
	/**
	 * Changes status back to match process
	 * @since 1.0.0
	 * @return void
	 */
	public function fixstatusAction() {
		$importModel = $this->_initImportModel()->statusCheck();
		$this->_redirect ( '*/*/edit', array ('id' => $importModel->getId() ) );
		return;
	}
	
	/**
	 * Resets to the begining of the wizard
	 * @since 1.0.0
	 * @return void
	 */
	public function resetAction() {
		$importModel = $this->_initImportModel();
		$importModel->setData('step', 'import')->save();
		$this->_redirect ( '*/*/edit', array ('id' => $importModel->getId() ) );
		return;
	}
	
	/**
	 * Creates a new import based off of a category
	 * @since 1.0.0
	 * @return void
	 */
	public function newByCategoryAction() {
		$importModel = Mage::helper ( 'crownimport' )->resetImportModel()->getImportModel();
		
		$categoryId = $this->getRequest ()->getParam ( 'category_id' );
		$category = Mage::getModel ( 'catalog/category' )->load ( $categoryId );
		
		if (! ! $category && ! ! $category->getId ()) {
			$defaultPoId = 0;
			$poArray = Mage::helper ( 'stockhistory' )->getFormPoArrayByCategoryId ( $category->getId (), Harapartners_Stockhistory_Model_Purchaseorder::STATUS_OPEN );
			if (count ( $poArray ) && isset ( $poArray [0] ['value'] )) {
				$defaultPoId = $poArray [0] ['value'];
			}
			
			$defaultVendorCode = "";
			$vendorArray = Mage::helper ( 'stockhistory' )->getFormVendorArrayByCategoryId ( $category->getId (), Harapartners_Stockhistory_Model_Purchaseorder::STATUS_OPEN );
			// If there is only 1 vendor that has been used for previous imports, set it as the default
			if (count ( $vendorArray ) == 1 && isset ( $vendorArray [0] ['label'] )) {
				$defaultVendorCode = $vendorArray [0] ['label'];
			}
			
			$importModel
				->setImportTitle($category->getName ())
				->setCategoryId($category->getId ())
				->setImportFilename($_FILES ['import_filename'] ['name'])
				->setVendorCode($defaultVendorCode)
				->setUpdatedAt(now())
				->save();
				
			Mage::helper ( 'crownimport' )->setImportModel($importModel);
			
			Mage::getSingleton ( 'adminhtml/session' )->setHpImportFormData ( array (
				'import_title' => $category->getName (), //Default title is the event name
				'category_id' => $category->getId (), 
				'category_name' => $category->getName (), 
				'po_id' => $defaultPoId, 
				'vendor_code' => $defaultVendorCode 
			) );
		} else {
			Mage::getSingleton ( 'adminhtml/session' )->addError ( Mage::helper ( 'crownimport' )->__ ( 'Invalid Category ID' ) );
			$this->_redirect ( '*/*/new');
			return;
		}
		$this->_redirect ( '*/*/edit', array ('id' => $importModel->getId() ) );
		return;
	}
	
	/**
	 * Loads a profile to be edited or continued.
	 * @since 1.0.0
	 * @return void
	 */
	public function editAction() {
		$model = $this->_initImportModel ()->statusCheck();
		if (! $model->getId ()) {
			Mage::getSingleton ( 'adminhtml/session' )->addError ( Mage::helper ( 'crownimport' )->__ ( 'Invalid ID' ) );
			$this->_redirect ( '*/*/' );
			return;
		}
		
		$this->loadLayout ();
		$this->_addContent ( $this->getLayout ()
			->createBlock ( 'crownimport/adminhtml_import_edit' ) )
			->_addLeft ( $this->getLayout ()->createBlock ( 'crownimport/adminhtml_import_edit_tabs' )->setStep($model->getStep()) );
		Mage::getSingleton ( 'adminhtml/session' )->setHpImportFormData ( null );
		$this->_initLayoutMessages ( 'adminhtml/session' );
		$this->renderLayout ();
	}
	
	/**
	 * Handles all save request.
	 * @since 1.0.0
	 * @return void
	 */
	public function saveAction() {
		$importModel = $this->_initImportModel()->statusCheck();
		switch ($importModel->getStep()) {
			case 'validation':
				if (!is_null($importModel->getUrapidflowProfileId())) {
					$profile = $importModel->getUrapidflowProfile();
					if ($profile->getRowsErrors() > 0) {
						Mage::getSingleton ( 'adminhtml/session' )->addError ( Mage::helper ( 'crownimport' )->__ ( 'Errors found in the import. Please correct then try again.' ) );
						$importModel->setData('step', 'import')->save();
						$this->_redirect ( '*/*/edit', array ('id' => $importModel->getId() ) );
						return;
					} else {
						$importModel->setStatus(Crown_Import_Model_Importhistory::IMPORT_STATUS_RUNNING)->save();
						$this->runProductImport();
						$importModel->setData('step', 'product-import')->save();
						$this->_redirect ( '*/*/edit', array ('id' => $importModel->getId() ) );
						return;
					}
				} else{
					Mage::getSingleton ( 'adminhtml/session' )->addError ( Mage::helper ( 'crownimport' )->__ ( 'Unable to initate uRapidFlow Profile.' ) );
				}
				$this->_redirect ( '*/*/' );
				return;
				break;
			case 'product-import':
				if ( 1 == $importModel->getData('has_configurable') ) {
					$importModel->setData('step', 'product-extra-import')->save();
					$importModel->setStatus(Crown_Import_Model_Importhistory::IMPORT_STATUS_RUNNING)->save();
					$this->runProductExtraImport();
					$importModel->setData('step', 'product-extra-import')->save();
					$this->_redirect ( '*/*/edit', array ('id' => $importModel->getId() ) );
					return;
				} else {
					$importModel
						->setData('step', 'complete')
						->setData('status', Crown_Import_Model_Importhistory::IMPORT_STATUS_COMPLETE)
						->save();
					Mage::getSingleton ( 'adminhtml/session' )->addSuccess ( Mage::helper ( 'crownimport' )->__ ( 'Import complete' ) );
					$this->_redirect ( '*/*/' );
					return;
				}
				$this->_redirect ( '*/*/edit', array ('id' => $importModel->getId() ) );
				return;
				break;
			case 'product-extra-import':
				$importModel
					->setData('step', 'complete')
					->setData('status', Crown_Import_Model_Importhistory::IMPORT_STATUS_COMPLETE)
					->save();
				Mage::getSingleton ( 'adminhtml/session' )->addSuccess ( Mage::helper ( 'crownimport' )->__ ( 'Import complete' ) );
				$this->_redirect ( '*/*/' );
				return;
				break;
			case 'complete':
				Mage::getSingleton ( 'adminhtml/session' )->addSuccess ( Mage::helper ( 'crownimport' )->__ ( 'Import complete' ) );
				$this->_redirect ( '*/*/' );
				return;
			case 'import';
			default:
				$importModel->setStatus(Crown_Import_Model_Importhistory::IMPORT_STATUS_RUNNING)->save();
				$this->importFile();
		}
	}
	
	/**
	 * Runs the product import.
	 * @since 1.0.0
	 * @return void
	 */
	protected function runProductImport() {
		$importModel = $this->_initImportModel();

		try {
			$profile = $importModel->getUrapidflowProfile();
		} catch (Exception $e) {
			Mage::getSingleton ( 'adminhtml/session' )->addError ( Mage::helper ( 'crownimport' )->__ ( $e->getMessage() ) );
			$this->_redirect ( '*/*/' );
			return;
		}
		
		$profileOptions = $profile->getOptions();
		$profileOptions['import']['dryrun'] = '0';
		$profile->setOptions($profileOptions);
		$profile = $profile->factory ();
		
		if ($profile->getCreatedTime == NULL || $profile->getUpdateTime () == NULL) {
			$profile->setCreatedTime ( now () )->setUpdateTime ( now () );
		} else {
			$profile->setUpdateTime ( now () );
		}
		
		$profile->save ();
		
		try { $profile->stop(); } catch (Exception $e) { };
        $profile->pending('ondemand')->save();
		
		Mage::getSingleton ( 'adminhtml/session' )->addSuccess ( Mage::helper ( 'urapidflow' )->__ ( 'Profile started successfully' ) );
	}
	
	/**
	 * Runs the product extra import.
	 * @since 1.0.0
	 * @return void
	 */
	protected function runProductExtraImport() {
		$importModel = $this->_initImportModel();
		
		try {
			$profile = $importModel->getUrapidflowProfileProductExtra();
		} catch (Exception $e) {
			Mage::getSingleton ( 'adminhtml/session' )->addError ( Mage::helper ( 'crownimport' )->__ ( $e->getMessage() ) );
			$this->_redirect ( '*/*/' );
			return;
		}
		
		$profile = $profile->factory ();
		
		if ($profile->getCreatedTime == NULL || $profile->getUpdateTime () == NULL) {
			$profile->setCreatedTime ( now () )->setUpdateTime ( now () );
		} else {
			$profile->setUpdateTime ( now () );
		}
		
		$profile->save ();
		
		try { $profile->stop(); } catch (Exception $e) { };
        $profile->pending('ondemand')->save();
        
		Mage::getSingleton ( 'adminhtml/session' )->addSuccess ( Mage::helper ( 'urapidflow' )->__ ( 'Profile started successfully' ) );
	}
	
	/**
	 * Uploads the CSV from the user and converts it to a usable file. Runs validation.
	 * @since 1.0.0
	 * @return void
	 */
	protected function importFile() {
		/* @var $importModel Crown_Import_Model_Importhistory */
		$importModel = $this->_initImportModel();
		$data = $this->getRequest ()->getPost ();
		$postDataObject = new Varien_Object ( $data );
		
		// Verify data exist
		if (! $data) {
			Mage::getSingleton ( 'adminhtml/session' )->addError ( Mage::helper ( 'crownimport' )->__ ( 'Nothing to save.' ) );
			$this->_redirect ( '*/*/edit', array ('id' => $importModel->getId() ) );
			return;
		}
		
		// Database validation fix because of contstraints... cannot be 0.
		if ( 0 == $data['po_id']) {
			unset($data['po_id']);
		}

		// Load category model
		$category = Mage::getModel ( 'catalog/category' )->load ( $postDataObject->getdata ( 'category_id' ) );
		
		// Database validation fix because of contstraints... cannot be 0.
		if (! $category->getId ()) {
			unset($data['category_id']);
		}
		
		// Import model has been created for this session
		$importModel->addData($data)->save();
		Mage::helper ( 'crownimport' )->setImportModel($importModel);
		
		// Verify a uRapidFlowProfile is chosen
		/* @var $profile Unirgy_RapidFlow_Model_Profile */
		try {
			$profile = $importModel->getUrapidFlowProfile ();
		} catch (Exception $e) {
			Mage::getSingleton ( 'adminhtml/session' )->addError ( Mage::helper ( 'crownimport' )->__ ( $e->getMessage() ) );
			$this->_redirect ( '*/*/edit', array ('id' => $importModel->getId() ) );
			return;
		}
		
		// Verify Category/Event
		if (! $category->getId ()) {
			Mage::getSingleton ( 'adminhtml/session' )->addError ( Mage::helper ( 'crownimport' )->__ ( 'Invalid Category/Event' ) );
			$this->_redirect ( '*/*/edit', array ('id' => $importModel->getId() ) );
			return;
		}

		// Load/Create PO
		$purchaseOrder = Mage::getModel ( 'stockhistory/purchaseorder' );
		if (! ! $postDataObject->getdata ( 'po_id' ) && $postDataObject->getdata ( 'po_id' ) > 0) {
			$purchaseOrder->load ( $postDataObject->getdata ( 'po_id' ) );
			$vendor = Mage::getModel ( 'stockhistory/vendor' )->load ( $purchaseOrder->getVendorId () );
		} else {
			//Load vendor
			$vendor = Mage::getModel ( 'stockhistory/vendor' );
			if (! ! $postDataObject->getdata ( 'vendor_id' )) {
				$vendor->load ( $postDataObject->getdata ( 'vendor_id' ) );
			} elseif (! ! $postDataObject->getdata ( 'vendor_code' )) {
				$vendor->loadByCode ( $postDataObject->getdata ( 'vendor_code' ) );
			}
			if (! $vendor || ! $vendor->getId ()) {
				Mage::throwException ( 'Invalid Vendor.' );
			}
			
			$purchaseOrderDataObj = new Varien_object ();
			$purchaseOrderDataObj->setData ( 'vendor_id', $vendor->getId () );
			$purchaseOrderDataObj->setData ( 'vendor_code', $vendor->getVendorCode () );
			$purchaseOrderDataObj->setData ( 'name', $postDataObject->getData ( 'import_title' ) );
			$purchaseOrderDataObj->setData ( 'category_id', $category->getId () );
			$purchaseOrderDataObj->setData ( 'comment', 'Category/Event Import ' . date ( 'Y-n-j H:i:s' ) );
			$purchaseOrder->importData ( $purchaseOrderDataObj->getData () )->save ();
			$ponumber = strtoupper ( substr ( $vendor->getVendorCode (), 0, 3 ) ) . $purchaseOrder->generatePoNumber ();
			$savedData = $purchaseOrder->getData ();
			$savedData ['po_number'] = $ponumber;
			$purchaseOrder->importData ( $savedData )->save ();
			Mage::getSingleton ( 'adminhtml/session' )->addNotice ( Mage::helper ( 'crownimport' )->__ ( 'New PO created during import: ' . $purchaseOrder->getData ( 'name' ) ) );
		}
		
		// Update import history model
		$importModel
			->setImportTitle($postDataObject->getData ( 'import_title' ))
			->setImportFilename($_FILES ['import_filename'] ['name'])
			->setVendorId($vendor->getId ())
			->setVendorCode($vendor->getVendorCode ())
			->setPoId($purchaseOrder->getId())
			->setUpdatedAt(now())
			->setCategoryId($category->getId ())
			->save();
			
		try {
			/* @var $importHandler Crown_Import_Model_Productimport */
			$importHandler = Mage::getModel ( 'crownimport/productimport' );
			$importHandler->setSourceFile ( $_FILES ['import_filename'] ['tmp_name'] );
			$importHandler->setFilename ( pathinfo ( $_FILES ['import_filename'] ['name'], PATHINFO_FILENAME ) );
			$importHandler->setProductExtraFilename( pathinfo ( $_FILES ['import_filename'] ['name'], PATHINFO_FILENAME ) . '_product_extra' );
			$importHandler->setDefaultProductCategoryId ( $category->getId () );
			$importHandler->setDefaultProductVendorCode ( $vendor->getVendorCode () );
			$importHandler->setDefaultProductVendorId ( $vendor->getId () );
			$importHandler->setDefaultProductPoId ( $purchaseOrder->getId () );
			$importHandler->setFileBaseDir ( $profile->getFileBaseDir () );
			$importHandler->run ();
			
			if ( $importHandler->getHasConfigurableProducts() ){
				$importModel->setHasConfigurable(true)->save();
				try {
					$profileProductExtra = $importModel->getUrapidflowProfileProductExtra();
					$profileProductExtra->setFilename($importHandler->getProductExtraFilename());
					$profileProductExtra->save();
				} catch (Exception $e) {
					Mage::getSingleton ( 'adminhtml/session' )->addError ( Mage::helper ( 'crownimport' )->__ ( $e->getMessage() ) );
					$this->_redirect ( '*/*/edit', array ('id' => $importModel->getId() ) );
					return;
				}
			}
			
			//Data preparation
			try {
				$profile->setFilename ( $importHandler->getFilename () );
				$profileOptions = $profile->getOptions();
				$profileOptions['import']['dryrun'] = '1';
				$profile->setOptions($profileOptions);
				$profile = $profile->factory ();
				
				if ($profile->getCreatedTime == NULL || $profile->getUpdateTime () == NULL) {
					$profile->setCreatedTime ( now () )->setUpdateTime ( now () );
				} else {
					$profile->setUpdateTime ( now () );
				}
				
				$profile->save ();
				
				try { $profile->stop(); } catch (Exception $e) { };
		        $profile->pending('ondemand')->save();
		        
				$importModel->setData('step', 'validation')->save();
				
				Mage::getSingleton ( 'adminhtml/session' )->addSuccess ( Mage::helper ( 'urapidflow' )->__ ( 'Profile started successfully' ) );
				
				$this->_redirect ( '*/*/edit', array ('id' => $importModel->getId() ) );
				return;
			
			} catch ( Exception $e ) {
				Mage::getSingleton ( 'adminhtml/session' )->addError ( $e->getMessage () );
				Mage::getSingleton ( 'adminhtml/session' )->setHpImportFormData ( $data );
				$this->_redirect ( '*/*/edit', array ('id' => $importModel->getId() ) );
				return;
			}
		
		} catch ( Mage_Core_Exception $mageE ) {
			Mage::getSingleton ( 'adminhtml/session' )->addError ( Mage::helper ( 'crownimport' )->__ ( $mageE->getMessage () ) );
			$this->_redirect ( '*/*/edit', array ('id' => $importModel->getId() ) );
			return;
		} catch ( Exception $e ) {
			Mage::getSingleton ( 'adminhtml/session' )->addError ( $e->getMessage () );
			Mage::getSingleton ( 'adminhtml/session' )->setHpImportFormData ( $data );
			$this->_redirect ( '*/*/edit', array ('id' => $importModel->getId() ) );
			return;
		}
	}
	
	/**
	 * Mass delete of import history action
	 * @since 1.0.0
	 * @return void
	 */
	public function massDeleteAction() {
		$importIds = $this->getRequest ()->getParam ( 'import' );
		if (! is_array ( $importIds )) {
			Mage::getSingleton ( 'adminhtml/session' )->addError ( Mage::helper ( 'adminhtml' )->__ ( 'Please select item(s)' ) );
		} else {
			try {
				foreach ( $importIds as $importId ) {
					$import = Mage::getModel ( 'import/importhistory' )->load ( $importId );
					if ( $import->getUrapidflowProfileId() )
						Mage::getModel('urapidflow/profile')->load($import->getUrapidflowProfileId())->delete();
					if ( $import->getUrapidflowProfileIdProductExtra() )
						Mage::getModel('urapidflow/profile')->load($import->getUrapidflowProfileIdProductExtra())->delete();
					$import->delete ();
				}
				Mage::getSingleton ( 'adminhtml/session' )->addSuccess ( Mage::helper ( 'adminhtml' )->__ ( 'Total of %d record(s) were successfully deleted', count ( $importIds ) ) );
			} catch ( Exception $e ) {
				Mage::getSingleton ( 'adminhtml/session' )->addError ( $e->getMessage () );
			}
		}
		$this->_redirect ( '*/*/index' );
	}

}