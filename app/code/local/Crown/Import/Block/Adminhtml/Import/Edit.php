<?php
/**
 * 
 * @category 	Crown
 * @package 	Crown_Import 
 * @since 		1.0.0
 */
class Crown_Import_Block_Adminhtml_Import_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {
	
	/**
	 * Assigns objects and buttons
	 * @since 1.0.0
	 * @return void
	 */
	public function __construct() {
		parent::__construct ();
		
		$this->_objectId = 'id';
		$this->_blockGroup = 'import';
		$this->_controller = 'adminhtml_import';
		
		$importModel = Mage::helper ( 'crownimport' )->getImportModel ();
		
		// Remove the back button while it's running
		if (Crown_Import_Model_Importhistory::IMPORT_STATUS_RUNNING == $importModel->getStatus ()) {
			$this->removeButton ( 'back' );
		}
		
		// Remove buttons while running or the profile has already been completed
		if (Crown_Import_Model_Importhistory::IMPORT_STATUS_RUNNING == $importModel->getStatus () || Crown_Import_Model_Importhistory::IMPORT_STATUS_COMPLETE == $importModel->getStatus ()) {
			$this->removeButton ( 'save' );
			$this->removeButton ( 'reset' );
			$this->removeButton ( 'delete' );
			$this->removeButton ( 'reset' );
		} else {
			
			// If still in the import or validation step
			if (in_array ( $importModel->getStep (), array ('import', '', 'validation' ) )) {
				
				// Set buttons based off of urapid flow status
				if (! is_null ( $importModel->getUrapidflowProfileId () ) && 'validation' == $importModel->getStep() ) {
					$profile = $importModel->getUrapidflowProfile ();
					if ($profile->getRowsErrors () > 0) {
						Mage::getSingleton ( 'adminhtml/session' )->addError ( Mage::helper ( 'crownimport' )->__ ( 'Errors found in the import. Please correct them then try again.' ) );
						$this->_updateButton ( 'save', 'label', Mage::helper ( 'crownimport' )->__ ( 'Restart' ) );
						$this->_updateButton ( 'save', 'onclick', 'setLocation(\'' . $this->getUrl ( '*/*/reset', array ($this->_objectId => $this->getRequest ()->getParam ( $this->_objectId ) ) ) . '\')' );
						$this->removeButton ( 'reset' );
					} else {
						$this->_updateButton ( 'save', 'label', Mage::helper ( 'crownimport' )->__ ( 'Next' ) );
						$this->_updateButton ( 'reset', 'label', Mage::helper ( 'crownimport' )->__ ( 'Restart' ) );
						$this->_updateButton ( 'reset', 'onclick', 'setLocation(\'' . $this->getUrl ( '*/*/reset', array ($this->_objectId => $this->getRequest ()->getParam ( $this->_objectId ) ) ) . '\')' );
						
					}
				// Set button to next if profile not set or not on validation step
				} else {
					$this->_updateButton ( 'save', 'label', Mage::helper ( 'crownimport' )->__ ( 'Next' ) );
					$this->removeButton ( 'reset' );
				}
			// Set default buttons for all other steps and status
			} else {
				$this->_updateButton ( 'save', 'label', Mage::helper ( 'crownimport' )->__ ( 'Next' ) );
				$this->removeButton ( 'delete' );
				$this->removeButton ( 'reset' );
			}
		}
		
        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('import_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'import_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'import_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }
	
    /**
     * (non-PHPdoc)
     * @see Mage_Adminhtml_Block_Widget_Container::getHeaderText()
     */
	public function getHeaderText() {
		$importModel = Mage::helper ( 'crownimport' )->getImportModel ();
		if ($importModel->getImportTitle ()) {
			return Mage::helper ( 'crownimport' )->__ ( "Event Import '%s' (ID: {$importModel->getId()})", $this->htmlEscape ( $importModel->getImportTitle () ) );
		} else {
			return Mage::helper ( 'crownimport' )->__ ( 'New Event Import' );
		}
	}
}