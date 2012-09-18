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
class Unirgy_SimpleLicense_Block_Adminhtml_License_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('licensesGrid');
        $this->setDefaultSort('license_key');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('module_filter');
    }

    protected function _prepareLayout()
    {
        $this->setChild('check_updates_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('usimpleup')->__('Refresh Licenses'),
                    'onclick'   => "location.href = '{$this->getUrl('usimplelicadmin/adminhtml_license/checkUpdates')}'",
                    'class'     => 'save',
                ))
        );
        $this->setChild('send_server_info_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('usimpleup')->__('Send Server Info'),
                    'onclick'   => "location.href = '{$this->getUrl('usimplelicadmin/adminhtml_license/serverInfo')}'",
                    'class'     => 'save',
                ))
        );
        return parent::_prepareLayout();
    }

    public function getMainButtonsHtml() {
        return parent::getMainButtonsHtml().$this->getChildHtml('send_server_info_button').$this->getChildHtml('check_updates_button');
    }

    protected function _prepareCollection()
    {
        //Mage::helper('usimpleup')->refreshMeta();
        $this->setCollection(Mage::getModel('usimplelic/license')->getCollection());
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {

        $this->addColumn('license_status', array(
            'header'    => Mage::helper('usimpleup')->__('Status'),
            'index'     => 'license_status',
            'renderer'  => 'usimplelic/adminhtml_license_status',
        ));

        $this->addColumn('license_key', array(
            'header'    => Mage::helper('usimpleup')->__('License Key'),
            'index'     => 'license_key',
            'column_css_class' => 'price', //nowrap
        ));

        $this->addColumn('products', array(
            'header'    => Mage::helper('usimpleup')->__('Products Covered'),
            'index'     => 'products',
            'renderer'  => 'usimpleup/adminhtml_module_nl2br',
        ));

        $this->addColumn('server_restriction', array(
            'header'    => Mage::helper('usimpleup')->__('Server Restrictions'),
            'index'     => 'server_restriction',
            'renderer'  => 'usimpleup/adminhtml_module_nl2br',
        ));

        $this->addColumn('license_expire', array(
            'header'    => Mage::helper('usimpleup')->__('License Expires'),
            'index'     => 'license_expire',
            'type'      => 'date',
            'width'     => '160px',
        ));

        $this->addColumn('upgrade_expire', array(
            'header'    => Mage::helper('usimpleup')->__('Upgrades Expire'),
            'index'     => 'upgrade_expire',
            'type'      => 'date',
            'width'     => '160px',
        ));

        $this->addColumn('module_actions', array(
            'header'    => Mage::helper('cms')->__('Action'),
            'width'     => 70,
            'sortable'  => false,
            'filter'    => false,
            'renderer'  => 'usimpleup/adminhtml_module_action',
        ));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('license_id');
        $this->getMassactionBlock()->setFormFieldName('licenses');
        /*
        $this->getMassactionBlock()->addItem('upgrade', array(
             'label'=> Mage::helper('usimpleup')->__('Upgrade / Reinstall'),
             'url'  => $this->getUrl('* / * /massUpgrade', array('_current'=>true)),
        ));
        */
        $this->getMassactionBlock()->addItem('remove', array(
             'label'=> Mage::helper('usimpleup')->__('Remove'),
             'url'  => $this->getUrl('usimplelicadmin/adminhtml_license/massRemove'),
             'confirm' => Mage::helper('usimpleup')->__('Removing selected licenses(s). Are you sure?')
        ));

        return $this;
    }

    public function getGridUrl()
    {
        return $this->getUrl('usimplelicadmin/adminhtml_license/grid', array('_current'=>true));
    }
}
