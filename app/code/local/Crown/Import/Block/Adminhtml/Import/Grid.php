<?php
/**
 * 
 * @category 	Crown
 * @package 	Crown_Import 
 * @since 		1.0.0
 */
class Crown_Import_Block_Adminhtml_Import_Grid extends Mage_Adminhtml_Block_Widget_Grid {
	
	/**
	 * 
	 * @since 1.0.0
	 * @return void
	 */
	public function __construct() {
		parent::__construct ();
		$this->setId ( 'importGrid' );
		$this->setDefaultSort ( 'import_id' );
		$this->setDefaultDir ( 'DESC' );
		$this->setSaveParametersInSession ( true );
	}
	/**
	 * (non-PHPdoc)
	 * @see Mage_Adminhtml_Block_Widget_Grid::_prepareCollection()
	 */
	protected function _prepareCollection() {
		$res = Mage::getSingleton ( 'core/resource' );
		$eav = Mage::getModel ( 'eav/config' );
		$nameattr = $eav->getAttribute ( 'catalog_category', 'name' );
		$nametable = $res->getTableName ( 'catalog/category' ) . '_' . $nameattr->getBackendType ();
		$nameattrid = $nameattr->getAttributeId ();
		
		/* @var $collection Crown_Import_Model_Mysql4_Importhistory_Collection */
		$collection = Mage::getModel ( 'crownimport/importhistory' )->getCollection ();
		$collection->getSelect()->joinLeft( $nametable, "entity_id=category_id AND attribute_id={$nameattrid}", array ('category_name' => 'value' ));
		$this->setCollection ( $collection );
		return parent::_prepareCollection ();
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Mage_Adminhtml_Block_Widget_Grid::_prepareColumns()
	 */
	protected function _prepareColumns() {
		$this->addColumn ( 'import_id', array (
			'header' => Mage::helper ( 'crownimport' )->__ ( 'ID' ), 
			'align' => 'right', 
			'width' => '50px', 
			'index' => 'import_id' 
		));
		
		$this->addColumn ( 'category_id', array (
			'header' => Mage::helper ( 'crownimport' )->__ ( 'Category ID' ), 
			'align' => 'left', 
			'width' => '50px', 
			'index' => 'category_id' 
		));
		
		$this->addColumn ( 'category_name', array (
			'header' => Mage::helper ( 'crownimport' )->__ ( 'Category Name' ), 
			'align' => 'left', 
			'index' => 'category_name' 
		));
		
		$this->addColumn ( 'import_title', array (
			'header' => Mage::helper ( 'crownimport' )->__ ( 'Title' ), 
			'align' => 'left', 
			'index' => 'import_title' 
		));
		
		$this->addColumn ( 'import_filename', array (
			'header' => Mage::helper ( 'crownimport' )->__ ( 'Filename' ), 
			'align' => 'left', 
			'index' => 'import_filename'
		));
		
		$this->addColumn ( 'status', array (
			'header' => Mage::helper ( 'crownimport' )->__ ( 'Status' ), 
			'align' => 'left', 
			'index' => 'status',
			'width' => '100px', 
			'type'  => 'options',
			'options' => Mage::getModel ( 'crownimport/importhistory' )->getGridStatusArray(),
		));
		
		$this->addColumn ( 'created_at', array (
			'header' => Mage::helper ( 'crownimport' )->__ ( 'Created' ), 
			'align' => 'left', 
			'width' => '150px', 
			'index' => 'created_at' 
		));
		
		$this->addColumn ( 'updated_at', array (
			'header' => Mage::helper ( 'crownimport' )->__ ( 'Updated' ), 
			'align' => 'left', 
			'width' => '150px',
			'index' => 'updated_at' 
		));
		
		return parent::_prepareColumns ();
	}
	
	/**
     * Prepare grid massaction actions
     * @since 1.0.0
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
	protected function _prepareMassaction() {
		$this->setMassactionIdField ( 'import_id' );
		$this->getMassactionBlock ()->setFormFieldName ( 'import' );
		
		$this->getMassactionBlock ()->addItem ( 'delete', array (
			'label' => Mage::helper ( 'import' )->__ ( 'Delete' ), 
			'url' => $this->getUrl ( '*/*/massDelete' ), 
			'confirm' => Mage::helper ( 'import' )->__ ( 'Are you sure?' ) 
		));
		return $this;
	}
	
	/**
     * Return row url for js event handlers
     * @param Crown_Import_Model_Importhistory $row
     * @since 1.0.0
     * @return string
     */
	public function getRowUrl($row) {
        $row->statusCheck();
		if ( !$row->getUrapidflowProfileId() && Crown_Import_Model_Importhistory::IMPORT_STATUS_COMPLETE == $row->getStatus() ) {
			return $this->getUrl ( '*/*/profilemessage', array ('mid' => 1 ) );
		} elseif( Crown_Import_Model_Importhistory::IMPORT_STATUS_RUNNING == $row->getStatus() ) {
			return $this->getUrl ( '*/*/profilemessage', array ('mid' => 2 ) );
		} else {
			return $this->getUrl ( '*/*/edit', array ('id' => $row->getId () ) );
		}
	}
}
