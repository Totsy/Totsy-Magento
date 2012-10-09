<?php
/**
 * 
 * @category 	Crown
 * @package 	Crown_Import 
 * @since 		1.0.0
 */
class Crown_Import_Block_Adminhtml_Import_Edit_Form extends Mage_Adminhtml_Block_Widget_Form {
	
	/**
	 * (non-PHPdoc)
	 * @see Mage_Adminhtml_Block_Widget_Form::_prepareForm()
	 */
	protected function _prepareForm() {
		$form = new Varien_Data_Form ( array (
			'id' => 'edit_form', 
			'action' => $this->getUrl ( '*/*/save', array ('id' => $this->getRequest ()->getParam ( 'id' ) ) ), 
			'method' => 'post', 
			'enctype' => 'multipart/form-data' 
		));
		
		$form->setUseContainer ( true );
		$this->setForm ( $form );
		return parent::_prepareForm ();
	}
}