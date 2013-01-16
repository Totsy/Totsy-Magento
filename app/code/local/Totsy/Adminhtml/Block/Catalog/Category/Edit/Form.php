<?php
/**
 * @category    Totsy
 * @package     Totsy_Adminhtml_Block
 * @author      Tharsan Bhuvanendran <tbhuvanendran@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

class Totsy_Adminhtml_Block_Catalog_Category_Edit_Form
    extends Mage_Adminhtml_Block_Catalog_Category_Edit_Form
{
    protected function _getAdminStore()
    {
        return Mage::app()->getRequest()->getParam('store', 0);
    }

    protected function _getPreviewStore()
    {
        return Mage::app()->getRequest()->getParam('store', 1);
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $category = $this->getCategory();

        // 0 when we create category, otherwise some value for editing category
        $categoryId = (int) $category->getId();
        $expiredEventCategory = Mage::getModel('categoryevent/sortentry')->getParentCategory(
            'Expired Events',
            Mage::app()->getStore()->getId()
        );

        // Harapartners, Jun/Yang
        if (!in_array($categoryId, $this->getRootIds())) {
            // Release button HP Yang
            $button = $this->getLayout()->createBlock('adminhtml/widget_button')->setData(
                array(
                    'label'     => Mage::helper('catalog')->__('Disable Products'),
                    'onclick'   => "if(confirm('Are you sure to disable all products within this event?')) {setLocation('" . $this->getUrl('*/*/release', array('_current' => true, 'store' => $this->_getAdminStore(), 'con' => Mage_Catalog_Model_Product_Status::STATUS_DISABLED)) . "')}",
                    'class'     => 'delete'
                )
            );
            $this->setChild('release_button', $button);

            // Undo Release button HP Yang
            $button = $this->getLayout()->createBlock('adminhtml/widget_button')->setData(
                array(
                    'label'     => Mage::helper('catalog')->__('Enable Products'),
                    'onclick'   => "if(confirm('Are you sure to enable all products within this event')) {setLocation('" . $this->getUrl('*/*/release', array('_current' => true, 'store' => $this->_getAdminStore(), 'con' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED)) . "')}",
                    'class'     => 'release'
                )
            );
            $this->setChild('undo_release_button', $button);

            // Preview button HP Yang
            $button = $this->getLayout()->createBlock('adminhtml/widget_button')->setData(
                array(
                    'label'   => Mage::helper('catalog')->__('Preview Event'),
                    'onclick' => "window.open('" . $this->getUrl('*/*/preview', array('_current' => true, 'store' => $this->_getPreviewStore())) . "')",
                    'class'   => 'preview'
                )
            );
            $this->setChild('event_preveiw_button', $button);

            if (!$this->_isAllowedAction('delete_category')) {
                $this->unsetChild('delete_button');
            }

            if (!$this->_isAllowedAction('reset')) {
                $this->unsetChild('reset_button');
            }

            if (!$this->_isAllowedAction('save')) {
                $this->unsetChild('save_button');
                $this->unsetChild('release_button');
                $this->unsetChild('undo_release_button');
            }

            if ($this->_isAllowedAction('import')) {
                //Jun import products
                $button = $this->getLayout()->createBlock('adminhtml/widget_button')->setData(
                    array(
                        'label'   => Mage::helper('catalog')->__('Import Products'),
                        'onclick' => "setLocation('" . $this->getUrl('import/adminhtml_import/newByCategory', array('category_id' => $categoryId)) . "')",
                        'class'   => 'add'
                    )
                );
                $this->setChild('import_product_button', $button);
            }

            if($this->_isAllowedAction('delete_product')) {
                //Harapartners Li Lu
                $button = $this->getLayout()->createBlock('adminhtml/widget_button')->setData(
                    array(
                        'label'   => Mage::helper('catalog')->__('Delete Products'),
                        'onclick' => "if(confirm('The selected products will be deleted PERMANENTLY, are you sure?')) {deleteProductsSubmit();}",
                        'class'   => 'delete'
                    )
                );
                $this->setChild('delete_product_button', $button);
            }
        }

        return $this;
    }

    protected function _isAllowedAction($action)
    {
        return Mage::getSingleton('admin/session')
            ->isAllowed('catalog/categories/actions/' . $action);
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

        if (strtotime($now) <= strtotime($startcount_lc)) {
            if ($this->hasStoreRootCategory()) {
                return $this->getChildHtml('delete_product_button');
            }
        }

        return '';
    }
}
