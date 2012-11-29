<?php
/**
 * Magento Webshopapps Module
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
 * @category   Webshopapps
 * @package    Webshopapps_Importshipments
 * @copyright  Copyright (c) 2010 Zowta Ltd (http://www.webshopapps.com)
 * @license   www.webshopapps.com/license/license.txt
 * @author    Genevieve Eddison <sales@webshopapps.com>
 *
 */
class Webshopapps_Wsacommon_Block_Sales_Order_Grid extends Mage_Adminhtml_Block_Sales_Order_Grid
{
	protected $_importTypes = array();
	
	protected function _prepareMassaction()
    {
        parent::_prepareMassaction();
        if (Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Importshipments', 'shipping/importshipments/active')) {
    		
	        $this->getMassactionBlock()->addItem('importshipments', array(
	             'label'=> Mage::helper('sales')->__('Create import template'),
	             'url'  => $this->getUrl('*/sales_order_export/csvexport'),
	        ));
    	}
    	
    	 if (Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Oneclickcomplete', 'sales/oneclickcomplete/active')) {
    		 $this->getMassactionBlock()->addItem('oneclickcomplete', array(
             'label'=> Mage::helper('sales')->__('Create Shipment and Complete Orders'),
             'url'  => $this->getUrl('*/sales_order_complete/pdfcomplete'),
       		));
    	 }
    	 
    	  if (Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Ordermanager', 'order_export/export_orders/active')) { 
	    	 $this->getMassactionBlock()->addItem('ordermanager', array(
		             'label'=> Mage::helper('sales')->__('Export orders'),
		             'url'  => $this->getUrl('*/sales_order_export/csvexport'),
		        ));
    	  }
       		
    }
 
	public function getButtonHtml($label, $onclick, $class='', $id=null)
	{
		return $this->getLayout()->createBlock('adminhtml/widget_button')
		->setData(array(
		'label'=> $label,
		'onclick'=> $onclick,
		'class'=> $class,
		'type'=> 'button',
		'id'=> $id,
		))
		->toHtml();
	}
	
	public function getImportTypes()
    {
        return empty($this->_importTypes) ? false : $this->_importTypes;
    }

     public function addImportType($label)
    {
        $this->_importTypes[] = new Varien_Object(
            array(
                'label' => $label
            )
        );
        return $this;
    }

     protected function _prepareColumns()
    {
    	$this->addImportType('orders');
    	return parent::_prepareColumns();

    }
    
}
?>