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
		
		$importModel = Mage::helper ( 'import' )->getImportModel ();
		if (Crown_Import_Model_Importhistory::IMPORT_STATUS_RUNNING == $importModel->getStatus () || Crown_Import_Model_Importhistory::IMPORT_STATUS_COMPLETE == $importModel->getStatus ()) {
			$this->removeButton ( 'save' );
			$this->removeButton ( 'reset' );
		} else {
			$this->_updateButton ( 'save', 'label', Mage::helper ( 'import' )->__ ( 'Next' ) );
			
			if ( !in_array($importModel->getStatus (), array('import','')) ) {
				$this->_updateButton ( 'reset', 'label', Mage::helper ( 'import' )->__ ( 'Restart' ) );
				$this->_updateButton ( 'reset', 'onclick', 'setLocation(\'' . $this->getUrl ( '*/*/reset', array ($this->_objectId => $this->getRequest ()->getParam ( $this->_objectId ) ) ) . '\')' );
			} else {
				$this->removeButton ( 'reset' );
			}
		}
		
		$this->_updateButton ( 'delete', 'label', Mage::helper ( 'import' )->__ ( 'Delete' ) );
        
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
		$importModel = Mage::helper ( 'import' )->getImportModel ();
		if ($importModel->getId ()) {
			return Mage::helper ( 'import' )->__ ( "Event Import '%s' (ID: {$importModel->getId()})", $this->htmlEscape ( $importModel->getImportTitle () ) );
		} else {
			return Mage::helper ( 'import' )->__ ( 'Event Import' );
		}
	}
}