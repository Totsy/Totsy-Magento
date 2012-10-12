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

class Unirgy_RapidFlow_Block_Adminhtml_Profile extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_profile';
        $this->_blockGroup = 'urapidflow';
        $this->_headerText = Mage::helper('urapidflow')->__('RapidFlow Profile Manager');
        $this->_addButtonLabel = Mage::helper('urapidflow')->__('Add Profile');

        parent::__construct();

        if (Mage::getStoreConfig('urapidflow/advanced/disable_changes')) {
            $this->_removeButton('add');
        }
    }
}