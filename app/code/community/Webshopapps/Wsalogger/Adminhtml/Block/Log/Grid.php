<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * WebShopApps
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    WebShopApps
 * @package     WebShopApps WsaLogger
 * @copyright   Copyright (c) 2011 Zowta Ltd (http://www.webshopapps.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Webshopapps_Wsalogger_Adminhtml_Block_Log_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    protected function _construct()
    {
        $this->setSaveParametersInSession(true);
        $this->setId('logGrid');
        $this->setIdFieldName('notification_id');
        $this->setDefaultSort('date_added', 'desc');
        $this->setFilterVisibility(false);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('wsalogger/log')
            ->getCollection()
            ->addRemoveFilter();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    
    protected function _prepareColumns()
    {
    	$this->setTemplate('webshopapps_wsalogger/grid.phtml');   
    	
    	$this->addColumn('code', array(
            'header'    => Mage::helper('adminnotification')->__('Code'),
            'width'     => '30px',
            'index'     => 'code',
    	   // 'renderer'  => 'wsalogger_adminhtml/notification_grid_renderer_notice',    	
        ));    	
    	
        $this->addColumn('severity', array(
            'header'    => Mage::helper('adminnotification')->__('Severity'),
            'width'     => '60px',
            'index'     => 'severity',
            'renderer'  => 'wsalogger_adminhtml/notification_grid_renderer_severity',
        ));

        $this->addColumn('date_added', array(
            'header'    => Mage::helper('adminnotification')->__('Date Added'),
            'index'     => 'date_added',
            'width'     => '150px',
            'type'      => 'datetime'
        ));
        
        $this->addColumn('extension', array(
            'header'    => Mage::helper('adminnotification')->__('Extension'),
            'index'     => 'extension',
            'width'     => '80px',
        ));

        $this->addColumn('title', array(
            'header'    => Mage::helper('adminnotification')->__('Message'),
            'index'     => 'title',
        	'renderer'  => 'wsalogger_adminhtml/notification_grid_renderer_notice',
        ));

        $this->addColumn('actions', array(
            'header'    => Mage::helper('adminnotification')->__('Actions'),
            'width'     => '100px',
            'sortable'  => false,
            'renderer'  => 'wsalogger_adminhtml/log_grid_renderer_actions',
        ));

        return parent::_prepareColumns();
    }

    
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('notification_id');
        $this->getMassactionBlock()->setFormFieldName('notification');

        $this->getMassactionBlock()->addItem('mark_as_read', array(
             'label'    => Mage::helper('adminnotification')->__('Mark as Read'),
            'url'      => $this->getUrl('*/*/massMarkAsRead', array('_current'=>true)),
        ));

        $this->getMassactionBlock()->addItem('remove', array(
             'label'    => Mage::helper('adminnotification')->__('Remove from View'),
             'url'      => $this->getUrl('*/*/massRemove'),
             'confirm'  => Mage::helper('adminnotification')->__('Are you sure?')
        ));
        $this->getMassactionBlock()->addItem('delete_table', array(
             'label'    => Mage::helper('adminnotification')->__('Destroy all Stored Logs'),
             'url'      => $this->getUrl('*/*/massDestroy'),
             'confirm'  => Mage::helper('adminnotification')->__('Are you sure?')
        ));

//        $this->getColumn('massaction')->setWidth('30px');

        return $this;
    }

    public function getRowClass(Varien_Object $row) {
        return $row->getIsRead() ? 'read' : 'unread';
    }

    public function getRowClickCallback()
    {
        return false;
    }
    
    /**
     * Row click url
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/view', array('notification_id' => $row->getId()));
    }
    
}
