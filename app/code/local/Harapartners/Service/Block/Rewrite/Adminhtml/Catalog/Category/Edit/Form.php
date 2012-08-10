<?php

/*
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User Software Agreement (EULA).
 * It is also available through the world-wide-web at this URL:
 * http://www.harapartners.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to eula@harapartners.com so we can send you a copy immediately.
 * 
 */

class Harapartners_Service_Block_Rewrite_Adminhtml_Catalog_Category_Edit_Form extends Mage_Adminhtml_Block_Catalog_Category_Edit_Form {
    
    // Get current edit store HP Yang
    protected function _getAdminStore(){
        return Mage::app()->getRequest()->getParam('store', 0);
    }
    protected function _getPreviewStore(){
        return Mage::app()->getRequest()->getParam('store', 1);
    }

    protected function _prepareLayout(){
        parent::_prepareLayout();
        $category = $this->getCategory();
        $categoryId = (int) $category->getId(); // 0 when we create category, otherwise some value for editing category
        $expiredEventCategory = Mage::getModel('categoryevent/sortentry')->getParentCategory('Expired Events',Mage::app()->getStore()->getId());
        
        //Haraparnters, Jun/Yang
        if (!in_array($categoryId, $this->getRootIds())) {
            // Release button HP Yang
            $this->setChild('release_button',
                $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setData(array(
                        'label'     => Mage::helper('catalog')->__('Disable Products'),
                        //'onclick'   => "setLocation('" . $this->getUrl('*/*/release', array('_current' => true, 'store' => $this->_getAdminStore(), 'con' => Mage_Catalog_Model_Product_Status::STATUS_DISABLED)) . "')",
                        'onclick'	=> "if(confirm('Are you sure to disable all products within this event?')) {setLocation('" . $this->getUrl('*/*/release', array('_current' => true, 'store' => $this->_getAdminStore(), 'con' => Mage_Catalog_Model_Product_Status::STATUS_DISABLED)) . "')}",
                        'class' => 'delete'
                    ))
            );
            // Undo Release button HP Yang
            $this->setChild('undo_release_button',
                $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setData(array(
                        'label'     => Mage::helper('catalog')->__('Enable Products'),
                        //'onclick'   => "setLocation('" . $this->getUrl('*/*/release', array('_current' => true, 'store' => $this->_getAdminStore(), 'con' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED)) . "')",
                        'onclick'	=> "if(confirm('Are you sure to enable all products within this event')) {setLocation('" . $this->getUrl('*/*/release', array('_current' => true, 'store' => $this->_getAdminStore(), 'con' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED)) . "')}",
                        'class' => 'release'
                    ))
            );
            // Preview button HP Yang
            $this->setChild('event_preveiw_button',
                $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setData(array(
                        'label'     => Mage::helper('catalog')->__('Preview Event'),
                        'onclick'   => "window.open('" . $this->getUrl('*/*/preview', array('_current' => true, 'store' => $this->_getPreviewStore())) . "')",
                        'class' => 'preview'
                    ))
            );
            
            if($category->getParentId() == $expiredEventCategory->getId()) {
                $this->setChild('revert_move',
                $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setData(array(
                        'label'     => Mage::helper('catalog')->__('Revert Event Move'),
                        'onclick'   => "if(confirm('You sure want to move this event back to the \'Events\' folder?')) {setLocation('" . $this->getUrl('*/*/clearExpiredEvents', array('revert' => true,'category_id' => $categoryId)) . "')}"
                    ))
                );
            }          
            //Jun import products
            $this->setChild('import_product_button',
                $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setData(array(
                        'label'     => Mage::helper('catalog')->__('Import Products'),
                        'onclick'   => "setLocation('" . $this->getUrl('import/adminhtml_import/newByCategory', array('category_id' => $categoryId)) . "')",
                        'class' => 'add'
                    ))
            );
            
            
            //Harapartners Li Lu
            $this->setChild('delete_product_button',
                $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setData(array(
                        'label'     => Mage::helper('catalog')->__('Delete Products'),
                        'onclick'   => "if(confirm('The selected products will be deleted PERMANENTLY, are you sure?')) {deleteProductsSubmit();}",
                        'class' => 'delete'
                    ))
            );
        }
        
        return $this;
    }

    
    // Release button HP Yang
    public function getReleaseButtonHtml()
    {
        if ($this->hasStoreRootCategory()) {
            return $this->getChildHtml('release_button');
        }
        return '';
    } 
    
    // Undo Release button HP Yang
    public function getUndoReleaseButtonHtml()
    {
        if ($this->hasStoreRootCategory()) {
            return $this->getChildHtml('undo_release_button');
        }
        return '';
    }
    
    // Undo Release button HP Yang
    public function getPreviewButtonHtml()
    {
        if ($this->hasStoreRootCategory()) {
            return $this->getChildHtml('event_preveiw_button');
        }
        return '';
    }

    public function getRevertMoveButtonHtml()
    {
        if ($this->hasStoreRootCategory()) {
            return $this->getChildHtml('revert_move');
        }
        return '';
    }
    
    // Harapartners, Jun, Import products
    public function getImportProductButtonHtml()
    {
        if ($this->hasStoreRootCategory()) {
            return $this->getChildHtml('import_product_button');
        }
        return '';
    }
    
	public function getDeleteProductsButtonHtml()
    {
        $category = $this->getCategory();

	  	$defaultTimezone = date_default_timezone_get();
		$mageTimezone = Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE);
		date_default_timezone_set($mageTimezone);
		$now = now();
		date_default_timezone_set($defaultTimezone);
		
		$startcount_utc = strtotime($category->getEventStartDate());
		$startcount_lc = date("F j, Y, G:i:s", $startcount_utc);
		
		if (strtotime($now) <= strtotime( $startcount_lc )){
	        if ($this->hasStoreRootCategory()) {
	            return $this->getChildHtml('delete_product_button');
	        }
		}
        return '';
    }

  
}
