<?php
/**
 * 
 * @category 	Crown
 * @package 	Crown_Import 
 * @since 		1.0.0
 */
class Crown_Import_Block_Adminhtml_Import_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {
	
	/**
	 * 
	 */
	public function __construct() {
		parent::__construct ();
		$this->setId ( 'import_tabs' );
		$this->setDestElementId ( 'edit_form' );
		$this->setTitle ( Mage::helper ( 'import' )->__ ( 'Import Information' ) );
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Mage_Adminhtml_Block_Widget_Tabs::_beforeToHtml()
	 */
	protected function _beforeToHtml() {
		$importModel = Mage::helper('import')->getImportModel();
		switch ($this->getStep()) {
			case 'validation' :
				$profile = $importModel->getUrapidflowProfile();
				$this->addTab ( 'validate_section', array (
					'label' => Mage::helper ( 'import' )->__ ( 'Validating Import' ), 
					'title' => Mage::helper ( 'import' )->__ ( 'Validating Import' ), 
					'content' => $this->getLayout ()->createBlock ( 'import/adminhtml_profile_status' )
						->setProfile($profile)
						->setImportModel($importModel)
						->setCategoryId($importModel->getCategoryId())
						->toHtml (), 
				));
				$this->setActiveTab('validate_section');
				break;
			case 'product-import':
				$profile = $importModel->getUrapidflowProfile();
				$this->addTab ( 'running_product_section', array (
					'label' => Mage::helper ( 'import' )->__ ( 'Running Product Import' ), 
					'title' => Mage::helper ( 'import' )->__ ( 'Running Product Import' ), 
					'content' => $this->getLayout ()->createBlock ( 'import/adminhtml_profile_status' )
						->setProfile($profile)
						->setImportModel($importModel)
						->setCategoryId($importModel->getCategoryId())
						->toHtml (), 
				));
				$this->setActiveTab('running_product_section');
				break;
			case 'product-extra-import':
				$profile = $importModel->getUrapidflowProfileProductExtra();
				$this->addTab ( 'running_product_extra_section', array (
					'label' => Mage::helper ( 'import' )->__ ( 'Running Product Extra Import' ), 
					'title' => Mage::helper ( 'import' )->__ ( 'Running Product Extra Import' ), 
					'content' => $this->getLayout ()->createBlock ( 'import/adminhtml_profile_status' )
						->setProfile($profile)
						->setImportModel($importModel)
						->setCategoryId($importModel->getCategoryId())
						->toHtml (), 
				));
				$this->setActiveTab('running_product_extra_section');
				break;
			case 'complete':
				
				if ($profile = $importModel->getUrapidflowProfile()) {
					$this->addTab ( 'running_product_section', array (
						'label' => Mage::helper ( 'import' )->__ ( 'Product Import' ), 
						'title' => Mage::helper ( 'import' )->__ ( 'Product Import' ), 
						'content' => $this->getLayout ()->createBlock ( 'import/adminhtml_profile_status' )
							->setProfile($profile)
							->setImportModel($importModel)
							->setCategoryId($importModel->getCategoryId())
							->toHtml (), 
					));
				}
				
				if ( $importModel->getHasConfigurable() && ( $profileExtra = $importModel->getUrapidflowProfileProductExtra()) ) {
					$this->addTab ( 'running_product_extra_section', array (
						'label' => Mage::helper ( 'import' )->__ ( 'Product Extra Import' ), 
						'title' => Mage::helper ( 'import' )->__ ( 'Product Extra Import' ), 
						'content' => $this->getLayout ()->createBlock ( 'import/adminhtml_profile_status' )
							->setProfile($profileExtra)
							->setImportModel($importModel)
							->setCategoryId($importModel->getCategoryId())
							->toHtml (), 
					));
				}
				
				$category = Mage::getModel('catalog/category')->load($importModel->getCategoryId());
				
				$this->addTab ( 'complete_section', array (
						'label' => Mage::helper ( 'import' )->__ ( 'Complete' ), 
						'title' => Mage::helper ( 'import' )->__ ( 'Complete' ), 
						'content' => $this->getLayout ()->createBlock ( 'import/adminhtml_import_complete' )
							->setImportModel($importModel)
							->setCategory($category)
							->toHtml (),
					));
				$this->setActiveTab('complete_section');
				break;
			default:
				$this->addTab ( 'form_section', array (
					'label' => Mage::helper ( 'import' )->__ ( 'Import File' ), 
					'title' => Mage::helper ( 'import' )->__ ( 'Import File' ), 
					'content' => $this->getLayout ()->createBlock ( 'import/adminhtml_import_edit_tab_form' )->toHtml () 
				));
		}
		
		return parent::_beforeToHtml ();
	}
}