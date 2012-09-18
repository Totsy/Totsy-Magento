<?php
/**
 * Unirgy_StoreLocator extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Unirgy
 * @package    Unirgy_SimpleUp
 * @copyright  Copyright (c) 2011 Unirgy LLC
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @category   Unirgy
 * @package    Unirgy_SimpleUp
 * @author     Boris (Moshe) Gurvich <support@unirgy.com>
 */
class Unirgy_SimpleUp_Block_Adminhtml_Module extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /*
    public function __construct()
    {
        $this->_blockGroup = 'usimpleup';
        $this->_controller = 'adminhtml_module';
        $this->_headerText = Mage::helper('usimpleup')->__('Unirgy Simple Upgrades');

        $this->_addButton('check_updates', array(
            'label'     => $this->__('Check for version updates'),
            'onclick'   => "location.href = '{$this->getUrl('usimpleupadmin/adminhtml_module/checkUpdates')}'",
            'class'     => 'save',
        ), 0);

        parent::__construct();

        $this->_removeButton('add');
        $this->setTemplate('usimpleup/container.phtml');
    }
    */

    public function __construct()
    {
        parent::__construct();

        $this->_headerText = Mage::helper('usimpleup')->__('Unirgy Installer');

        $this->_objectId = 'id';
        $this->_blockGroup = 'usimpleup';
        $this->_controller = 'adminhtml_module';

        $this->_addButton('check_updates', array(
            'label'     => $this->__('Check for version updates'),
            'onclick'   => "location.href = '{$this->getUrl('usimpleupadmin/adminhtml_module/checkUpdates')}'",
            'class'     => 'save',
        ), 0);

        $this->_removeButton('save');
        $this->_removeButton('delete');
        $this->_removeButton('reset');
        $this->_removeButton('back');

    }

}