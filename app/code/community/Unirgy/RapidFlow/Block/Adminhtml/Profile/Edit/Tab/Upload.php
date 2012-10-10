<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    Unirgy_RapidFlow
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

class Unirgy_RapidFlow_Block_Adminhtml_Profile_Edit_Tab_Upload
    extends Mage_Adminhtml_Block_Catalog_Product_Helper_Form_Gallery_Content
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('urapidflow/upload.phtml');
    }

    protected function _prepareLayout()
    {
        $this->setChild('uploader',
            $this->getLayout()->createBlock('adminhtml/media_uploader')
        );

        $this->getUploader()->getConfig()
            ->setUrl(Mage::getModel('adminhtml/url')->addSessionParam()->getUrl('*/*/upload'))
            ->setFileField('file')
            ->setFilters(array(
                'csv' => array(
                    'label' => Mage::helper('adminhtml')->__('CSV and Tab Separated files (.csv, .txt)'),
                    'files' => array('*.csv', '*.txt')
                ),
                'all'    => array(
                    'label' => Mage::helper('adminhtml')->__('All Files'),
                    'files' => array('*.*')
                )
            ));

        return Mage_Adminhtml_Block_Widget::_prepareLayout();
    }
}